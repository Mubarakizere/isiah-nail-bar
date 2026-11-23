"use client";

import { useState, useEffect } from "react";
import Image from "next/image";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { motion, AnimatePresence } from "framer-motion";
import {
  CalendarCheck,
  Clock,
  User,
  ShieldCheck,
  CheckCircle,
  Receipt,
  PhoneCall,
  XCircle,
  Plus,
  ArrowRight,
  ArrowClockwise,
  SignOut,
  House,
  CreditCard,
  Crown,
  WarningCircle,
  FileArrowDown,
  Info,
  Calendar,
  Wallet,
  X,
  Gear,
  DeviceMobile,
  Check,
  Sparkle,
  Gift,
} from "@phosphor-icons/react";
import { useAuth } from "@/lib/auth-context";
import ClientLoyaltyCard from "./ClientLoyaltyCard";

interface BookingServiceItem {
  id: number;
  name: string;
  price: number;
  duration_minutes: number;
}

interface BookingPaymentItem {
  id: number;
  amount: string | number;
  method: string;
  status: string;
  reference: string;
  paid_at?: string;
}

interface ClientBooking {
  id: number;
  reference: string;
  date: string;
  time: string;
  status: string;
  is_home_service: boolean;
  notes?: string;
  is_past: boolean;
  services: BookingServiceItem[];
  provider: {
    id: number;
    name: string;
  };
  payments: BookingPaymentItem[];
  total_price: number;
  total_paid: number;
  balance: number;
  payment_status: "paid_in_full" | "deposit_paid" | "unpaid";
}

interface DashboardStats {
  total_rituals: number;
  completed_rituals: number;
  sanctuary_points: number;
  tier: string;
  total_spent: number;
}

