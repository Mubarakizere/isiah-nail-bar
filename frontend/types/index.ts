export interface User {
  id: number;
  name: string;
  email: string;
  role?: 'customer' | 'provider' | 'admin';
  phone?: string;
  created_at: string;
}

export interface Category {
  id: number;
  name: string;
  description?: string;
  slug?: string;
  image?: string;
  services_count?: number;
  created_at?: string;
}

export interface Service {
  id: number;
  name: string;
  description: string;
  price: number;
  duration_minutes: number;
  image?: string;
  provider_id?: number;
  category_id?: number;
  approved?: boolean;
  status?: 'pending' | 'approved' | 'rejected';
  category?: Category;
  provider?: Provider;
  tags?: { id: number; name: string }[];
  reviews_avg_rating?: number;
  reviews_count?: number;
}

export interface ProviderWorkingHour {
  id: number;
  provider_id: number;
  day_of_week: number; // 0=Sunday, 1=Monday...
  start_time: string; // HH:mm:ss
  end_time: string;
  is_available: boolean;
}

export interface Provider {
  id: number;
  name: string;
  email?: string;
  phone?: string;
  bio?: string;
  photo?: string;
  active: boolean;
  user_id?: number;
  working_hours?: ProviderWorkingHour[];
  services?: Service[];
  rating?: number;
  reviews_count?: number;
}

export interface PickupLocation {
  id: number;
  name: string;
  fee: number;
  is_active: boolean;
  bookings_count?: number;
  created_at?: string | null;
}

export interface Customer {
  id: number;
  name: string;
  email: string | null;
  phone?: string;
  address?: string;
}

export interface Booking {
  id: number;
  customer_id: number;
  provider_id?: number;
  date: string; // YYYY-MM-DD
  time: string; // HH:mm
  status: 'pending' | 'confirmed' | 'accepted' | 'in_progress' | 'completed' | 'declined' | 'cancelled';
  source?: 'website' | 'admin_manual' | 'walk_in' | 'legacy';
  payment_option: 'deposit' | 'full' | 'momo';
  deposit_amount?: number;
  reference?: string;
  is_home_service: boolean;
  address?: string;
  pickup_location_id?: number;
  pickup_fee?: number;
  pickup_address?: string;
  notes?: string;
  is_fully_paid?: boolean;
  created_at: string;
  services?: Service[];
  service?: Service;
  customer?: Customer;
  provider?: Provider;
  pickup_location?: PickupLocation;
  review?: Review;
}

export interface Review {
  id: number;
  customer_id?: number;
  booking_id?: number;
  service_id?: number;
  provider_id?: number;
  customer_name: string;
  rating: number; // 1 to 5
  comment?: string;
  created_at: string;
}

export interface SalonSetting {
  id: number;
  key: string;
  value: string;
}

export interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
  errors?: Record<string, string[]>;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface TimeSlot {
  time: string; // "09:00"
  available: boolean;
  provider_id?: number;
}

export interface ServiceItem {
  id: number;
  name: string;
  price: number;
  duration_minutes: number;
}

export interface PaymentRecord {
  id: number;
  amount: number;
  method: string;
  status: string;
  reference: string;
  paid_at: string | null;
}

export interface AdminBookingItem {
  id: number;
  reference: string;
  customer_name: string;
  customer_email: string;
  customer_phone: string;
  date: string;
  date_formatted: string;
  day_name: string;
  is_today: boolean;
  time: string;
  time_formatted: string;
  created_at: string | null;
  created_at_formatted: string;
  status: 'pending' | 'accepted' | 'in_progress' | 'completed' | 'declined' | 'cancelled';
  source: 'website' | 'admin_manual' | 'walk_in' | 'legacy';
  is_home_service: boolean;
  address: string | null;
  pickup_location_id?: number | null;
  pickup_location_name?: string | null;
  pickup_fee?: number;
  pickup_address?: string | null;
  payment_option?: string | null;
  deposit_amount?: number;
  notes: string | null;
  provider_id?: number | null;
  provider_name: string;
  provider_phone: string;
  services: ServiceItem[];
  total_duration: number;
  base_service_price: number;
  total_price: number;
  total_paid: number;
  balance: number;
  payment_status: 'paid' | 'deposit' | 'pending' | 'failed' | 'unpaid';
  payment_method: string | null;
  payments: PaymentRecord[];
}

