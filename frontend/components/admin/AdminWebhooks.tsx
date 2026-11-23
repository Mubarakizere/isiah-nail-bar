"use client";

import { useCallback, useEffect, useState } from "react";
import {
  ArrowsClockwise,
  CaretLeft,
  CaretRight,
  CheckCircle,
  ClipboardText,
  Code,
  WebhooksLogo,
  X,
  XCircle,
  Warning,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";

interface WebhookLog {
  id: number;
  type: string | null;
  status: string | null;
  payload: unknown;
  created_at: string | null;
  created_ago: string | null;
}

interface WebhookResponse {
  success: boolean;
  data: WebhookLog[];
  meta: { current_page: number; last_page: number; per_page: number; total: number };
  stats: { total: number; success: number; failed: number; other: number };
}

const EMPTY_META = { current_page: 1, last_page: 1, per_page: 20, total: 0 };
const EMPTY_STATS = { total: 0, success: 0, failed: 0, other: 0 };

async function readResponse(response: Response) {
  const result = await response.json().catch(() => ({}));
  if (!response.ok || result.success === false) {
    const validation = result.errors ? Object.values(result.errors).flat().join(" ") : "";
    throw new Error(validation || result.message || "The request could not be completed.");
  }
  return result;
}

function label(value: string | null | undefined): string {
  if (!value) return "N/A";
  return value.replace(/_/g, " ").replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function normalizedStatus(value: string | null): string {
  return (value || "").toLowerCase();
}

function statusClass(value: string | null): string {
  const status = normalizedStatus(value);
  if (status === "success") return "bg-emerald-50 text-emerald-700 ring-emerald-200";
  if (status === "failed") return "bg-rose-50 text-rose-700 ring-rose-200";
  return "bg-gray-100 text-gray-700 ring-gray-200";
}

function formatDate(value: string | null): string {
  if (!value) return "Date unavailable";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "Date unavailable";
  return new Intl.DateTimeFormat("en", { dateStyle: "medium", timeStyle: "medium" }).format(date);
}

function prettyPayload(payload: unknown): string {
  try {
    return JSON.stringify(payload ?? null, null, 2) ?? "null";
  } catch {
    return String(payload);
  }
}

export default function AdminWebhooks() {
  const [logs, setLogs] = useState<WebhookLog[]>([]);
  const [page, setPage] = useState(1);
  const [meta, setMeta] = useState(EMPTY_META);
  const [stats, setStats] = useState(EMPTY_STATS);
  const [refreshKey, setRefreshKey] = useState(0);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [selectedLog, setSelectedLog] = useState<WebhookLog | null>(null);
  const [toast, setToast] = useState<string | null>(null);

  const fetchLogs = useCallback(async (signal?: AbortSignal) => {
    setLoading(true);
    setLoadError(null);
    try {
      const response = await fetch(`/api/v1/admin/webhooks?page=${page}`, {
        headers: { Accept: "application/json" },
        cache: "no-store",
        signal,
      });
      const result = (await readResponse(response)) as WebhookResponse;
      if (signal?.aborted) return;
      if (result.meta?.last_page && page > result.meta.last_page) {
        setPage(result.meta.last_page);
        return;
      }
      setLogs(Array.isArray(result.data) ? result.data : []);
      setMeta(result.meta || EMPTY_META);
      setStats(result.stats || EMPTY_STATS);
    } catch (error) {
      if (signal?.aborted) return;
      setLoadError(error instanceof Error ? error.message : "Failed to load webhook logs.");
      setLogs([]);
    } finally {
      if (!signal?.aborted) setLoading(false);
    }
  }, [page]);

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

  const copyPayload = async () => {
    if (!selectedLog) return;
    try {
      await navigator.clipboard.writeText(prettyPayload(selectedLog.payload));
      setToast("Webhook payload copied.");
    } catch {
      setToast("Could not copy payload. Select and copy it manually.");
    }
    window.setTimeout(() => setToast(null), 3500);
  };

  const firstResult = meta.total === 0 ? 0 : (meta.current_page - 1) * meta.per_page + 1;
  const lastResult = Math.min(meta.current_page * meta.per_page, meta.total);

  return (
    <div className="flex min-w-0 flex-1 flex-col bg-gray-50 pb-16">
      <AdminHeader title="Webhook Logs" subtitle="Monitor payment gateway webhook activity" />

      {toast && <div role="status" className="fixed bottom-5 right-5 z-[100] max-w-[calc(100vw-2.5rem)] rounded-xl bg-gray-900 px-4 py-3 text-xs font-semibold text-white shadow-xl">{toast}</div>}

      <div className="mx-auto w-full max-w-7xl space-y-4 p-4 sm:p-6">
        <section className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          {([
            ["Total events", stats.total, "bg-blue-50 text-blue-700", <WebhooksLogo size={18} key="all" />],
            ["Successful", stats.success, "bg-emerald-50 text-emerald-700", <CheckCircle size={18} key="success" />],
            ["Failed", stats.failed, "bg-rose-50 text-rose-700", <XCircle size={18} key="failed" />],
            ["Other status", stats.other, "bg-gray-100 text-gray-700", <Code size={18} key="other" />],
          ] as const).map(([title, count, color, icon]) => <div key={title} className="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-2xs"><div><p className="text-[10px] font-bold uppercase tracking-wider text-gray-500">{title}</p><p className="mt-1 text-2xl font-bold tabular-nums text-gray-900">{loading ? "—" : count}</p></div><span className={`rounded-lg p-2.5 ${color}`}>{icon}</span></div>)}
        </section>

        {loadError ? <div role="alert" className="flex flex-col items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-10 text-center"><Warning size={28} className="text-rose-500" /><div><h2 className="text-sm font-bold text-rose-900">Webhook logs could not be loaded</h2><p className="mt-1 text-xs text-rose-800">{loadError}</p></div><button type="button" onClick={() => setRefreshKey((current) => current + 1)} className="rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-800 transition hover:bg-rose-100">Try again</button></div>
          : <section className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xs"><div className="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 sm:px-5"><div><h2 className="text-sm font-bold text-gray-900">Webhook activity</h2><p className="mt-0.5 text-[11px] text-gray-500">{loading ? "Loading activity…" : `${meta.total} ${meta.total === 1 ? "event" : "events"} recorded`}</p></div><button type="button" onClick={() => setRefreshKey((current) => current + 1)} disabled={loading} aria-label="Refresh webhook logs" title="Refresh" className="rounded-lg border border-gray-200 p-2 text-gray-600 transition hover:bg-gray-50 disabled:opacity-50"><ArrowsClockwise size={15} className={loading ? "animate-spin" : ""} /></button></div>
            <div className="overflow-x-auto"><table className="w-full min-w-[760px] text-left text-xs"><thead><tr className="border-b border-gray-200 bg-gray-50/80 text-[10px] font-bold uppercase tracking-wider text-gray-600"><th className="px-4 py-3">ID</th><th className="px-4 py-3">Type</th><th className="px-4 py-3">Status</th><th className="px-4 py-3">Payload</th><th className="px-4 py-3">Received at</th><th className="px-4 py-3 text-right">Action</th></tr></thead><tbody className="divide-y divide-gray-100">
              {loading ? Array.from({ length: 5 }).map((_, index) => <tr key={index} className="animate-pulse"><td colSpan={6} className="px-4 py-4"><div className="h-5 rounded bg-gray-100" /></td></tr>)
                : logs.length === 0 ? <tr><td colSpan={6} className="px-5 py-14 text-center"><WebhooksLogo size={38} className="mx-auto mb-3 text-blue-300" /><h3 className="text-sm font-semibold text-gray-800">No webhook logs yet</h3><p className="mt-1 text-xs text-gray-500">Webhook activity will be recorded here for debugging.</p></td></tr>
                  : logs.map((log) => <tr key={log.id} className="transition-colors hover:bg-gray-50/70"><td className="whitespace-nowrap px-4 py-3.5 font-semibold text-gray-900">#{log.id}</td><td className="px-4 py-3.5"><span className="font-medium capitalize text-gray-700">{label(log.type)}</span></td><td className="px-4 py-3.5"><span className={`inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase ring-1 ring-inset ${statusClass(log.status)}`}>{normalizedStatus(log.status) === "success" ? <CheckCircle size={12} /> : normalizedStatus(log.status) === "failed" ? <XCircle size={12} /> : null}{label(log.status)}</span></td><td className="px-4 py-3.5"><span className="inline-flex items-center gap-1.5 rounded-lg border border-blue-100 bg-blue-50 px-2.5 py-1.5 text-[10px] font-semibold text-blue-700"><Code size={12} />JSON payload</span></td><td className="px-4 py-3.5"><p className="whitespace-nowrap font-medium text-gray-700">{formatDate(log.created_at)}</p><p className="mt-0.5 text-[10px] text-gray-400">{log.created_ago || ""}</p></td><td className="px-4 py-3.5 text-right"><button type="button" onClick={() => setSelectedLog(log)} className="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-[10px] font-semibold text-blue-700 transition hover:bg-blue-100"><Code size={13} />View payload</button></td></tr>)}
            </tbody></table></div>
            {!loading && meta.total > 0 && <div className="flex flex-col gap-3 border-t border-gray-100 bg-gray-50/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5"><p className="text-[11px] text-gray-500">Showing <span className="font-semibold text-gray-700">{firstResult}–{lastResult}</span> of <span className="font-semibold text-gray-700">{meta.total}</span></p><div className="flex items-center justify-between gap-2 sm:justify-end"><button type="button" onClick={() => setPage((current) => Math.max(1, current - 1))} disabled={page <= 1} className="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-2 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"><CaretLeft size={13} />Previous</button><span className="px-1 text-[11px] tabular-nums text-gray-500">Page {page} of {meta.last_page}</span><button type="button" onClick={() => setPage((current) => Math.min(meta.last_page, current + 1))} disabled={page >= meta.last_page} className="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-2 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40">Next<CaretRight size={13} /></button></div></div>}
          </section>}
      </div>

      {selectedLog && <div className="fixed inset-0 z-[90] flex items-center justify-center bg-black/50 p-3 backdrop-blur-xs sm:p-4" onMouseDown={(event) => { if (event.target === event.currentTarget) setSelectedLog(null); }}><section role="dialog" aria-modal="true" aria-labelledby="webhook-payload-title" className="flex max-h-[calc(100dvh-1.5rem)] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl sm:max-h-[90dvh]"><div className="flex items-start justify-between gap-3 border-b border-gray-100 bg-gray-50/70 p-4 sm:px-5"><div className="min-w-0"><h2 id="webhook-payload-title" className="flex items-center gap-2 text-sm font-bold text-gray-900"><Code size={17} className="text-blue-600" />Webhook payload</h2><p className="mt-1 truncate text-[11px] text-gray-500">Event #{selectedLog.id} · {label(selectedLog.type)} · {formatDate(selectedLog.created_at)}</p></div><button type="button" onClick={() => setSelectedLog(null)} aria-label="Close payload" className="shrink-0 rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700"><X size={18} /></button></div><div className="min-h-0 flex-1 overflow-auto bg-gray-950 p-3 sm:p-5"><pre className="whitespace-pre-wrap break-words font-mono text-[11px] leading-relaxed text-emerald-300 sm:text-xs">{prettyPayload(selectedLog.payload)}</pre></div><div className="flex flex-col-reverse gap-2 border-t border-gray-100 bg-gray-50/70 p-3 sm:flex-row sm:justify-end sm:p-4"><button type="button" onClick={() => void copyPayload()} className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-blue-800"><ClipboardText size={15} />Copy payload</button><button type="button" onClick={() => setSelectedLog(null)} className="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">Close</button></div></section></div>}
    </div>
  );
}
