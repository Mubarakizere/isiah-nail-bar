"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import {
  ArrowClockwise,
  CalendarBlank,
  Check,
  Clock,
  CurrencyDollar,
  EnvelopeSimple,
  SignOut,
  User,
  WarningCircle,
  X,
} from "@phosphor-icons/react";
import { useAuth } from "@/lib/auth-context";

type BookingStatus = "pending" | "accepted" | "in_progress" | "completed" | "declined" | string;

interface ProviderBooking {
  id: number;
  reference: string;
  date: string;
  time: string;
  status: BookingStatus;
  customer: string;
  customer_email: string;
  customer_phone: string;
  services: Array<{ id: number; name: string; price: number; duration_minutes: number }>;
  notes?: string | null;
}

interface ProviderOverview {
  provider: { id: number; name: string };
  stats: { total: number; pending: number; upcoming: number; completed: number; revenue: number };
  data: ProviderBooking[];
}

const initialStats: ProviderOverview["stats"] = {
  total: 0,
  pending: 0,
  upcoming: 0,
  completed: 0,
  revenue: 0,
};

const statusStyle: Record<string, string> = {
  pending: "bg-amber-50 text-amber-800 ring-amber-200",
  accepted: "bg-blue-50 text-blue-800 ring-blue-200",
  in_progress: "bg-violet-50 text-violet-800 ring-violet-200",
  completed: "bg-emerald-50 text-emerald-800 ring-emerald-200",
  declined: "bg-gray-100 text-gray-600 ring-gray-200",
};

function formatDate(date: string) {
  const parsed = new Date(`${date}T00:00:00`);
  return Number.isNaN(parsed.getTime())
    ? date
    : new Intl.DateTimeFormat("en", { weekday: "short", day: "numeric", month: "short", year: "numeric" }).format(parsed);
}

function formatMoney(amount: number) {
  return `RWF ${new Intl.NumberFormat("en").format(amount || 0)}`;
}

