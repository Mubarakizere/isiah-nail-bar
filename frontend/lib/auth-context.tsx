"use client";

import React, { createContext, useContext, useState, useEffect } from "react";

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  phone?: string;
  role?: string;
  customer_id?: number | null;
}

interface AuthContextType {
  user: AuthUser | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<{ success: boolean; message?: string; user?: AuthUser }>;
  register: (
    name: string,
    email: string,
    password: string,
    password_confirmation: string,
    phone?: string
  ) => Promise<{ success: boolean; message?: string }>;
  logout: () => void;
  updateUser: (updated: Partial<AuthUser>) => void;
}

const AuthContext = createContext<AuthContextType>({
  user: null,
  loading: true,
  login: async () => ({ success: false }),
  register: async () => ({ success: false }),
  logout: () => {},
  updateUser: () => {},
});

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [loading, setLoading] = useState(true);

  // Revalidate the cached profile with the Laravel API before trusting it.
  useEffect(() => {
    let active = true;

    const initializeAuth = async () => {
      let cachedUser: AuthUser | null = null;
      let token: string | null = null;

      try {
        const stored = localStorage.getItem("isaiah_auth_user");
        if (stored) cachedUser = JSON.parse(stored) as AuthUser;
        token = localStorage.getItem("token");
        if (cachedUser) setUser(cachedUser);
      } catch {
        // Ignore malformed or unavailable local storage.
      }

      try {
        const response = await fetch("/api/v1/auth/me", {
          headers: {
            Accept: "application/json",
            ...(token ? { Authorization: "Bearer " + token } : {}),
          },
          credentials: "same-origin",
          cache: "no-store",
        });

        if (response.ok) {
          const data = await response.json();
          if (data.success && data.user && active) {
            setUser(data.user);
            localStorage.setItem("isaiah_auth_user", JSON.stringify(data.user));
          }
        } else if (response.status === 401 && active) {
          setUser(null);
          localStorage.removeItem("isaiah_auth_user");
          localStorage.removeItem("token");
        }
      } catch {
        // Keep a previously authenticated client profile during a temporary outage.
      } finally {
        if (active) setLoading(false);
      }
    };

    void initializeAuth();
    return () => {
      active = false;
    };
  }, []);

  const persistAuthSession = (authUser: AuthUser, token?: string | null) => {
    setUser(authUser);
    try {
      localStorage.setItem("isaiah_auth_user", JSON.stringify(authUser));
      if (token) {
        localStorage.setItem("token", token);
      } else {
        localStorage.removeItem("token");
      }
    } catch {
      // Ignore storage errors
    }
  };

  const postAuthJson = async (
    url: string,
    payload: Record<string, unknown>
  ): Promise<{ ok: boolean; status: number; data: Record<string, any> | null; networkError: boolean; retriedAfterTransient: boolean }> => {
    const attemptRequest = async () => {
      try {
        const res = await fetch(url, {
          method: "POST",
          headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
          },
          credentials: "same-origin",
          body: JSON.stringify(payload),
        });

        const raw = await res.text();
        let data: Record<string, any> | null = null;
        if (raw) {
          try {
            data = JSON.parse(raw);
          } catch {
            data = null;
          }
        }

        return { ok: res.ok, status: res.status, data, networkError: false };
      } catch {
        return { ok: false, status: 0, data: null, networkError: true };
      }
    };

    const first = await attemptRequest();
    const isTransientProxyFailure =
      first.networkError ||
      first.status >= 502 ||
      (first.status >= 500 && !first.data);

    if (!isTransientProxyFailure) {
      return { ...first, retriedAfterTransient: false };
    }

    await new Promise((resolve) => setTimeout(resolve, 350));
    const second = await attemptRequest();
    return { ...second, retriedAfterTransient: true };
  };

  const login = async (email: string, password: string) => {
    const { ok, status, data, networkError } = await postAuthJson("/api/v1/auth/login", {
      email,
      password,
    });

    if (networkError) {
      return { success: false, message: "Could not reach the sign-in service. Please try again." };
    }

    if (ok && data?.success && data.user) {
      persistAuthSession(data.user, data.token);
      return { success: true, message: data.message, user: data.user };
    }

    if (!data) {
      return {
        success: false,
        message: `Sign-in service temporarily unavailable (HTTP ${status || "error"}). Please try again.`,
      };
    }

    return {
      success: false,
      message: data.message || "Invalid email or password credentials.",
    };
  };

  const register = async (
    name: string,
    email: string,
    password: string,
    password_confirmation: string,
    phone?: string
  ) => {
    const { ok, status, data, networkError, retriedAfterTransient } = await postAuthJson(
      "/api/v1/auth/register",
      {
        name,
        email,
        ...(phone ? { phone } : {}),
        password,
        password_confirmation,
      }
    );

    if (networkError) {
      return { success: false, message: "Could not reach the registration service. Please try again." };
    }

    if (ok && data?.success && data.user) {
      persistAuthSession(data.user, data.token);
      return { success: true, message: data.message };
    }

    // If the first attempt succeeded server-side before a proxy socket reset and the retry
    // received "email already taken", sign the new user in seamlessly with their password.
    if (retriedAfterTransient && status === 422 && data?.errors?.email) {
      const autoLogin = await login(email, password);
      if (autoLogin.success) {
        return { success: true, message: autoLogin.message };
      }
    }

    if (!data) {
      return {
        success: false,
        message: `Registration service temporarily unavailable (HTTP ${status || "error"}). Please try again.`,
      };
    }

    return {
      success: false,
      message:
        data.message ||
        (data.errors ? Object.values(data.errors).flat().join(" ") : "Registration failed."),
    };
  };

  const logout = () => {
    void fetch("/api/v1/auth/logout", { method: "POST", credentials: "same-origin" }).catch(() => {});
    setUser(null);
    try {
      localStorage.removeItem("isaiah_auth_user");
      localStorage.removeItem("token");
    } catch {
      // Ignore
    }
  };

  const updateUser = (updated: Partial<AuthUser>) => {
    setUser((prev) => {
      if (!prev) return null;
      const next = { ...prev, ...updated };
      try {
        localStorage.setItem("isaiah_auth_user", JSON.stringify(next));
      } catch {
        // Ignore
      }
      return next;
    });
  };

  return (
    <AuthContext.Provider value={{ user, loading, login, register, logout, updateUser }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}

