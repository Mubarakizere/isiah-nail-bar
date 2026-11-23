"use client";

import { useState, useEffect, useMemo } from "react";
import Link from "next/link";
import {
  CurrencyDollar,
  CalendarCheck,
  UsersThree,
  Wallet,
  MagnifyingGlass,
  Check,
  CheckCircle,
  Clock,
  XCircle,
  X,
  Scissors,
  ChartBar,
  CreditCard,
  DeviceMobile,
  ArrowsClockwise,
  Eye,
  House,
  MapPin,
  Phone,
  EnvelopeSimple,
  Calendar,
  ArrowSquareOut,
  Sparkle,
} from "@phosphor-icons/react";
import AdminHeader from "./AdminHeader";

interface ServiceItem {
  id: number;
  name: string;
  price: number;
  duration_minutes: number;
}

interface PaymentRecord {
  id: number;
  amount: number;
  method: string;
  status: string;
  reference: string;
  paid_at: string | null;
}

interface BookingRecord {
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
  status: string;
  is_home_service: boolean;
  address: string | null;
  notes: string | null;
  provider_name: string;
  provider_phone: string;
  services: ServiceItem[];
  total_duration: number;
  total_price: number;
  total_paid: number;
  balance: number;
  payment_status: "paid" | "deposit" | "unpaid";
  payments: PaymentRecord[];
}

interface ChartPoint {
  date: string;
  day_name: string;
  full_date: string;
  total: number;
}

interface AdminOverviewData {
  kpis: {
    total_revenue: number;
    tax_amount: number;
    revenue_after_tax: number;
    momo_revenue: number;
    card_revenue: number;
    total_bookings: number;
    total_customers: number;
    total_providers: number;
    completion_rate: number;
    avg_booking_value: number;
    top_customer: {
      name: string;
      bookings: number;
    };
  };
  today_ops: {
    today_date: string;
    today_bookings_count: number;
    today_revenue: number;
    pending_confirmations: number;
  };
  status_counts: {
    pending: number;
    accepted: number;
    completed: number;
    cancelled: number;
  };
  chart_data: ChartPoint[];
  recent_bookings: BookingRecord[];
}

