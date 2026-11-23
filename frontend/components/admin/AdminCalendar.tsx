"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import Link from "next/link";
import {
  ArrowsClockwise,
  Calendar,
  CalendarBlank,
  CaretLeft,
  CaretRight,
  Check,
  Clock,
  CurrencyDollar,
  EnvelopeSimple,
  MapPin,
  Phone,
  Plus,
  Scissors,
  User,
  X,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";
import AdminWalkInModal from "@/components/admin/AdminWalkInModal";

type ProviderOption = { id: number; name: string; active: boolean; service_ids: number[] };
type CalendarService = { id: number; name: string; price: number; duration_minutes: number };
type CalendarEvent = {
  id: number;
  reference: string;
  date: string;
  time: string;
  end_time: string;
  duration_minutes: number;
  status: string;
  source: "website" | "admin_manual" | "walk_in" | "legacy";
  provider_id: number | null;
  provider_name: string;
  customer_name: string;
  customer_email: string | null;
  customer_phone: string | null;
  services: CalendarService[];
  total_price: number;
  total_paid: number;
  balance: number;
  payment_status: string;
  payment_option: string | null;
  deposit_amount: number;
  notes: string | null;
  is_home_service: boolean;
  address: string | null;
  pickup_location_name: string | null;
  pickup_fee: number;
};
type BlockedSlot = { id: number; provider_id: number; provider_name: string; date: string; time: string };
type WorkingHour = {
  provider_id: number;
  day_of_week: number;
  start_time: string | null;
  end_time: string | null;
  break_start: string | null;
  break_end: string | null;
  is_day_off: boolean;
  is_holiday: boolean;
};

const inputClass = "w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10";

function localDateKey(date: Date): string {
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`;
}

function dateFromKey(value: string): Date {
  const [year, month, day] = value.split("-").map(Number);
  return new Date(year, month - 1, day, 12);
}

function todayKey(): string {
  return localDateKey(new Date());
}

function dateLabel(value: string, options: Intl.DateTimeFormatOptions = { weekday: "short", month: "short", day: "numeric" }): string {
  return new Intl.DateTimeFormat("en", options).format(dateFromKey(value));
}

function addDays(value: string, amount: number): string {
  const date = dateFromKey(value);
  date.setDate(date.getDate() + amount);
  return localDateKey(date);
}

function money(value: number): string {
  return `${Math.round(Number(value || 0)).toLocaleString()} RWF`;
}

function sourceLabel(source: CalendarEvent["source"]): string {
  return source === "walk_in" ? "Walk-in" : source === "admin_manual" ? "Manual" : source === "website" ? "Online" : "Legacy";
}

function statusLabel(status: string): string {
  return status.replaceAll("_", " ").replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function statusTone(status: string): string {
  if (status === "accepted" || status === "confirmed") return "border-blue-200 bg-blue-50 text-blue-700";
  if (status === "in_progress") return "border-amber-200 bg-amber-50 text-amber-800";
  if (status === "completed") return "border-emerald-200 bg-emerald-50 text-emerald-700";
  if (status === "pending") return "border-orange-200 bg-orange-50 text-orange-700";
  if (status === "cancelled" || status === "declined") return "border-gray-200 bg-gray-100 text-gray-600";
  return "border-gray-200 bg-white text-gray-700";
}

async function readResponse(response: Response): Promise<any> {
  const text = await response.text();
  let result: any = {};
  try {
    result = text ? JSON.parse(text) : {};
  } catch {
    throw new Error(response.status >= 500 ? "The server could not complete this action." : "The server returned an unreadable response.");
  }
  if (!response.ok || result.success === false) {
    const validation = result.errors ? Object.values(result.errors).flat().join(" ") : "";
    throw new Error(validation || result.message || "The request could not be completed.");
  }
  return result;
}

export default function AdminCalendar() {
  const [anchorDate, setAnchorDate] = useState(todayKey);
  const [view, setView] = useState<"week" | "day">("week");
  const [providerFilter, setProviderFilter] = useState("all");
  const [sourceFilter, setSourceFilter] = useState("all");
  const [statusFilter, setStatusFilter] = useState("all");
  const [providers, setProviders] = useState<ProviderOption[]>([]);
  const [events, setEvents] = useState<CalendarEvent[]>([]);
  const [blockedSlots, setBlockedSlots] = useState<BlockedSlot[]>([]);
  const [workingHours, setWorkingHours] = useState<WorkingHour[]>([]);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState("");
  const [refreshToken, setRefreshToken] = useState(0);
  const [selectedEvent, setSelectedEvent] = useState<CalendarEvent | null>(null);
  const [showReschedule, setShowReschedule] = useState(false);
  const [rescheduleDate, setRescheduleDate] = useState("");
  const [rescheduleTime, setRescheduleTime] = useState("");
  const [rescheduleProviderId, setRescheduleProviderId] = useState("");
  const [paymentAmount, setPaymentAmount] = useState("");
  const [paymentMethod, setPaymentMethod] = useState("cash");
  const [showWalkIn, setShowWalkIn] = useState(false);
  const [walkInDate, setWalkInDate] = useState(todayKey);
  const [walkInTime, setWalkInTime] = useState("");
  const [walkInProviderId, setWalkInProviderId] = useState<number | null>(null);
  const [busy, setBusy] = useState(false);
  const [toast, setToast] = useState<{ message: string; error?: boolean } | null>(null);

  const range = useMemo(() => {
    if (view === "day") return { start: anchorDate, end: anchorDate };
    const start = dateFromKey(anchorDate);
    start.setDate(start.getDate() - ((start.getDay() + 6) % 7));
    const firstDay = localDateKey(start);
    return { start: firstDay, end: addDays(firstDay, 6) };
  }, [anchorDate, view]);

  const refresh = useCallback(() => setRefreshToken((current) => current + 1), []);

  useEffect(() => {
    const requestedProvider = new URLSearchParams(window.location.search).get("provider_id");
    if (requestedProvider && /^\d+$/.test(requestedProvider)) setProviderFilter(requestedProvider);
  }, []);

  useEffect(() => {
    const controller = new AbortController();
    const loadCalendar = async () => {
      setLoading(true);
      setLoadError("");
      try {
        const params = new URLSearchParams({ start: range.start, end: range.end });
        if (providerFilter !== "all") params.set("provider_id", providerFilter);
        const response = await fetch(`/api/v1/admin/calendar?${params.toString()}`, {
          headers: { Accept: "application/json" },
          cache: "no-store",
          signal: controller.signal,
        });
        const result = await readResponse(response);
        setProviders(Array.isArray(result.providers) ? result.providers : []);
        setEvents(Array.isArray(result.events) ? result.events : []);
        setBlockedSlots(Array.isArray(result.blocked_slots) ? result.blocked_slots : []);
        setWorkingHours(Array.isArray(result.working_hours) ? result.working_hours : []);
      } catch (error) {
        if (error instanceof DOMException && error.name === "AbortError") return;
        setLoadError(error instanceof Error ? error.message : "Calendar data could not be loaded.");
        setEvents([]);
        setBlockedSlots([]);
      } finally {
        if (!controller.signal.aborted) setLoading(false);
      }
    };

    void loadCalendar();
    return () => controller.abort();
  }, [providerFilter, range.end, range.start, refreshToken]);

  useEffect(() => {
    if (!selectedEvent) return;
    setRescheduleDate(selectedEvent.date);
    setRescheduleTime(selectedEvent.time);
    setRescheduleProviderId(selectedEvent.provider_id ? String(selectedEvent.provider_id) : "");
    setPaymentAmount(selectedEvent.balance > 0 ? String(Math.round(selectedEvent.balance)) : "");
    setPaymentMethod("cash");
    setShowReschedule(false);
  }, [selectedEvent]);

  useEffect(() => {
    if (!selectedEvent && !showWalkIn) return;
    const oldOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => { document.body.style.overflow = oldOverflow; };
  }, [selectedEvent, showWalkIn]);

  const visibleEvents = useMemo(() => events.filter((event) =>
    (sourceFilter === "all" || event.source === sourceFilter)
    && (statusFilter === "all" || event.status === statusFilter),
  ), [events, sourceFilter, statusFilter]);

  const days = useMemo(() => {
    const count = view === "day" ? 1 : 7;
    return Array.from({ length: count }, (_, index) => addDays(range.start, index));
  }, [range.start, view]);

  const rangeLabel = view === "day"
    ? dateLabel(anchorDate, { weekday: "long", month: "long", day: "numeric", year: "numeric" })
    : `${dateLabel(range.start, { month: "short", day: "numeric" })} – ${dateLabel(range.end, { month: "short", day: "numeric", year: "numeric" })}`;

  const notify = (message: string, error = false) => {
    setToast({ message, error });
    window.setTimeout(() => setToast(null), 4000);
  };

  const moveRange = (direction: -1 | 1) => {
    setAnchorDate((current) => addDays(current, direction * (view === "day" ? 1 : 7)));
  };

  const openWalkIn = (date: string, time?: string) => {
    if (date > todayKey()) {
      notify("Use Manual Booking to schedule an appointment in the future.", true);
      return;
    }
    setWalkInDate(date);
    setWalkInTime(time || (date === todayKey()
      ? (() => {
        const now = new Date();
        return `${String(now.getHours()).padStart(2, "0")}:${String(now.getMinutes()).padStart(2, "0")}`;
      })()
      : "10:00"));
    const preferredProvider = providers.find((provider) => String(provider.id) === providerFilter && provider.active);
    setWalkInProviderId(preferredProvider?.id ?? null);
    setShowWalkIn(true);
  };

  const updateStatus = async (event: CalendarEvent, status: string) => {
    setBusy(true);
    try {
      const response = await fetch(`/api/v1/admin/bookings/${event.id}/status`, {
        method: "POST",
        headers: { Accept: "application/json", "Content-Type": "application/json" },
        body: JSON.stringify({ status }),
      });
      const result = await readResponse(response);
      notify(result.message || `Booking marked ${statusLabel(status).toLowerCase()}.`);
      setSelectedEvent(null);
      refresh();
    } catch (error) {
      notify(error instanceof Error ? error.message : "Booking status could not be updated.", true);
    } finally {
      setBusy(false);
    }
  };

  const saveReschedule = async () => {
    if (!selectedEvent || !rescheduleProviderId) {
      notify("Choose an active provider before saving.", true);
      return;
    }
    setBusy(true);
    try {
      const response = await fetch(`/api/v1/admin/bookings/${selectedEvent.id}`, {
        method: "PUT",
        headers: { Accept: "application/json", "Content-Type": "application/json" },
        body: JSON.stringify({
          date: rescheduleDate,
          time: rescheduleTime,
          provider_id: Number(rescheduleProviderId),
        }),
      });
      const result = await readResponse(response);
      notify(result.message || "Appointment updated.");
      setSelectedEvent(null);
      refresh();
    } catch (error) {
      notify(error instanceof Error ? error.message : "Appointment could not be rescheduled.", true);
    } finally {
      setBusy(false);
    }
  };

  const recordPayment = async () => {
    if (!selectedEvent || Number(paymentAmount) <= 0) return;
    if (Number(paymentAmount) > selectedEvent.balance) {
      notify("Payment cannot be greater than the remaining balance.", true);
      return;
    }
    setBusy(true);
    try {
      const response = await fetch(`/api/v1/admin/bookings/${selectedEvent.id}/record-payment`, {
        method: "POST",
        headers: { Accept: "application/json", "Content-Type": "application/json" },
        body: JSON.stringify({ amount: Number(paymentAmount), method: paymentMethod }),
      });
      const result = await readResponse(response);
      notify(result.message || "Payment recorded.");
      setSelectedEvent(null);
      refresh();
    } catch (error) {
      notify(error instanceof Error ? error.message : "Payment could not be recorded.", true);
    } finally {
      setBusy(false);
    }
  };

  const visibleProviders = providerFilter === "all" ? providers : providers.filter((provider) => String(provider.id) === providerFilter);

  return (
    <div className="flex min-w-0 flex-1 flex-col bg-gray-50 pb-16">
      <AdminHeader title="Unified Calendar" subtitle="One live schedule for online bookings, manual appointments, and salon walk-ins" />

      <div className="mx-auto w-full max-w-[1600px] space-y-5 p-4 sm:p-6">
        <section className="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-2xs xl:flex-row xl:items-end xl:justify-between sm:p-5">
          <div className="flex flex-wrap items-center gap-2">
            <div className="inline-flex items-center rounded-xl border border-gray-200 bg-gray-50 p-1">
              <button type="button" onClick={() => moveRange(-1)} aria-label="Previous date range" className="rounded-lg p-2 text-gray-600 transition hover:bg-white hover:text-gray-900"><CaretLeft size={16} /></button>
              <button type="button" onClick={() => setAnchorDate(todayKey())} className="rounded-lg px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-white">Today</button>
              <button type="button" onClick={() => moveRange(1)} aria-label="Next date range" className="rounded-lg p-2 text-gray-600 transition hover:bg-white hover:text-gray-900"><CaretRight size={16} /></button>
            </div>
            <label className="relative block">
              <CalendarBlank size={15} className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
              <input aria-label="Choose calendar date" type="date" value={anchorDate} onChange={(event) => event.target.value && setAnchorDate(event.target.value)} className={`${inputClass} pl-9`} />
            </label>
            <div className="inline-flex rounded-xl border border-gray-200 bg-gray-50 p-1" aria-label="Calendar view">
              {(["week", "day"] as const).map((mode) => <button key={mode} type="button" onClick={() => setView(mode)} aria-pressed={view === mode} className={`rounded-lg px-3 py-2 text-xs font-semibold capitalize transition ${view === mode ? "bg-gray-900 text-white shadow-sm" : "text-gray-600 hover:bg-white"}`}>{mode}</button>)}
            </div>
          </div>

          <div className="flex flex-col gap-2 sm:flex-row sm:items-end">
            <label className="min-w-44 space-y-1">
              <span className="text-[10px] font-bold uppercase tracking-wide text-gray-500">Provider</span>
              <select value={providerFilter} onChange={(event) => setProviderFilter(event.target.value)} className={inputClass}>
                <option value="all">All providers</option>
                {providers.map((provider) => <option key={provider.id} value={provider.id}>{provider.name}{provider.active ? "" : " · inactive"}</option>)}
              </select>
            </label>
            <label className="min-w-36 space-y-1">
              <span className="text-[10px] font-bold uppercase tracking-wide text-gray-500">Source</span>
              <select value={sourceFilter} onChange={(event) => setSourceFilter(event.target.value)} className={inputClass}>
                <option value="all">All sources</option><option value="website">Online</option><option value="admin_manual">Manual</option><option value="walk_in">Walk-ins</option><option value="legacy">Legacy</option>
              </select>
            </label>
            <label className="min-w-36 space-y-1">
              <span className="text-[10px] font-bold uppercase tracking-wide text-gray-500">Status</span>
              <select value={statusFilter} onChange={(event) => setStatusFilter(event.target.value)} className={inputClass}>
                <option value="all">All statuses</option><option value="pending">Pending</option><option value="accepted">Confirmed</option><option value="in_progress">In progress</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option><option value="declined">Declined</option>
              </select>
            </label>
            <button type="button" onClick={() => openWalkIn(anchorDate)} disabled={anchorDate > todayKey()} title={anchorDate > todayKey() ? "Use Manual Booking for a future appointment" : undefined} className="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-700 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50"><Plus size={15} weight="bold" />Record walk-in</button>
          </div>
        </section>

        <section className="grid grid-cols-2 gap-3 lg:grid-cols-4">
          {[
            { label: "Appointments", value: visibleEvents.length, tone: "bg-blue-50 text-blue-700", icon: <Calendar size={17} /> },
            { label: "Walk-ins", value: visibleEvents.filter((event) => event.source === "walk_in").length, tone: "bg-emerald-50 text-emerald-700", icon: <User size={17} /> },
            { label: "Awaiting action", value: visibleEvents.filter((event) => event.status === "pending").length, tone: "bg-orange-50 text-orange-700", icon: <Clock size={17} /> },
            { label: "In service", value: visibleEvents.filter((event) => event.status === "in_progress").length, tone: "bg-amber-50 text-amber-800", icon: <Scissors size={17} /> },
          ].map((item) => <div key={item.label} className="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-2xs"><div><p className="text-[10px] font-bold uppercase tracking-wide text-gray-500">{item.label}</p><p className="mt-1 text-2xl font-bold tabular-nums text-gray-900">{loading ? "—" : item.value}</p></div><span className={`rounded-lg p-2.5 ${item.tone}`}>{item.icon}</span></div>)}
        </section>

        {loadError && <div role="alert" className="flex items-center justify-between gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"><span>{loadError}</span><button type="button" onClick={refresh} className="inline-flex shrink-0 items-center gap-1.5 font-semibold"><ArrowsClockwise size={14} />Retry</button></div>}

        <section className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xs">
          <div className="flex flex-col gap-3 border-b border-gray-100 bg-gray-50/70 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div><h2 className="text-base font-bold text-gray-900">{rangeLabel}</h2><p className="mt-0.5 text-xs text-gray-500">Appointments are shown by start time and service duration.</p></div>
            <div className="flex items-center gap-2"><Link href="/admin/providers" className="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-100">Manage providers & hours</Link><Link href="/admin/slots" className="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-100">Block time</Link><button type="button" onClick={refresh} disabled={loading} aria-label="Refresh calendar" className="rounded-lg border border-gray-200 bg-white p-2 text-gray-600 transition hover:bg-gray-100 disabled:opacity-50"><ArrowsClockwise size={15} className={loading ? "animate-spin" : ""} /></button></div>
          </div>

          {loading ? (
            <div className="grid min-h-96 grid-cols-1 gap-px bg-gray-100 sm:grid-cols-2 lg:grid-cols-7">{days.map((day) => <div key={day} className="min-h-64 animate-pulse bg-white p-4"><div className="h-4 w-24 rounded bg-gray-100" /><div className="mt-6 h-24 rounded-xl bg-gray-50" /></div>)}</div>
          ) : visibleProviders.length === 0 ? (
            <div className="px-6 py-16 text-center"><User size={28} className="mx-auto text-gray-300" /><h3 className="mt-3 text-sm font-bold text-gray-900">No providers found</h3><p className="mt-1 text-xs text-gray-500">Add or activate a provider to use the calendar.</p><Link href="/admin/providers" className="mt-4 inline-flex rounded-lg bg-gray-900 px-4 py-2 text-xs font-semibold text-white">Manage providers</Link></div>
          ) : (
            <div className={view === "week" ? "overflow-x-auto" : ""}>
              <div className={view === "week" ? "grid min-w-[1120px] grid-cols-7 divide-x divide-gray-100" : "mx-auto max-w-4xl divide-y divide-gray-100"}>
                {days.map((day) => {
                  const dayEvents = visibleEvents.filter((event) => event.date === day).sort((a, b) => a.time.localeCompare(b.time));
                  const dayBlocks = blockedSlots.filter((slot) => slot.date === day);
                  const date = dateFromKey(day);
                  const hoursByProvider = providerFilter === "all" ? [] : workingHours.filter((hours) => hours.provider_id === Number(providerFilter) && hours.day_of_week === date.getDay());
                  const dayHours = hoursByProvider[0];
                  const isToday = day === todayKey();
                  return (
                    <div key={day} className={`flex min-h-[440px] flex-col ${isToday ? "bg-blue-50/20" : "bg-white"}`}>
                      <header className={`sticky top-0 z-10 border-b border-gray-100 px-3 py-3 ${isToday ? "bg-blue-50/80" : "bg-gray-50/90"}`}>
                        <div className="flex items-start justify-between gap-1"><div><p className={`text-[10px] font-bold uppercase tracking-wide ${isToday ? "text-blue-700" : "text-gray-500"}`}>{dateLabel(day, { weekday: "short" })}{isToday ? " · Today" : ""}</p><p className="mt-0.5 text-lg font-bold tabular-nums text-gray-900">{dateFromKey(day).getDate()}</p></div><button type="button" onClick={() => openWalkIn(day)} disabled={day > todayKey()} title={day > todayKey() ? "Use Manual Booking for a future appointment" : "Record walk-in for this day"} aria-label={`Record walk-in for ${dateLabel(day)}`} className="rounded-lg border border-gray-200 bg-white p-1.5 text-gray-500 transition hover:border-emerald-300 hover:text-emerald-700 disabled:cursor-not-allowed disabled:opacity-40"><Plus size={14} weight="bold" /></button></div>
                        {dayHours && <p className="mt-2 truncate text-[10px] text-gray-500">{dayHours.is_day_off || dayHours.is_holiday || !dayHours.start_time ? "Off" : `${dayHours.start_time}–${dayHours.end_time}`}</p>}
                      </header>
                      <div className="flex-1 space-y-2 p-2.5">
                        {dayBlocks.map((slot) => <div key={`block-${slot.id}`} className="rounded-lg border border-dashed border-rose-200 bg-rose-50 px-2.5 py-2 text-[10px] text-rose-800"><p className="font-bold">{slot.time} · Blocked</p><p className="mt-0.5 truncate text-rose-700">{slot.provider_name}</p></div>)}
                        {dayEvents.map((event) => <button key={event.id} type="button" onClick={() => setSelectedEvent(event)} className={`w-full rounded-xl border p-2.5 text-left transition hover:-translate-y-0.5 hover:shadow-sm ${event.source === "walk_in" ? "border-emerald-200 bg-emerald-50/70 hover:border-emerald-300" : "border-gray-200 bg-white hover:border-blue-200"}`}>
                          <div className="flex items-start justify-between gap-1"><span className="text-xs font-bold tabular-nums text-gray-900">{event.time}</span><span className={`max-w-20 truncate rounded-full border px-1.5 py-0.5 text-[8px] font-bold uppercase ${statusTone(event.status)}`}>{statusLabel(event.status)}</span></div>
                          <p className="mt-1 truncate text-[11px] font-bold text-gray-900">{event.customer_name}</p>
                          <p className="mt-0.5 line-clamp-2 text-[10px] leading-4 text-gray-600">{event.services.map((service) => service.name).join(", ") || "No service recorded"}</p>
                          <div className="mt-2 flex items-center justify-between gap-1"><span className={`truncate text-[9px] font-semibold ${event.source === "walk_in" ? "text-emerald-700" : "text-gray-500"}`}>{sourceLabel(event.source)} · {event.provider_name}</span><span className="shrink-0 text-[9px] tabular-nums text-gray-500">{event.duration_minutes}m</span></div>
                        </button>)}
                        {!dayEvents.length && !dayBlocks.length && <p className="px-1 py-3 text-center text-[10px] text-gray-400">No scheduled activity</p>}
                        <button type="button" onClick={() => openWalkIn(day)} disabled={day > todayKey()} title={day > todayKey() ? "Use Manual Booking for a future appointment" : undefined} className="flex w-full items-center justify-center gap-1 rounded-lg border border-dashed border-gray-200 py-2 text-[10px] font-semibold text-gray-500 transition hover:border-emerald-300 hover:bg-emerald-50/50 hover:text-emerald-700 disabled:cursor-not-allowed disabled:opacity-40"><Plus size={12} />{day > todayKey() ? "Future appointment" : "Record walk-in"}</button>
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>
          )}
        </section>
      </div>

      {selectedEvent && <div className="fixed inset-0 z-[80] flex justify-end bg-black/40 backdrop-blur-[1px]" onMouseDown={(event) => { if (event.target === event.currentTarget && !busy) setSelectedEvent(null); }}>
        <aside role="dialog" aria-modal="true" aria-labelledby="calendar-event-title" className="flex h-full w-full max-w-xl flex-col overflow-hidden bg-white shadow-2xl animate-in slide-in-from-right-3 duration-200">
          <header className="flex shrink-0 items-start justify-between gap-4 border-b border-gray-200 bg-gray-50 px-5 py-4 sm:px-6"><div><div className="flex flex-wrap items-center gap-2"><span className={`rounded-full border px-2 py-1 text-[9px] font-bold uppercase ${statusTone(selectedEvent.status)}`}>{statusLabel(selectedEvent.status)}</span><span className="rounded-full bg-gray-200 px-2 py-1 text-[9px] font-bold uppercase text-gray-600">{sourceLabel(selectedEvent.source)}</span></div><h2 id="calendar-event-title" className="mt-2 text-lg font-bold text-gray-900">{selectedEvent.customer_name}</h2><p className="mt-0.5 font-mono text-[11px] text-gray-500">{selectedEvent.reference}</p></div><button type="button" onClick={() => setSelectedEvent(null)} disabled={busy} aria-label="Close appointment details" className="rounded-lg p-2 text-gray-500 transition hover:bg-gray-200"><X size={18} /></button></header>
          <div className="min-h-0 flex-1 space-y-5 overflow-y-auto p-5 sm:p-6">
            <div className="grid grid-cols-2 gap-3"><div className="rounded-xl border border-gray-100 bg-gray-50 p-3"><p className="text-[9px] font-bold uppercase tracking-wide text-gray-500">Date & time</p><p className="mt-1 text-sm font-semibold text-gray-900">{dateLabel(selectedEvent.date, { weekday: "long", month: "short", day: "numeric" })}</p><p className="mt-0.5 text-xs text-gray-600">{selectedEvent.time}–{selectedEvent.end_time} · {selectedEvent.duration_minutes} min</p></div><div className="rounded-xl border border-gray-100 bg-gray-50 p-3"><p className="text-[9px] font-bold uppercase tracking-wide text-gray-500">Provider</p><p className="mt-1 text-sm font-semibold text-gray-900">{selectedEvent.provider_name}</p><p className="mt-0.5 text-xs text-gray-600">{selectedEvent.status === "in_progress" ? "Currently serving" : "Assigned provider"}</p></div></div>

            <section className="space-y-2"><h3 className="text-xs font-bold text-gray-900">Customer & visit</h3><div className="space-y-2 rounded-xl border border-gray-100 p-3 text-xs text-gray-600">{selectedEvent.customer_phone && <a className="flex items-center gap-2 hover:text-gray-900" href={`tel:${selectedEvent.customer_phone}`}><Phone size={14} />{selectedEvent.customer_phone}</a>}{selectedEvent.customer_email && <a className="flex items-center gap-2 break-all hover:text-gray-900" href={`mailto:${selectedEvent.customer_email}`}><EnvelopeSimple size={14} />{selectedEvent.customer_email}</a>}{selectedEvent.is_home_service && selectedEvent.address && <p className="flex items-start gap-2"><MapPin size={14} className="mt-0.5 shrink-0" />{selectedEvent.address}</p>}{selectedEvent.pickup_location_name && <p className="flex items-center gap-2"><MapPin size={14} />Pickup: {selectedEvent.pickup_location_name} · {money(selectedEvent.pickup_fee)}</p>}<p className="text-[10px] text-gray-500">Payment {statusLabel(selectedEvent.payment_status)} · {selectedEvent.payment_option || "full"}{selectedEvent.deposit_amount > 0 ? ` · Deposit ${money(selectedEvent.deposit_amount)}` : ""}</p></div></section>

            <section><h3 className="mb-2 text-xs font-bold text-gray-900">Services</h3><div className="space-y-2">{selectedEvent.services.map((service) => <div key={service.id} className="flex items-center justify-between gap-3 rounded-lg border border-gray-100 px-3 py-2.5"><span className="min-w-0"><span className="block truncate text-xs font-semibold text-gray-800">{service.name}</span><span className="mt-0.5 block text-[10px] text-gray-500">{service.duration_minutes} minutes</span></span><span className="shrink-0 text-xs font-semibold tabular-nums text-gray-700">{money(service.price)}</span></div>)}</div>{selectedEvent.notes && <p className="mt-2 whitespace-pre-wrap rounded-lg bg-amber-50 px-3 py-2 text-[11px] text-amber-900">{selectedEvent.notes}</p>}</section>

            <section className="rounded-xl border border-gray-200 p-3"><div className="flex items-center justify-between text-xs text-gray-600"><span>Booking total</span><strong className="text-gray-900">{money(selectedEvent.total_price)}</strong></div><div className="mt-1.5 flex items-center justify-between text-xs text-gray-600"><span>Paid</span><strong className="text-emerald-700">{money(selectedEvent.total_paid)}</strong></div><div className="mt-2 flex items-center justify-between border-t border-gray-100 pt-2 text-sm font-bold text-gray-900"><span>Balance</span><span>{money(selectedEvent.balance)}</span></div></section>

            {selectedEvent.status === "pending" && <section className="space-y-2"><h3 className="text-xs font-bold text-gray-900">Booking actions</h3><div className="grid grid-cols-3 gap-2"><button type="button" onClick={() => void updateStatus(selectedEvent, "accepted")} disabled={busy} className="rounded-lg bg-blue-700 px-3 py-2 text-xs font-semibold text-white disabled:opacity-50">Confirm</button><button type="button" onClick={() => void updateStatus(selectedEvent, "declined")} disabled={busy} className="rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 disabled:opacity-50">Decline</button><button type="button" onClick={() => void updateStatus(selectedEvent, "cancelled")} disabled={busy} className="rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 disabled:opacity-50">Cancel</button></div></section>}
            {(selectedEvent.status === "accepted" || selectedEvent.status === "confirmed") && <section className="space-y-2"><h3 className="text-xs font-bold text-gray-900">Service actions</h3><div className="grid grid-cols-2 gap-2"><button type="button" onClick={() => void updateStatus(selectedEvent, "in_progress")} disabled={busy} className="rounded-lg bg-amber-600 px-3 py-2.5 text-xs font-semibold text-white disabled:opacity-50">Start service</button><button type="button" onClick={() => void updateStatus(selectedEvent, "completed")} disabled={busy} className="rounded-lg bg-emerald-700 px-3 py-2.5 text-xs font-semibold text-white disabled:opacity-50">Mark completed</button></div></section>}
            {selectedEvent.status === "in_progress" && <section className="space-y-2"><h3 className="text-xs font-bold text-gray-900">Walk-in / service progress</h3><button type="button" onClick={() => void updateStatus(selectedEvent, "completed")} disabled={busy} className="w-full rounded-lg bg-emerald-700 px-3 py-2.5 text-xs font-semibold text-white disabled:opacity-50">Complete visit</button></section>}

            {selectedEvent.balance > 0 && !["cancelled", "declined"].includes(selectedEvent.status) && <section className="space-y-2 rounded-xl border border-emerald-100 bg-emerald-50/50 p-3"><h3 className="flex items-center gap-2 text-xs font-bold text-gray-900"><CurrencyDollar size={15} />Record salon payment</h3><div className="grid grid-cols-[1fr_120px] gap-2"><input aria-label="Payment amount" type="number" min="1" max={Math.ceil(selectedEvent.balance)} step="1" value={paymentAmount} onChange={(event) => setPaymentAmount(event.target.value)} className={inputClass} /><select aria-label="Payment method" value={paymentMethod} onChange={(event) => setPaymentMethod(event.target.value)} className={inputClass}><option value="cash">Cash</option><option value="card">Card / POS</option><option value="momo">MoMo</option></select></div><button type="button" onClick={() => void recordPayment()} disabled={busy || Number(paymentAmount) <= 0} className="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-700 px-3 py-2.5 text-xs font-semibold text-white disabled:opacity-50">{busy ? <ArrowsClockwise size={14} className="animate-spin" /> : <Check size={14} />}Save payment</button></section>}

            {["pending", "accepted", "confirmed"].includes(selectedEvent.status) && <section className="space-y-2"><div className="flex items-center justify-between"><h3 className="text-xs font-bold text-gray-900">Provider & schedule</h3><button type="button" onClick={() => setShowReschedule((current) => !current)} className="text-[11px] font-semibold text-blue-700 hover:text-blue-900">{showReschedule ? "Close" : "Reschedule / reassign"}</button></div>{showReschedule && <div className="space-y-3 rounded-xl border border-gray-200 p-3"><label className="block space-y-1"><span className="text-[10px] font-semibold text-gray-600">Provider</span><select value={rescheduleProviderId} onChange={(event) => setRescheduleProviderId(event.target.value)} className={inputClass}><option value="">Choose provider</option>{providers.filter((provider) => provider.active).map((provider) => <option key={provider.id} value={provider.id}>{provider.name}</option>)}</select></label><div className="grid grid-cols-2 gap-2"><label className="space-y-1"><span className="text-[10px] font-semibold text-gray-600">Date</span><input type="date" value={rescheduleDate} onChange={(event) => setRescheduleDate(event.target.value)} className={inputClass} /></label><label className="space-y-1"><span className="text-[10px] font-semibold text-gray-600">Start time</span><input type="time" value={rescheduleTime} onChange={(event) => setRescheduleTime(event.target.value)} className={inputClass} /></label></div><p className="text-[10px] leading-4 text-gray-500">The server checks provider service assignments, working hours, breaks, blocked time, and overlapping appointments before saving.</p><button type="button" onClick={() => void saveReschedule()} disabled={busy || !rescheduleDate || !rescheduleTime || !rescheduleProviderId} className="w-full rounded-lg bg-gray-900 px-3 py-2.5 text-xs font-semibold text-white disabled:opacity-50">{busy ? "Saving…" : "Save schedule"}</button></div>}</section>}
          </div>
          <footer className="shrink-0 border-t border-gray-200 bg-gray-50 px-5 py-3 text-[10px] text-gray-500 sm:px-6">Use the Bookings page for receipts and the full payment history.</footer>
        </aside>
      </div>}

      {showWalkIn && <AdminWalkInModal onClose={() => setShowWalkIn(false)} initialDate={walkInDate} initialTime={walkInTime} initialProviderId={walkInProviderId} onCreated={(message, reference) => { setShowWalkIn(false); notify(`${message} · ${reference}`); refresh(); }} />}
      {toast && <div role="status" className={`fixed bottom-5 right-5 z-[100] max-w-[calc(100vw-2.5rem)] rounded-xl px-4 py-3 text-xs font-semibold text-white shadow-xl ${toast.error ? "bg-rose-700" : "bg-gray-900"}`}>{toast.message}</div>}
    </div>
  );
}
