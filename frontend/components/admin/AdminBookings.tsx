"use client";

import { useState, useEffect, useCallback, useMemo } from "react";
import {
  Calendar,
  MagnifyingGlass,
  Check,
  Clock,
  XCircle,
  X,
  Scissors,
  CreditCard,
  DeviceMobile,
  ArrowsClockwise,
  Eye,
  Phone,
  EnvelopeSimple,
  DownloadSimple,
  Car,
  CheckCircle,
  House,
  Printer,
  CaretLeft,
  CaretRight,
  User,
  Plus,
  PaperPlaneTilt,
  Receipt,
  MapPin,
  WarningCircle,
  WhatsappLogo,
  Rows,
  Table,
  SquaresFour,
  Money,
  ArrowsLeftRight,
} from "@phosphor-icons/react";
import AdminHeader from "./AdminHeader";
import AdminWalkInModal from "./AdminWalkInModal";
import { AdminBookingItem, AdminBookingsResponse } from "@/types";

export default function AdminBookings() {
  // Data state
  const [bookings, setBookings] = useState<AdminBookingItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [pagination, setPagination] = useState({
    total: 0,
    per_page: 15,
    current_page: 1,
    last_page: 1,
    from: 0 as number | null,
    to: 0 as number | null,
  });
  const [statusCounts, setStatusCounts] = useState({
    all: 0,
    pending: 0,
    accepted: 0,
    in_progress: 0,
    completed: 0,
    declined: 0,
    cancelled: 0,
  });
  const [providers, setProviders] = useState<{ id: number; name: string }[]>([]);

  // View & Density settings
  const [viewMode, setViewMode] = useState<"table" | "cards">("table");
  const [density, setDensity] = useState<"compact" | "comfortable">("compact");

  // Filter state
  const [search, setSearch] = useState("");
  const [debouncedSearch, setDebouncedSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState<string>("all");
  const [paymentFilter, setPaymentFilter] = useState<string>("all");
  const [providerFilter, setProviderFilter] = useState<string>("all");
  const [dateFilter, setDateFilter] = useState<string>("all");
  const [serviceTypeFilter, setServiceTypeFilter] = useState<string>("all");
  const [sourceFilter, setSourceFilter] = useState<string>("all");
  const [perPage, setPerPage] = useState<number>(15);
  const [currentPage, setCurrentPage] = useState<number>(1);

  // Drawer & Action states
  const [selectedBooking, setSelectedBooking] = useState<AdminBookingItem | null>(null);
  const [showWalkInModal, setShowWalkInModal] = useState(false);
  const [updatingId, setUpdatingId] = useState<number | null>(null);
  const [feedbackMessage, setFeedbackMessage] = useState<{ type: "success" | "error"; text: string } | null>(null);

  // In-Drawer Payment form state
  const [showPaymentForm, setShowPaymentForm] = useState(false);
  const [payAmount, setPayAmount] = useState<string>("");
  const [payMethod, setPayMethod] = useState<string>("cash");
  const [payingState, setPayingState] = useState(false);

  // In-Drawer Reschedule / Reassign state
  const [showReschedule, setShowReschedule] = useState(false);
  const [editDate, setEditDate] = useState("");
  const [editTime, setEditTime] = useState("");
  const [editProviderId, setEditProviderId] = useState<string>("");
  const [savingReschedule, setSavingReschedule] = useState(false);

  // Debounce search
  useEffect(() => {
    const timer = setTimeout(() => {
      setDebouncedSearch(search);
      setCurrentPage(1);
    }, 350);
    return () => clearTimeout(timer);
  }, [search]);

  // Fetch bookings
  const fetchBookings = useCallback(async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams();
      if (statusFilter !== "all") params.append("status", statusFilter);
      if (debouncedSearch.trim()) params.append("search", debouncedSearch.trim());
      if (paymentFilter !== "all") params.append("payment_status", paymentFilter);
      if (providerFilter !== "all") params.append("provider_id", providerFilter);
      if (dateFilter !== "all") params.append("date", dateFilter);
      if (serviceTypeFilter !== "all") params.append("service_type", serviceTypeFilter);
      if (sourceFilter !== "all") params.append("source", sourceFilter);
      params.append("page", currentPage.toString());
      params.append("per_page", perPage.toString());

      const res = await fetch(`/api/v1/admin/bookings?${params.toString()}`, {
        cache: "no-store",
      });
      const data: AdminBookingsResponse = await res.json();

      if (res.ok && data.success) {
        setBookings(data.data);
        setPagination(data.pagination);
        setStatusCounts(data.status_counts);
        if (data.providers) setProviders(data.providers);
      }
    } catch (err) {
      console.error("Failed to load admin bookings", err);
      setFeedbackMessage({
        type: "error",
        text: "Could not fetch bookings. Backend server may be offline.",
      });
    } finally {
      setLoading(false);
    }
  }, [
    statusFilter,
    debouncedSearch,
    paymentFilter,
    providerFilter,
    dateFilter,
    serviceTypeFilter,
    sourceFilter,
    currentPage,
    perPage,
  ]);

  useEffect(() => {
    fetchBookings();
  }, [fetchBookings]);

  useEffect(() => {
    const url = new URL(window.location.href);
    if (url.searchParams.get("action") !== "walk-in") return;
    setShowWalkInModal(true);
    url.searchParams.delete("action");
    window.history.replaceState(window.history.state, "", `${url.pathname}${url.search}${url.hash}`);
  }, []);

  // When selecting a booking, initialize payment & reschedule state
  const handleOpenDrawer = (booking: AdminBookingItem) => {
    setSelectedBooking(booking);
    setShowPaymentForm(false);
    setShowReschedule(false);
    setPayAmount(booking.balance > 0 ? booking.balance.toString() : "");
    setEditDate(booking.date);
    setEditTime(booking.time);
    setEditProviderId(booking.provider_id ? booking.provider_id.toString() : "");
  };

  // Update Status Action
  const handleUpdateStatus = async (bookingId: number, newStatus: string) => {
    setUpdatingId(bookingId);
    setFeedbackMessage(null);
    try {
      const res = await fetch(`/api/v1/admin/bookings/${bookingId}/status`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ status: newStatus }),
      });
      const json = await res.json();

      if (res.ok && json.success) {
        setFeedbackMessage({ type: "success", text: json.message });

        setBookings((prev) =>
          prev.map((b) => (b.id === bookingId ? { ...b, status: newStatus as any } : b))
        );

        setStatusCounts((prev) => {
          const current = bookings.find((b) => b.id === bookingId)?.status;
          if (!current || current === newStatus) return prev;
          return {
            ...prev,
            [current]: Math.max(0, (prev[current as keyof typeof prev] || 1) - 1),
            [newStatus]: (prev[newStatus as keyof typeof prev] || 0) + 1,
          };
        });

        if (selectedBooking && selectedBooking.id === bookingId) {
          setSelectedBooking((prev) => (prev ? { ...prev, status: newStatus as any } : null));
        }

        setTimeout(() => setFeedbackMessage(null), 4000);
      } else {
        setFeedbackMessage({
          type: "error",
          text: json.message || "Failed to update booking status.",
        });
      }
    } catch (err) {
      console.error("Status update error", err);
      setFeedbackMessage({ type: "error", text: "Network error occurred." });
    } finally {
      setUpdatingId(null);
    }
  };

  // Record In-Salon Payment
  const handleRecordPayment = async () => {
    if (!selectedBooking || !payAmount || Number(payAmount) <= 0) return;
    setPayingState(true);
    try {
      const res = await fetch(`/api/v1/admin/bookings/${selectedBooking.id}/record-payment`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          amount: Number(payAmount),
          method: payMethod,
        }),
      });
      const json = await res.json();
      if (res.ok && json.success && json.data) {
        setFeedbackMessage({ type: "success", text: json.message });
        setSelectedBooking(json.data);
        setBookings((prev) =>
          prev.map((b) => (b.id === selectedBooking.id ? json.data : b))
        );
        setShowPaymentForm(false);
        setTimeout(() => setFeedbackMessage(null), 4000);
      } else {
        setFeedbackMessage({ type: "error", text: json.message || "Failed to record payment." });
      }
    } catch (err) {
      console.error("Payment recording failed", err);
      setFeedbackMessage({ type: "error", text: "Error recording payment." });
    } finally {
      setPayingState(false);
    }
  };

  // Reschedule or Reassign Provider
  const handleSaveReschedule = async () => {
    if (!selectedBooking) return;
    setSavingReschedule(true);
    try {
      const res = await fetch(`/api/v1/admin/bookings/${selectedBooking.id}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          date: editDate,
          time: editTime,
          provider_id: editProviderId ? Number(editProviderId) : null,
        }),
      });
      const json = await res.json();
      if (res.ok && json.success && json.data) {
        setFeedbackMessage({ type: "success", text: json.message });
        setSelectedBooking(json.data);
        setBookings((prev) =>
          prev.map((b) => (b.id === selectedBooking.id ? json.data : b))
        );
        setShowReschedule(false);
        setTimeout(() => setFeedbackMessage(null), 4000);
      } else {
        setFeedbackMessage({ type: "error", text: json.message || "Failed to reschedule." });
      }
    } catch (err) {
      console.error("Reschedule failed", err);
      setFeedbackMessage({ type: "error", text: "Error updating schedule." });
    } finally {
      setSavingReschedule(false);
    }
  };

  const handleWalkInCreated = (message: string, reference: string) => {
    setShowWalkInModal(false);
    setSearch(reference);
    setDebouncedSearch(reference);
    setStatusFilter("all");
    setPaymentFilter("all");
    setProviderFilter("all");
    setServiceTypeFilter("salon");
    setSourceFilter("walk_in");
    setDateFilter("all");
    setCurrentPage(1);
    setFeedbackMessage({ type: "success", text: message });
    window.setTimeout(() => setFeedbackMessage(null), 4000);
    void fetchBookings();
  };

  // WhatsApp quick helper
  const getWhatsAppLink = (b: AdminBookingItem) => {
    if (!b.customer_phone || b.customer_phone === "N/A") return null;
    let clean = b.customer_phone.replace(/[^0-9]/g, "");
    if (clean.startsWith("0")) clean = "250" + clean.substring(1);
    const message = encodeURIComponent(
      `Hello ${b.customer_name}, this is Isaiah Nail Bar confirming your appointment on ${b.date_formatted} at ${b.time_formatted}. Reference: ${b.reference}. See you soon!`
    );
    return `https://wa.me/${clean}?text=${message}`;
  };

  // Export to CSV
  const handleExportCSV = () => {
    if (bookings.length === 0) return;
    const headers = [
      "ID",
      "Reference",
      "Customer Name",
      "Customer Phone",
      "Customer Email",
      "Source",
      "Date",
      "Time",
      "Services",
      "Stylist",
      "Total Price (RWF)",
      "Amount Paid (RWF)",
      "Balance (RWF)",
      "Payment Status",
      "Payment Method",
      "Booking Status",
      "Type",
    ];
    const rows = bookings.map((b) => [
      b.id,
      `"${b.reference}"`,
      `"${b.customer_name}"`,
      `"${b.customer_phone || ""}"`,
      `"${b.customer_email || ""}"`,
      b.source,
      b.date,
      b.time_formatted,
      `"${b.services.map((s) => s.name).join(", ")}"`,
      `"${b.provider_name}"`,
      b.total_price,
      b.total_paid,
      b.balance,
      b.payment_status,
      b.payment_method || "N/A",
      b.status,
      b.is_home_service ? "Home Service" : b.pickup_location_id ? "Pickup Route" : "Sanctuary",
    ]);

    const csvContent =
      "data:text/csv;charset=utf-8," +
      [headers.join(","), ...rows.map((e) => e.join(","))].join("\n");
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `isaiah_bookings_${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  // Helper formatting badges
  const renderStatusBadge = (status: string) => {
    switch (status) {
      case "pending":
        return (
          <span className="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider rounded-md bg-amber-50 text-amber-800 border border-amber-200">
            <Clock size={11} weight="bold" /> Pending
          </span>
        );
      case "accepted":
        return (
          <span className="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider rounded-md bg-blue-50 text-blue-800 border border-blue-200">
            <Check size={11} weight="bold" /> Accepted
          </span>
        );
      case "in_progress":
        return (
          <span className="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider rounded-md bg-violet-50 text-violet-800 border border-violet-200">
            <Clock size={11} weight="bold" /> In Progress
          </span>
        );
      case "completed":
        return (
          <span className="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200">
            <CheckCircle size={11} weight="bold" /> Completed
          </span>
        );
      case "declined":
        return (
          <span className="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider rounded-md bg-rose-50 text-rose-800 border border-rose-200">
            <X size={11} weight="bold" /> Declined
          </span>
        );
      case "cancelled":
        return (
          <span className="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider rounded-md bg-gray-100 text-gray-700 border border-gray-200">
            <XCircle size={11} weight="bold" /> Cancelled
          </span>
        );
      default:
        return (
          <span className="inline-flex items-center px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider rounded-md bg-gray-100 text-gray-700">
            {status}
          </span>
        );
    }
  };

  const renderSourceBadge = (source: AdminBookingItem["source"]) => {
    const labels = {
      website: "Website",
      admin_manual: "Manual",
      walk_in: "Walk-in",
      legacy: "Legacy",
    };
    const isWalkIn = source === "walk_in";
    return (
      <span className={`inline-flex items-center rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide ${isWalkIn ? "bg-emerald-50 text-emerald-700" : "bg-gray-100 text-gray-500"}`}>
        {labels[source] || "Website"}
      </span>
    );
  };

  const renderPaymentBadge = (b: AdminBookingItem) => {
    switch (b.payment_status) {
      case "paid":
        return (
          <span className="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-bold uppercase rounded bg-emerald-100 text-emerald-800 border border-emerald-200">
            ✓ Paid
          </span>
        );
      case "deposit":
        return (
          <span className="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-bold uppercase rounded bg-blue-100 text-blue-800 border border-blue-200">
            Deposit
          </span>
        );
      case "pending":
        return (
          <span className="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-bold uppercase rounded bg-amber-100 text-amber-800 border border-amber-200">
            ⏳ Pending
          </span>
        );
      case "failed":
        return (
          <span className="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-bold uppercase rounded bg-rose-100 text-rose-800 border border-rose-200">
            ✗ Failed
          </span>
        );
      default:
        return (
          <span className="inline-flex items-center px-1.5 py-0.5 text-[10px] font-bold uppercase rounded bg-gray-100 text-gray-600 border border-gray-200">
            Unpaid
          </span>
        );
    }
  };

  const hasActiveFilters =
    debouncedSearch.trim() !== "" ||
    statusFilter !== "all" ||
    paymentFilter !== "all" ||
    providerFilter !== "all" ||
    dateFilter !== "all" ||
    serviceTypeFilter !== "all" ||
    sourceFilter !== "all";
    

  const handleResetFilters = () => {
    setSearch("");
    setDebouncedSearch("");
    setStatusFilter("all");
    setPaymentFilter("all");
    setProviderFilter("all");
    setDateFilter("all");
    setServiceTypeFilter("all");
    setSourceFilter("all");
    setCurrentPage(1);
  };

  return (
    <div className="flex-1 flex flex-col min-w-0 bg-gray-50 pb-16">
      {/* Top Bar Header */}
      <AdminHeader
        title="All Bookings"
        subtitle="Manage customer reservations, track deposits, confirm slots, and inspect receipts."
      />

      {/* Main Container */}
      <div className="p-4 sm:p-6 max-w-7xl w-full mx-auto space-y-4">
        {/* Toast Alert */}
        {feedbackMessage && (
          <div
            className={`p-3.5 rounded-lg flex items-center justify-between text-xs font-medium transition-all shadow-xs ${
              feedbackMessage.type === "success"
                ? "bg-emerald-50 text-emerald-800 border border-emerald-200"
                : "bg-rose-50 text-rose-800 border border-rose-200"
            }`}
          >
            <div className="flex items-center gap-2">
              {feedbackMessage.type === "success" ? (
                <CheckCircle size={16} weight="fill" className="text-emerald-600" />
              ) : (
                <WarningCircle size={16} weight="fill" className="text-rose-600" />
              )}
              <span>{feedbackMessage.text}</span>
            </div>
            <button
              onClick={() => setFeedbackMessage(null)}
              className="text-gray-400 hover:text-gray-600 p-0.5"
            >
              <X size={14} />
            </button>
          </div>
        )}

        {/* Action Header Row */}
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-gray-200 shadow-2xs">
          <div>
            <h2 className="text-base font-bold text-gray-900 tracking-tight flex items-center gap-2">
              <Calendar size={18} className="text-gray-900" />
              Reservation Management
            </h2>
            <p className="text-xs text-gray-500 mt-0.5">
              Click any booking to view comprehensive details, record payments, or print receipts.
            </p>
          </div>

          <div className="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            {/* View & Density Toggles */}
            <div className="inline-flex items-center bg-gray-100 rounded-lg p-0.5 border border-gray-200">
              <button
                onClick={() => setDensity("compact")}
                title="Compact view (short rows)"
                className={`p-1.5 rounded-md text-xs transition ${
                  density === "compact"
                    ? "bg-white text-gray-900 shadow-2xs font-semibold"
                    : "text-gray-500 hover:text-gray-900"
                }`}
              >
                <Rows size={14} />
              </button>
              <button
                onClick={() => setDensity("comfortable")}
                title="Comfortable view (spacious rows)"
                className={`p-1.5 rounded-md text-xs transition ${
                  density === "comfortable"
                    ? "bg-white text-gray-900 shadow-2xs font-semibold"
                    : "text-gray-500 hover:text-gray-900"
                }`}
              >
                <Table size={14} />
              </button>
              <span className="w-px h-3 bg-gray-300 mx-1" />
              <button
                onClick={() => setViewMode(viewMode === "table" ? "cards" : "table")}
                title={viewMode === "table" ? "Switch to Cards View" : "Switch to Table View"}
                className={`p-1.5 rounded-md text-xs transition ${
                  viewMode === "cards"
                    ? "bg-white text-gray-900 shadow-2xs font-semibold"
                    : "text-gray-500 hover:text-gray-900"
                }`}
              >
                <SquaresFour size={14} />
              </button>
            </div>

            <button
              onClick={handleExportCSV}
              disabled={bookings.length === 0}
              className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 transition shadow-2xs disabled:opacity-40"
              title="Export Current Filtered List to CSV"
            >
              <DownloadSimple size={13} />
              <span>Export CSV</span>
            </button>

            <button
              onClick={() => setShowWalkInModal(true)}
              className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-700 text-white rounded-lg text-xs font-semibold hover:bg-emerald-800 transition shadow-xs"
            >
              <User size={13} weight="bold" />
              <span>Record Walk-in</span>
            </button>

            <button
              onClick={fetchBookings}
              disabled={loading}
              title="Refresh"
              className="p-1.5 border border-gray-300 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition shadow-2xs"
            >
              <ArrowsClockwise size={14} className={loading ? "animate-spin" : ""} />
            </button>
          </div>
        </div>

        {/* Quick Status Tabs */}
        <div className="flex items-center gap-1 overflow-x-auto pb-1 scrollbar-none border-b border-gray-200">
          {[
            { id: "all", label: "All Bookings", count: statusCounts.all },
            { id: "pending", label: "Pending", count: statusCounts.pending, color: "text-amber-700 bg-amber-50" },
            { id: "accepted", label: "Accepted", count: statusCounts.accepted, color: "text-blue-700 bg-blue-50" },
            { id: "in_progress", label: "In Progress", count: statusCounts.in_progress, color: "text-violet-700 bg-violet-50" },
            { id: "completed", label: "Completed", count: statusCounts.completed, color: "text-emerald-700 bg-emerald-50" },
            { id: "declined", label: "Declined", count: statusCounts.declined, color: "text-rose-700 bg-rose-50" },
            { id: "cancelled", label: "Cancelled", count: statusCounts.cancelled, color: "text-gray-700 bg-gray-100" },
          ].map((tab) => {
            const active = statusFilter === tab.id;
            return (
              <button
                key={tab.id}
                onClick={() => {
                  setStatusFilter(tab.id);
                  setCurrentPage(1);
                }}
                className={`flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-all whitespace-nowrap ${
                  active
                    ? "bg-gray-900 text-white shadow-xs"
                    : "text-gray-600 hover:bg-white hover:text-gray-900"
                }`}
              >
                <span>{tab.label}</span>
                <span
                  className={`px-1.5 py-0.2 rounded-full text-[10px] font-bold ${
                    active ? "bg-gray-800 text-white" : tab.color || "bg-gray-100 text-gray-600"
                  }`}
                >
                  {tab.count}
                </span>
              </button>
            );
          })}
        </div>

        {/* Compact Filters Bar */}
        <div className="bg-white border border-gray-200 rounded-xl p-3 shadow-2xs space-y-2">
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-2.5">
            {/* Search */}
            <div className="sm:col-span-2">
              <div className="relative">
                <MagnifyingGlass
                  size={14}
                  className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                />
                <input
                  type="text"
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  placeholder="Search customer, phone, ref..."
                  className="w-full pl-8 pr-3 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs text-gray-900 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gray-900 transition"
                />
                {search && (
                  <button
                    onClick={() => setSearch("")}
                    className="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                  >
                    <X size={12} />
                  </button>
                )}
              </div>
            </div>

            {/* Payment Filter */}
            <div>
              <select
                value={paymentFilter}
                onChange={(e) => {
                  setPaymentFilter(e.target.value);
                  setCurrentPage(1);
                }}
                className="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs text-gray-800 focus:bg-white focus:outline-none"
              >
                <option value="all">All Payments</option>
                <option value="paid">Paid in Full</option>
                <option value="deposit">Deposit Paid</option>
                <option value="pending">Payment Pending</option>
                <option value="failed">Payment Failed</option>
                <option value="unpaid">Unpaid</option>
              </select>
            </div>

            {/* Provider Filter */}
            <div>
              <select
                value={providerFilter}
                onChange={(e) => {
                  setProviderFilter(e.target.value);
                  setCurrentPage(1);
                }}
                className="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs text-gray-800 focus:bg-white focus:outline-none"
              >
                <option value="all">All Stylists</option>
                {providers.map((p) => (
                  <option key={p.id} value={p.id}>
                    {p.name}
                  </option>
                ))}
              </select>
            </div>

            {/* Date Filter */}
            <div>
              <select
                value={dateFilter}
                onChange={(e) => {
                  setDateFilter(e.target.value);
                  setCurrentPage(1);
                }}
                className="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs text-gray-800 focus:bg-white focus:outline-none"
              >
                <option value="all">All Dates</option>
                <option value="today">Today</option>
                <option value="tomorrow">Tomorrow</option>
                <option value="upcoming">Upcoming</option>
                <option value="this_week">This Week</option>
                <option value="this_month">This Month</option>
                <option value="past">Past</option>
              </select>
            </div>

            {/* Service Location Type */}
            <div>
              <select
                value={serviceTypeFilter}
                onChange={(e) => {
                  setServiceTypeFilter(e.target.value);
                  setCurrentPage(1);
                }}
                className="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs text-gray-800 focus:bg-white focus:outline-none"
              >
                <option value="all">All Locations</option>
                <option value="salon">In-Salon</option>
                <option value="home">Home Service</option>
                <option value="pickup">Pickup Shuttle</option>
              </select>
            </div>

            {/* Booking Source */}
            <div>
              <select
                value={sourceFilter}
                onChange={(e) => {
                  setSourceFilter(e.target.value);
                  setCurrentPage(1);
                }}
                className="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs text-gray-800 focus:bg-white focus:outline-none"
              >
                <option value="all">All Sources</option>
                <option value="website">Website</option>
                <option value="admin_manual">Manual</option>
                <option value="walk_in">Walk-in</option>
              </select>
            </div>
          </div>

          {hasActiveFilters && (
            <div className="flex items-center justify-between pt-1.5 border-t border-gray-100 text-[11px]">
              <span className="text-gray-500">Filtered view active</span>
              <button
                onClick={handleResetFilters}
                className="text-gray-700 hover:text-gray-900 font-medium underline"
              >
                Reset Filters
              </button>
            </div>
          )}
        </div>

        {/* View Mode: Cards vs Sleek Table */}
        {viewMode === "cards" ? (
          /* Card / Grid View */
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            {loading ? (
              Array.from({ length: 6 }).map((_, i) => (
                <div key={i} className="bg-white rounded-xl border border-gray-200 p-4 animate-pulse space-y-2">
                  <div className="h-4 bg-gray-200 rounded w-1/2"></div>
                  <div className="h-3 bg-gray-100 rounded w-3/4"></div>
                  <div className="h-8 bg-gray-100 rounded mt-3"></div>
                </div>
              ))
            ) : bookings.length === 0 ? (
              <div className="col-span-full bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500">
                <p className="font-semibold text-gray-800">No reservations found</p>
                <p className="text-xs text-gray-400 mt-1">Try resetting your filters</p>
              </div>
            ) : (
              bookings.map((b) => (
                <div
                  key={b.id}
                  onClick={() => handleOpenDrawer(b)}
                  className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs hover:shadow-xs hover:border-gray-300 transition cursor-pointer flex flex-col justify-between space-y-3"
                >
                  <div className="flex items-start justify-between gap-2">
                    <div>
                      <div className="flex items-center gap-1.5">
                        <span className="font-bold text-gray-900 text-sm">{b.customer_name}</span>
                        {b.is_home_service && (
                          <span className="p-0.5 text-rose-600 bg-rose-50 rounded" title="Home Service">
                            <House size={12} />
                          </span>
                        )}
                        {b.pickup_location_id && (
                          <span className="p-0.5 text-blue-600 bg-blue-50 rounded" title="Pickup Route">
                            <Car size={12} />
                          </span>
                        )}
                      </div>
                      <div className="mt-1 flex flex-wrap items-center gap-1.5">
                        <span className="text-[11px] text-gray-400 font-medium tabular-nums">{b.reference}</span>
                        {renderSourceBadge(b.source)}
                      </div>
                    </div>
                    {renderStatusBadge(b.status)}
                  </div>

                  <div className="bg-gray-50 rounded-lg p-2.5 text-xs space-y-1 text-gray-600">
                    <div className="flex justify-between items-center font-medium text-gray-900">
                      <span>{b.date_formatted}</span>
                      <span>{b.time_formatted}</span>
                    </div>
                    <div className="text-[11px] text-gray-500 truncate">
                      {b.services.map((s) => s.name).join(", ")}
                    </div>
                    <div className="text-[11px] text-gray-400">
                      Stylist: <span className="text-gray-700 font-medium">{b.provider_name}</span>
                    </div>
                  </div>

                  <div className="flex items-center justify-between pt-1 border-t border-gray-100 text-xs">
                    <div>
                      <div className="font-bold tabular-nums text-gray-900">
                        {b.total_price.toLocaleString()} RWF
                      </div>
                      <div className="flex items-center gap-1 mt-0.5">
                        {renderPaymentBadge(b)}
                        {b.payment_method && (
                          <span className="text-[10px] text-gray-400 uppercase font-medium">
                            {b.payment_method}
                          </span>
                        )}
                      </div>
                    </div>

                    <button
                      onClick={(e) => {
                        e.stopPropagation();
                        handleOpenDrawer(b);
                      }}
                      className="px-2.5 py-1 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                    >
                      View Details
                    </button>
                  </div>
                </div>
              ))
            )}
          </div>
        ) : (
          /* Streamlined 5-Column Table */
          <div className="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
            <div className="overflow-x-auto min-h-[280px]">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-gray-50 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                    <th className="px-4 py-2.5">Customer & Ref</th>
                    <th className="px-4 py-2.5">Appointment</th>
                    <th className="px-4 py-2.5">Treatments & Stylist</th>
                    <th className="px-4 py-2.5">Total & Payment</th>
                    <th className="px-4 py-2.5 text-right">Status & Action</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100 text-xs">
                  {loading ? (
                    Array.from({ length: 5 }).map((_, i) => (
                      <tr key={i} className="animate-pulse">
                        <td colSpan={5} className="px-4 py-3">
                          <div className="h-3.5 bg-gray-200 rounded w-full mb-1"></div>
                          <div className="h-2.5 bg-gray-100 rounded w-2/3"></div>
                        </td>
                      </tr>
                    ))
                  ) : bookings.length === 0 ? (
                    <tr>
                      <td colSpan={5} className="px-4 py-12 text-center text-gray-500">
                        <div className="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2 text-gray-400">
                          <Calendar size={20} />
                        </div>
                        <p className="text-sm font-semibold text-gray-900">No reservations found</p>
                        <p className="text-xs text-gray-400 mt-0.5">Try adjusting your filters</p>
                      </td>
                    </tr>
                  ) : (
                    bookings.map((booking) => {
                      const isCompact = density === "compact";
                      const primaryService = booking.services[0]?.name || "Nail Service";
                      const extraCount = booking.services.length - 1;
                      const waLink = getWhatsAppLink(booking);

                      return (
                        <tr
                          key={booking.id}
                          onClick={() => handleOpenDrawer(booking)}
                          className={`hover:bg-gray-50/80 transition-colors cursor-pointer group ${
                            isCompact ? "py-2" : "py-3.5"
                          }`}
                        >
                          {/* 1. Customer & Ref */}
                          <td className={`px-4 ${isCompact ? "py-2" : "py-3"} align-middle`}>
                            <div className="flex items-center gap-1.5">
                              <span className="font-semibold text-gray-900 text-xs">
                                {booking.customer_name}
                              </span>
                              {booking.is_home_service && (
                                <span
                                  className="px-1 py-0.2 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-100 inline-flex items-center gap-0.5"
                                  title={`Home Service: ${booking.address || ""}`}
                                >
                                  <House size={10} /> Home
                                </span>
                              )}
                              {booking.pickup_location_id && (
                                <span
                                  className="px-1 py-0.2 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100 inline-flex items-center gap-0.5"
                                  title={`Pickup: ${booking.pickup_address || ""}`}
                                >
                                  <Car size={10} /> Shuttle
                                </span>
                              )}
                            </div>
                            <div className="text-[11px] text-gray-400 flex items-center gap-1.5 mt-0.5 tabular-nums">
                              <span>{booking.reference}</span>
                              {renderSourceBadge(booking.source)}
                              {booking.customer_phone && booking.customer_phone !== "N/A" && (
                                <>
                                  <span className="text-gray-300">·</span>
                                  <span className="font-sans text-gray-500 font-normal">
                                    {booking.customer_phone}
                                  </span>
                                </>
                              )}
                            </div>
                          </td>

                          {/* 2. Appointment Schedule */}
                          <td className={`px-4 ${isCompact ? "py-2" : "py-3"} align-middle`}>
                            <div className="flex items-center gap-1.5">
                              <span className="font-medium text-gray-900 text-xs">
                                {booking.date_formatted}
                              </span>
                              {booking.is_today && (
                                <span className="px-1 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                  TODAY
                                </span>
                              )}
                            </div>
                            <div className="text-[11px] text-gray-500 mt-0.5">
                              <span>{booking.time_formatted}</span>
                              <span className="text-gray-400 ml-1">({booking.day_name})</span>
                            </div>
                          </td>

                          {/* 3. Treatments & Stylist */}
                          <td className={`px-4 ${isCompact ? "py-2" : "py-3"} align-middle`}>
                            <div className="flex items-center gap-1 max-w-[240px]">
                              <span className="font-medium text-gray-900 truncate">
                                {primaryService}
                              </span>
                              {extraCount > 0 && (
                                <span className="px-1.5 py-0.2 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-600 shrink-0">
                                  +{extraCount}
                                </span>
                              )}
                            </div>
                            <div className="text-[11px] text-gray-500 mt-0.5 flex items-center gap-1">
                              <span className="text-gray-400">with</span>
                              <span className="text-gray-700 font-medium">
                                {booking.provider_name}
                              </span>
                            </div>
                          </td>

                          {/* 4. Total & Payment */}
                          <td className={`px-4 ${isCompact ? "py-2" : "py-3"} align-middle`}>
                            <div className="font-bold tabular-nums text-gray-900 text-xs">
                              {booking.total_price.toLocaleString()} RWF
                            </div>
                            <div className="flex items-center gap-1.5 mt-0.5">
                              {renderPaymentBadge(booking)}
                              {booking.payment_method && (
                                <span className="text-[10px] text-gray-400 uppercase font-medium">
                                  {booking.payment_method}
                                </span>
                              )}
                            </div>
                          </td>

                          {/* 5. Status & Actions */}
                          <td
                            className={`px-4 ${isCompact ? "py-2" : "py-3"} align-middle text-right whitespace-nowrap`}
                            onClick={(e) => e.stopPropagation()}
                          >
                            <div className="flex items-center justify-end gap-1.5">
                              {renderStatusBadge(booking.status)}

                              {/* Quick 1-click status actions */}
                              {booking.status === "pending" && (
                                <>
                                  <button
                                    onClick={() => handleUpdateStatus(booking.id, "accepted")}
                                    disabled={updatingId === booking.id}
                                    className="px-2 py-1 rounded bg-emerald-600 text-white text-[11px] font-semibold hover:bg-emerald-700 transition"
                                    title="Accept Appointment"
                                  >
                                    Accept
                                  </button>
                                  <button
                                    onClick={() => handleUpdateStatus(booking.id, "declined")}
                                    disabled={updatingId === booking.id}
                                    className="px-2 py-1 rounded bg-rose-600 text-white text-[11px] font-semibold hover:bg-rose-700 transition"
                                    title="Decline"
                                  >
                                    Decline
                                  </button>
                                </>
                              )}

                              {(booking.status === "accepted" || booking.status === "in_progress") && (
                                <button
                                  onClick={() => handleUpdateStatus(booking.id, "completed")}
                                  disabled={updatingId === booking.id}
                                  className="px-2 py-1 rounded bg-blue-600 text-white text-[11px] font-semibold hover:bg-blue-700 transition"
                                  title="Mark as Completed"
                                >
                                  Complete
                                </button>
                              )}

                              {/* Quick WhatsApp Reminder */}
                              {waLink && (
                                <a
                                  href={waLink}
                                  target="_blank"
                                  rel="noopener noreferrer"
                                  className="p-1 rounded text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 transition"
                                  title="Send WhatsApp Reminder"
                                >
                                  <WhatsappLogo size={14} weight="bold" />
                                </a>
                              )}

                              {/* Open Details Drawer */}
                              <button
                                onClick={() => handleOpenDrawer(booking)}
                                className="px-2 py-1 rounded border border-gray-300 text-gray-700 hover:text-gray-900 hover:bg-gray-100 transition text-[11px] font-medium"
                                title="View Comprehensive Details"
                              >
                                View
                              </button>
                            </div>
                          </td>
                        </tr>
                      );
                    })
                  )}
                </tbody>
              </table>
            </div>

            {/* Pagination Footer */}
            <div className="bg-white px-4 py-2.5 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-gray-500">
              <div>
                {pagination.total > 0 ? (
                  <span>
                    Showing <span className="font-semibold text-gray-800">{pagination.from || 1}</span> to{" "}
                    <span className="font-semibold text-gray-800">{pagination.to || bookings.length}</span> of{" "}
                    <span className="font-semibold text-gray-800">{pagination.total}</span> bookings
                  </span>
                ) : (
                  <span>0 bookings found</span>
                )}
              </div>

              <div className="flex items-center gap-2">
                <span className="text-gray-400">Rows:</span>
                <select
                  value={perPage}
                  onChange={(e) => {
                    setPerPage(Number(e.target.value));
                    setCurrentPage(1);
                  }}
                  className="px-2 py-0.5 bg-gray-50 border border-gray-300 rounded text-xs text-gray-700 focus:outline-none"
                >
                  <option value={10}>10</option>
                  <option value={15}>15</option>
                  <option value={25}>25</option>
                  <option value={50}>50</option>
                </select>

                <div className="flex items-center gap-1 ml-2">
                  <button
                    onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
                    disabled={currentPage <= 1 || loading}
                    className="p-1 rounded border border-gray-300 text-gray-600 hover:text-gray-900 hover:bg-gray-50 disabled:opacity-40 transition"
                  >
                    <CaretLeft size={13} />
                  </button>
                  <span className="px-2 font-medium text-gray-700">
                    {currentPage} / {pagination.last_page || 1}
                  </span>
                  <button
                    onClick={() => setCurrentPage((p) => Math.min(pagination.last_page, p + 1))}
                    disabled={currentPage >= pagination.last_page || loading}
                    className="p-1 rounded border border-gray-300 text-gray-600 hover:text-gray-900 hover:bg-gray-50 disabled:opacity-40 transition"
                  >
                    <CaretRight size={13} />
                  </button>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Comprehensive View Drawer (Modal) */}
      {selectedBooking && (
        <div className="fixed inset-0 z-50 overflow-hidden flex justify-end bg-black/40 backdrop-blur-2xs transition-opacity animate-in fade-in">
          <div className="relative w-full max-w-xl bg-white h-full shadow-2xl flex flex-col z-10 animate-in slide-in-from-right duration-200">
            {/* Drawer Header */}
            <div className="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-gray-50">
              <div>
                <div className="flex items-center gap-2">
                  <Receipt size={18} className="text-gray-900" />
                  <h3 className="text-base font-bold text-gray-900">
                    {selectedBooking.source === "walk_in" ? "Walk-in" : "Booking"} #{selectedBooking.reference}
                  </h3>
                  {renderSourceBadge(selectedBooking.source)}
                </div>
                <p className="text-xs text-gray-500 mt-0.5">
                  Placed on {selectedBooking.created_at_formatted || "Recently"}
                </p>
              </div>
              <button
                onClick={() => setSelectedBooking(null)}
                className="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-200 transition"
              >
                <X size={18} />
              </button>
            </div>

            {/* Drawer Body */}
            <div className="flex-1 overflow-y-auto p-6 space-y-5">
              {/* Quick Status Bar */}
              <div className="bg-gray-50 rounded-xl p-3.5 border border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                  <span className="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block mb-1">
                    Current Status
                  </span>
                  <div>{renderStatusBadge(selectedBooking.status)}</div>
                </div>

                <div className="flex flex-wrap gap-1.5">
                  {selectedBooking.source === "walk_in" && selectedBooking.status !== "in_progress" && selectedBooking.status !== "completed" && (
                    <button
                      onClick={() => handleUpdateStatus(selectedBooking.id, "in_progress")}
                      disabled={updatingId === selectedBooking.id}
                      className="px-2.5 py-1 text-xs font-semibold rounded bg-violet-600 text-white hover:bg-violet-700 transition"
                    >
                      Start Visit
                    </button>
                  )}
                  {selectedBooking.status !== "accepted" && selectedBooking.source !== "walk_in" && (
                    <button
                      onClick={() => handleUpdateStatus(selectedBooking.id, "accepted")}
                      disabled={updatingId === selectedBooking.id}
                      className="px-2.5 py-1 text-xs font-semibold rounded bg-blue-600 text-white hover:bg-blue-700 transition"
                    >
                      Set Accepted
                    </button>
                  )}
                  {selectedBooking.status !== "completed" && (
                    <button
                      onClick={() => handleUpdateStatus(selectedBooking.id, "completed")}
                      disabled={updatingId === selectedBooking.id}
                      className="px-2.5 py-1 text-xs font-semibold rounded bg-emerald-600 text-white hover:bg-emerald-700 transition"
                    >
                      Set Completed
                    </button>
                  )}
                  {selectedBooking.status !== "cancelled" && (
                    <button
                      onClick={() => handleUpdateStatus(selectedBooking.id, "cancelled")}
                      disabled={updatingId === selectedBooking.id}
                      className="px-2.5 py-1 text-xs font-semibold rounded bg-gray-200 text-gray-800 hover:bg-gray-300 transition"
                    >
                      Cancel
                    </button>
                  )}
                </div>
              </div>

              {/* Customer & Stylist Info */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {/* Customer Card */}
                <div className="bg-white border border-gray-200 rounded-xl p-3.5 shadow-2xs">
                  <div className="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">
                    <span className="flex items-center gap-1.5">
                      <User size={13} className="text-gray-700" />
                      Customer
                    </span>
                    {getWhatsAppLink(selectedBooking) && (
                      <a
                        href={getWhatsAppLink(selectedBooking)!}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-emerald-600 hover:text-emerald-700 flex items-center gap-1 text-[11px] lowercase font-normal"
                        title="Chat on WhatsApp"
                      >
                        <WhatsappLogo size={13} weight="bold" /> WhatsApp
                      </a>
                    )}
                  </div>
                  <div className="font-semibold text-gray-900 text-sm">
                    {selectedBooking.customer_name}
                  </div>
                  {selectedBooking.customer_phone && (
                    <div className="text-xs text-gray-600 mt-1 flex items-center gap-1.5">
                      <Phone size={12} className="text-gray-400" />
                      <a href={`tel:${selectedBooking.customer_phone}`} className="hover:underline">
                        {selectedBooking.customer_phone}
                      </a>
                    </div>
                  )}
                  {selectedBooking.customer_email && (
                    <div className="text-xs text-gray-600 mt-0.5 flex items-center gap-1.5">
                      <EnvelopeSimple size={12} className="text-gray-400" />
                      <span>{selectedBooking.customer_email}</span>
                    </div>
                  )}
                </div>

                {/* Stylist & Schedule Card */}
                <div className="bg-white border border-gray-200 rounded-xl p-3.5 shadow-2xs">
                  <div className="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">
                    <span className="flex items-center gap-1.5">
                      <Scissors size={13} className="text-gray-700" />
                      Stylist & Time
                    </span>
                    <button
                      onClick={() => setShowReschedule(!showReschedule)}
                      className="text-blue-600 hover:text-blue-700 text-[11px] font-semibold lowercase"
                    >
                      {showReschedule ? "close" : "edit"}
                    </button>
                  </div>
                  <div className="font-semibold text-gray-900 text-sm">
                    {selectedBooking.provider_name}
                  </div>
                  <div className="text-xs text-gray-600 mt-1">
                    {selectedBooking.date_formatted} at {selectedBooking.time_formatted}
                  </div>
                  {selectedBooking.provider_phone && (
                    <div className="text-xs text-gray-400 mt-0.5">
                      Stylist Tel: {selectedBooking.provider_phone}
                    </div>
                  )}
                </div>
              </div>

              {/* Inline Reschedule / Reassign Provider Form */}
              {showReschedule && (
                <div className="bg-blue-50/70 border border-blue-200 rounded-xl p-4 space-y-3 animate-in fade-in">
                  <div className="text-xs font-bold text-blue-900 uppercase tracking-wider flex items-center gap-1.5">
                    <ArrowsLeftRight size={14} /> Reschedule or Reassign Stylist
                  </div>
                  <div className="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <div>
                      <label className="block text-[11px] font-medium text-gray-700 mb-1">Date</label>
                      <input
                        type="date"
                        value={editDate}
                        onChange={(e) => setEditDate(e.target.value)}
                        className="w-full px-2.5 py-1.5 bg-white border border-gray-300 rounded text-xs text-gray-900 focus:outline-none"
                      />
                    </div>
                    <div>
                      <label className="block text-[11px] font-medium text-gray-700 mb-1">Time (HH:mm)</label>
                      <input
                        type="time"
                        value={editTime}
                        onChange={(e) => setEditTime(e.target.value)}
                        className="w-full px-2.5 py-1.5 bg-white border border-gray-300 rounded text-xs text-gray-900 focus:outline-none"
                      />
                    </div>
                    <div>
                      <label className="block text-[11px] font-medium text-gray-700 mb-1">Stylist</label>
                      <select
                        value={editProviderId}
                        onChange={(e) => setEditProviderId(e.target.value)}
                        className="w-full px-2.5 py-1.5 bg-white border border-gray-300 rounded text-xs text-gray-900 focus:outline-none"
                      >
                        <option value="">Select stylist</option>
                        {providers.map((p) => (
                          <option key={p.id} value={p.id}>
                            {p.name}
                          </option>
                        ))}
                      </select>
                    </div>
                  </div>
                  <div className="flex justify-end gap-2 pt-1">
                    <button
                      onClick={() => setShowReschedule(false)}
                      className="px-2.5 py-1 text-xs text-gray-600 hover:text-gray-800"
                    >
                      Cancel
                    </button>
                    <button
                      onClick={handleSaveReschedule}
                      disabled={savingReschedule}
                      className="px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition"
                    >
                      {savingReschedule ? "Saving..." : "Save Changes"}
                    </button>
                  </div>
                </div>
              )}

              {/* Location Details (if Home or Pickup) */}
              {selectedBooking.is_home_service ? (
                <div className="bg-rose-50 border border-rose-200 rounded-xl p-3.5 flex items-start gap-3">
                  <House size={18} className="text-rose-600 shrink-0 mt-0.5" />
                  <div>
                    <div className="text-xs font-bold uppercase text-rose-800">
                      Home Service Location
                    </div>
                    <div className="text-xs font-semibold text-rose-950 mt-0.5">
                      {selectedBooking.address || "Address provided"}
                    </div>
                    <p className="text-[11px] text-rose-700 mt-0.5">
                      +100% Home treatment surcharge applied.
                    </p>
                  </div>
                </div>
              ) : selectedBooking.pickup_location_id ? (
                <div className="bg-blue-50 border border-blue-200 rounded-xl p-3.5 flex items-start gap-3">
                  <Car size={18} className="text-blue-600 shrink-0 mt-0.5" />
                  <div>
                    <div className="text-xs font-bold uppercase text-blue-800">
                      Pickup Shuttle Route ({selectedBooking.pickup_location_name})
                    </div>
                    <div className="text-xs font-semibold text-blue-950 mt-0.5">
                      {selectedBooking.pickup_address}
                    </div>
                    <p className="text-[11px] text-blue-700 mt-0.5">
                      Transport fee: {selectedBooking.pickup_fee?.toLocaleString()} RWF included.
                    </p>
                  </div>
                </div>
              ) : null}

              {/* Treatments List */}
              <div className="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
                <div className="bg-gray-50 px-3.5 py-2 border-b border-gray-200 font-bold text-xs uppercase tracking-wider text-gray-700 flex justify-between items-center">
                  <span>Selected Treatments</span>
                  <span>Duration: {selectedBooking.total_duration}m</span>
                </div>
                <div className="divide-y divide-gray-100 p-1.5">
                  {selectedBooking.services.map((s) => (
                    <div
                      key={s.id}
                      className="flex items-center justify-between py-1.5 px-2.5 text-xs"
                    >
                      <div>
                        <span className="font-semibold text-gray-900">{s.name}</span>
                        <span className="text-gray-400 text-[11px] ml-2">({s.duration_minutes}m)</span>
                      </div>
                      <span className="font-semibold tabular-nums text-gray-800">
                        {s.price.toLocaleString()} RWF
                      </span>
                    </div>
                  ))}
                </div>
              </div>

              {/* Financial Statement & Balance */}
              <div className="bg-gray-900 text-white rounded-xl p-4 space-y-2.5">
                <div className="text-xs font-bold uppercase tracking-wider text-gray-400 border-b border-gray-800 pb-1.5 flex justify-between items-center">
                  <span>Financial Statement</span>
                  {selectedBooking.balance > 0 && (
                    <button
                      onClick={() => setShowPaymentForm(!showPaymentForm)}
                      className="text-amber-400 hover:text-amber-300 font-semibold text-[11px] lowercase"
                    >
                      {showPaymentForm ? "hide payment" : "+ record payment"}
                    </button>
                  )}
                </div>

                <div className="space-y-1.5 text-xs text-gray-300">
                  <div className="flex justify-between">
                    <span>Services Subtotal:</span>
                    <span>{selectedBooking.base_service_price?.toLocaleString()} RWF</span>
                  </div>
                  {selectedBooking.is_home_service && (
                    <div className="flex justify-between text-rose-300">
                      <span>Home Service Surcharge (+100%):</span>
                      <span>+{selectedBooking.base_service_price?.toLocaleString()} RWF</span>
                    </div>
                  )}
                  {(selectedBooking.pickup_fee ?? 0) > 0 && (
                    <div className="flex justify-between text-blue-300">
                      <span>Transport / Pickup Fee:</span>
                      <span>+{(selectedBooking.pickup_fee ?? 0).toLocaleString()} RWF</span>
                    </div>
                  )}
                  <div className="flex justify-between font-bold text-white text-sm pt-1.5 border-t border-gray-800">
                    <span>Total Valuation:</span>
                    <span className="text-emerald-400 font-bold tabular-nums">
                      {selectedBooking.total_price.toLocaleString()} RWF
                    </span>
                  </div>
                  <div className="flex justify-between text-xs pt-0.5">
                    <span className="text-gray-400">Total Paid:</span>
                    <span className="font-semibold text-emerald-400 tabular-nums">
                      {selectedBooking.total_paid.toLocaleString()} RWF
                    </span>
                  </div>
                  <div className="flex justify-between text-xs">
                    <span className="text-gray-400">Balance Due:</span>
                    <span
                      className={`font-bold tabular-nums ${
                        selectedBooking.balance > 0 ? "text-amber-400" : "text-gray-400"
                      }`}
                    >
                      {selectedBooking.balance.toLocaleString()} RWF
                    </span>
                  </div>
                </div>
              </div>

              {/* Record Payment Form Modal in Drawer */}
              {showPaymentForm && (
                <div className="bg-emerald-50/70 border border-emerald-200 rounded-xl p-3.5 space-y-3 animate-in fade-in">
                  <div className="text-xs font-bold text-emerald-900 uppercase tracking-wider flex items-center gap-1.5">
                    <Money size={14} /> Record Desk / In-Salon Payment
                  </div>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div>
                      <label className="block text-[11px] font-medium text-gray-700 mb-1">
                        Amount (RWF)
                      </label>
                      <input
                        type="number"
                        value={payAmount}
                        onChange={(e) => setPayAmount(e.target.value)}
                        placeholder="Amount in RWF"
                        className="w-full px-2.5 py-1.5 bg-white border border-gray-300 rounded text-xs text-gray-900 focus:outline-none"
                      />
                    </div>
                    <div>
                      <label className="block text-[11px] font-medium text-gray-700 mb-1">
                        Method
                      </label>
                      <select
                        value={payMethod}
                        onChange={(e) => setPayMethod(e.target.value)}
                        className="w-full px-2.5 py-1.5 bg-white border border-gray-300 rounded text-xs text-gray-900 focus:outline-none"
                      >
                        <option value="cash">Desk Cash</option>
                        <option value="pos_card">Salon POS (Card)</option>
                        <option value="momo">Desk Mobile Money (MoMo)</option>
                      </select>
                    </div>
                  </div>
                  <div className="flex justify-end gap-2 pt-1">
                    <button
                      onClick={() => setShowPaymentForm(false)}
                      className="px-2.5 py-1 text-xs text-gray-600 hover:text-gray-800"
                    >
                      Cancel
                    </button>
                    <button
                      onClick={handleRecordPayment}
                      disabled={payingState || !payAmount || Number(payAmount) <= 0}
                      className="px-3 py-1 bg-emerald-700 text-white text-xs font-semibold rounded hover:bg-emerald-800 transition"
                    >
                      {payingState ? "Recording..." : "Record Payment"}
                    </button>
                  </div>
                </div>
              )}

              {/* Transactions List */}
              <div className="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
                <div className="bg-gray-50 px-3.5 py-2 border-b border-gray-200 font-bold text-xs uppercase tracking-wider text-gray-700">
                  Transactions ({selectedBooking.payments.length})
                </div>
                <div className="divide-y divide-gray-100">
                  {selectedBooking.payments.length === 0 ? (
                    <div className="p-3 text-center text-xs text-gray-500">
                      No payment transactions recorded yet.
                    </div>
                  ) : (
                    selectedBooking.payments.map((p) => (
                      <div
                        key={p.id}
                        className="p-2.5 text-xs flex items-center justify-between hover:bg-gray-50"
                      >
                        <div>
                          <div className="font-semibold text-gray-900 flex items-center gap-1.5">
                            <span>Ref: {p.reference || `#${p.id}`}</span>
                            <span className="text-[10px] uppercase font-bold text-gray-500 bg-gray-100 px-1 py-0.2 rounded">
                              {p.method}
                            </span>
                          </div>
                          <div className="text-[11px] text-gray-400 mt-0.5">
                            {p.paid_at || "Recent"}
                          </div>
                        </div>
                        <div className="text-right">
                          <div className="font-bold tabular-nums text-emerald-700">
                            +{p.amount.toLocaleString()} RWF
                          </div>
                          <span
                            className={`text-[10px] font-bold uppercase ${
                              p.status === "paid" ? "text-emerald-600" : "text-amber-600"
                            }`}
                          >
                            {p.status}
                          </span>
                        </div>
                      </div>
                    ))
                  )}
                </div>
              </div>

              {/* Client Notes */}
              {selectedBooking.notes && (
                <div className="bg-amber-50/60 border border-amber-200 rounded-xl p-3.5">
                  <div className="text-xs font-bold uppercase tracking-wider text-amber-800 mb-0.5">
                    Client Notes
                  </div>
                  <p className="text-xs text-amber-950 italic">
                    &ldquo;{selectedBooking.notes}&rdquo;
                  </p>
                </div>
              )}
            </div>

            {/* Drawer Footer Actions */}
            <div className="px-6 py-3.5 bg-gray-50 border-t border-gray-200 flex flex-wrap items-center justify-between gap-2">
              <a
                href={`/api/v1/admin/bookings/${selectedBooking.id}/receipt-pdf`}
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-semibold hover:bg-gray-800 transition shadow-xs"
              >
                <DownloadSimple size={13} />
                <span>PDF Receipt</span>
              </a>

              <div className="flex items-center gap-2">
                <button
                  onClick={() => window.print()}
                  className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 transition"
                >
                  <Printer size={13} />
                  <span>Print</span>
                </button>
                <button
                  onClick={() => setSelectedBooking(null)}
                  className="px-3 py-1.5 bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-300 transition"
                >
                  Close
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
      {showWalkInModal && (
        <AdminWalkInModal
          onClose={() => setShowWalkInModal(false)}
          onCreated={handleWalkInCreated}
        />
      )}
    </div>
  );
}