export default function AdminOverview() {
  const [data, setData] = useState<AdminOverviewData | null>(null);
  const [loading, setLoading] = useState(true);
  const [dateRange, setDateRange] = useState<"7days" | "30days" | "this_month">("7days");
  const [statusFilter, setStatusFilter] = useState<"all" | "pending" | "accepted" | "completed" | "cancelled">("all");
  const [searchQuery, setSearchQuery] = useState("");
  const [updatingId, setUpdatingId] = useState<number | null>(null);
  const [actionMessage, setActionMessage] = useState<string | null>(null);
  const [selectedBooking, setSelectedBooking] = useState<BookingRecord | null>(null);

  const fetchOverview = async (range = dateRange) => {
    try {
      setLoading(true);
      const res = await fetch(`/api/v1/admin/overview?range=${range}`, { cache: "no-store" });
      const json = await res.json();
      if (res.ok && json.success) {
        setData(json);
      }
    } catch (err) {
      console.error("Failed to load admin overview data", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchOverview(dateRange);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [dateRange]);

  const handleUpdateStatus = async (bookingId: number, newStatus: string) => {
    setUpdatingId(bookingId);
    setActionMessage(null);
    try {
      const res = await fetch(`/api/v1/admin/bookings/${bookingId}/status`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ status: newStatus }),
      });
      const json = await res.json();
      if (res.ok && json.success) {
        setActionMessage(json.message);

        // Update local list instantly
        setData((prev) => {
          if (!prev) return null;
          const updatedRecent = prev.recent_bookings.map((b) =>
            b.id === bookingId ? { ...b, status: newStatus } : b
          );

          // Update counts
          const newCounts = { ...prev.status_counts };
          return {
            ...prev,
            recent_bookings: updatedRecent,
            status_counts: newCounts,
          };
        });

        if (selectedBooking && selectedBooking.id === bookingId) {
          setSelectedBooking((prev) => (prev ? { ...prev, status: newStatus } : null));
        }

        // Auto dismiss banner after 4s
        setTimeout(() => setActionMessage(null), 4000);
      }
    } catch (err) {
      console.error("Status update error", err);
    } finally {
      setUpdatingId(null);
    }
  };

  // Filtered bookings
  const filteredBookings = useMemo(() => {
    if (!data?.recent_bookings) return [];
    return data.recent_bookings.filter((b) => {
      // Status filter
      if (statusFilter !== "all" && b.status !== statusFilter) {
        return false;
      }
      // Search filter
      if (searchQuery.trim() !== "") {
        const q = searchQuery.toLowerCase();
        const matchesClient = b.customer_name.toLowerCase().includes(q);
        const matchesRef = b.reference.toLowerCase().includes(q);
        const matchesPhone = b.customer_phone.toLowerCase().includes(q);
        const matchesProvider = b.provider_name.toLowerCase().includes(q);
        return matchesClient || matchesRef || matchesPhone || matchesProvider;
      }
      return true;
    });
  }, [data?.recent_bookings, statusFilter, searchQuery]);

  // Chart calculation
  const chartPoints = data?.chart_data || [];
  const maxRevenuePoint = Math.max(...chartPoints.map((p) => p.total), 1);
  const totalChartRevenue = chartPoints.reduce((acc, p) => acc + p.total, 0);

  return (
    <div className="flex-1 flex flex-col bg-gray-50/50 min-h-screen">
      {/* Top Header */}
      <AdminHeader
        title="Dashboard Overview"
        subtitle="Real-time salon operations, reservations & financial summary"
      />

      <div className="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 max-w-7xl mx-auto w-full">

        {/* ========================================================= */}
        {/* Operational Today Bar                                     */}
        {/* ========================================================= */}
        <div className="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-lg bg-gray-900 text-white flex items-center justify-center shrink-0">
              <Calendar size={20} weight="bold" />
            </div>
            <div>
              <div className="flex items-center gap-2">
                <span className="text-xs font-semibold uppercase tracking-wider text-gray-500">
                  Salon Schedule
                </span>
                <span className="w-1.5 h-1.5 rounded-full bg-emerald-500" />
                <span className="text-xs text-emerald-600 font-medium">Live</span>
              </div>
              <h2 className="text-base sm:text-lg font-bold text-gray-900 leading-tight">
                {data?.today_ops?.today_date || "Today's Schedule"}
              </h2>
            </div>
          </div>

          <div className="flex flex-wrap items-center gap-2 sm:gap-3">
            {/* Scheduled Today Badge */}
            <div className="px-3 py-1.5 rounded-lg bg-gray-100 border border-gray-200 text-xs text-gray-700 flex items-center gap-1.5">
              <Clock size={14} className="text-gray-500" />
              <span>
                <strong>{data?.today_ops?.today_bookings_count ?? 0}</strong> Booked Today
              </span>
            </div>

            {/* Pending Confirmations Badge */}
            {(data?.today_ops?.pending_confirmations ?? 0) > 0 ? (
              <button
                type="button"
                onClick={() => setStatusFilter("pending")}
                className="px-3 py-1.5 rounded-lg bg-amber-50 border border-amber-200 text-xs font-medium text-amber-800 hover:bg-amber-100 transition-colors flex items-center gap-1.5 cursor-pointer"
              >
                <span className="w-2 h-2 rounded-full bg-amber-500 animate-pulse" />
                <span>
                  <strong>{data?.today_ops?.pending_confirmations}</strong> Awaiting Confirmation
                </span>
              </button>
            ) : (
              <div className="px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-700 flex items-center gap-1.5">
                <CheckCircle size={14} className="text-emerald-600" />
                <span>All confirmed</span>
              </div>
            )}

            {/* Today Revenue */}
            <div className="px-3 py-1.5 rounded-lg bg-gray-900 text-white text-xs flex items-center gap-1.5 font-medium">
              <span>Today:</span>
              <span className="font-semibold text-rose-300">
                RWF {(data?.today_ops?.today_revenue ?? 0).toLocaleString()}
              </span>
            </div>
          </div>
        </div>

        {/* Status Notification Toast */}
        {actionMessage && (
          <div className="p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between animate-fadeIn">
            <div className="flex items-center gap-2 font-medium">
              <CheckCircle size={16} weight="fill" className="text-emerald-600 shrink-0" />
              <span>{actionMessage}</span>
            </div>
            <button
              onClick={() => setActionMessage(null)}
              className="text-emerald-700 hover:text-emerald-900 text-sm font-bold p-1 cursor-pointer"
            >
              &times;
            </button>
          </div>
        )}

        {/* ========================================================= */}
        {/* High-Level Metric Cards (Responsive Grid & Flexible Rows) */}
        {/* ========================================================= */}
        <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">

          {/* 1. Total Revenue */}
          <div className="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 shadow-2xs hover:border-gray-300 transition-all flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between mb-2 sm:mb-3">
                <span className="text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  Total Revenue
                </span>
                <div className="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                  <CurrencyDollar size={18} weight="bold" />
                </div>
              </div>
              <h3 className="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                RWF {(data?.kpis?.total_revenue ?? 0).toLocaleString()}
              </h3>
            </div>
            <div className="mt-3 pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-x-2 gap-y-1 text-xs text-gray-500">
              <span className="flex items-center gap-1 shrink-0">
                <DeviceMobile size={13} className="text-gray-400" />
                MOMO: <strong className="text-gray-800">{(data?.kpis?.momo_revenue ?? 0).toLocaleString()}</strong>
              </span>
              <span className="flex items-center gap-1 shrink-0">
                <CreditCard size={13} className="text-gray-400" />
                Card: <strong className="text-gray-800">{(data?.kpis?.card_revenue ?? 0).toLocaleString()}</strong>
              </span>
            </div>
          </div>

          {/* 2. Net Income After Tax */}
          <div className="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 shadow-2xs hover:border-gray-300 transition-all flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between mb-2 sm:mb-3">
                <span className="text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  Net Income (After Tax)
                </span>
                <div className="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                  <Wallet size={18} weight="bold" />
                </div>
              </div>
              <h3 className="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                RWF {(data?.kpis?.revenue_after_tax ?? 0).toLocaleString()}
              </h3>
            </div>
            <div className="mt-3 pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-x-2 gap-y-1 text-xs text-gray-500">
              <span className="shrink-0">Tax (5%): <strong className="text-gray-700">RWF {(data?.kpis?.tax_amount ?? 0).toLocaleString()}</strong></span>
              <Link href="/admin/reports" className="text-rose-600 hover:text-rose-700 font-medium shrink-0">
                Reports &rarr;
              </Link>
            </div>
          </div>

          {/* 3. Total Bookings */}
          <div className="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 shadow-2xs hover:border-gray-300 transition-all flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between mb-2 sm:mb-3">
                <span className="text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  Total Bookings
                </span>
                <div className="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                  <CalendarCheck size={18} weight="bold" />
                </div>
              </div>
              <h3 className="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                {data?.kpis?.total_bookings ?? 0}
              </h3>
            </div>
            <div className="mt-3 pt-3 border-t border-gray-100 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
              <span className="shrink-0">
                <strong className="text-emerald-700">{data?.status_counts?.completed ?? 0}</strong> Done
              </span>
              <span className="shrink-0">
                <strong className="text-blue-700">{data?.status_counts?.accepted ?? 0}</strong> Confirmed
              </span>
              <span className="shrink-0">
                <strong className="text-amber-700">{data?.status_counts?.pending ?? 0}</strong> Pending
              </span>
            </div>
          </div>

          {/* 4. Active Clients & Top Customer */}
          <div className="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 shadow-2xs hover:border-gray-300 transition-all flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between mb-2 sm:mb-3">
                <span className="text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  Clientele & Stylists
                </span>
                <div className="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                  <UsersThree size={18} weight="bold" />
                </div>
              </div>
              <div className="flex items-baseline gap-2">
                <h3 className="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                  {data?.kpis?.total_customers ?? 0}
                </h3>
                <span className="text-xs text-gray-500">
                  ({data?.kpis?.total_providers ?? 1} active stylist)
                </span>
              </div>
            </div>
            <div className="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between gap-2 text-xs text-gray-500 min-w-0">
              <span className="truncate">
                Top: <strong className="text-gray-900 font-medium">{data?.kpis?.top_customer?.name || "None yet"}</strong>
              </span>
              <span className="text-gray-400 text-[11px] shrink-0 font-medium">
                {data?.kpis?.completion_rate}% rate
              </span>
            </div>
          </div>
        </div>

        {/* ========================================================= */}
        {/* Revenue Performance Chart (Fully Responsive & Scrollable) */}
        {/* ========================================================= */}
        <div className="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 shadow-2xs space-y-4 sm:space-y-5 min-w-0">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 sm:pb-4 border-b border-gray-100">
            <div className="min-w-0">
              <div className="flex flex-wrap items-baseline gap-2">
                <h3 className="text-base font-bold text-gray-900">
                  Revenue Performance
                </h3>
                <span className="text-xs text-gray-500 font-normal">
                  — Total: <strong className="text-gray-800">RWF {totalChartRevenue.toLocaleString()}</strong>
                </span>
              </div>
              <p className="text-xs text-gray-500 mt-0.5">
                Daily earnings tracked directly from verified customer payments
              </p>
            </div>

            {/* Range Selector */}
            <div className="flex items-center gap-2 shrink-0 flex-wrap">
              <div className="inline-flex rounded-lg border border-gray-200 p-0.5 bg-gray-50 text-xs">
                <button
                  type="button"
                  onClick={() => setDateRange("7days")}
                  className={`px-2.5 py-1 text-xs font-medium rounded-md transition-colors cursor-pointer ${
                    dateRange === "7days"
                      ? "bg-white text-gray-900 shadow-2xs font-semibold"
                      : "text-gray-600 hover:text-gray-900"
                  }`}
                >
                  7 Days
                </button>
                <button
                  type="button"
                  onClick={() => setDateRange("30days")}
                  className={`px-2.5 py-1 text-xs font-medium rounded-md transition-colors cursor-pointer ${
                    dateRange === "30days"
                      ? "bg-white text-gray-900 shadow-2xs font-semibold"
                      : "text-gray-600 hover:text-gray-900"
                  }`}
                >
                  30 Days
                </button>
                <button
                  type="button"
                  onClick={() => setDateRange("this_month")}
                  className={`px-2.5 py-1 text-xs font-medium rounded-md transition-colors cursor-pointer ${
                    dateRange === "this_month"
                      ? "bg-white text-gray-900 shadow-2xs font-semibold"
                      : "text-gray-600 hover:text-gray-900"
                  }`}
                >
                  This Month
                </button>
              </div>

              <button
                type="button"
                onClick={() => fetchOverview(dateRange)}
                className="p-1.5 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors border border-gray-200 cursor-pointer"
                title="Refresh metrics"
              >
                <ArrowsClockwise size={16} />
              </button>
            </div>
          </div>

          {/* Bar Chart Visualization with Horizontal Scroll Support */}
          {chartPoints.length === 0 ? (
            <div className="h-56 flex flex-col items-center justify-center bg-gray-50/50 rounded-lg border border-dashed border-gray-200 text-gray-400 text-xs">
              <ChartBar size={32} className="mb-2 text-gray-300" />
              <span>No revenue activity in this period.</span>
            </div>
          ) : (
            <div className="w-full min-w-0">
              {/* Horizontal scroll wrapper for mobile & 30-day views */}
              <div className="w-full overflow-x-auto pb-2 -mx-1 px-1 custom-scrollbar">
                <div
                  className={`h-56 sm:h-64 flex flex-col justify-end pt-6 relative ${
                    dateRange === "7days"
                      ? "min-w-[300px] sm:min-w-full"
                      : "min-w-[650px]"
                  }`}
                >
                  {/* Subtle Y-Axis Grid Lines */}
                  <div className="absolute inset-x-0 top-6 bottom-10 flex flex-col justify-between pointer-events-none opacity-40">
                    <div className="border-b border-dashed border-gray-200 w-full" />
                    <div className="border-b border-dashed border-gray-200 w-full" />
                    <div className="border-b border-gray-200 w-full" />
                  </div>

                  <div className="flex-1 flex items-end gap-1.5 sm:gap-3 justify-between px-2 relative z-10">
                    {chartPoints.map((point) => {
                      const barHeightPercent =
                        point.total > 0
                          ? Math.max(12, Math.round((point.total / maxRevenuePoint) * 100))
                          : 4;

                      return (
                        <div
                          key={point.full_date}
                          className="flex-1 flex flex-col items-center gap-2 group h-full justify-end min-w-[24px] sm:min-w-[32px] relative"
                        >
                          {/* Tooltip on hover */}
                          <div className="opacity-0 group-hover:opacity-100 transition-all text-[11px] font-semibold tabular-nums bg-gray-900 text-white px-2 py-0.5 rounded shadow-md pointer-events-none whitespace-nowrap absolute bottom-full mb-1 z-30">
                            RWF {point.total.toLocaleString()}
                          </div>

                          {/* Bar */}
                          <div className="w-full max-w-[48px] bg-gray-100 rounded-t-md overflow-hidden h-full flex items-end">
                            <div
                              style={{ height: `${barHeightPercent}%` }}
                              className={`w-full rounded-t-md transition-all duration-300 ${
                                point.total > 0
                                  ? "bg-rose-600 group-hover:bg-rose-700"
                                  : "bg-gray-200"
                              }`}
                            />
                          </div>

                          {/* Date label */}
                          <div className="text-center w-full">
                            {dateRange === "7days" && (
                              <span className="block text-[11px] font-medium text-gray-700 group-hover:text-gray-900 leading-tight">
                                {point.day_name}
                              </span>
                            )}
                            <span className="block text-[10px] text-gray-500 font-medium leading-tight">
                              {point.date}
                            </span>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </div>
              </div>

              {/* Mobile swipe hint when 30 days is active */}
              {dateRange !== "7days" && (
                <div className="sm:hidden text-center pt-1 text-[11px] text-gray-400 flex items-center justify-center gap-1">
                  <span>&larr; Swipe to view all 30 days &rarr;</span>
                </div>
              )}
            </div>
          )}
        </div>

        {/* ========================================================= */}
        {/* Quick Navigation Cards                                    */}
        {/* ========================================================= */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <Link
            href="/admin/bookings"
            className="p-4 rounded-xl bg-white border border-gray-200 hover:border-gray-400 hover:shadow-xs transition-all flex items-center justify-between group"
          >
            <div className="flex items-center gap-3.5">
              <div className="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <CalendarCheck size={20} weight="bold" />
              </div>
              <div>
                <p className="text-sm font-semibold text-gray-900">Manage Bookings</p>
                <p className="text-xs text-gray-500">Filter, edit and search all reservations</p>
              </div>
            </div>
            <ArrowSquareOut size={16} className="text-gray-400 group-hover:text-gray-700" />
          </Link>

          <Link
            href="/admin/reports"
            className="p-4 rounded-xl bg-white border border-gray-200 hover:border-gray-400 hover:shadow-xs transition-all flex items-center justify-between group"
          >
            <div className="flex items-center gap-3.5">
              <div className="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <CurrencyDollar size={20} weight="bold" />
              </div>
              <div>
                <p className="text-sm font-semibold text-gray-900">Revenue Reports</p>
                <p className="text-xs text-gray-500">Financial audits & tax breakdown</p>
              </div>
            </div>
            <ArrowSquareOut size={16} className="text-gray-400 group-hover:text-gray-700" />
          </Link>

          <Link
            href="/admin/services"
            className="p-4 rounded-xl bg-white border border-gray-200 hover:border-gray-400 hover:shadow-xs transition-all flex items-center justify-between group"
          >
            <div className="flex items-center gap-3.5">
              <div className="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <Scissors size={20} weight="bold" />
              </div>
              <div>
                <p className="text-sm font-semibold text-gray-900">Salon Services</p>
                <p className="text-xs text-gray-500">Update treatments, duration & pricing</p>
              </div>
            </div>
            <ArrowSquareOut size={16} className="text-gray-400 group-hover:text-gray-700" />
          </Link>
        </div>

        {/* ========================================================= */}
        {/* Live Database Reservations Table                          */}
        {/* ========================================================= */}
        <div className="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">

          {/* Table Header Controls */}
          <div className="p-4 sm:p-5 border-b border-gray-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
              <div className="flex items-center gap-2">
                <h3 className="text-base font-bold text-gray-900">Live Reservations</h3>
                <span className="text-xs bg-gray-100 text-gray-600 font-semibold px-2 py-0.5 rounded-full">
                  {filteredBookings.length} {filteredBookings.length === 1 ? "record" : "records"}
                </span>
              </div>
              <p className="text-xs text-gray-500 mt-0.5">
                Real-time booking records synchronized directly from the database
              </p>
            </div>

            {/* Filter controls */}
            <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
              {/* Search Bar */}
              <div className="relative min-w-[200px] sm:min-w-[240px]">
                <MagnifyingGlass size={15} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <input
                  type="text"
                  placeholder="Search client, ref, phone..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full pl-9 pr-3 py-1.5 text-xs rounded-lg border border-gray-200 focus:outline-none focus:border-gray-900 bg-gray-50/50"
                />
                {searchQuery && (
                  <button
                    onClick={() => setSearchQuery("")}
                    className="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs"
                  >
                    &times;
                  </button>
                )}
              </div>

              {/* Status Filter Buttons */}
              <div className="overflow-x-auto max-w-full pb-1 sm:pb-0">
                <div className="inline-flex rounded-lg border border-gray-200 p-0.5 bg-gray-50 text-xs shrink-0">
                  <button
                    type="button"
                    onClick={() => setStatusFilter("all")}
                    className={`px-2.5 py-1 rounded-md transition-colors cursor-pointer font-medium ${
                      statusFilter === "all" ? "bg-white text-gray-900 shadow-2xs font-semibold" : "text-gray-600 hover:text-gray-900"
                    }`}
                  >
                    All ({data?.recent_bookings.length ?? 0})
                  </button>
                  <button
                    type="button"
                    onClick={() => setStatusFilter("pending")}
                    className={`px-2.5 py-1 rounded-md transition-colors cursor-pointer font-medium ${
                      statusFilter === "pending" ? "bg-white text-amber-800 shadow-2xs font-semibold" : "text-gray-600 hover:text-gray-900"
                    }`}
                  >
                    Pending ({data?.status_counts?.pending ?? 0})
                  </button>
                  <button
                    type="button"
                    onClick={() => setStatusFilter("accepted")}
                    className={`px-2.5 py-1 rounded-md transition-colors cursor-pointer font-medium ${
                      statusFilter === "accepted" ? "bg-white text-blue-800 shadow-2xs font-semibold" : "text-gray-600 hover:text-gray-900"
                    }`}
                  >
                    Confirmed ({data?.status_counts?.accepted ?? 0})
                  </button>
                  <button
                    type="button"
                    onClick={() => setStatusFilter("completed")}
                    className={`px-2.5 py-1 rounded-md transition-colors cursor-pointer font-medium ${
                      statusFilter === "completed" ? "bg-white text-emerald-800 shadow-2xs font-semibold" : "text-gray-600 hover:text-gray-900"
                    }`}
                  >
                    Completed ({data?.status_counts?.completed ?? 0})
                  </button>
                </div>
              </div>
            </div>
          </div>

          {/* Table */}
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px] border-b border-gray-200">
                <tr>
                  <th className="py-3 px-5 font-semibold">Reference & Client</th>
                  <th className="py-3 px-4 font-semibold">Scheduled Date</th>
                  <th className="py-3 px-4 font-semibold">Services</th>
                  <th className="py-3 px-4 font-semibold">Stylist</th>
                  <th className="py-3 px-4 font-semibold">Financials</th>
                  <th className="py-3 px-4 font-semibold">Status</th>
                  <th className="py-3 px-5 font-semibold text-right">Quick Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {loading ? (
                  <tr>
                    <td colSpan={7} className="py-12 text-center text-gray-500">
                      <div className="w-6 h-6 border-2 border-gray-900 border-t-transparent rounded-full animate-spin mx-auto mb-2" />
                      Loading bookings from database...
                    </td>
                  </tr>
                ) : filteredBookings.length === 0 ? (
                  <tr>
                    <td colSpan={7} className="py-12 text-center text-gray-500">
                      No reservations matching current filter.
                    </td>
                  </tr>
                ) : (
                  filteredBookings.map((b) => (
                    <tr key={b.id} className="hover:bg-gray-50/70 transition-colors">
                      {/* Ref & Client */}
                      <td className="py-3.5 px-5">
                        <span className="text-[11px] font-semibold tabular-nums text-gray-500 block">
                          #{b.reference}
                        </span>
                        <p className="font-semibold text-sm text-gray-900">
                          {b.customer_name}
                        </p>
                        <p className="text-[11px] text-gray-500">{b.customer_phone}</p>
                      </td>

                      {/* Scheduled Date */}
                      <td className="py-3.5 px-4 whitespace-nowrap">
                        <div className="flex items-center gap-1.5">
                          <span className="font-medium text-gray-900">{b.date_formatted}</span>
                          {b.is_today && (
                            <span className="text-[10px] bg-rose-100 text-rose-700 font-bold px-1.5 py-0.5 rounded">
                              TODAY
                            </span>
                          )}
                        </div>
                        <p className="text-[11px] text-gray-500">{b.time_formatted}</p>
                        {b.is_home_service && (
                          <span className="inline-flex items-center gap-1 text-[10px] text-amber-700 font-medium mt-0.5">
                            <House size={11} /> Home Service
                          </span>
                        )}
                      </td>

                      {/* Services */}
                      <td className="py-3.5 px-4 max-w-[200px]">
                        <p className="font-medium text-gray-900 truncate">
                          {b.services.map((s) => s.name).join(", ")}
                        </p>
                        <span className="text-[11px] text-gray-500">
                          {b.total_duration} mins total
                        </span>
                      </td>

                      {/* Stylist */}
                      <td className="py-3.5 px-4 whitespace-nowrap">
                        <span className="font-medium text-gray-900">{b.provider_name}</span>
                      </td>

                      {/* Financials */}
                      <td className="py-3.5 px-4 whitespace-nowrap">
                        <p className="font-semibold text-gray-900">
                          RWF {b.total_price.toLocaleString()}
                        </p>
                        {b.balance === 0 ? (
                          <span className="text-[10px] font-semibold text-emerald-600">
                            Paid in Full
                          </span>
                        ) : (
                          <span className="text-[10px] font-semibold text-amber-600">
                            Due: RWF {b.balance.toLocaleString()}
                          </span>
                        )}
                      </td>

                      {/* Status Badge */}
                      <td className="py-3.5 px-4 whitespace-nowrap">
                        {b.status === "accepted" && (
                          <span className="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                            Confirmed
                          </span>
                        )}
                        {b.status === "pending" && (
                          <span className="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            Pending
                          </span>
                        )}
                        {b.status === "completed" && (
                          <span className="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Completed
                          </span>
                        )}
                        {(b.status === "cancelled" || b.status === "declined") && (
                          <span className="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                            Cancelled
                          </span>
                        )}
                      </td>

                      {/* Actions */}
                      <td className="py-3.5 px-5 text-right whitespace-nowrap">
                        <div className="inline-flex items-center gap-1.5">
                          {b.status === "pending" && (
                            <button
                              type="button"
                              disabled={updatingId === b.id}
                              onClick={() => handleUpdateStatus(b.id, "accepted")}
                              className="px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-900 text-white hover:bg-gray-800 transition-colors cursor-pointer disabled:opacity-50"
                            >
                              Confirm
                            </button>
                          )}
                          {b.status === "accepted" && (
                            <button
                              type="button"
                              disabled={updatingId === b.id}
                              onClick={() => handleUpdateStatus(b.id, "completed")}
                              className="px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors cursor-pointer disabled:opacity-50"
                            >
                              Complete
                            </button>
                          )}
                          <button
                            type="button"
                            onClick={() => setSelectedBooking(b)}
                            className="p-1.5 rounded-md text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors cursor-pointer"
                            title="View full booking details"
                          >
                            <Eye size={16} />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>

      </div>

      {/* ========================================================= */}
      {/* Booking Details Modal (Pro Feature)                       */}
      {/* ========================================================= */}
      {selectedBooking && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-fadeIn">
          <div className="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-gray-200 max-h-[90vh] flex flex-col">

            {/* Modal Header */}
            <div className="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
              <div>
                <span className="text-xs text-gray-500 font-semibold tabular-nums block">
                  #{selectedBooking.reference}
                </span>
                <h3 className="text-base font-bold text-gray-900">
                  Reservation Details
                </h3>
              </div>
              <button
                type="button"
                onClick={() => setSelectedBooking(null)}
                className="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer"
              >
                <X size={18} />
              </button>
            </div>

            {/* Modal Body */}
            <div className="p-5 overflow-y-auto space-y-4 text-xs">

              {/* Status and Created Date */}
              <div className="flex items-center justify-between pb-3 border-b border-gray-100">
                <span className="text-gray-500">Booked: {selectedBooking.created_at_formatted}</span>
                <span
                  className={`px-2.5 py-1 rounded-md text-xs font-semibold uppercase ${
                    selectedBooking.status === "completed"
                      ? "bg-emerald-100 text-emerald-800"
                      : selectedBooking.status === "accepted"
                      ? "bg-blue-100 text-blue-800"
                      : selectedBooking.status === "pending"
                      ? "bg-amber-100 text-amber-800"
                      : "bg-rose-100 text-rose-800"
                  }`}
                >
                  {selectedBooking.status}
                </span>
              </div>

              {/* Customer Information */}
              <div className="bg-gray-50 rounded-xl p-3.5 space-y-2 border border-gray-100">
                <p className="font-semibold text-gray-700 uppercase tracking-wider text-[10px]">
                  Client Information
                </p>
                <div className="space-y-1">
                  <p className="font-bold text-sm text-gray-900">{selectedBooking.customer_name}</p>
                  <div className="flex items-center gap-3 text-gray-600">
                    <span className="flex items-center gap-1">
                      <Phone size={13} className="text-gray-400" />
                      {selectedBooking.customer_phone}
                    </span>
                    <span className="flex items-center gap-1">
                      <EnvelopeSimple size={13} className="text-gray-400" />
                      {selectedBooking.customer_email}
                    </span>
                  </div>
                </div>
              </div>

              {/* Appointment Date & Stylist */}
              <div className="grid grid-cols-2 gap-3">
                <div className="bg-gray-50 rounded-xl p-3 border border-gray-100">
                  <span className="text-gray-500 text-[10px] uppercase font-semibold block">Date & Time</span>
                  <p className="font-bold text-gray-900 text-sm mt-0.5">{selectedBooking.date_formatted}</p>
                  <p className="text-gray-600">{selectedBooking.time_formatted} ({selectedBooking.total_duration} mins)</p>
                </div>
                <div className="bg-gray-50 rounded-xl p-3 border border-gray-100">
                  <span className="text-gray-500 text-[10px] uppercase font-semibold block">Assigned Stylist</span>
                  <p className="font-bold text-gray-900 text-sm mt-0.5">{selectedBooking.provider_name}</p>
                  <p className="text-gray-600">{selectedBooking.provider_phone || "Sanctuary Team"}</p>
                </div>
              </div>

              {/* Home Service Address if any */}
              {selectedBooking.is_home_service && (
                <div className="bg-amber-50/70 border border-amber-200/80 rounded-xl p-3 space-y-1 text-amber-900">
                  <div className="flex items-center gap-1.5 font-bold text-xs">
                    <House size={14} />
                    <span>Home Service Requested</span>
                  </div>
                  <p className="text-xs">{selectedBooking.address || "Client Residence"}</p>
                </div>
              )}

              {/* Notes */}
              {selectedBooking.notes && (
                <div className="bg-gray-50 rounded-xl p-3 border border-gray-100 space-y-1">
                  <span className="text-gray-500 text-[10px] uppercase font-semibold block">Client Notes</span>
                  <p className="text-gray-700 italic">{selectedBooking.notes}</p>
                </div>
              )}

              {/* Services Breakdown */}
              <div className="space-y-2">
                <span className="text-gray-500 text-[10px] uppercase font-semibold block">Treatments Booked</span>
                <div className="divide-y divide-gray-100 border border-gray-100 rounded-xl overflow-hidden">
                  {selectedBooking.services.map((s) => (
                    <div key={s.id} className="p-2.5 flex items-center justify-between bg-white text-xs">
                      <div>
                        <p className="font-medium text-gray-900">{s.name}</p>
                        <p className="text-[11px] text-gray-400">{s.duration_minutes} minutes</p>
                      </div>
                      <span className="font-semibold text-gray-900">
                        RWF {s.price.toLocaleString()}
                      </span>
                    </div>
                  ))}
                </div>
              </div>

              {/* Financials & Payment Records */}
              <div className="bg-gray-50 rounded-xl p-3.5 border border-gray-100 space-y-2">
                <div className="flex items-center justify-between text-xs font-semibold text-gray-700">
                  <span>Total Amount</span>
                  <span className="text-sm font-bold text-gray-900">
                    RWF {selectedBooking.total_price.toLocaleString()}
                  </span>
                </div>
                <div className="flex items-center justify-between text-xs text-emerald-700">
                  <span>Total Paid (Online/Verified)</span>
                  <span className="font-bold">
                    RWF {selectedBooking.total_paid.toLocaleString()}
                  </span>
                </div>
                <div className="flex items-center justify-between text-xs pt-1.5 border-t border-gray-200">
                  <span className="font-semibold text-gray-700">Remaining Balance</span>
                  <span className={`font-bold ${selectedBooking.balance === 0 ? "text-emerald-700" : "text-amber-700"}`}>
                    RWF {selectedBooking.balance.toLocaleString()}
                  </span>
                </div>

                {/* List payments */}
                {selectedBooking.payments.length > 0 && (
                  <div className="pt-2 border-t border-gray-200 space-y-1">
                    <span className="text-[10px] uppercase font-semibold text-gray-400 block">Payments:</span>
                    {selectedBooking.payments.map((p) => (
                      <div key={p.id} className="text-[11px] flex justify-between text-gray-600">
                        <span>#{p.reference} ({p.method.toUpperCase()})</span>
                        <span>RWF {p.amount.toLocaleString()} on {p.paid_at}</span>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>

            {/* Modal Actions */}
            <div className="p-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
              <div>
                {selectedBooking.status !== "cancelled" && (
                  <button
                    type="button"
                    disabled={updatingId === selectedBooking.id}
                    onClick={() => handleUpdateStatus(selectedBooking.id, "cancelled")}
                    className="text-xs text-rose-600 hover:text-rose-800 font-medium cursor-pointer"
                  >
                    Cancel Reservation
                  </button>
                )}
              </div>

              <div className="flex items-center gap-2">
                {selectedBooking.status === "pending" && (
                  <button
                    type="button"
                    disabled={updatingId === selectedBooking.id}
                    onClick={() => handleUpdateStatus(selectedBooking.id, "accepted")}
                    className="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-900 text-white hover:bg-gray-800 transition-colors cursor-pointer"
                  >
                    Confirm Booking
                  </button>
                )}
                {selectedBooking.status === "accepted" && (
                  <button
                    type="button"
                    disabled={updatingId === selectedBooking.id}
                    onClick={() => handleUpdateStatus(selectedBooking.id, "completed")}
                    className="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors cursor-pointer"
                  >
                    Mark as Completed
                  </button>
                )}
                <button
                  type="button"
                  onClick={() => setSelectedBooking(null)}
                  className="px-3.5 py-1.5 rounded-lg text-xs font-semibold border border-gray-300 text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer"
                >
                  Close
                </button>
              </div>
            </div>

          </div>
        </div>
      )}

    </div>
  );
}
