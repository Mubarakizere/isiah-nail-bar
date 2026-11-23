const API_BASE =
  typeof window === "undefined"
    ? process.env.LARAVEL_API_URL
      ? `${process.env.LARAVEL_API_URL}/api/v1`
      : "http://127.0.0.1:8000/api/v1"
    : "/api/v1";

interface RequestOptions extends RequestInit {
  params?: Record<string, string | number | boolean | undefined>;
  token?: string;
}

export class ApiError extends Error {
  status: number;
  data: any;

  constructor(message: string, status: number, data?: any) {
    super(message);
    this.name = "ApiError";
    this.status = status;
    this.data = data;
  }
}

export async function apiClient<T>(
  endpoint: string,
  options: RequestOptions = {}
): Promise<T> {
  const { params, token, headers, ...customConfig } = options;

  let url = `${API_BASE}${endpoint.startsWith("/") ? endpoint : `/${endpoint}`}`;

  if (params) {
    const searchParams = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        searchParams.append(key, String(value));
      }
    });
    const queryString = searchParams.toString();
    if (queryString) {
      url += (url.includes("?") ? "&" : "?") + queryString;
    }
  }

  const defaultHeaders: Record<string, string> = {
    Accept: "application/json",
    "Content-Type": "application/json",
  };

  if (token) {
    defaultHeaders.Authorization = `Bearer ${token}`;
  } else if (typeof window !== "undefined") {
    const localToken = localStorage.getItem("token");
    if (localToken) {
      defaultHeaders.Authorization = `Bearer ${localToken}`;
    }
  }

  const config: RequestInit = {
    headers: {
      ...defaultHeaders,
      ...headers,
    },
    ...customConfig,
  };

  const response = await fetch(url, config);

  if (!response.ok) {
    let errorData;
    try {
      errorData = await response.json();
    } catch {
      errorData = null;
    }
    const message =
      errorData?.message || `Request failed with status ${response.status}`;
    throw new ApiError(message, response.status, errorData);
  }

  if (response.status === 204) {
    return {} as T;
  }

  return response.json();
}

// Convenient domain methods
export const api = {
  // Public
  getServices: (params?: { category_id?: number; search?: string }) =>
    apiClient<{ data: any[] }>("/services", { params }),

  getService: (id: number) =>
    apiClient<{ data: any }>(`/services/${id}`),

  getCategories: () =>
    apiClient<{ data: any[] }>("/categories"),

  getProviders: () =>
    apiClient<{ data: any[] }>("/providers"),

  getReviews: (params?: { service_id?: number; page?: number }) =>
    apiClient<{ data: any[] }>("/reviews", { params }),

  getSettings: () =>
    apiClient<{ data: Record<string, any> }>("/settings"),

  getAvailableSlots: (date: string, providerId?: number, serviceId?: number) =>
    apiClient<{ data: string[] }>("/available-slots", {
      params: { date, provider_id: providerId, service_id: serviceId },
    }),

  // Bookings
  createBooking: (payload: any) =>
    apiClient<{ data: any; message: string }>("/bookings", {
      method: "POST",
      body: JSON.stringify(payload),
    }),

  getBookingStatus: (reference: string) =>
    apiClient<{ data: any }>(`/bookings/status/${reference}`),

  // Admin
  getAdminBookings: (params?: Record<string, any>) =>
    apiClient<any>("/admin/bookings", { params }),

  updateAdminBookingStatus: (id: number, status: string) =>
    apiClient<any>(`/admin/bookings/${id}/status`, {
      method: "POST",
      body: JSON.stringify({ status }),
    }),
};
