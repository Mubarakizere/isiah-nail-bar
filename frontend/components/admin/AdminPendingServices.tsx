"use client";

import { useCallback, useEffect, useState } from "react";
import {
  ArrowsClockwise,
  CaretLeft,
  CaretRight,
  Check,
  Clock,
  Folder,
  Hourglass,
  Scissors,
  Trash,
  User,
  Warning,
  X,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";

interface PendingService {
  id: number;
  name: string;
  description: string | null;
  price: number;
  duration_minutes: number;
  category: { id: number; name: string } | null;
  provider_name: string;
  image_url: string | null;
  created_at: string | null;
  created_ago: string | null;
}

interface PendingServicesResponse {
  success: boolean;
  data: PendingService[];
  meta: { current_page: number; last_page: number; per_page: number; total: number };
}

async function readResponse(response: Response) {
  const result = await response.json().catch(() => ({}));
  if (!response.ok || result.success === false) {
    const validation = result.errors ? Object.values(result.errors).flat().join(" ") : "";
    throw new Error(validation || result.message || "The request could not be completed.");
  }
  return result;
}

function formatRwf(value: number): string {
  return `RWF ${new Intl.NumberFormat("en-RW", { maximumFractionDigits: 0 }).format(value || 0)}`;
}

export default function AdminPendingServices() {
  const [services, setServices] = useState<PendingService[]>([]);
  const [page, setPage] = useState(1);
  const [meta, setMeta] = useState({ current_page: 1, last_page: 1, per_page: 10, total: 0 });
  const [refreshKey, setRefreshKey] = useState(0);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [busyId, setBusyId] = useState<number | null>(null);
  const [serviceToReject, setServiceToReject] = useState<PendingService | null>(null);
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(null);

  const fetchServices = useCallback(async (signal?: AbortSignal) => {
    setLoading(true);
    setLoadError(null);
    try {
      const response = await fetch(`/api/v1/admin/pending-services?page=${page}`, {
        headers: { Accept: "application/json" },
        cache: "no-store",
        signal,
      });
      const result = (await readResponse(response)) as PendingServicesResponse;
      if (signal?.aborted) return;
      if (result.meta?.last_page && page > result.meta.last_page) {
        setPage(result.meta.last_page);
        return;
      }
      setServices(Array.isArray(result.data) ? result.data : []);
      setMeta(result.meta || { current_page: 1, last_page: 1, per_page: 10, total: 0 });
    } catch (error) {
      if (signal?.aborted) return;
      setLoadError(error instanceof Error ? error.message : "Failed to load pending services.");
      setServices([]);
    } finally {
      if (!signal?.aborted) setLoading(false);
    }
  }, [page]);

  useEffect(() => {
    const controller = new AbortController();
    void fetchServices(controller.signal);
    return () => controller.abort();
  }, [fetchServices, refreshKey]);

  useEffect(() => {
    if (!serviceToReject) return;
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape" && busyId === null) setServiceToReject(null);
    };
    window.addEventListener("keydown", onKeyDown);
    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener("keydown", onKeyDown);
    };
  }, [serviceToReject, busyId]);

  const notify = (message: string, type: "success" | "error" = "success") => {
    setToast({ message, type });
    window.setTimeout(() => setToast(null), 4500);
  };

  const runAction = async (service: PendingService, action: "approve" | "reject") => {
    setBusyId(service.id);
    try {
      const response = await fetch(`/api/v1/admin/pending-services/${service.id}/${action}`, {
        method: "POST",
        headers: { Accept: "application/json" },
      });
      const result = await readResponse(response);
      notify(result.message || (action === "approve" ? "Service approved." : "Service rejected."));
      const rejectingLastItem = action === "reject" && services.length === 1 && page > 1;
      setServiceToReject(null);
      if (rejectingLastItem) setPage((current) => current - 1);
      else setRefreshKey((current) => current + 1);
    } catch (error) {
      notify(error instanceof Error ? error.message : "The service could not be updated.", "error");
    } finally {
      setBusyId(null);
    }
  };

  const firstResult = meta.total === 0 ? 0 : (meta.current_page - 1) * meta.per_page + 1;
  const lastResult = Math.min(meta.current_page * meta.per_page, meta.total);

  return (
    <div className="flex min-w-0 flex-1 flex-col bg-gray-50 pb-16">
      <AdminHeader title="Pending Services" subtitle="Review and approve provider submissions" />

      {toast && <div role="status" className={`fixed bottom-5 right-5 z-[100] flex max-w-[calc(100vw-2.5rem)] items-center gap-2 rounded-xl px-4 py-3 text-xs font-semibold shadow-xl ${toast.type === "error" ? "bg-rose-50 text-rose-800 ring-1 ring-rose-200" : "bg-gray-900 text-white"}`}>{toast.type === "success" ? <Check size={15} className="shrink-0 text-emerald-400" weight="bold" /> : <Warning size={15} className="shrink-0 text-rose-500" />}<span>{toast.message}</span></div>}

      <div className="mx-auto w-full max-w-7xl space-y-4 p-4 sm:p-6">
        <section className="flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50/70 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5"><div className="flex items-center gap-3"><span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700"><Hourglass size={19} weight="duotone" /></span><div><p className="text-[10px] font-bold uppercase tracking-wider text-amber-800">Awaiting review</p><p className="mt-0.5 text-lg font-bold tabular-nums text-gray-900">{loading ? "—" : meta.total} <span className="text-xs font-medium text-gray-500">provider submissions</span></p></div></div><button type="button" onClick={() => setRefreshKey((current) => current + 1)} disabled={loading} className="inline-flex items-center justify-center gap-2 rounded-lg border border-amber-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 transition hover:bg-amber-50 disabled:opacity-50"><ArrowsClockwise size={14} className={loading ? "animate-spin" : ""} />Refresh</button></section>

        {loadError ? <div role="alert" className="flex flex-col items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-10 text-center"><Warning size={28} className="text-rose-500" /><div><h2 className="text-sm font-bold text-rose-900">Pending services could not be loaded</h2><p className="mt-1 text-xs text-rose-800">{loadError}</p></div><button type="button" onClick={() => setRefreshKey((current) => current + 1)} className="rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-800 transition hover:bg-rose-100">Try again</button></div>
          : loading ? <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">{Array.from({ length: 6 }).map((_, index) => <div key={index} className="animate-pulse rounded-2xl border border-gray-200 bg-white p-5"><div className="h-5 w-3/4 rounded bg-gray-100" /><div className="mt-5 h-4 rounded bg-gray-100" /><div className="mt-2 h-4 w-2/3 rounded bg-gray-100" /><div className="mt-6 h-10 rounded bg-gray-100" /></div>)}</div>
            : services.length === 0 ? <div className="rounded-2xl border border-blue-200 bg-blue-50 px-6 py-14 text-center"><span className="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-blue-500"><Check size={29} weight="bold" /></span><h2 className="mt-4 text-base font-bold text-blue-950">All caught up!</h2><p className="mt-1 text-xs text-blue-800">No pending service submissions to review right now.</p></div>
              : <><div className="grid items-stretch gap-4 sm:grid-cols-2 lg:grid-cols-3">{services.map((service) => <article key={service.id} className="flex min-w-0 flex-col overflow-hidden rounded-2xl border-2 border-amber-200 bg-white shadow-2xs transition hover:shadow-md">{service.image_url && <img src={service.image_url} alt="" className="h-40 w-full bg-gray-100 object-cover" />}<div className="flex flex-1 flex-col p-4 sm:p-5"><div className="mb-4 flex items-start justify-between gap-2"><h2 className="min-w-0 text-base font-bold text-gray-900">{service.name}</h2><span className="inline-flex shrink-0 items-center gap-1 rounded-full bg-amber-100 px-2 py-1 text-[10px] font-bold text-amber-800"><Hourglass size={11} />Pending</span></div><div className="mb-3 grid grid-cols-2 gap-2 text-xs text-gray-600"><span className="inline-flex min-w-0 items-center gap-1.5"><Folder size={13} className="shrink-0 text-gray-400" /><span className="truncate">{service.category?.name || "Uncategorized"}</span></span><span className="inline-flex items-center gap-1.5"><Clock size={13} className="shrink-0 text-gray-400" />{service.duration_minutes} min</span><span className="inline-flex items-center gap-1.5 font-semibold text-emerald-700"><Scissors size={13} className="shrink-0" />{formatRwf(service.price)}</span><span className="inline-flex min-w-0 items-center gap-1.5"><User size={13} className="shrink-0 text-gray-400" /><span className="truncate">{service.provider_name || "Unknown provider"}</span></span></div><p className="line-clamp-4 min-h-[4.5rem] text-xs leading-relaxed text-gray-600">{service.description || "No description provided."}</p><p className="mt-auto pt-3 text-[10px] text-gray-400">Submitted {service.created_ago || "recently"}</p></div><div className="flex gap-2 border-t border-gray-100 bg-gray-50/70 p-4"><button type="button" onClick={() => void runAction(service, "approve")} disabled={busyId !== null} className="inline-flex min-w-0 flex-1 items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2.5 text-xs font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50">{busyId === service.id ? <ArrowsClockwise size={14} className="animate-spin" /> : <Check size={14} weight="bold" />}{busyId === service.id ? "Working…" : "Approve"}</button><button type="button" onClick={() => setServiceToReject(service)} disabled={busyId !== null} className="inline-flex min-w-0 flex-1 items-center justify-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-50"><X size={14} weight="bold" />Reject</button></div></article>)}</div>
              {meta.total > 0 && <div className="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5"><p className="text-[11px] text-gray-500">Showing <span className="font-semibold text-gray-700">{firstResult}–{lastResult}</span> of <span className="font-semibold text-gray-700">{meta.total}</span> pending services</p><div className="flex items-center justify-between gap-2 sm:justify-end"><button type="button" onClick={() => setPage((current) => Math.max(1, current - 1))} disabled={page <= 1} className="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-2 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"><CaretLeft size={13} />Previous</button><span className="px-1 text-[11px] tabular-nums text-gray-500">Page {page} of {meta.last_page}</span><button type="button" onClick={() => setPage((current) => Math.min(meta.last_page, current + 1))} disabled={page >= meta.last_page} className="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-2 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40">Next<CaretRight size={13} /></button></div></div>}</>}
      </div>

      {serviceToReject && <div className="fixed inset-0 z-[90] flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs" onMouseDown={(event) => { if (event.target === event.currentTarget && busyId === null) setServiceToReject(null); }}><section role="alertdialog" aria-modal="true" aria-labelledby="reject-service-title" className="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6"><div className="flex items-start gap-3"><span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-600"><Warning size={20} /></span><div className="min-w-0"><h2 id="reject-service-title" className="text-sm font-bold text-gray-900">Reject this service?</h2><p className="mt-1 break-words text-xs leading-relaxed text-gray-600"><strong className="text-gray-900">{serviceToReject.name}</strong> will be removed from the submissions and the provider will be notified. This cannot be undone.</p></div></div><div className="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"><button type="button" onClick={() => setServiceToReject(null)} disabled={busyId !== null} className="rounded-lg border border-gray-200 px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-50">Cancel</button><button type="button" onClick={() => void runAction(serviceToReject, "reject")} disabled={busyId !== null} className="inline-flex items-center justify-center gap-2 rounded-lg bg-rose-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-rose-700 disabled:opacity-50">{busyId === serviceToReject.id ? <ArrowsClockwise size={14} className="animate-spin" /> : <Trash size={14} />}{busyId === serviceToReject.id ? "Rejecting…" : "Reject service"}</button></div></section></div>}
    </div>
  );
}
