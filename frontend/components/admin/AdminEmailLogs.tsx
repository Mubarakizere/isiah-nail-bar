"use client";

import { useCallback, useEffect, useRef, useState } from "react";
import type { FormEvent } from "react";
import {
  ArrowsClockwise,
  CalendarBlank,
  CaretLeft,
  CaretRight,
  CheckCircle,
  Clock,
  EnvelopeSimple,
  Eye,
  MagnifyingGlass,
  X,
  XCircle,
  Warning,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";

interface EmailLog {
  id: number;
  recipient_email: string;
  recipient_name: string | null;
  recipient_type: string;
  subject: string;
  email_type: string;
  booking_id: number | null;
  status: string;
  sent_at: string | null;
  sent_at_human: string | null;
  error_message?: string | null;
  metadata?: Record<string, unknown> | null;
  booking?: {
    id: number;
    reference: string | null;
    date: string | null;
    time: string | null;
    status: string | null;
    customer_name: string | null;
    provider_name: string | null;
  } | null;
}

interface Filters {
  search: string;
  type: string;
  status: string;
  date_from: string;
  date_to: string;
}

interface EmailLogsResponse {
  success: boolean;
  data: EmailLog[];
  email_types: string[];
  meta: { current_page: number; last_page: number; per_page: number; total: number };
  stats: { total: number; sent: number; failed: number; pending: number };
}

const EMPTY_FILTERS: Filters = { search: "", type: "", status: "", date_from: "", date_to: "" };
const EMPTY_META = { current_page: 1, last_page: 1, per_page: 20, total: 0 };
const EMPTY_STATS = { total: 0, sent: 0, failed: 0, pending: 0 };

async function readResponse(response: Response) {
  const result = await response.json().catch(() => ({}));
  if (!response.ok || result.success === false) {
    const validation = result.errors ? Object.values(result.errors).flat().join(" ") : "";
    throw new Error(validation || result.message || "The request could not be completed.");
  }
  return result;
}

function titleCase(value: string): string {
  return value.replace(/_/g, " ").replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function formatDate(value: string | null, withTime = false): string {
  if (!value) return "—";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "—";
  return new Intl.DateTimeFormat("en", withTime
    ? { dateStyle: "medium", timeStyle: "short" }
    : { dateStyle: "medium" }).format(date);
}

function statusClass(status: string): string {
  if (status === "sent") return "bg-emerald-50 text-emerald-700 ring-emerald-200";
  if (status === "failed") return "bg-rose-50 text-rose-700 ring-rose-200";
  return "bg-gray-100 text-gray-600 ring-gray-200";
}

export default function AdminEmailLogs() {
  const [logs, setLogs] = useState<EmailLog[]>([]);
  const [emailTypes, setEmailTypes] = useState<string[]>([]);
  const [stats, setStats] = useState(EMPTY_STATS);
  const [meta, setMeta] = useState(EMPTY_META);
  const [page, setPage] = useState(1);
  const [draftFilters, setDraftFilters] = useState<Filters>(EMPTY_FILTERS);
  const [filters, setFilters] = useState<Filters>(EMPTY_FILTERS);
  const [refreshKey, setRefreshKey] = useState(0);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);

  const [selectedLog, setSelectedLog] = useState<EmailLog | null>(null);
  const [detailLoading, setDetailLoading] = useState(false);
  const [detailError, setDetailError] = useState<string | null>(null);
  const detailRequest = useRef(0);

  const fetchLogs = useCallback(async (signal?: AbortSignal) => {
    setLoading(true);
    setLoadError(null);
    const params = new URLSearchParams({ page: String(page) });
    Object.entries(filters).forEach(([key, value]) => {
      if (value.trim()) params.set(key, value.trim());
    });

    try {
      const response = await fetch(`/api/v1/admin/email-logs?${params.toString()}`, {
        headers: { Accept: "application/json" },
        cache: "no-store",
        signal,
      });
      const result = (await readResponse(response)) as EmailLogsResponse;
      if (signal?.aborted) return;
      if (result.meta?.last_page && page > result.meta.last_page) {
        setPage(result.meta.last_page);
        return;
      }
      setLogs(Array.isArray(result.data) ? result.data : []);
      setEmailTypes(Array.isArray(result.email_types) ? result.email_types : []);
      setStats(result.stats || EMPTY_STATS);
      setMeta(result.meta || EMPTY_META);
    } catch (error) {
      if (signal?.aborted) return;
      setLoadError(error instanceof Error ? error.message : "Failed to load email logs.");
      setLogs([]);
    } finally {
      if (!signal?.aborted) setLoading(false);
    }
  }, [filters, page]);

  useEffect(() => {
    const controller = new AbortController();
    void fetchLogs(controller.signal);
    return () => controller.abort();
  }, [fetchLogs, refreshKey]);

  useEffect(() => {
    if (!selectedLog) return;
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") setSelectedLog(null);
    };
    window.addEventListener("keydown", onKeyDown);
    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener("keydown", onKeyDown);
    };
  }, [selectedLog]);

  const openDetails = async (log: EmailLog) => {
    const requestId = ++detailRequest.current;
    setSelectedLog(log);
    setDetailLoading(true);
    setDetailError(null);
    try {
      const response = await fetch(`/api/v1/admin/email-logs/${log.id}`, {
        headers: { Accept: "application/json" },
        cache: "no-store",
      });
      const result = await readResponse(response);
      if (requestId === detailRequest.current) setSelectedLog(result.data as EmailLog);
    } catch (error) {
      if (requestId === detailRequest.current) setDetailError(error instanceof Error ? error.message : "Could not load email details.");
    } finally {
      if (requestId === detailRequest.current) setDetailLoading(false);
    }
  };

  const closeDetails = () => {
    detailRequest.current += 1;
    setSelectedLog(null);
    setDetailLoading(false);
    setDetailError(null);
  };

  const applyFilters = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setPage(1);
    setFilters({ ...draftFilters });
  };

  const clearFilters = () => {
    setDraftFilters(EMPTY_FILTERS);
    setFilters(EMPTY_FILTERS);
    setPage(1);
  };

  const firstResult = meta.total === 0 ? 0 : (meta.current_page - 1) * meta.per_page + 1;
  const lastResult = Math.min(meta.current_page * meta.per_page, meta.total);

  return (
    <div className="flex min-w-0 flex-1 flex-col bg-gray-50 pb-16">
      <AdminHeader title="Email Logs" subtitle="Review email delivery history" />

      <div className="mx-auto w-full max-w-7xl space-y-4 p-4 sm:p-6">
        <section className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          {([
            ["Total emails", stats.total, "bg-blue-50 text-blue-700", <EnvelopeSimple size={18} key="total" />],
            ["Sent", stats.sent, "bg-emerald-50 text-emerald-700", <CheckCircle size={18} key="sent" />],
            ["Failed", stats.failed, "bg-rose-50 text-rose-700", <XCircle size={18} key="failed" />],
            ["Pending", stats.pending, "bg-gray-100 text-gray-600", <Clock size={18} key="pending" />],
          ] as const).map(([label, count, iconClass, icon]) => <div key={label} className="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-2xs"><div><p className="text-[10px] font-bold uppercase tracking-wider text-gray-500">{label}</p><p className="mt-1 text-2xl font-bold tabular-nums text-gray-900">{loading ? "—" : count}</p></div><span className={`rounded-lg p-2.5 ${iconClass}`}>{icon}</span></div>)}
        </section>

        <form onSubmit={applyFilters} className="rounded-2xl border border-gray-200 bg-white p-4 shadow-2xs sm:p-5">
          <div className="mb-3 flex items-center justify-between"><div><h2 className="text-sm font-bold text-gray-900">Filter email history</h2><p className="mt-0.5 text-[11px] text-gray-500">Search recipients and subjects, then narrow by type, status, or date.</p></div><button type="button" onClick={() => setRefreshKey((current) => current + 1)} disabled={loading} aria-label="Refresh email logs" title="Refresh" className="rounded-lg border border-gray-200 p-2 text-gray-600 transition hover:bg-gray-50 disabled:opacity-50"><ArrowsClockwise size={15} className={loading ? "animate-spin" : ""} /></button></div>
          <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div><label htmlFor="email-search" className="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-gray-600">Search</label><div className="relative"><MagnifyingGlass size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" /><input id="email-search" value={draftFilters.search} onChange={(event) => setDraftFilters((current) => ({ ...current, search: event.target.value }))} placeholder="Email, name, subject…" className="w-full rounded-lg border border-gray-200 bg-white py-2.5 pl-9 pr-3 text-xs outline-none focus:border-blue-500" /></div></div>
            <div><label htmlFor="email-type" className="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-gray-600">Email type</label><select id="email-type" value={draftFilters.type} onChange={(event) => setDraftFilters((current) => ({ ...current, type: event.target.value }))} className="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-xs text-gray-700 outline-none focus:border-blue-500"><option value="">All types</option>{emailTypes.map((type) => <option key={type} value={type}>{titleCase(type)}</option>)}</select></div>
            <div><label htmlFor="email-status" className="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-gray-600">Status</label><select id="email-status" value={draftFilters.status} onChange={(event) => setDraftFilters((current) => ({ ...current, status: event.target.value }))} className="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-xs text-gray-700 outline-none focus:border-blue-500"><option value="">All statuses</option><option value="sent">Sent</option><option value="failed">Failed</option><option value="pending">Pending</option></select></div>
            <div><label htmlFor="email-date-from" className="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-gray-600">From date</label><input id="email-date-from" type="date" value={draftFilters.date_from} onChange={(event) => setDraftFilters((current) => ({ ...current, date_from: event.target.value }))} className="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-xs text-gray-700 outline-none focus:border-blue-500" /></div>
            <div><label htmlFor="email-date-to" className="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-gray-600">To date</label><input id="email-date-to" type="date" value={draftFilters.date_to} onChange={(event) => setDraftFilters((current) => ({ ...current, date_to: event.target.value }))} className="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-xs text-gray-700 outline-none focus:border-blue-500" /></div>
          </div>
          <div className="mt-3 flex flex-col-reverse gap-2 border-t border-gray-100 pt-3 sm:flex-row sm:justify-end"><button type="button" onClick={clearFilters} className="rounded-lg border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-50">Clear filters</button><button type="submit" className="rounded-lg bg-blue-700 px-4 py-2 text-xs font-semibold text-white transition hover:bg-blue-800">Apply filters</button></div>
        </form>

        {loadError ? <div role="alert" className="flex flex-col items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-10 text-center"><Warning size={28} className="text-rose-500" /><div><h2 className="text-sm font-bold text-rose-900">Email logs could not be loaded</h2><p className="mt-1 text-xs text-rose-800">{loadError}</p></div><button type="button" onClick={() => setRefreshKey((current) => current + 1)} className="rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-800 transition hover:bg-rose-100">Try again</button></div>
          : <section className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xs"><div className="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 sm:px-5"><div><h2 className="text-sm font-bold text-gray-900">Email history</h2><p className="mt-0.5 text-[11px] text-gray-500">{loading ? "Loading records…" : `${meta.total} matching ${meta.total === 1 ? "email" : "emails"}`}</p></div></div><div className="overflow-x-auto"><table className="w-full min-w-[900px] text-left text-xs"><thead><tr className="border-b border-gray-200 bg-gray-50/80 text-[10px] font-bold uppercase tracking-wider text-gray-600"><th className="px-4 py-3">Recipient</th><th className="px-4 py-3">Subject</th><th className="px-4 py-3">Type</th><th className="px-4 py-3">Status</th><th className="px-4 py-3">Sent at</th><th className="px-4 py-3 text-right">Details</th></tr></thead><tbody className="divide-y divide-gray-100">
            {loading ? Array.from({ length: 5 }).map((_, index) => <tr key={index} className="animate-pulse"><td colSpan={6} className="px-4 py-4"><div className="h-5 rounded bg-gray-100" /></td></tr>)
              : logs.length === 0 ? <tr><td colSpan={6} className="px-5 py-14 text-center"><EnvelopeSimple size={35} className="mx-auto mb-2 text-blue-300" /><p className="text-sm font-semibold text-gray-700">No emails found</p><p className="mt-1 text-xs text-gray-500">Email history will appear here when messages are logged.</p></td></tr>
                : logs.map((log) => <tr key={log.id} className="transition-colors hover:bg-gray-50/70"><td className="px-4 py-3.5"><p className="max-w-[190px] truncate font-semibold text-gray-900">{log.recipient_name || "—"}</p><p className="max-w-[190px] truncate text-[10px] text-gray-500">{log.recipient_email}</p></td><td className="px-4 py-3.5"><p className="max-w-[280px] truncate font-medium text-gray-800" title={log.subject}>{log.subject}</p>{log.booking_id && <p className="mt-0.5 text-[10px] text-gray-400">Booking #{log.booking_id}</p>}</td><td className="px-4 py-3.5"><span className="inline-flex rounded-md bg-blue-50 px-2 py-1 text-[10px] font-semibold uppercase text-blue-700">{titleCase(log.email_type)}</span></td><td className="px-4 py-3.5"><span className={`inline-flex items-center gap-1 rounded-md px-2 py-1 text-[10px] font-bold uppercase ring-1 ring-inset ${statusClass(log.status)}`}>{log.status === "sent" ? <CheckCircle size={12} /> : log.status === "failed" ? <XCircle size={12} /> : <Clock size={12} />}{log.status}</span></td><td className="px-4 py-3.5"><p className="whitespace-nowrap font-medium text-gray-700">{formatDate(log.sent_at, true)}</p><p className="mt-0.5 text-[10px] text-gray-400">{log.sent_at_human || (log.status === "pending" ? "Not sent yet" : "")}</p></td><td className="px-4 py-3.5 text-right"><button type="button" onClick={() => void openDetails(log)} className="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-[10px] font-semibold text-blue-700 transition hover:bg-blue-100"><Eye size={13} />View</button></td></tr>)}
          </tbody></table></div>
          {!loading && meta.total > 0 && <div className="flex flex-col gap-3 border-t border-gray-100 bg-gray-50/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5"><p className="text-[11px] text-gray-500">Showing <span className="font-semibold text-gray-700">{firstResult}–{lastResult}</span> of <span className="font-semibold text-gray-700">{meta.total}</span></p><div className="flex items-center justify-between gap-2 sm:justify-end"><button type="button" onClick={() => setPage((current) => Math.max(1, current - 1))} disabled={page <= 1} className="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-2 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"><CaretLeft size={13} />Previous</button><span className="px-1 text-[11px] tabular-nums text-gray-500">Page {page} of {meta.last_page}</span><button type="button" onClick={() => setPage((current) => Math.min(meta.last_page, current + 1))} disabled={page >= meta.last_page} className="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-2 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40">Next<CaretRight size={13} /></button></div></div>}
        </section>}
      </div>

      {selectedLog && <div className="fixed inset-0 z-[90] flex items-center justify-center bg-black/50 p-3 backdrop-blur-xs sm:p-4" onMouseDown={(event) => { if (event.target === event.currentTarget) closeDetails(); }}><section role="dialog" aria-modal="true" aria-labelledby="email-detail-title" className="max-h-[calc(100dvh-1.5rem)] w-full max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white shadow-2xl sm:max-h-[calc(100dvh-2rem)]"><div className="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-gray-100 bg-gray-50/95 p-4 backdrop-blur sm:px-6"><div className="min-w-0"><div className="mb-2 flex flex-wrap items-center gap-2"><span className={`inline-flex items-center gap-1 rounded-md px-2 py-1 text-[10px] font-bold uppercase ring-1 ring-inset ${statusClass(selectedLog.status)}`}>{selectedLog.status}</span><span className="rounded-md bg-blue-50 px-2 py-1 text-[10px] font-semibold uppercase text-blue-700">{titleCase(selectedLog.email_type)}</span></div><h2 id="email-detail-title" className="break-words text-base font-bold text-gray-900 sm:text-lg">{selectedLog.subject}</h2></div><button type="button" onClick={closeDetails} aria-label="Close email details" className="shrink-0 rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700"><X size={18} /></button></div>
        <div className="space-y-5 p-4 sm:p-6">{detailLoading ? <div className="space-y-3"><div className="h-20 animate-pulse rounded-xl bg-gray-100" /><div className="h-28 animate-pulse rounded-xl bg-gray-100" /></div> : detailError ? <div role="alert" className="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800">{detailError}</div> : <>
          <div className="grid gap-4 rounded-xl border border-gray-200 bg-white p-4 text-xs sm:grid-cols-2"><div><p className="text-[10px] font-bold uppercase tracking-wider text-gray-500">Recipient</p><p className="mt-1 font-semibold text-gray-900">{selectedLog.recipient_name || "—"}</p><p className="break-all text-gray-600">{selectedLog.recipient_email}</p></div><div><p className="text-[10px] font-bold uppercase tracking-wider text-gray-500">Recipient type</p><p className="mt-1 font-semibold text-gray-900">{titleCase(selectedLog.recipient_type || "unknown")}</p></div><div><p className="text-[10px] font-bold uppercase tracking-wider text-gray-500">Sent at</p><p className="mt-1 font-semibold text-gray-900">{formatDate(selectedLog.sent_at, true)}</p><p className="text-gray-500">{selectedLog.sent_at_human || (selectedLog.status === "pending" ? "Not sent yet" : "")}</p></div><div><p className="text-[10px] font-bold uppercase tracking-wider text-gray-500">Log ID</p><p className="mt-1 font-semibold text-gray-900">#{selectedLog.id}</p></div></div>
          {selectedLog.status === "failed" && selectedLog.error_message && <section className="rounded-xl border border-rose-200 bg-rose-50 p-4"><h3 className="flex items-center gap-2 text-xs font-bold text-rose-900"><Warning size={15} />Delivery error</h3><p className="mt-2 whitespace-pre-wrap break-words text-xs leading-relaxed text-rose-800">{selectedLog.error_message}</p></section>}
          {selectedLog.metadata && Object.keys(selectedLog.metadata).length > 0 && <section><h3 className="mb-2 text-xs font-bold text-gray-900">Email metadata</h3><pre className="max-h-72 overflow-auto whitespace-pre-wrap break-words rounded-xl border border-gray-200 bg-gray-50 p-4 text-[11px] leading-relaxed text-gray-700">{JSON.stringify(selectedLog.metadata, null, 2)}</pre></section>}
          {selectedLog.booking && <section className="rounded-xl border border-blue-200 bg-blue-50/70 p-4"><h3 className="mb-3 flex items-center gap-2 text-xs font-bold text-blue-950"><CalendarBlank size={15} />Related booking</h3><div className="grid gap-3 text-xs sm:grid-cols-2"><div><p className="text-[10px] uppercase tracking-wide text-blue-700">Reference</p><p className="mt-0.5 font-semibold text-gray-900">{selectedLog.booking.reference || `Booking #${selectedLog.booking.id}`}</p></div><div><p className="text-[10px] uppercase tracking-wide text-blue-700">Status</p><p className="mt-0.5 font-semibold capitalize text-gray-900">{selectedLog.booking.status || "—"}</p></div><div><p className="text-[10px] uppercase tracking-wide text-blue-700">Appointment</p><p className="mt-0.5 font-semibold text-gray-900">{selectedLog.booking.date || "—"}{selectedLog.booking.time ? ` at ${selectedLog.booking.time}` : ""}</p></div><div><p className="text-[10px] uppercase tracking-wide text-blue-700">Customer / provider</p><p className="mt-0.5 font-semibold text-gray-900">{selectedLog.booking.customer_name || "—"} / {selectedLog.booking.provider_name || "—"}</p></div></div></section>}
        </>}</div></section></div>}
    </div>
  );
}