export interface AdminBookingsResponse {
  success: boolean;
  data: AdminBookingItem[];
  pagination: {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
  };
  status_counts: {
    all: number;
    pending: number;
    accepted: number;
    in_progress: number;
    completed: number;
    declined: number;
    cancelled: number;
  };
  providers: {
    id: number;
    name: string;
  }[];
}

export interface DailyReportPoint {
  date: string;
  full_date: string;
  day_name: string;
  revenue: number;
  count: number;
}

export interface ServiceReportStat {
  id: number;
  name: string;
  count: number;
  revenue: number;
}

export interface StylistReportStat {
  id: number;
  name: string;
  count: number;
  revenue: number;
}

export interface CategoryReportStat {
  id: number;
  name: string;
  count: number;
  revenue: number;
  percentage: number;
}

export interface PeriodComparison {
  prev_from: string;
  prev_to: string;
  prev_revenue: number;
  prev_count: number;
  prev_avg: number;
  revenue_growth: number | null;
  bookings_growth: number | null;
  avg_growth: number | null;
}

export interface LocationSplitChannel {
  count: number;
  revenue: number;
  percentage: number;
}

export interface AdminReportsData {
  success: boolean;
  from: string;
  to: string;
  from_formatted: string;
  to_formatted: string;
  kpis: {
    total_revenue: number;
    total_bookings: number;
    avg_booking_value: number;
    momo_total: number;
    card_total: number;
    cash_total: number;
    tax_amount: number;
    net_revenue: number;
    total_valuation: number;
    outstanding_balance: number;
    monthly_target: number;
    target_progress: number;
  };
  comparison: PeriodComparison;
  location_split: {
    in_salon: LocationSplitChannel;
    home_service: LocationSplitChannel;
    pickup: LocationSplitChannel;
  };
  category_stats: CategoryReportStat[];
  daily_chart: DailyReportPoint[];
  top_services: ServiceReportStat[];
  stylist_performance: StylistReportStat[];
  bookings: AdminBookingItem[];
  providers: { id: number; name: string }[];
}

export interface ProviderPerformanceItem {
  id: number;
  name: string;
  email: string | null;
  phone: string | null;
  photo: string | null;
  active: boolean;
  total_bookings: number;
  completed_bookings: number;
  accepted_bookings: number;
  cancelled_bookings: number;
  completion_rate: number;
  total_revenue: number;
  avg_ticket: number;
  daily_trend: {
    date: string;
    day: string;
    total: number;
  }[];
  top_services: {
    name: string;
    count: number;
    revenue: number;
  }[];
  recent_appointments?: AdminBookingItem[];
}

export interface AdminPerformanceData {
  success: boolean;
  range: string;
  summary: {
    total_providers: number;
    active_providers: number;
    team_revenue: number;
    team_total_bookings: number;
    team_completed_bookings: number;
    team_avg_completion_rate: number;
    top_performer: {
      id: number;
      name: string;
      revenue: number;
      completed: number;
    } | null;
  };
  providers: ProviderPerformanceItem[];
  selected_provider: ProviderPerformanceItem | null;
}

export interface AdminCategorySampleService {
  id: number;
  name: string;
  price: number;
  active: boolean | string;
}

export interface AdminCategoryItem {
  id: number;
  name: string;
  slug: string;
  services_count: number;
  active_services_count: number;
  sample_services: AdminCategorySampleService[];
  created_at: string | null;
  created_at_human: string | null;
  updated_at: string | null;
}

export interface AdminCategoriesSummary {
  total_categories: number;
  total_services_linked: number;
  uncategorized_services_count: number;
  top_category: {
    id: number;
    name: string;
    services_count: number;
  } | null;
}

export interface AdminCategoriesResponse {
  categories: AdminCategoryItem[];
  summary: AdminCategoriesSummary;
}