export default function ClientDashboard() {
  const router = useRouter();
  const { user, loading: authLoading, logout, updateUser } = useAuth();

  const [activeTab, setActiveTab] = useState<"appointments" | "profile" | "loyalty">("appointments");
  const [filter, setFilter] = useState<"upcoming" | "past" | "all">("upcoming");
  const [bookings, setBookings] = useState<ClientBooking[]>([]);
  const [stats, setStats] = useState<DashboardStats>({
    total_rituals: 0,
    completed_rituals: 0,
    sanctuary_points: 0,
    tier: "Sanctuary Guest",
    total_spent: 0,
  });
  const [loading, setLoading] = useState(true);

  // Action states
  const [cancellingId, setCancellingId] = useState<number | null>(null);
  const [downloadingPdfId, setDownloadingPdfId] = useState<number | null>(null);
  const [payingBookingId, setPayingBookingId] = useState<number | null>(null);
  const [feedbackMsg, setFeedbackMsg] = useState<{ type: "success" | "error"; text: string } | null>(null);

  // Modals & Panels
  const [confirmCancelModal, setConfirmCancelModal] = useState<ClientBooking | null>(null);
  const [selectedDetailBooking, setSelectedDetailBooking] = useState<ClientBooking | null>(null);
  const [calendarModalBooking, setCalendarModalBooking] = useState<ClientBooking | null>(null);

  // Profile Form state
  const [profileName, setProfileName] = useState("");
  const [profilePhone, setProfilePhone] = useState("");
  const [savingProfile, setSavingProfile] = useState(false);
  const [profileSuccessMsg, setProfileSuccessMsg] = useState("");
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
  }, []);

  // Sync profile form when user loads
  useEffect(() => {
    if (user) {
      setProfileName(user.name || "");
      setProfilePhone(user.phone || "");
    }
  }, [user]);

  // Redirect to login if user is not authenticated
  useEffect(() => {
    if (!authLoading && user?.role === "provider") {
      router.replace("/dashboard/provider");
    } else if (!authLoading && user?.role === "admin") {
      router.replace("/admin");
    } else if (!authLoading && !user) {
      router.push("/login?redirect=/dashboard");
    }
  }, [user, authLoading, router]);

  // Fetch customer bookings strictly from Database API
  const fetchBookings = async () => {
    if (!user?.email) return;
    setLoading(true);
    try {
      const res = await fetch("/api/v1/customer/bookings", {
        cache: "no-store",
      });
      const data = await res.json();

      if (res.ok && data.success) {
        setBookings(data.bookings || []);
        if (data.stats) {
          setStats(data.stats);
        }
        if (data.customer?.phone && !user.phone) {
          updateUser({ phone: data.customer.phone });
        }
      } else {
        setBookings([]);
      }
    } catch {
      setFeedbackMsg({
        type: "error",
        text: "Could not synchronize with the database. Please check your connection.",
      });
    } finally {
      setLoading(false);
    }
  };

  const handlePayBalance = async (booking: ClientBooking) => {
    if (payingBookingId !== null) return;
    setPayingBookingId(booking.id);
    setFeedbackMsg(null);

    try {
      const response = await fetch(`/api/v1/customer/bookings/${booking.id}/pay-remaining`, {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify({ idempotency_key: crypto.randomUUID(), payment_method: "momo" }),
      });
      const result = await response.json();

      if (result.reference && result.access_token) {
        sessionStorage.setItem(`isaiah_booking_access_${result.reference}`, result.access_token);
      }

      if (result.status === "failed" && result.reference) {
        router.push(`/booking/failed?reference=${encodeURIComponent(result.reference)}&reason=declined`);
        return;
      }

      if (!response.ok || !result.success || !result.reference) {
        throw new Error(result.message || "Could not start the balance payment.");
      }

      router.push(`/booking/payment?reference=${encodeURIComponent(result.reference)}`);
    } catch (error) {
      setFeedbackMsg({
        type: "error",
        text: error instanceof Error ? error.message : "Could not start the balance payment. Please try again.",
      });
    } finally {
      setPayingBookingId(null);
    }
  };

  useEffect(() => {
    if (user?.email) {
      fetchBookings();
    }
  }, [user?.email]);

  // Download PDF Receipt from DomPDF Backend
  const handleDownloadPdf = async (booking: ClientBooking) => {
    setDownloadingPdfId(booking.id);
    setFeedbackMsg(null);
    try {
      const res = await fetch(`/api/v1/customer/bookings/${booking.id}/receipt-pdf`);
      if (!res.ok) {
        throw new Error("Failed to generate PDF");
      }
      const blob = await res.blob();
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = url;
      link.download = `isaiah-receipt-${booking.reference}.pdf`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);

      setFeedbackMsg({
        type: "success",
        text: `Official PDF Receipt for #${booking.reference} downloaded successfully.`,
      });
    } catch {
      setFeedbackMsg({
        type: "error",
        text: "Unable to generate PDF receipt. Please contact our concierge desk.",
      });
    } finally {
      setDownloadingPdfId(null);
    }
  };

  // Google Calendar URL Generator
  const getGoogleCalendarUrl = (b: ClientBooking) => {
    const title = encodeURIComponent(
      `Isaiah Nail Bar — ${b.services.map((s) => s.name).join(", ")}`
    );
    const details = encodeURIComponent(
      `Sanctuary Reservation Ref: ${b.reference}\nStylist: ${b.provider.name}\nTotal: RWF ${b.total_price.toLocaleString()}\nConcierge: +250 790 395 169\nWebsite: https://isaiahnailbar.com`
    );
    const location = encodeURIComponent(
      b.is_home_service
        ? "Client Residence (Home Service)"
        : "Isaiah Nail Bar Sanctuary, KG 4 Roundabout, Kigali, Rwanda"
    );

    const [year, month, day] = b.date.split("-");
    const [hour, minute] = b.time.split(":");
    const startStr = `${year}${month}${day}T${hour}${minute}00`;

    const totalMin = b.services.reduce((acc, s) => acc + (s.duration_minutes || 60), 0);
    const totalMinutesFromMidnight = Number(hour) * 60 + Number(minute) + totalMin;
    const endH = String(Math.floor(totalMinutesFromMidnight / 60) % 24).padStart(2, "0");
    const endM = String(totalMinutesFromMidnight % 60).padStart(2, "0");
    const endStr = `${year}${month}${day}T${endH}${endM}00`;

    return `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${title}&dates=${startStr}/${endStr}&details=${details}&location=${location}`;
  };

  // Apple / Outlook (.ics) download
  const handleDownloadICS = (b: ClientBooking) => {
    const title = `Isaiah Nail Bar — ${b.services.map((s) => s.name).join(", ")}`;
    const description = `Appointment Reference: ${b.reference}\\nStylist: ${b.provider.name}\\nTotal: RWF ${b.total_price.toLocaleString()}`;
    const location = b.is_home_service
      ? "Client Residence (Home Service)"
      : "Isaiah Nail Bar Sanctuary, Kigali, Rwanda";

    const cleanDate = b.date.replace(/-/g, "");
    const cleanTime = b.time.replace(/:/g, "") + "00";
    const startIso = `${cleanDate}T${cleanTime}`;

    const totalMinutes = b.services.reduce((acc, s) => acc + (s.duration_minutes || 60), 0);
    const [h, m] = b.time.split(":").map(Number);
    const startDate = new Date();
    startDate.setHours(h, m, 0, 0);
    const endDate = new Date(startDate.getTime() + totalMinutes * 60000);
    const endHour = String(endDate.getHours()).padStart(2, "0");
    const endMin = String(endDate.getMinutes()).padStart(2, "0");
    const endIso = `${cleanDate}T${endHour}${endMin}00`;

    const icsData = [
      "BEGIN:VCALENDAR",
      "VERSION:2.0",
      "PRODID:-//Isaiah Nail Bar//Appointment Booking//EN",
      "CALSCALE:GREGORIAN",
      "METHOD:PUBLISH",
      "BEGIN:VEVENT",
      `SUMMARY:${title}`,
      `DESCRIPTION:${description}`,
      `LOCATION:${location}`,
      `DTSTART:${startIso}`,
      `DTEND:${endIso}`,
      `STATUS:CONFIRMED`,
      "END:VEVENT",
      "END:VCALENDAR",
    ].join("\r\n");

    const blob = new Blob([icsData], { type: "text/calendar;charset=utf-8" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", `isaiah-ritual-${b.reference}.ics`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);

    setCalendarModalBooking(null);
    setFeedbackMsg({
      type: "success",
      text: "Calendar file downloaded! Tap or double-click to add to Apple Calendar or Outlook.",
    });
  };

  // Handle Cancel Appointment in DB
  const handleCancelBooking = async (booking: ClientBooking) => {
    setCancellingId(booking.id);
    setFeedbackMsg(null);
    try {
      const res = await fetch(`/api/v1/customer/bookings/${booking.id}/cancel`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
      });
      const data = await res.json();
      if (res.ok && data.success) {
        setBookings((prev) =>
          prev.map((b) => (b.id === booking.id ? { ...b, status: "cancelled", is_past: true } : b))
        );
        if (selectedDetailBooking?.id === booking.id) {
          setSelectedDetailBooking((prev) => (prev ? { ...prev, status: "cancelled", is_past: true } : null));
        }
        setFeedbackMsg({
          type: "success",
          text: "Your appointment has been cancelled successfully.",
        });
      } else {
        setFeedbackMsg({
          type: "error",
          text: data.message || "Failed to cancel appointment. Please contact our concierge.",
        });
      }
    } catch {
      setFeedbackMsg({
        type: "error",
        text: "Connection error. Please try again or call concierge directly.",
      });
    } finally {
      setCancellingId(null);
      setConfirmCancelModal(null);
    }
  };

  // Handle Profile Update in DB
  const handleSaveProfile = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!user?.email) return;

    setSavingProfile(true);
    setProfileSuccessMsg("");
    setFeedbackMsg(null);

    try {
      const res = await fetch("/api/v1/customer/profile", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          email: user.email,
          name: profileName.trim(),
          phone: profilePhone.trim(),
        }),
      });

      const data = await res.json();

      if (res.ok && data.success) {
        updateUser({
          name: profileName.trim(),
          phone: profilePhone.trim(),
        });
        setProfileSuccessMsg("Your sanctuary profile has been updated in our database.");
      } else {
        setFeedbackMsg({
          type: "error",
          text: data.message || "Failed to update profile. Please check the fields.",
        });
      }
    } catch {
      setFeedbackMsg({
        type: "error",
        text: "Connection error while updating profile.",
      });
    } finally {
      setSavingProfile(false);
    }
  };

  // Filtered lists
  const upcomingBookings = bookings.filter((b) => !b.is_past && b.status !== "cancelled");
  const pastBookings = bookings.filter((b) => b.is_past || b.status === "cancelled" || b.status === "completed");

  const displayedBookings =
    filter === "upcoming"
      ? upcomingBookings
      : filter === "past"
      ? pastBookings
      : bookings;

  if (!mounted) {
    return null;
  }

  if (authLoading || (!user && loading)) {
    return (
      <div
        suppressHydrationWarning
        className="min-h-screen bg-[#FAF7F2] flex items-center justify-center"
      >
        <div suppressHydrationWarning className="flex flex-col items-center gap-3">
          <div
            suppressHydrationWarning
            className="w-10 h-10 border-2 border-[#B8975A] border-t-transparent rounded-full animate-spin"
          />
          <p className="font-serif text-sm text-[#5C5550]">Connecting to Sanctuary Database...</p>
        </div>
      </div>
    );
  }

  return (
    <div
      suppressHydrationWarning
      className="min-h-screen bg-[#FAF7F2] text-[#221C1A] py-8 sm:py-12 px-4 sm:px-6 lg:px-12"
    >
      <div className="max-w-6xl mx-auto space-y-8">
        {/* ========================================================= */}
        {/* Top Header: Welcome, Tier Status & Actions */}
        {/* ========================================================= */}
        <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-6 pb-6 border-b border-[#EAE4D9]">
          <div className="space-y-1.5">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FAF0E4] border border-[#E8C99B]/50 text-[#8C6D37] text-xs font-medium">
              <Crown size={14} weight="bold" />
              <span>{stats.tier}</span>
              <span className="text-[#8E857F]">&bull;</span>
              <span>{stats.sanctuary_points} Sanctuary Pts</span>
            </div>

            <h1 className="font-serif text-3xl sm:text-4xl text-[#221C1A] font-normal tracking-tight">
              Bonjour, {user?.name || "Cher Guest"}
            </h1>

            <p className="text-xs sm:text-sm text-[#6E6660] font-light max-w-xl">
              Manage your upcoming rituals, download official PDF receipts, and schedule bespoke treatments.
            </p>
          </div>

          <div className="flex items-center gap-3 shrink-0">
            {/* View Tab Switcher */}
            <div className="inline-flex p-1 bg-white border border-[#EAE4D9] rounded-full">
              <button
                type="button"
                onClick={() => setActiveTab("appointments")}
                className={`px-4 py-2 rounded-full text-xs font-semibold tracking-wide transition-all ${
                  activeTab === "appointments"
                    ? "bg-[#221C1A] text-white shadow-xs"
                    : "text-[#6E6660] hover:text-[#221C1A]"
                }`}
              >
                Reservations
              </button>
              <button
                type="button"
                onClick={() => setActiveTab("loyalty")}
                className={`px-4 py-2 rounded-full text-xs font-semibold tracking-wide transition-all flex items-center gap-1.5 ${
                  activeTab === "loyalty"
                    ? "bg-[#221C1A] text-white shadow-xs"
                    : "text-[#6E6660] hover:text-[#221C1A]"
                }`}
              >
                <Sparkle size={13} weight="fill" className={activeTab === "loyalty" ? "text-[#E8C99B]" : "text-[#B8975A]"} />
                <span>Loyalty & Perks</span>
              </button>
              <button
                type="button"
                onClick={() => setActiveTab("profile")}
                className={`px-4 py-2 rounded-full text-xs font-semibold tracking-wide transition-all ${
                  activeTab === "profile"
                    ? "bg-[#221C1A] text-white shadow-xs"
                    : "text-[#6E6660] hover:text-[#221C1A]"
                }`}
              >
                My Profile
              </button>
            </div>

            <Link
              href="/booking"
              className="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-all duration-300 shadow-sm cursor-pointer"
            >
              <Plus size={15} weight="bold" />
              <span>Book Ritual</span>
            </Link>

            <button
              type="button"
              onClick={() => {
                logout();
                router.push("/login");
              }}
              className="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-full text-xs font-semibold text-[#8E857F] hover:text-[#C0392B] bg-white border border-[#EAE4D9] hover:border-[#F5B7B1] transition-colors cursor-pointer"
              title="Sign Out"
            >
              <SignOut size={16} />
              <span className="hidden sm:inline">Sign Out</span>
            </button>
          </div>
        </div>

        {/* Global Feedback Alert */}
        {feedbackMsg && (
          <motion.div
            initial={{ opacity: 0, y: -10 }}
            animate={{ opacity: 1, y: 0 }}
            className={`p-4 rounded-2xl text-xs flex items-center justify-between gap-3 shadow-xs ${
              feedbackMsg.type === "success"
                ? "bg-[#E6F4EA] border border-[#34A853]/30 text-[#2D8E47]"
                : "bg-[#FDEDEC] border border-[#F5B7B1] text-[#C0392B]"
            }`}
          >
            <div className="flex items-center gap-2">
              {feedbackMsg.type === "success" ? (
                <CheckCircle size={18} weight="fill" />
              ) : (
                <WarningCircle size={18} weight="fill" />
              )}
              <span>{feedbackMsg.text}</span>
            </div>
            <button
              onClick={() => setFeedbackMsg(null)}
              className="text-xs opacity-70 hover:opacity-100 font-bold p-1 cursor-pointer"
            >
              <X size={14} />
            </button>
          </motion.div>
        )}

        {/* ========================================================= */}
        {/* TAB 1: APPOINTMENTS & RESERVATIONS */}
        {/* ========================================================= */}
        {activeTab === "appointments" && (
          <div className="space-y-8">
            {/* Quick Metrics Grid */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
              <div className="p-5 rounded-2xl bg-white border border-[#EAE4D9] shadow-2xs space-y-2">
                <div className="flex items-center justify-between">
                  <span className="text-[10px] font-semibold uppercase tracking-wider text-[#8E857F]">
                    Upcoming Rituals
                  </span>
                  <div className="w-8 h-8 rounded-full bg-[#FAF7F2] text-[#B8975A] flex items-center justify-center">
                    <CalendarCheck size={16} />
                  </div>
                </div>
                <p className="font-serif text-2xl sm:text-3xl font-normal text-[#221C1A]">
                  {upcomingBookings.length}
                </p>
                <p className="text-[11px] text-[#6E6660]">
                  {upcomingBookings.length > 0
                    ? `Next: ${upcomingBookings[0].date}`
                    : "No active bookings"}
                </p>
              </div>

              <div className="p-5 rounded-2xl bg-white border border-[#EAE4D9] shadow-2xs space-y-2">
                <div className="flex items-center justify-between">
                  <span className="text-[10px] font-semibold uppercase tracking-wider text-[#8E857F]">
                    Completed Visits
                  </span>
                  <div className="w-8 h-8 rounded-full bg-[#FAF7F2] text-[#34A853] flex items-center justify-center">
                    <CheckCircle size={16} />
                  </div>
                </div>
                <p className="font-serif text-2xl sm:text-3xl font-normal text-[#221C1A]">
                  {stats.completed_rituals}
                </p>
                <p className="text-[11px] text-[#6E6660]">Archived care sessions</p>
              </div>

              <div className="p-5 rounded-2xl bg-white border border-[#EAE4D9] shadow-2xs space-y-2">
                <div className="flex items-center justify-between">
                  <span className="text-[10px] font-semibold uppercase tracking-wider text-[#8E857F]">
                    Sanctuary Points
                  </span>
                  <div className="w-8 h-8 rounded-full bg-[#FAF0E4] text-[#B8975A] flex items-center justify-center">
                    <Crown size={16} />
                  </div>
                </div>
                <p className="font-serif text-2xl sm:text-3xl font-normal text-[#B8975A]">
                  {stats.sanctuary_points}
                </p>
                <p className="text-[11px] text-[#6E6660]">Redeemable on next ritual</p>
              </div>

              <div className="p-5 rounded-2xl bg-white border border-[#EAE4D9] shadow-2xs space-y-2">
                <div className="flex items-center justify-between">
                  <span className="text-[10px] font-semibold uppercase tracking-wider text-[#8E857F]">
                    Total Investment
                  </span>
                  <div className="w-8 h-8 rounded-full bg-[#FAF7F2] text-[#221C1A] flex items-center justify-center">
                    <CreditCard size={16} />
                  </div>
                </div>
                <p className="font-serif text-xl sm:text-2xl font-normal text-[#221C1A] truncate">
                  RWF {stats.total_spent.toLocaleString()}
                </p>
                <p className="text-[11px] text-[#6E6660]">{stats.tier}</p>
              </div>
            </div>

            {/* Filter Pills & Section Header */}
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <div>
                <span className="text-[10px] font-semibold uppercase tracking-[0.24em] text-[#B8975A] block mb-0.5">
                  Live Reservations Database
                </span>
                <h2 className="font-serif text-2xl text-[#221C1A] font-normal">
                  Your Appointments
                </h2>
              </div>

              <div className="inline-flex p-1 bg-white border border-[#EAE4D9] rounded-full self-start">
                <button
                  type="button"
                  onClick={() => setFilter("upcoming")}
                  className={`px-4 py-1.5 rounded-full text-xs font-semibold tracking-wide transition-all ${
                    filter === "upcoming"
                      ? "bg-[#221C1A] text-white shadow-xs"
                      : "text-[#6E6660] hover:text-[#221C1A]"
                  }`}
                >
                  Upcoming ({upcomingBookings.length})
                </button>
                <button
                  type="button"
                  onClick={() => setFilter("past")}
                  className={`px-4 py-1.5 rounded-full text-xs font-semibold tracking-wide transition-all ${
                    filter === "past"
                      ? "bg-[#221C1A] text-white shadow-xs"
                      : "text-[#6E6660] hover:text-[#221C1A]"
                  }`}
                >
                  Past History ({pastBookings.length})
                </button>
                <button
                  type="button"
                  onClick={() => setFilter("all")}
                  className={`px-4 py-1.5 rounded-full text-xs font-semibold tracking-wide transition-all ${
                    filter === "all"
                      ? "bg-[#221C1A] text-white shadow-xs"
                      : "text-[#6E6660] hover:text-[#221C1A]"
                  }`}
                >
                  All ({bookings.length})
                </button>
              </div>
            </div>

            {/* Bookings List */}
            {loading ? (
              <div className="py-16 text-center space-y-3">
                <div className="w-8 h-8 border-2 border-[#B8975A] border-t-transparent rounded-full animate-spin mx-auto" />
                <p className="text-xs text-[#8E857F]">Querying reservations from database...</p>
              </div>
            ) : displayedBookings.length === 0 ? (
              <div className="py-16 px-6 text-center rounded-3xl bg-white border border-dashed border-[#EAE4D9] space-y-4">
                <div className="w-14 h-14 rounded-2xl bg-[#FAF7F2] border border-[#EAE4D9] text-[#B8975A] flex items-center justify-center mx-auto">
                  <CalendarCheck size={28} />
                </div>
                <div className="max-w-md mx-auto space-y-1.5">
                  <h3 className="font-serif text-lg text-[#221C1A]">
                    No {filter === "past" ? "past" : "upcoming"} reservations recorded
                  </h3>
                  <p className="text-xs text-[#6E6660] font-light leading-relaxed">
                    {filter === "past"
                      ? "You haven't completed any appointments yet. Once your session is concluded, receipts and treatment archives will appear here."
                      : "You currently have no scheduled appointments. Treat yourself to our signature Russian precision care."}
                  </p>
                </div>
                <Link
                  href="/booking"
                  className="inline-flex items-center gap-2 px-6 py-3 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-colors shadow-xs"
                >
                  <span>Reserve Appointment</span>
                  <ArrowRight size={14} />
                </Link>
              </div>
            ) : (
              <div className="space-y-4">
                {displayedBookings.map((b) => {
                  const isCancelled = b.status === "cancelled" || b.status === "declined";
                  const isAccepted = b.status === "accepted";
                  const isPending = b.status === "pending";
                  const isCompleted = b.status === "completed";
                  const hasDueBalance = b.balance > 0 && !isCancelled;

                  return (
                    <motion.div
                      key={b.id}
                      layout
                      initial={{ opacity: 0, y: 10 }}
                      animate={{ opacity: 1, y: 0 }}
                      className="p-6 sm:p-7 rounded-2xl bg-white border border-[#EAE4D9] shadow-2xs hover:border-[#D5CBBF] transition-all space-y-5"
                    >
                      {/* Top Row: Date, Reference & Status Badge */}
                      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-[#FAF7F2]">
                        <div className="flex items-center gap-3.5">
                          <div className="w-12 h-12 rounded-xl bg-[#FAF7F2] border border-[#EAE4D9] flex flex-col items-center justify-center text-[#221C1A] shrink-0">
                            <span className="text-[10px] font-semibold uppercase text-[#8E857F] leading-none">
                              {new Date(b.date).toLocaleDateString("en-US", { month: "short" })}
                            </span>
                            <span className="font-serif text-lg font-bold leading-none mt-0.5">
                              {new Date(b.date).toLocaleDateString("en-US", { day: "numeric" })}
                            </span>
                          </div>

                          <div>
                            <div className="flex items-center gap-2">
                              <h3 className="font-serif text-lg font-normal text-[#221C1A]">
                                {new Date(b.date).toLocaleDateString("en-US", {
                                  weekday: "long",
                                  month: "long",
                                  day: "numeric",
                                  year: "numeric",
                                })}
                              </h3>
                              {b.is_home_service && (
                                <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#FAF0E4] text-[#8C6D37] text-[10px] font-semibold">
                                  <House size={11} /> Home Service
                                </span>
                              )}
                            </div>
                            <div className="flex items-center gap-3 text-xs text-[#6E6660] mt-0.5">
                              <span className="flex items-center gap-1">
                                <Clock size={13} className="text-[#8E857F]" />
                                {b.time}
                              </span>
                              <span>&bull;</span>
                              <span className="font-mono text-[11px] text-[#8E857F]">
                                Ref: {b.reference}
                              </span>
                            </div>
                          </div>
                        </div>

                        {/* Status Badges */}
                        <div className="flex items-center gap-2 self-start sm:self-center">
                          {isAccepted && (
                            <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#E6F4EA] text-[#2D8E47] border border-[#34A853]/20">
                              <CheckCircle size={13} weight="fill" />
                              <span>Confirmed</span>
                            </span>
                          )}
                          {isPending && (
                            <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#FEF7E0] text-[#B06000] border border-[#F9AB00]/25">
                              <Clock size={13} weight="fill" />
                              <span>Pending Confirmation</span>
                            </span>
                          )}
                          {isCompleted && (
                            <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#F1EFEA] text-[#5C5550] border border-[#EAE4D9]">
                              <CheckCircle size={13} />
                              <span>Completed</span>
                            </span>
                          )}
                          {isCancelled && (
                            <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#FDEDEC] text-[#C0392B] border border-[#F5B7B1]/30">
                              <XCircle size={13} weight="fill" />
                              <span>Cancelled</span>
                            </span>
                          )}
                        </div>
                      </div>

                      {/* Middle Row: Services & Stylist */}
                      <div className="grid grid-cols-1 md:grid-cols-12 gap-4">
                        {/* Services breakdown (7 cols) */}
                        <div className="md:col-span-7 space-y-2">
                          <span className="text-[10px] font-semibold uppercase tracking-wider text-[#8E857F] block mb-1">
                            Reserved Treatments
                          </span>
                          <div className="space-y-1.5">
                            {b.services.map((srv) => (
                              <div
                                key={srv.id}
                                className="flex items-center justify-between text-xs py-1.5 px-3 rounded-lg bg-[#FAF7F2]"
                              >
                                <span className="font-medium text-[#221C1A]">{srv.name}</span>
                                <div className="flex items-center gap-3 text-[#6E6660]">
                                  <span className="text-[11px]">{srv.duration_minutes}m</span>
                                  <span className="font-semibold text-[#221C1A]">
                                    RWF {srv.price.toLocaleString()}
                                  </span>
                                </div>
                              </div>
                            ))}
                          </div>

                          {b.notes && (
                            <p className="text-[11px] text-[#6E6660] italic pt-1">
                              &ldquo;{b.notes}&rdquo;
                            </p>
                          )}
                        </div>

                        {/* Stylist & Financial Overview (5 cols) */}
                        <div className="md:col-span-5 flex flex-col justify-between p-4 rounded-xl bg-[#FAF7F2] border border-[#EAE4D9]">
                          <div>
                            <span className="text-[10px] font-semibold uppercase tracking-wider text-[#8E857F] block mb-1.5">
                              Assigned Master Stylist
                            </span>
                            <div className="flex items-center gap-2.5">
                              <div className="w-8 h-8 rounded-full bg-white border border-[#B8975A]/40 text-[#B8975A] flex items-center justify-center font-bold text-xs">
                                {b.provider?.name?.charAt(0) || "S"}
                              </div>
                              <div>
                                <p className="text-xs font-semibold text-[#221C1A]">
                                  {b.provider?.name || "Sanctuary Specialist"}
                                </p>
                                <p className="text-[10px] text-[#8E857F]">
                                  European Autoclave Certified
                                </p>
                              </div>
                            </div>
                          </div>

                          <div className="pt-3 border-t border-[#EAE4D9]/80 mt-3 space-y-1 text-xs">
                            <div className="flex items-center justify-between">
                              <span className="text-[#8E857F]">Total Price:</span>
                              <span className="font-semibold text-[#221C1A]">
                                RWF {b.total_price.toLocaleString()}
                              </span>
                            </div>
                            <div className="flex items-center justify-between">
                              <span className="text-[#8E857F]">Paid to date:</span>
                              <span className="font-semibold text-[#2D8E47]">
                                RWF {b.total_paid.toLocaleString()}
                              </span>
                            </div>
                            {b.balance > 0 && (
                              <div className="flex items-center justify-between pt-1 border-t border-[#EAE4D9]">
                                <span className="text-[#B06000] font-semibold">Remaining Balance:</span>
                                <span className="font-serif font-bold text-[#B06000]">
                                  RWF {b.balance.toLocaleString()}
                                </span>
                              </div>
                            )}
                          </div>
                        </div>
                      </div>

                      {/* Bottom Row: Actions */}
                      <div className="pt-3 border-t border-[#FAF7F2] flex flex-wrap items-center justify-between gap-3 text-xs">
                        {/* Financial Status badge */}
                        <div className="flex items-center gap-2">
                          {b.balance === 0 ? (
                            <span className="inline-flex items-center gap-1 text-[11px] font-semibold text-[#2D8E47]">
                              <CheckCircle size={14} weight="fill" />
                              <span>Paid in Full</span>
                            </span>
                          ) : (
                            <span className="inline-flex items-center gap-1 text-[11px] font-semibold text-[#B06000]">
                              <Info size={14} />
                              <span>Due at arrival: RWF {b.balance.toLocaleString()}</span>
                            </span>
                          )}
                        </div>

                        {/* Action Buttons */}
                        <div className="flex flex-wrap items-center gap-2 ml-auto">
                          {/* PAY BALANCE BUTTON (If balance > 0) */}
                          {hasDueBalance && (
                            <button
                              type="button"
                              disabled={payingBookingId !== null}
                              onClick={() => void handlePayBalance(b)}
                              className="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold text-white bg-[#221C1A] hover:bg-[#B8975A] transition-colors shadow-xs disabled:cursor-wait disabled:opacity-60"
                            >
                              {payingBookingId === b.id ? <ArrowClockwise size={14} className="animate-spin" /> : <Wallet size={14} />}
                              <span>{payingBookingId === b.id ? "Preparing payment…" : `Pay Balance (RWF ${b.balance.toLocaleString()})`}</span>
                            </button>
                          )}

                          {/* DOWNLOAD OFFICIAL PDF RECEIPT */}
                          <button
                            type="button"
                            disabled={downloadingPdfId === b.id}
                            onClick={() => handleDownloadPdf(b)}
                            className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium text-[#4A423D] bg-white border border-[#EAE4D9] hover:border-[#221C1A] transition-colors cursor-pointer disabled:opacity-50"
                            title="Download Official PDF Invoice / Receipt"
                          >
                            <FileArrowDown size={14} />
                            <span>
                              {downloadingPdfId === b.id ? "Generating PDF..." : "PDF Receipt"}
                            </span>
                          </button>

                          {/* ADD TO CALENDAR (Google / Apple popover trigger) */}
                          {!b.is_past && !isCancelled && (
                            <button
                              type="button"
                              onClick={() => setCalendarModalBooking(b)}
                              className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium text-[#4A423D] bg-white border border-[#EAE4D9] hover:border-[#221C1A] transition-colors cursor-pointer"
                              title="Add to Google Calendar or Apple / Outlook"
                            >
                              <Calendar size={14} />
                              <span>Add to Calendar</span>
                            </button>
                          )}

                          {/* VIEW DETAILS DRAWER */}
                          <button
                            type="button"
                            onClick={() => setSelectedDetailBooking(b)}
                            className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium text-[#4A423D] bg-white border border-[#EAE4D9] hover:border-[#221C1A] transition-colors cursor-pointer"
                          >
                            <Receipt size={14} />
                            <span>View Details</span>
                          </button>

                          {/* 1-CLICK REBOOK FOR COMPLETED APPOINTMENTS */}
                          {isCompleted && (
                            <Link
                              href="/booking"
                              className="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold text-white bg-[#221C1A] hover:bg-[#B8975A] transition-colors"
                            >
                              <ArrowClockwise size={14} />
                              <span>Book Again</span>
                            </Link>
                          )}

                          {/* CANCEL BUTTON (Active bookings) */}
                          {!b.is_past && !isCancelled && (
                            <button
                              type="button"
                              disabled={cancellingId === b.id}
                              onClick={() => setConfirmCancelModal(b)}
                              className="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium text-[#C0392B] hover:bg-[#FDEDEC] transition-colors cursor-pointer disabled:opacity-50"
                            >
                              <XCircle size={14} />
                              <span>Cancel</span>
                            </button>
                          )}
                        </div>
                      </div>
                    </motion.div>
                  );
                })}
              </div>
            )}
          </div>
        )}

        {/* ========================================================= */}
        {/* TAB 2: MY SANCTUARY PROFILE (Direct DB update) */}
        {/* ========================================================= */}
        {activeTab === "profile" && (
          <div className="max-w-2xl mx-auto space-y-6">
            <div className="p-8 rounded-3xl bg-white border border-[#EAE4D9] shadow-2xs space-y-6">
              <div className="pb-4 border-b border-[#FAF7F2]">
                <span className="text-[10px] font-semibold uppercase tracking-[0.22em] text-[#B8975A] block mb-1">
                  Patron Preferences
                </span>
                <h2 className="font-serif text-2xl text-[#221C1A]">Sanctuary Guest Profile</h2>
                <p className="text-xs text-[#6E6660] font-light mt-1">
                  Update your contact coordinates stored in our client database to ensure seamless booking confirmations and SMS appointment reminders.
                </p>
              </div>

              {profileSuccessMsg && (
                <div className="p-3.5 rounded-2xl bg-[#E6F4EA] border border-[#34A853]/30 text-[#2D8E47] text-xs flex items-center gap-2">
                  <CheckCircle size={17} weight="fill" />
                  <span>{profileSuccessMsg}</span>
                </div>
              )}

              <form onSubmit={handleSaveProfile} className="space-y-4">
                {/* Full Name */}
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                    Full Name
                  </label>
                  <div className="relative">
                    <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8E857F]">
                      <User size={17} />
                    </div>
                    <input
                      type="text"
                      required
                      value={profileName}
                      onChange={(e) => setProfileName(e.target.value)}
                      className="w-full pl-10 pr-4 py-2.5 bg-[#FAF7F2] border border-[#EAE4D9] focus:border-[#221C1A] rounded-xl text-xs sm:text-sm text-[#221C1A] outline-none transition-colors"
                    />
                  </div>
                </div>

                {/* Email (Readonly) */}
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                    Email Address (Account Identifier)
                  </label>
                  <input
                    type="email"
                    disabled
                    value={user?.email || ""}
                    className="w-full px-4 py-2.5 bg-[#FAF7F2] border border-[#EAE4D9] rounded-xl text-xs sm:text-sm text-[#8E857F] outline-none cursor-not-allowed font-mono"
                  />
                  <span className="text-[10px] text-[#8E857F] block mt-1">
                    Primary login identity cannot be modified online. Contact concierge for changes.
                  </span>
                </div>

                {/* Mobile Phone */}
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                    Rwandan Mobile Phone (SMS Reminders & Concierge)
                  </label>
                  <div className="relative">
                    <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8E857F]">
                      <DeviceMobile size={17} />
                    </div>
                    <input
                      type="tel"
                      value={profilePhone}
                      onChange={(e) => setProfilePhone(e.target.value)}
                      placeholder="+250 788 000 000"
                      className="w-full pl-10 pr-4 py-2.5 bg-[#FAF7F2] border border-[#EAE4D9] focus:border-[#221C1A] rounded-xl text-xs sm:text-sm text-[#221C1A] outline-none transition-colors font-mono"
                    />
                  </div>
                </div>

                {/* Save button */}
                <div className="pt-2">
                  <button
                    type="submit"
                    disabled={savingProfile}
                    className="w-full py-3 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                  >
                    <Check size={16} />
                    <span>{savingProfile ? "Saving to Database..." : "Save Profile Changes"}</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* ========================================================= */}
        {/* TAB 3: LOYALTY PASSPORT & STAMP CARDS                     */}
        {/* ========================================================= */}
        {activeTab === "loyalty" && (
          <ClientLoyaltyCard />
        )}

        {/* ========================================================= */}
        {/* Concierge & Medical Sterilization Banner */}
        {/* ========================================================= */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
          <div className="p-6 rounded-2xl bg-white border border-[#EAE4D9] flex flex-col justify-between space-y-4">
            <div className="space-y-1">
              <span className="text-[10px] font-semibold uppercase tracking-wider text-[#B8975A]">
                Direct Concierge Desk
              </span>
              <h3 className="font-serif text-lg text-[#221C1A]">
                Need bespoke styling or private party reservation?
              </h3>
              <p className="text-xs text-[#6E6660] font-light leading-relaxed">
                Our front desk concierge is available daily to assist with appointment adjustments, VIP group bookings, and specialized Russian cuticular care questions.
              </p>
            </div>

            <div className="flex items-center gap-4 pt-2">
              <a
                href="tel:0790395169"
                className="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold text-[#221C1A] bg-[#FAF7F2] border border-[#EAE4D9] hover:border-[#221C1A] transition-colors"
              >
                <PhoneCall size={14} className="text-[#B8975A]" />
                <span>+250 790 395 169</span>
              </a>

              <a
                href="https://wa.me/250790395169"
                target="_blank"
                rel="noreferrer"
                className="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold text-white bg-[#221C1A] hover:bg-[#B8975A] transition-colors"
              >
                <span>WhatsApp Concierge</span>
              </a>
            </div>
          </div>

          <div className="p-6 rounded-2xl bg-white border border-[#EAE4D9] flex flex-col justify-between space-y-4">
            <div className="space-y-1">
              <div className="inline-flex items-center gap-1.5 text-xs text-[#34A853] font-semibold">
                <ShieldCheck size={16} />
                <span>Autoclave Sterilization Protocol</span>
              </div>
              <h3 className="font-serif text-lg text-[#221C1A]">
                European Medical-Grade Sanctuary Standards
              </h3>
              <p className="text-xs text-[#6E6660] font-light leading-relaxed">
                Every metal tool undergoes medical autoclave sterilization in single-use sealed pouches opened in your presence. Nail files and buffers are strictly single-use.
              </p>
            </div>

            <div className="flex items-center gap-3 text-xs text-[#8E857F] pt-2">
              <span>&bull; HEMA-Free Gels</span>
              <span>&bull; Disposable Files</span>
              <span>&bull; Pure Organic Cuticle Oils</span>
            </div>
          </div>
        </div>
      </div>

      {/* ========================================================= */}
      {/* MODAL 1: ADD TO CALENDAR (Google Web Intent vs .ics) */}
      {/* ========================================================= */}
      <AnimatePresence>
        {calendarModalBooking && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <motion.div
              initial={{ opacity: 0, scale: 0.95 }}
              animate={{ opacity: 1, scale: 1 }}
              exit={{ opacity: 0, scale: 0.95 }}
              className="w-full max-w-md bg-white rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6"
            >
              <div className="flex items-center justify-between pb-3 border-b border-[#FAF7F2]">
                <div className="flex items-center gap-2.5">
                  <div className="w-9 h-9 rounded-xl bg-[#FAF0E4] text-[#B8975A] flex items-center justify-center">
                    <Calendar size={18} />
                  </div>
                  <div>
                    <h3 className="font-serif text-lg text-[#221C1A]">Add to Calendar</h3>
                    <p className="text-[11px] text-[#8E857F]">
                      {calendarModalBooking.date} at {calendarModalBooking.time}
                    </p>
                  </div>
                </div>
                <button
                  type="button"
                  onClick={() => setCalendarModalBooking(null)}
                  className="p-1 rounded-full text-[#8E857F] hover:text-[#221C1A] hover:bg-[#FAF7F2] transition-colors cursor-pointer"
                >
                  <X size={18} />
                </button>
              </div>

              <div className="space-y-3">
                {/* Option 1: 1-Click Google Calendar */}
                <a
                  href={getGoogleCalendarUrl(calendarModalBooking)}
                  target="_blank"
                  rel="noopener noreferrer"
                  onClick={() => setCalendarModalBooking(null)}
                  className="w-full p-4 rounded-2xl bg-[#FAF7F2] border border-[#EAE4D9] hover:border-[#221C1A] flex items-center justify-between transition-all group"
                >
                  <div className="flex items-center gap-3">
                    <div className="w-10 h-10 rounded-xl bg-white border border-[#EAE4D9] flex items-center justify-center font-bold text-[#4285F4] text-xs">
                      G
                    </div>
                    <div className="text-left">
                      <p className="text-xs font-semibold text-[#221C1A] group-hover:text-[#B8975A] transition-colors">
                        Google Calendar
                      </p>
                      <p className="text-[11px] text-[#6E6660]">
                        1-Click direct web link (no download required)
                      </p>
                    </div>
                  </div>
                  <ArrowRight size={15} className="text-[#8E857F] group-hover:translate-x-1 transition-transform" />
                </a>

                {/* Option 2: Apple Calendar / Outlook / iCal */}
                <button
                  type="button"
                  onClick={() => handleDownloadICS(calendarModalBooking)}
                  className="w-full p-4 rounded-2xl bg-[#FAF7F2] border border-[#EAE4D9] hover:border-[#221C1A] flex items-center justify-between transition-all group cursor-pointer"
                >
                  <div className="flex items-center gap-3">
                    <div className="w-10 h-10 rounded-xl bg-white border border-[#EAE4D9] flex items-center justify-center text-[#221C1A] text-xs">
                      <FileArrowDown size={18} />
                    </div>
                    <div className="text-left">
                      <p className="text-xs font-semibold text-[#221C1A] group-hover:text-[#B8975A] transition-colors">
                        Apple Calendar / Outlook (.ics)
                      </p>
                      <p className="text-[11px] text-[#6E6660]">
                        Downloads iCal event file with import instructions
                      </p>
                    </div>
                  </div>
                  <ArrowRight size={15} className="text-[#8E857F] group-hover:translate-x-1 transition-transform" />
                </button>
              </div>

              <p className="text-[11px] text-center text-[#8E857F]">
                Event includes salon address, assigned stylist, and reservation reference.
              </p>
            </motion.div>
          </div>
        )}
      </AnimatePresence>

      {/* ========================================================= */}
      {/* MODAL 2: FULL RESERVATION DETAILS (show.blade.php equivalent) */}
      {/* ========================================================= */}
      <AnimatePresence>
        {selectedDetailBooking && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <motion.div
              initial={{ opacity: 0, scale: 0.95 }}
              animate={{ opacity: 1, scale: 1 }}
              exit={{ opacity: 0, scale: 0.95 }}
              className="w-full max-w-xl max-h-[90vh] overflow-y-auto bg-white rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6"
            >
              <div className="flex items-start justify-between pb-4 border-b border-[#FAF7F2]">
                <div>
                  <span className="text-[10px] font-semibold uppercase tracking-wider text-[#B8975A] block mb-1">
                    Reservation Details
                  </span>
                  <h3 className="font-serif text-2xl text-[#221C1A]">
                    Order #{selectedDetailBooking.reference}
                  </h3>
                  <p className="text-xs text-[#8E857F] mt-0.5">
                    {selectedDetailBooking.is_home_service
                      ? "Exclusive Home Service Appointment"
                      : "Isaiah Nail Bar Sanctuary (Kigali)"}
                  </p>
                </div>
                <button
                  type="button"
                  onClick={() => setSelectedDetailBooking(null)}
                  className="p-1 rounded-full text-[#8E857F] hover:text-[#221C1A] hover:bg-[#FAF7F2] transition-colors cursor-pointer"
                >
                  <X size={20} />
                </button>
              </div>

              {/* Status & Schedule Banner */}
              <div className="grid grid-cols-2 gap-3 p-4 rounded-2xl bg-[#FAF7F2] border border-[#EAE4D9]">
                <div>
                  <span className="text-[10px] uppercase font-semibold text-[#8E857F] block">
                    Scheduled Date & Time
                  </span>
                  <p className="font-serif font-bold text-sm text-[#221C1A]">
                    {new Date(selectedDetailBooking.date).toLocaleDateString("en-US", {
                      weekday: "short",
                      month: "short",
                      day: "numeric",
                    })}
                  </p>
                  <p className="text-xs text-[#6E6660]">at {selectedDetailBooking.time}</p>
                </div>

                <div>
                  <span className="text-[10px] uppercase font-semibold text-[#8E857F] block">
                    Stylist
                  </span>
                  <p className="font-serif font-bold text-sm text-[#221C1A]">
                    {selectedDetailBooking.provider?.name || "Sanctuary Specialist"}
                  </p>
                  <p className="text-xs text-[#6E6660]">Autoclave Certified</p>
                </div>
              </div>

              {/* Itemized Services */}
              <div className="space-y-2">
                <span className="text-xs font-semibold uppercase tracking-wider text-[#221C1A] block">
                  Reserved Treatments
                </span>
                <div className="space-y-1.5">
                  {selectedDetailBooking.services.map((srv) => (
                    <div
                      key={srv.id}
                      className="flex items-center justify-between text-xs py-2 px-3 rounded-xl bg-[#FAF7F2]"
                    >
                      <div>
                        <p className="font-semibold text-[#221C1A]">{srv.name}</p>
                        <p className="text-[10px] text-[#8E857F]">{srv.duration_minutes} minutes duration</p>
                      </div>
                      <span className="font-serif font-bold text-sm text-[#221C1A]">
                        RWF {srv.price.toLocaleString()}
                      </span>
                    </div>
                  ))}
                </div>
              </div>

              {/* Payment Summary */}
              <div className="p-4 rounded-2xl bg-[#221C1A] text-white space-y-2 text-xs">
                <div className="flex items-center justify-between text-white/70">
                  <span>Services Total:</span>
                  <span>RWF {selectedDetailBooking.total_price.toLocaleString()}</span>
                </div>
                <div className="flex items-center justify-between text-white/70">
                  <span>Paid Amount:</span>
                  <span className="text-[#34A853] font-semibold">
                    RWF {selectedDetailBooking.total_paid.toLocaleString()}
                  </span>
                </div>
                <div className="flex items-center justify-between pt-2 border-t border-white/10 text-sm">
                  <span className="font-semibold">Remaining Due:</span>
                  <span className="font-serif font-bold text-lg text-[#E8C99B]">
                    RWF {selectedDetailBooking.balance.toLocaleString()}
                  </span>
                </div>
              </div>

              {/* Modal Actions */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                <button
                  type="button"
                  disabled={downloadingPdfId === selectedDetailBooking.id}
                  onClick={() => handleDownloadPdf(selectedDetailBooking)}
                  className="py-3 px-4 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-[#FAF7F2] border border-[#EAE4D9] hover:bg-[#EAE4D9] transition-colors flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                >
                  <FileArrowDown size={15} />
                  <span>
                    {downloadingPdfId === selectedDetailBooking.id ? "Generating PDF..." : "Download Official PDF"}
                  </span>
                </button>

                {selectedDetailBooking.balance > 0 && selectedDetailBooking.status !== "cancelled" ? (
                  <button
                    type="button"
                    disabled={payingBookingId !== null}
                    onClick={() => void handlePayBalance(selectedDetailBooking)}
                    className="py-3 px-4 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-colors flex items-center justify-center gap-2 disabled:cursor-wait disabled:opacity-60"
                  >
                    {payingBookingId === selectedDetailBooking.id ? <ArrowClockwise size={15} className="animate-spin" /> : <Wallet size={15} />}
                    <span>{payingBookingId === selectedDetailBooking.id ? "Preparing payment…" : `Pay Balance (RWF ${selectedDetailBooking.balance.toLocaleString()})`}</span>
                  </button>
                ) : (
                  <button
                    type="button"
                    onClick={() => {
                      setSelectedDetailBooking(null);
                      setCalendarModalBooking(selectedDetailBooking);
                    }}
                    className="py-3 px-4 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-colors flex items-center justify-center gap-2 cursor-pointer"
                  >
                    <Calendar size={15} />
                    <span>Add to Calendar</span>
                  </button>
                )}
              </div>
            </motion.div>
          </div>
        )}
      </AnimatePresence>

      {/* ========================================================= */}
      {/* MODAL 3: CANCEL CONFIRMATION */}
      {/* ========================================================= */}
      <AnimatePresence>
        {confirmCancelModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <motion.div
              initial={{ opacity: 0, scale: 0.95 }}
              animate={{ opacity: 1, scale: 1 }}
              exit={{ opacity: 0, scale: 0.95 }}
              className="w-full max-w-md bg-white rounded-3xl p-6 sm:p-8 shadow-2xl space-y-5 text-center"
            >
              <div className="w-12 h-12 rounded-2xl bg-[#FDEDEC] text-[#C0392B] flex items-center justify-center mx-auto">
                <WarningCircle size={26} weight="fill" />
              </div>

              <div className="space-y-2">
                <h3 className="font-serif text-xl text-[#221C1A]">Cancel Appointment?</h3>
                <p className="text-xs text-[#6E6660] leading-relaxed">
                  Are you sure you wish to cancel your reservation for{" "}
                  <strong className="text-[#221C1A]">
                    {new Date(confirmCancelModal.date).toLocaleDateString("en-US", {
                      weekday: "short",
                      month: "short",
                      day: "numeric",
                    })}
                  </strong>{" "}
                  at <strong className="text-[#221C1A]">{confirmCancelModal.time}</strong>?
                </p>
              </div>

              <div className="p-3 rounded-xl bg-[#FAF7F2] text-[11px] text-[#6E6660] border border-[#EAE4D9]">
                If an online deposit was completed, our concierge will issue a salon credit note per etiquette policies.
              </div>

              <div className="grid grid-cols-2 gap-3 pt-2">
                <button
                  type="button"
                  disabled={cancellingId !== null}
                  onClick={() => setConfirmCancelModal(null)}
                  className="py-2.5 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-[#FAF7F2] hover:bg-[#EAE4D9] transition-colors cursor-pointer"
                >
                  Keep Booking
                </button>
                <button
                  type="button"
                  disabled={cancellingId !== null}
                  onClick={() => handleCancelBooking(confirmCancelModal)}
                  className="py-2.5 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#C0392B] hover:bg-[#A93226] transition-colors cursor-pointer disabled:opacity-50"
                >
                  {cancellingId !== null ? "Cancelling..." : "Yes, Cancel"}
                </button>
              </div>
            </motion.div>
          </div>
        )}
      </AnimatePresence>
    </div>
  );
}