export default function ProviderDashboard() {
  const router = useRouter();
  const { user, loading: authLoading, logout } = useAuth();
  const [overview, setOverview] = useState<ProviderOverview | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [busyId, setBusyId] = useState<number | null>(null);
  const [filter, setFilter] = useState("all");
  const [notice, setNotice] = useState<{ kind: "success" | "error"; text: string } | null>(null);

  useEffect(() => {
    if (authLoading) return;
    if (!user) router.replace("/login?redirect=/dashboard/provider");
    else if (user.role === "admin") router.replace("/admin");
    else if (user.role !== "provider") router.replace("/dashboard");
  }, [authLoading, user, router]);

  const loadOverview = useCallback(async (quiet = false) => {
    if (!quiet) setLoading(true);
    else setRefreshing(true);
    try {
      const response = await fetch("/api/v1/provider/overview", {
        credentials: "same-origin",
        cache: "no-store",
        headers: { Accept: "application/json" },
      });
      const result = await response.json();
      if (!response.ok || !result.success) {
        throw new Error(result.message || "Could not load your appointments.");
      }
      setOverview(result as ProviderOverview);
      setNotice(null);
    } catch (error) {
      setNotice({ kind: "error", text: error instanceof Error ? error.message : "Could not load your appointments." });
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    if (!authLoading && user?.role === "provider") void loadOverview();
  }, [authLoading, user?.role, loadOverview]);

  const filteredBookings = useMemo(() => {
    const bookings = overview?.data ?? [];
    return filter === "all" ? bookings : bookings.filter((booking) => booking.status === filter);
  }, [filter, overview?.data]);

  const updateStatus = async (booking: ProviderBooking, action: "accept" | "decline" | "complete") => {
    setBusyId(booking.id);
    setNotice(null);
    try {
      const response = await fetch(`/api/v1/provider/bookings/${booking.id}/status`, {
        method: "POST",
        credentials: "same-origin",
        headers: { Accept: "application/json", "Content-Type": "application/json" },
        body: JSON.stringify({ action }),
      });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.message || "Could not update this appointment.");
      setNotice({ kind: "success", text: result.message || "Appointment updated." });
      await loadOverview(true);
    } catch (error) {
      setNotice({ kind: "error", text: error instanceof Error ? error.message : "Could not update this appointment." });
    } finally {
      setBusyId(null);
    }
  };

  const signOut = () => {
    logout();
    router.replace("/login");
  };

  const stats = overview?.stats ?? initialStats;
  const providerName = overview?.provider.name || user?.name || "Provider";

  return (
    <main className="min-h-screen bg-[#f8f6f2] text-[#24211e]">
      <header className="border-b border-[#e8e1d8] bg-white">
        <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
          <Link href="/" className="text-sm font-semibold tracking-[0.18em] text-[#876b3e]">ISAIAH NAIL BAR</Link>
          <div className="flex items-center gap-3">
            <span className="hidden text-sm text-[#69635d] sm:inline">{providerName}</span>
            <button onClick={signOut} className="inline-flex items-center gap-2 rounded-lg border border-[#e8e1d8] px-3 py-2 text-sm hover:bg-[#f8f6f2]">
              <SignOut size={17} /> Sign out
            </button>
          </div>
        </div>
      </header>

      <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div className="flex flex-wrap items-end justify-between gap-4">
          <div>
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-[#9a7b4c]">Provider workspace</p>
            <h1 className="mt-2 font-serif text-3xl sm:text-4xl">Welcome, {providerName}</h1>
            <p className="mt-2 text-sm text-[#756e67]">Review your appointments and keep your clients up to date.</p>
          </div>
          <button onClick={() => void loadOverview(true)} disabled={refreshing} className="inline-flex items-center gap-2 rounded-lg border border-[#ddd3c6] bg-white px-4 py-2.5 text-sm font-medium hover:bg-[#fdfbf8] disabled:opacity-60">
            <ArrowClockwise size={17} className={refreshing ? "animate-spin" : ""} /> Refresh
          </button>
        </div>

        {notice && (
          <div className={`mt-6 flex items-start gap-2 rounded-xl border px-4 py-3 text-sm ${notice.kind === "error" ? "border-red-200 bg-red-50 text-red-800" : "border-emerald-200 bg-emerald-50 text-emerald-800"}`}>
            {notice.kind === "error" ? <WarningCircle size={18} className="mt-0.5 shrink-0" /> : <Check size={18} className="mt-0.5 shrink-0" />}
            <span>{notice.text}</span>
          </div>
        )}

        <section className="mt-7 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
          {[
            { label: "All appointments", value: stats.total, icon: CalendarBlank },
            { label: "Needs your response", value: stats.pending, icon: Clock },
            { label: "Upcoming", value: stats.upcoming, icon: User },
            { label: "Completed earnings", value: formatMoney(stats.revenue), icon: CurrencyDollar },
          ].map((item) => (
            <div key={item.label} className="rounded-2xl border border-[#e8e1d8] bg-white p-5 shadow-sm">
              <div className="flex items-center justify-between text-sm text-[#756e67]">
                <span>{item.label}</span><item.icon size={19} className="text-[#9a7b4c]" />
              </div>
              <p className="mt-4 text-2xl font-semibold">{item.value}</p>
            </div>
          ))}
        </section>

        <section className="mt-8 overflow-hidden rounded-2xl border border-[#e8e1d8] bg-white shadow-sm">
          <div className="flex flex-wrap items-center justify-between gap-3 border-b border-[#eee9e2] px-5 py-4 sm:px-6">
            <div>
              <h2 className="text-lg font-semibold">Appointments</h2>
              <p className="mt-1 text-sm text-[#756e67]">Your latest 100 assigned appointments.</p>
            </div>
            <select value={filter} onChange={(event) => setFilter(event.target.value)} className="rounded-lg border border-[#ddd3c6] bg-white px-3 py-2 text-sm outline-none focus:border-[#9a7b4c]">
              <option value="all">All statuses</option>
              <option value="pending">Pending</option>
              <option value="accepted">Accepted</option>
              <option value="in_progress">In progress</option>
              <option value="completed">Completed</option>
              <option value="declined">Declined</option>
            </select>
          </div>

          {loading ? (
            <div className="flex min-h-48 items-center justify-center text-sm text-[#756e67]">Loading your appointments…</div>
          ) : filteredBookings.length === 0 ? (
            <div className="flex min-h-48 flex-col items-center justify-center px-5 text-center">
              <CalendarBlank size={30} className="text-[#b8a78e]" />
              <p className="mt-3 font-medium">No appointments found</p>
              <p className="mt-1 text-sm text-[#756e67]">There are no appointments in this status right now.</p>
            </div>
          ) : (
            <div className="divide-y divide-[#eee9e2]">
              {filteredBookings.map((booking) => {
                const total = booking.services.reduce((sum, service) => sum + Number(service.price || 0), 0);
                return (
                  <article key={booking.id} className="px-5 py-5 sm:px-6">
                    <div className="flex flex-wrap items-start justify-between gap-4">
                      <div className="min-w-0">
                        <div className="flex flex-wrap items-center gap-2">
                          <h3 className="font-semibold">{booking.customer}</h3>
                          <span className={`rounded-full px-2.5 py-1 text-xs font-medium capitalize ring-1 ring-inset ${statusStyle[booking.status] || statusStyle.pending}`}>
                            {booking.status.replaceAll("_", " ")}
                          </span>
                        </div>
                        <p className="mt-1 text-xs text-[#817a72]">Booking {booking.reference}</p>
                      </div>
                      <p className="text-sm font-semibold">{formatMoney(total)}</p>
                    </div>

                    <div className="mt-4 grid gap-3 text-sm text-[#655f58] sm:grid-cols-2 lg:grid-cols-3">
                      <p className="flex items-center gap-2"><CalendarBlank size={17} className="text-[#9a7b4c]" />{formatDate(booking.date)} at {booking.time || "Time not set"}</p>
                      <p className="flex items-center gap-2"><EnvelopeSimple size={17} className="text-[#9a7b4c]" />{booking.customer_email || "No email provided"}</p>
                      {booking.customer_phone && <p className="flex items-center gap-2"><User size={17} className="text-[#9a7b4c]" />{booking.customer_phone}</p>}
                    </div>

                    <div className="mt-4">
                      <p className="text-xs font-semibold uppercase tracking-wide text-[#8b8175]">Services</p>
                      <div className="mt-2 flex flex-wrap gap-2">
                        {booking.services.map((service) => (
                          <span key={service.id} className="rounded-lg bg-[#f8f6f2] px-3 py-1.5 text-sm">
                            {service.name}<span className="ml-2 text-[#817a72]">{service.duration_minutes} min</span>
                          </span>
                        ))}
                      </div>
                      {booking.notes && <p className="mt-3 text-sm text-[#756e67]">Note: {booking.notes}</p>}
                    </div>

                    {(booking.status === "pending" || booking.status === "accepted" || booking.status === "in_progress") && (
                      <div className="mt-5 flex flex-wrap gap-2">
                        {booking.status === "pending" && <>
                          <button disabled={busyId === booking.id} onClick={() => void updateStatus(booking, "accept")} className="inline-flex items-center gap-2 rounded-lg bg-[#24211e] px-4 py-2 text-sm font-medium text-white hover:bg-[#3c3731] disabled:opacity-50"><Check size={16} /> Accept</button>
                          <button disabled={busyId === booking.id} onClick={() => void updateStatus(booking, "decline")} className="inline-flex items-center gap-2 rounded-lg border border-[#ddd3c6] px-4 py-2 text-sm font-medium hover:bg-[#f8f6f2] disabled:opacity-50"><X size={16} /> Decline</button>
                        </>}
                        {(booking.status === "accepted" || booking.status === "in_progress") && <button disabled={busyId === booking.id} onClick={() => void updateStatus(booking, "complete")} className="inline-flex items-center gap-2 rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800 disabled:opacity-50"><Check size={16} /> Mark completed</button>}
                        {busyId === booking.id && <span className="self-center text-sm text-[#756e67]">Saving…</span>}
                      </div>
                    )}
                  </article>
                );
              })}
            </div>
          )}
        </section>
      </div>
    </main>
  );
}
