"use client";

import { useEffect, useMemo, useState } from "react";
import {
  ArrowsClockwise,
  CalendarBlank,
  CheckCircle,
  Clock,
  Lock,
  LockOpen,
  Prohibit,
  UsersThree,
  Warning,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";

interface ProviderOption {
  id: number;
  name: string;
  active: boolean;
}

interface SlotItem {
  time: string;
  formatted: string;
  status: "available" | "blocked" | "fully_booked" | "unavailable";
  count: number;
  capacity: number;
  reason?: string | null;
}

interface WorkingHours {
  start_time: string;
  end_time: string;
  is_day_off: boolean;
  is_holiday: boolean;
  break_start?: string | null;
  break_end?: string | null;
}

interface SlotResponse {
  providers: ProviderOption[];
  selected_date: string;
  selected_provider_id: number | null;
  provider: { id: number; name: string } | null;
  working_hours: WorkingHours | null;
  unavailable_reason: "missing_working_hours" | "day_off" | "holiday" | null;
  slots: SlotItem[];
}

function todayLocalDate(): string {
  const now = new Date();
  const month = String(now.getMonth() + 1).padStart(2, "0");
  const day = String(now.getDate()).padStart(2, "0");
  return `${now.getFullYear()}-${month}-${day}`;
}

async function readResponse(response: Response) {
  const result = await response.json().catch(() => ({}));
  if (!response.ok) {
    const validation = result.errors ? Object.values(result.errors).flat().join(" ") : "";
    throw new Error(validation || result.message || "The request could not be completed.");
  }
  return result;
}

export default function AdminSlots() {
  const [providers, setProviders] = useState<ProviderOption[]>([]);
  const [providerId, setProviderId] = useState("");
  const [date, setDate] = useState(todayLocalDate);
  const [provider, setProvider] = useState<SlotResponse["provider"]>(null);
  const [workingHours, setWorkingHours] = useState<WorkingHours | null>(null);
  const [unavailableReason, setUnavailableReason] = useState<SlotResponse["unavailable_reason"]>(null);
  const [slots, setSlots] = useState<SlotItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [busyTime, setBusyTime] = useState<string | null>(null);
  const [refreshVersion, setRefreshVersion] = useState(0);
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(null);

  useEffect(() => {
    const controller = new AbortController();
    const loadSlots = async () => {
      setLoading(true);
      setLoadError(null);
      try {
        const params = new URLSearchParams({ date });
        if (providerId) params.set("provider_id", providerId);
        const response = await fetch("/api/v1/admin/slots?" + params.toString(), {
          headers: { Accept: "application/json" },
          cache: "no-store",
          signal: controller.signal,
        });
        const result: SlotResponse = await readResponse(response);
        setProviders(Array.isArray(result.providers) ? result.providers : []);
        setProvider(result.provider || null);
        setWorkingHours(result.working_hours || null);
        setUnavailableReason(result.unavailable_reason || null);
        setSlots(Array.isArray(result.slots) ? result.slots : []);

        if (!providerId && result.providers?.length) {
          setProviderId(String(result.providers[0].id));
        }
      } catch (error) {
        if (error instanceof DOMException && error.name === "AbortError") return;
        setLoadError(error instanceof Error ? error.message : "Failed to load time slots.");
        setSlots([]);
      } finally {
        if (!controller.signal.aborted) setLoading(false);
      }
    };

    void loadSlots();
    return () => controller.abort();
  }, [providerId, date, refreshVersion]);

  const notify = (message: string, type: "success" | "error" = "success") => {
    setToast({ message, type });
    window.setTimeout(() => setToast(null), 4000);
  };

  const counts = useMemo(() => ({
    available: slots.filter((slot) => slot.status === "available").length,
    blocked: slots.filter((slot) => slot.status === "blocked").length,
    full: slots.filter((slot) => slot.status === "fully_booked").length,
    breaks: slots.filter((slot) => slot.status === "unavailable").length,
  }), [slots]);

  const updateSlot = async (slot: SlotItem, action: "block" | "unblock") => {
    if (!providerId) return;
    setBusyTime(slot.time);
    try {
      const response = await fetch(`/api/v1/admin/slots/${action}`, {
        method: "POST",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ provider_id: Number(providerId), date, time: slot.time }),
      });
      const result = await readResponse(response);
      notify(result.message || `Slot ${action === "block" ? "blocked" : "unblocked"}.`);
      setRefreshVersion((value) => value + 1);
    } catch (error) {
      notify(error instanceof Error ? error.message : "The slot could not be updated.", "error");
    } finally {
      setBusyTime(null);
    }
  };

  const formattedDate = date
    ? new Intl.DateTimeFormat("en", { weekday: "long", month: "short", day: "numeric", year: "numeric", timeZone: "UTC" }).format(new Date(`${date}T12:00:00Z`))
    : "";

  return (
    <div className="flex min-w-0 flex-1 flex-col bg-gray-50 pb-16">
      <AdminHeader title="Time Slots" subtitle="Manage provider availability and block or release appointment times" />

      <div className="mx-auto w-full max-w-7xl space-y-5 p-4 sm:p-6">
        <form onSubmit={(event) => event.preventDefault()} className="grid gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-2xs sm:grid-cols-[1fr_1fr_auto] sm:items-end sm:p-5">
          <label className="space-y-1.5">
            <span className="text-xs font-semibold text-gray-700">Select provider</span>
            <select value={providerId} onChange={(event) => setProviderId(event.target.value)} className="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
              <option value="">Choose a provider</option>
              {providers.map((item) => <option key={item.id} value={item.id}>{item.name}{item.active ? "" : " (inactive)"}</option>)}
            </select>
          </label>
          <label className="space-y-1.5">
            <span className="text-xs font-semibold text-gray-700">Choose date</span>
            <span className="relative block"><CalendarBlank size={15} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" /><input type="date" value={date} onChange={(event) => setDate(event.target.value)} className="w-full rounded-lg border border-gray-200 bg-white py-2.5 pl-9 pr-3 text-sm text-gray-900 outline-none transition focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10" /></span>
          </label>
          <button type="button" onClick={() => setRefreshVersion((value) => value + 1)} disabled={loading} className="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-gray-800 disabled:opacity-50">
            <ArrowsClockwise size={14} className={loading ? "animate-spin" : ""} /> Refresh slots
          </button>
        </form>

        {loadError && <div role="alert" className="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2.5 text-xs text-rose-800">{loadError}</div>}

        {!loading && providers.length === 0 && !loadError && (
          <div className="rounded-2xl border border-gray-200 bg-white px-6 py-14 text-center shadow-2xs">
            <UsersThree size={30} className="mx-auto text-gray-300" />
            <h2 className="mt-3 text-sm font-bold text-gray-900">No providers available</h2>
            <p className="mt-1 text-xs text-gray-500">Add a provider before managing appointment slots.</p>
          </div>
        )}

        {provider && (
          <>
            <section className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
              {[
                { label: "Time slots", value: slots.length, tone: "bg-blue-50 text-blue-700", icon: <Clock size={17} /> },
                { label: "Available", value: counts.available, tone: "bg-emerald-50 text-emerald-700", icon: <CheckCircle size={17} /> },
                { label: "Blocked", value: counts.blocked, tone: "bg-rose-50 text-rose-700", icon: <Lock size={17} /> },
                { label: "Break time", value: counts.breaks, tone: "bg-amber-50 text-amber-700", icon: <Clock size={17} /> },
                { label: "Fully booked", value: counts.full, tone: "bg-gray-100 text-gray-700", icon: <UsersThree size={17} /> },
              ].map((item) => (
                <div key={item.label} className="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-2xs">
                  <div><p className="text-[11px] font-bold uppercase tracking-wide text-gray-500">{item.label}</p><p className="mt-1 text-2xl font-bold tabular-nums text-gray-900">{loading ? "—" : item.value}</p></div>
                  <span className={`rounded-lg p-2.5 ${item.tone}`}>{item.icon}</span>
                </div>
              ))}
            </section>

            <section className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xs">
              <div className="flex flex-col gap-1 border-b border-gray-100 bg-gray-50/70 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div><h2 className="text-sm font-bold text-gray-900">{provider.name}</h2><p className="mt-0.5 text-xs text-gray-500">{formattedDate}</p></div>
                {workingHours && <span className="inline-flex items-center gap-1.5 self-start rounded-full bg-white px-2.5 py-1 text-[11px] font-medium text-gray-600 ring-1 ring-gray-200 sm:self-auto"><Clock size={12} />{workingHours.start_time}–{workingHours.end_time}{workingHours.break_start && workingHours.break_end ? ` · Break ${workingHours.break_start}–${workingHours.break_end}` : ""}</span>}
              </div>

              {loading ? (
                <div className="grid grid-cols-2 gap-3 p-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 sm:p-5">
                  {Array.from({ length: 8 }).map((_, index) => <div key={index} className="h-32 animate-pulse rounded-xl bg-gray-100" />)}
                </div>
              ) : slots.length > 0 ? (
                <div className="grid grid-cols-2 gap-3 p-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 sm:p-5">
                  {slots.map((slot) => {
                    const blocked = slot.status === "blocked";
                    const full = slot.status === "fully_booked";
                    const unavailable = slot.status === "unavailable";
                    return (
                      <article key={slot.time} className={`rounded-xl border-2 p-3 text-center transition sm:p-4 ${blocked ? "border-rose-200 bg-rose-50" : full ? "border-gray-200 bg-gray-50" : unavailable ? "border-amber-200 bg-amber-50" : "border-gray-200 bg-white hover:border-emerald-200"}`}>
                        <h3 className={`mb-2 text-base font-bold sm:text-lg ${blocked ? "text-rose-700" : "text-gray-900"}`}>{slot.formatted}</h3>
                        <div className="mb-3">
                          {blocked ? <span className="inline-flex items-center rounded-full bg-rose-100 px-2 py-1 text-[10px] font-bold text-rose-800"><Lock size={11} className="mr-1" />Blocked</span>
                            : full ? <span className="inline-flex items-center rounded-full bg-gray-200 px-2 py-1 text-[10px] font-bold text-gray-700"><UsersThree size={11} className="mr-1" />Fully booked</span>
                              : unavailable ? <span className="inline-flex items-center rounded-full bg-amber-100 px-2 py-1 text-[10px] font-bold text-amber-800"><Clock size={11} className="mr-1" />Provider break</span>
                              : <span className="inline-flex items-center rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold text-emerald-800"><CheckCircle size={11} className="mr-1" />{slot.capacity - slot.count} spot{slot.capacity - slot.count === 1 ? "" : "s"}</span>}
                        </div>
                        {blocked ? (
                          <button type="button" onClick={() => void updateSlot(slot, "unblock")} disabled={busyTime !== null} className="inline-flex w-full items-center justify-center gap-1 rounded-lg bg-rose-600 px-2 py-1.5 text-[11px] font-semibold text-white transition hover:bg-rose-700 disabled:opacity-50">
                            {busyTime === slot.time ? <ArrowsClockwise size={12} className="animate-spin" /> : <LockOpen size={12} />} Unblock
                          </button>
                        ) : full ? (
                          <span className="block w-full rounded-lg bg-gray-100 px-2 py-1.5 text-[11px] font-semibold text-gray-400">At capacity</span>
                        ) : unavailable ? (
                          <span className="block w-full rounded-lg bg-amber-100 px-2 py-1.5 text-[11px] font-semibold text-amber-800">Break time</span>
                        ) : (
                          <button type="button" onClick={() => void updateSlot(slot, "block")} disabled={busyTime !== null} className="inline-flex w-full items-center justify-center gap-1 rounded-lg bg-amber-100 px-2 py-1.5 text-[11px] font-semibold text-amber-800 transition hover:bg-amber-200 disabled:opacity-50">
                            {busyTime === slot.time ? <ArrowsClockwise size={12} className="animate-spin" /> : <Prohibit size={12} />} Block
                          </button>
                        )}
                      </article>
                    );
                  })}
                </div>
              ) : (
                <div className="px-5 py-12 text-center">
                  <Warning size={26} className="mx-auto text-amber-500" />
                  <h3 className="mt-3 text-sm font-bold text-gray-900">{unavailableReason === "day_off" ? "Provider is off today" : unavailableReason === "holiday" ? "Provider is on holiday" : "No working hours configured"}</h3>
                  <p className="mt-1 text-xs text-gray-500">{unavailableReason === "missing_working_hours" ? "Set working hours for this weekday before managing its slots." : "No appointment times are available for this provider on the selected date."}</p>
                </div>
              )}
            </section>
          </>
        )}
      </div>

      {toast && <div role="status" className={`fixed bottom-5 right-5 z-[100] max-w-[calc(100vw-2.5rem)] rounded-xl px-4 py-3 text-xs font-semibold text-white shadow-xl ${toast.type === "error" ? "bg-rose-700" : "bg-gray-900"}`}>{toast.message}</div>}
    </div>
  );
}
