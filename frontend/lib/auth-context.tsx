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

  const login = async (email: string, password: string) => {
    try {
      const res = await fetch("/api/v1/auth/login", {
        method: "POST",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ email, password }),
      });

      const data = await res.json();

      if (res.ok && data.success && data.user) {
        setUser(data.user);
        try {
          localStorage.setItem("isaiah_auth_user", JSON.stringify(data.user));
          if (data.token) {
            localStorage.setItem("token", data.token);
          } else {
            localStorage.removeItem("token");
          }
        } catch {
          // Ignore
        }
        return { success: true, message: data.message, user: data.user };
      } else {
        return {
          success: false,
          message: data.message || "Invalid email or password credentials.",
        };
      }
    } catch {
      return { success: false, message: "Could not reach the sign-in service. Please try again." };
    }
  };

  const register = async (
    name: string,
    email: string,
    password: string,
    password_confirmation: string,
    phone?: string
  ) => {
    try {
      const res = await fetch("/api/v1/auth/register", {
        method: "POST",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          name,
          email,
          ...(phone ? { phone } : {}),
          password,
          password_confirmation,
        }),
      });

      const data = await res.json();

      if (res.ok && data.success && data.user) {
        setUser(data.user);
        try {
          localStorage.setItem("isaiah_auth_user", JSON.stringify(data.user));
          if (data.token) {
            localStorage.setItem("token", data.token);
          } else {
            localStorage.removeItem("token");
          }
        } catch {
          // Ignore
        }
        return { success: true, message: data.message };
      } else {
        return {
          success: false,
          message:
            data.message ||
            (data.errors ? Object.values(data.errors).flat().join(" ") : "Registration failed."),
        };
      }
    } catch {
      return { success: false, message: "Could not reach the registration service. Please try again." };
    }
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

