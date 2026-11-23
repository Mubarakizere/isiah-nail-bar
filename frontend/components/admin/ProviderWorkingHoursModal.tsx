"use client";

import { useEffect, useState } from "react";
import { ArrowsClockwise, Clock, X } from "@phosphor-icons/react";

interface ProviderSummary {
  id: number;
  name: string;
  services_count: number;
}

interface WorkingDay {
  day_of_week: number;
  day_name: string;
  configured: boolean;
  start_time: string;
  end_time: string;
  break_start: string;
  break_end: string;
  is_day_off: boolean;
  is_holiday: boolean;
}

interface ProviderWorkingHoursModalProps {
  provider: ProviderSummary;
  onClose: () => void;
  onSaved: () => void;
}

async function readResponse(response: Response) {
  const result = await response.json().catch(() => ({}));
  if (!response.ok) {
    const validation = result.errors ? Object.values(result.errors).flat().join(" ") : "";
    throw new Error(validation || result.message || "Could not load working hours.");
  }
  return result;
}

export default function ProviderWorkingHoursModal({
  provider,
  onClose,
  onSaved,
}: ProviderWorkingHoursModalProps) {
  const [workingHours, setWorkingHours] = useState<WorkingDay[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [copySourceDay, setCopySourceDay] = useState(1);
  const [copiedSchedule, setCopiedSchedule] = useState<Pick<WorkingDay, "start_time" | "end_time" | "break_start" | "break_end" | "is_day_off" | "is_holiday"> | null>(null);
  const [copiedDayName, setCopiedDayName] = useState("");
  const [selectedDays, setSelectedDays] = useState<Set<number>>(new Set());

  useEffect(() => {
    let cancelled = false;

    const load = async () => {
      setLoading(true);
      setError(null);
      try {
        const response = await fetch(`/api/v1/admin/providers/${provider.id}/working-hours`, {
          headers: { Accept: "application/json" },
          cache: "no-store",
        });
        const result = await readResponse(response);
        if (!cancelled) setWorkingHours(result.data?.working_hours || []);
      } catch (loadError) {
        if (!cancelled) setError(loadError instanceof Error ? loadError.message : "Could not load working hours.");
      } finally {
        if (!cancelled) setLoading(false);
      }
    };

    void load();
    return () => {
      cancelled = true;
    };
  }, [provider.id]);

  const updateDay = (day: number, changes: Partial<WorkingDay>) => {
    setWorkingHours((current) => current.map((item) =>
      item.day_of_week === day ? { ...item, ...changes } : item
    ));
  };

  const copyDaySchedule = () => {
    const source = workingHours.find((day) => day.day_of_week === copySourceDay);
    if (!source) return;
    setCopiedSchedule({
      start_time: source.start_time,
      end_time: source.end_time,
      break_start: source.break_start,
      break_end: source.break_end,
      is_day_off: source.is_day_off,
      is_holiday: source.is_holiday,
    });
    setCopiedDayName(source.day_name);
  };

  const pasteSchedule = (targetDays: number[]) => {
    if (!copiedSchedule) return;
    setWorkingHours((current) => current.map((day) => targetDays.includes(day.day_of_week)
      ? { ...day, ...copiedSchedule, configured: true }
      : day
    ));
  };

  const toggleDaySelection = (day: number) => {
    setSelectedDays((current) => {
      const next = new Set(current);
      if (next.has(day)) next.delete(day);
      else next.add(day);
      return next;
    });
  };

  const markSelectedDaysOff = () => {
    setWorkingHours((current) => current.map((day) => selectedDays.has(day.day_of_week)
      ? { ...day, is_day_off: true, is_holiday: false }
      : day
    ));
  };

  const scheduledDays = workingHours.filter((day) => !day.is_day_off && !day.is_holiday && day.start_time && day.end_time).length;
  const unavailableDays = workingHours.filter((day) => day.is_day_off || day.is_holiday).length;

  const timeToMinutes = (time: string) => {
    if (!time) return 0;
    const [hours, minutes] = time.split(":").map(Number);
    return hours * 60 + minutes;
  };

  const weeklyMinutes = workingHours.reduce((total, day) => {
    if (day.is_day_off || day.is_holiday || !day.start_time || !day.end_time) return total;
    const breakMinutes = day.break_start && day.break_end
      ? timeToMinutes(day.break_end) - timeToMinutes(day.break_start)
      : 0;
    return total + Math.max(0, timeToMinutes(day.end_time) - timeToMinutes(day.start_time) - breakMinutes);
  }, 0);

  const save = async () => {
    setSaving(true);
    setError(null);
    try {
      const response = await fetch(`/api/v1/admin/providers/${provider.id}/working-hours`, {
        method: "PUT",
        headers: { Accept: "application/json", "Content-Type": "application/json" },
        body: JSON.stringify({
          working_hours: workingHours.map((day) => ({
            day_of_week: day.day_of_week,
            start_time: day.start_time || null,
            end_time: day.end_time || null,
            break_start: day.break_start || null,
            break_end: day.break_end || null,
            is_day_off: day.is_day_off,
            is_holiday: day.is_holiday,
          })),
        }),
      });
      const result = await readResponse(response);
      setWorkingHours(result.data?.working_hours || workingHours);
      onSaved();
      onClose();
    } catch (saveError) {
      setError(saveError instanceof Error ? saveError.message : "Could not save working hours.");
    } finally {
      setSaving(false);
    }
  };

  return (
    <div
      className="fixed inset-0 z-[85] flex items-center justify-center overflow-y-auto bg-black/50 p-3 backdrop-blur-xs sm:p-5"
      onMouseDown={(event) => { if (event.target === event.currentTarget && !saving) onClose(); }}
    >
      <section
        role="dialog"
        aria-modal="true"
        aria-labelledby="provider-hours-title"
        className="flex max-h-[94dvh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl"
      >
        <header className="flex shrink-0 items-center justify-between border-b border-gray-100 bg-gray-50/70 px-4 py-4 sm:px-5">
          <div className="flex items-center gap-3">
            <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-700"><Clock size={18} /></span>
            <div>
              <h2 id="provider-hours-title" className="text-sm font-bold text-gray-900">Working hours</h2>
              <p className="mt-0.5 text-[11px] text-gray-500">{provider.name} · set the weekly schedule used by online booking.</p>
            </div>
          </div>
          <button type="button" onClick={onClose} disabled={saving} aria-label="Close working hours" className="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-800 disabled:opacity-50"><X size={17} /></button>
        </header>

        <div className="min-h-0 flex-1 space-y-3 overflow-y-auto overscroll-contain p-3 sm:p-5">
          <div className="rounded-lg border border-blue-100 bg-blue-50/60 px-3 py-2.5 text-[11px] leading-relaxed text-blue-900">
            Booking times are generated from these hours. A day without a saved schedule has no bookable times.
          </div>

          {provider.services_count === 0 && (
            <div role="status" className="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-[11px] leading-relaxed text-amber-900">
              This provider has no services assigned. Hours can be saved, but online booking will not offer this provider for any service until you assign services in the provider profile.
            </div>
          )}

          {error && <div role="alert" className="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2.5 text-xs text-rose-800">{error}</div>}

          {loading ? (
            <div className="space-y-2" aria-label="Loading working hours">
              {Array.from({ length: 7 }).map((_, index) => <div key={index} className="h-20 animate-pulse rounded-xl bg-gray-100" />)}
            </div>
          ) : <>
            <div className="rounded-xl border border-gray-200 bg-gray-50/60 p-3 sm:p-4">
              <div className="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <label className="space-y-1.5 lg:min-w-52">
                  <span className="block text-[10px] font-semibold uppercase tracking-wide text-gray-500">Copy schedule from</span>
                  <select value={copySourceDay} onChange={(event) => setCopySourceDay(Number(event.target.value))} className="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs text-gray-900 outline-none focus:border-gray-900">
                    {workingHours.map((day) => <option key={day.day_of_week} value={day.day_of_week}>{day.day_name}</option>)}
                  </select>
                </label>
                <div className="flex flex-wrap gap-2">
                  <button type="button" onClick={copyDaySchedule} className="rounded-lg border border-gray-200 bg-white px-3 py-2 text-[11px] font-semibold text-gray-700 hover:bg-gray-100">Copy day</button>
                  <button type="button" onClick={() => setSelectedDays(new Set([1, 2, 3, 4, 5]))} className="rounded-lg border border-gray-200 bg-white px-3 py-2 text-[11px] font-semibold text-gray-700 hover:bg-gray-100">Select weekdays</button>
                  <button type="button" onClick={() => setSelectedDays(new Set(workingHours.map((day) => day.day_of_week)))} className="rounded-lg border border-gray-200 bg-white px-3 py-2 text-[11px] font-semibold text-gray-700 hover:bg-gray-100">Select all</button>
                  <button type="button" onClick={() => setSelectedDays(new Set())} className="rounded-lg border border-gray-200 bg-white px-3 py-2 text-[11px] font-semibold text-gray-700 hover:bg-gray-100">Clear selection</button>
                </div>
              </div>
              <div className="mt-2 flex flex-wrap gap-2">
                <button type="button" onClick={() => pasteSchedule([...selectedDays])} disabled={!copiedSchedule || selectedDays.size === 0} className="rounded-lg bg-gray-900 px-3 py-2 text-[11px] font-semibold text-white hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-40">
                  {copiedSchedule ? `Paste ${copiedDayName} to ${selectedDays.size || "selected"} days` : "Copy a day to enable paste"}
                </button>
                <button type="button" onClick={() => pasteSchedule([1, 2, 3, 4, 5])} disabled={!copiedSchedule} className="rounded-lg border border-gray-200 bg-white px-3 py-2 text-[11px] font-semibold text-gray-700 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40">Paste to weekdays</button>
                <button type="button" onClick={markSelectedDaysOff} disabled={selectedDays.size === 0} className="rounded-lg border border-gray-200 bg-white px-3 py-2 text-[11px] font-semibold text-gray-700 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40">Set selected as days off</button>
              </div>
              {copiedSchedule && <p className="mt-2 text-[10px] text-emerald-700">{copiedDayName} schedule copied. Choose target days above, then paste.</p>}
            </div>

            <div className="grid grid-cols-3 gap-2">
              <div className="rounded-lg border border-gray-200 bg-white px-3 py-2"><p className="text-[9px] font-semibold uppercase tracking-wide text-gray-500">Scheduled days</p><p className="mt-0.5 text-sm font-bold text-gray-900">{scheduledDays}</p></div>
              <div className="rounded-lg border border-gray-200 bg-white px-3 py-2"><p className="text-[9px] font-semibold uppercase tracking-wide text-gray-500">Days off</p><p className="mt-0.5 text-sm font-bold text-gray-900">{unavailableDays}</p></div>
              <div className="rounded-lg border border-gray-200 bg-white px-3 py-2"><p className="text-[9px] font-semibold uppercase tracking-wide text-gray-500">Weekly hours</p><p className="mt-0.5 text-sm font-bold text-gray-900">{Math.floor(weeklyMinutes / 60)}h {weeklyMinutes % 60}m</p></div>
            </div>

            {workingHours.map((day) => {
            const unavailable = day.is_day_off || day.is_holiday;
            return (
              <article key={day.day_of_week} className="rounded-xl border border-gray-200 p-3 sm:p-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                  <div>
                    <div className="flex items-center gap-2">
                      <input type="checkbox" aria-label={`Select ${day.day_name} for bulk actions`} checked={selectedDays.has(day.day_of_week)} onChange={() => toggleDaySelection(day.day_of_week)} className="h-3.5 w-3.5 rounded border-gray-300 accent-gray-900" />
                      <h3 className="text-xs font-bold text-gray-900">{day.day_name}</h3>
                    </div>
                    <p className="mt-0.5 text-[10px] text-gray-500">
                      {day.is_day_off ? "Day off" : day.is_holiday ? "Unavailable" : day.configured ? "Weekly schedule" : "Not set yet"}
                    </p>
                  </div>
                  <div className="flex flex-wrap items-center gap-3">
                    <label className="inline-flex items-center gap-1.5 text-[11px] text-gray-700">
                      <input type="checkbox" checked={day.is_day_off} onChange={(event) => updateDay(day.day_of_week, { is_day_off: event.target.checked, is_holiday: event.target.checked ? false : day.is_holiday })} className="h-3.5 w-3.5 rounded border-gray-300 accent-gray-900" />
                      Day off
                    </label>
                    <label className="inline-flex items-center gap-1.5 text-[11px] text-gray-700">
                      <input type="checkbox" checked={day.is_holiday} onChange={(event) => updateDay(day.day_of_week, { is_holiday: event.target.checked, is_day_off: event.target.checked ? false : day.is_day_off })} className="h-3.5 w-3.5 rounded border-gray-300 accent-gray-900" />
                      Holiday / unavailable
                    </label>
                  </div>
                </div>

                <div className="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                  {([
                    ["Opens", "start_time"],
                    ["Closes", "end_time"],
                    ["Break starts", "break_start"],
                    ["Break ends", "break_end"],
                  ] as const).map(([label, field]) => (
                    <label key={field} className="space-y-1">
                      <span className="block text-[10px] font-semibold text-gray-500">{label}</span>
                      <input
                        aria-label={`${day.day_name} ${label.toLowerCase()}`}
                        type="time"
                        value={day[field]}
                        disabled={unavailable || (field.startsWith("break") && (!day.start_time || !day.end_time))}
                        onChange={(event) => updateDay(day.day_of_week, { [field]: event.target.value })}
                        className="w-full rounded-lg border border-gray-200 bg-white px-2.5 py-2 text-xs text-gray-900 outline-none focus:border-gray-900 disabled:bg-gray-50 disabled:text-gray-400"
                      />
                    </label>
                  ))}
                </div>
              </article>
            );
          })}
          </>}
        </div>

        <footer className="flex shrink-0 flex-col-reverse gap-2 border-t border-gray-100 bg-gray-50 p-3 sm:flex-row sm:justify-end sm:p-4">
          <button type="button" onClick={onClose} disabled={saving} className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 disabled:opacity-50">Cancel</button>
          <button type="button" onClick={() => void save()} disabled={loading || saving || workingHours.length !== 7} className="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-xs font-semibold text-white hover:bg-gray-800 disabled:cursor-wait disabled:opacity-50">
            {saving && <ArrowsClockwise size={13} className="animate-spin" />}
            {saving ? "Saving hours…" : "Save working hours"}
          </button>
        </footer>
      </section>
    </div>
  );
}
