"use client";

import { useCallback, useEffect, useState } from "react";
import type { FormEvent } from "react";
import {
  ArrowsClockwise,
  Check,
  GoogleLogo,
  MagnifyingGlass,
  PencilSimple,
  Plus,
  Star,
  Trash,
  TrendUp,
  Warning,
  X,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";

type ReviewSource = "google" | "internal";

interface ReviewRecord {
  id: number;
  reviewer_name: string;
  reviewer_email: string | null;
  rating: number;
  comment: string;
  source: ReviewSource;
  avatar_url: string | null;
  service_name: string | null;
  created_at: string | null;
  created_at_human: string | null;
}

interface Meta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
}

interface ReviewForm {
  reviewer_name: string;
  rating: number;
  comment: string;
  source: ReviewSource;
  avatar_url: string;
  review_date: string;
}

const EMPTY_META: Meta = { current_page: 1, last_page: 1, per_page: 10, total: 0, from: null, to: null };

function todayLocal(): string {
  const now = new Date();
  const offset = now.getTimezoneOffset() * 60000;
  return new Date(now.getTime() - offset).toISOString().slice(0, 10);
}

function freshForm(): ReviewForm {
  return { reviewer_name: "", rating: 5, comment: "", source: "google", avatar_url: "", review_date: todayLocal() };
}

async function readResponse(response: Response) {
  const result = await response.json().catch(() => ({}));
  if (!response.ok || result.success === false) {
    const validation = result.errors ? Object.values(result.errors).flat().join(" ") : "";
    throw new Error(validation || result.message || "The request could not be completed.");
  }
  return result;
}

function formatDate(value: string | null): string {
  if (!value) return "Date unavailable";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "Date unavailable";
  return new Intl.DateTimeFormat("en", { dateStyle: "medium" }).format(date);
}

function sourceLabel(source: ReviewSource): string {
  return source === "google" ? "Google" : "Internal";
}

export default function AdminReviews() {
  const [reviews, setReviews] = useState<ReviewRecord[]>([]);
  const [meta, setMeta] = useState<Meta>(EMPTY_META);
  const [stats, setStats] = useState({ total_reviews: 0, google_reviews: 0, average_rating: 0 });
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState("");
  const [source, setSource] = useState<"all" | ReviewSource>("all");
  const [refreshKey, setRefreshKey] = useState(0);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);

  const [modalOpen, setModalOpen] = useState(false);
  const [editingReview, setEditingReview] = useState<ReviewRecord | null>(null);
  const [form, setForm] = useState<ReviewForm>(freshForm);
  const [formError, setFormError] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);
  const [reviewToDelete, setReviewToDelete] = useState<ReviewRecord | null>(null);
  const [deleting, setDeleting] = useState(false);
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(null);

  const notify = useCallback((message: string, type: "success" | "error" = "success") => {
    setToast({ message, type });
    window.setTimeout(() => setToast(null), 4000);
  }, []);

  const fetchReviews = useCallback(async (signal?: AbortSignal) => {
    setLoading(true);
    setLoadError(null);
    const params = new URLSearchParams({ page: String(page) });
    if (search.trim()) params.set("search", search.trim());
    if (source !== "all") params.set("source", source);

    try {
      const response = await fetch(`/api/v1/admin/reviews?${params.toString()}`, {
        headers: { Accept: "application/json" },
        cache: "no-store",
        signal,
      });
      const result = await readResponse(response);
      if (signal?.aborted) return;
      if (result.meta?.last_page && page > result.meta.last_page) {
        setPage(result.meta.last_page);
        return;
      }
      setReviews(Array.isArray(result.data) ? result.data : []);
      setMeta(result.meta || EMPTY_META);
      setStats(result.stats || { total_reviews: 0, google_reviews: 0, average_rating: 0 });
    } catch (error) {
      if (signal?.aborted) return;
      setLoadError(error instanceof Error ? error.message : "Failed to load reviews.");
      setReviews([]);
    } finally {
      if (!signal?.aborted) setLoading(false);
    }
  }, [page, search, source]);

  useEffect(() => {
    const controller = new AbortController();
    const timer = window.setTimeout(() => void fetchReviews(controller.signal), search ? 250 : 0);
    return () => {
      window.clearTimeout(timer);
      controller.abort();
    };
  }, [fetchReviews, refreshKey, search]);

  useEffect(() => {
    if (!modalOpen && !reviewToDelete) return;
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key !== "Escape" || saving || deleting) return;
      setModalOpen(false);
      setReviewToDelete(null);
    };
    window.addEventListener("keydown", onKeyDown);
    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener("keydown", onKeyDown);
    };
  }, [modalOpen, reviewToDelete, saving, deleting]);

  const openCreate = () => {
    setEditingReview(null);
    setForm(freshForm());
    setFormError(null);
    setModalOpen(true);
  };

  const openEdit = (review: ReviewRecord) => {
    setEditingReview(review);
    setForm({
      reviewer_name: review.reviewer_name,
      rating: review.rating,
      comment: review.comment || "",
      source: review.source,
      avatar_url: review.avatar_url || "",
      review_date: "",
    });
    setFormError(null);
    setModalOpen(true);
  };

  const saveReview = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (!form.reviewer_name.trim() || !form.comment.trim()) {
      setFormError("Reviewer name and review text are required.");
      return;
    }

    setSaving(true);
    setFormError(null);
    const payload: Record<string, string | number> = {
      reviewer_name: form.reviewer_name.trim(),
      rating: form.rating,
      comment: form.comment.trim(),
      source: form.source,
      avatar_url: form.avatar_url.trim(),
    };
    if (!editingReview && form.review_date) payload.review_date = form.review_date;

    try {
      const response = await fetch(
        editingReview ? `/api/v1/admin/reviews/${editingReview.id}` : "/api/v1/admin/reviews",
        {
          method: editingReview ? "PUT" : "POST",
          headers: { Accept: "application/json", "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        },
      );
      const result = await readResponse(response);
      notify(result.message || "Review saved successfully.");
      setModalOpen(false);
      setEditingReview(null);
      if (!editingReview) setPage(1);
      setRefreshKey((current) => current + 1);
    } catch (error) {
      setFormError(error instanceof Error ? error.message : "The review could not be saved.");
    } finally {
      setSaving(false);
    }
  };

  const deleteReview = async () => {
    if (!reviewToDelete) return;
    setDeleting(true);
    try {
      const response = await fetch(`/api/v1/admin/reviews/${reviewToDelete.id}`, {
        method: "DELETE",
        headers: { Accept: "application/json" },
      });
      const result = await readResponse(response);
      const deletingLastOnPage = reviews.length === 1 && page > 1;
      setReviewToDelete(null);
      notify(result.message || "Review deleted successfully.");
      if (deletingLastOnPage) setPage((current) => current - 1);
      else setRefreshKey((current) => current + 1);
    } catch (error) {
      notify(error instanceof Error ? error.message : "The review could not be deleted.", "error");
    } finally {
      setDeleting(false);
    }
  };

  const updateFilter = (value: string) => {
    setSearch(value);
    setPage(1);
  };

  const selectSource = (value: "all" | ReviewSource) => {
    setSource(value);
    setPage(1);
  };

  const firstResult = meta.total === 0 ? 0 : (meta.current_page - 1) * meta.per_page + 1;
  const lastResult = Math.min(meta.current_page * meta.per_page, meta.total);

  return (
    <div className="flex min-w-0 flex-1 flex-col bg-gray-50 pb-16">
      <AdminHeader title="Customer Reviews" subtitle="Manage customer feedback and Google reviews" />

      {toast && <div role="status" className={`fixed bottom-5 right-5 z-[100] flex max-w-[calc(100vw-2.5rem)] items-center gap-2 rounded-xl px-4 py-3 text-xs font-semibold shadow-xl ${toast.type === "error" ? "bg-rose-50 text-rose-800 ring-1 ring-rose-200" : "bg-gray-900 text-white"}`}>{toast.type === "success" ? <Check size={15} className="shrink-0 text-emerald-400" weight="bold" /> : <Warning size={15} className="shrink-0 text-rose-500" />}<span>{toast.message}</span></div>}

      <div className="mx-auto w-full max-w-7xl space-y-4 p-4 sm:p-6">
        <section className="grid gap-3 sm:grid-cols-3">
          <div className="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-2xs"><div><p className="text-[10px] font-bold uppercase tracking-wider text-gray-500">Total reviews</p><p className="mt-1 text-2xl font-bold tabular-nums text-gray-900">{loading ? "—" : stats.total_reviews}</p></div><span className="rounded-lg bg-rose-50 p-2.5 text-rose-700"><Star size={18} weight="fill" /></span></div>
          <div className="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-2xs"><div><p className="text-[10px] font-bold uppercase tracking-wider text-gray-500">Average rating</p><p className="mt-1 flex items-center gap-1 text-2xl font-bold tabular-nums text-gray-900">{loading ? "—" : stats.average_rating.toFixed(1)}<span className="text-xs font-semibold text-gray-400">/5</span></p></div><span className="rounded-lg bg-amber-50 p-2.5 text-amber-600"><TrendUp size={18} /></span></div>
          <div className="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-2xs"><div><p className="text-[10px] font-bold uppercase tracking-wider text-gray-500">Google reviews</p><p className="mt-1 text-2xl font-bold tabular-nums text-gray-900">{loading ? "—" : stats.google_reviews}</p></div><span className="rounded-lg bg-blue-50 p-2.5 text-blue-700"><GoogleLogo size={18} /></span></div>
        </section>

        <section className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xs">
          <div className="flex flex-col gap-3 border-b border-gray-100 bg-gray-50/60 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div><h2 className="text-sm font-bold text-gray-900">Review directory</h2><p className="mt-0.5 text-[11px] text-gray-500">Internal customer feedback and manually entered Google reviews.</p></div>
            <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
              <div className="relative min-w-0 sm:w-56"><MagnifyingGlass size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" /><input value={search} onChange={(event) => updateFilter(event.target.value)} placeholder="Search reviews…" aria-label="Search reviews" className="w-full rounded-lg border border-gray-200 bg-white py-2 pl-9 pr-3 text-xs outline-none focus:border-gray-400" /></div>
              <div className="flex gap-2"><select value={source} onChange={(event) => selectSource(event.target.value as "all" | ReviewSource)} aria-label="Filter by review source" className="min-w-0 flex-1 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs text-gray-700 outline-none focus:border-gray-400 sm:flex-none"><option value="all">All sources</option><option value="google">Google</option><option value="internal">Internal</option></select><button type="button" onClick={() => setRefreshKey((current) => current + 1)} disabled={loading} aria-label="Refresh reviews" className="rounded-lg border border-gray-200 bg-white p-2 text-gray-600 transition hover:bg-gray-50 disabled:opacity-50"><ArrowsClockwise size={15} className={loading ? "animate-spin" : ""} /></button><button type="button" onClick={openCreate} className="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-rose-700"><Plus size={14} weight="bold" /><span className="hidden sm:inline">Add Google Review</span><span className="sm:hidden">Add</span></button></div>
            </div>
          </div>

          {loadError ? <div role="alert" className="m-4 flex flex-col items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 p-8 text-center sm:m-6"><Warning size={28} className="text-rose-500" /><div><h3 className="text-sm font-bold text-rose-900">Reviews could not be loaded</h3><p className="mt-1 text-xs text-rose-800">{loadError}</p></div><button type="button" onClick={() => setRefreshKey((current) => current + 1)} className="rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-800 hover:bg-rose-100">Try again</button></div>
            : <div className="overflow-x-auto"><table className="w-full min-w-[980px] text-left text-xs"><thead><tr className="border-b border-gray-200 bg-gray-50/80 text-[10px] font-bold uppercase tracking-wider text-gray-600"><th className="px-4 py-3">Customer</th><th className="px-4 py-3">Service</th><th className="px-4 py-3">Rating</th><th className="px-4 py-3">Comment</th><th className="px-4 py-3">Source</th><th className="px-4 py-3">Date</th><th className="px-4 py-3 text-right">Actions</th></tr></thead><tbody className="divide-y divide-gray-100">
              {loading ? Array.from({ length: 4 }).map((_, index) => <tr key={index} className="animate-pulse"><td colSpan={7} className="px-4 py-4"><div className="h-8 rounded bg-gray-100" /></td></tr>)
                : reviews.length === 0 ? <tr><td colSpan={7} className="px-5 py-14 text-center"><Star size={35} className="mx-auto mb-2 text-gray-300" /><p className="text-sm font-semibold text-gray-700">{search || source !== "all" ? "No matching reviews" : "No reviews yet"}</p><p className="mt-1 text-xs text-gray-400">{search || source !== "all" ? "Change your search or source filter." : "Add a Google Business review to feature it on the homepage."}</p>{!search && source === "all" && <button type="button" onClick={openCreate} className="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-800"><Plus size={13} weight="bold" />Add Google Review</button>}</td></tr>
                : reviews.map((review) => <tr key={review.id} className="transition-colors hover:bg-gray-50/70"><td className="px-4 py-3.5"><div className="flex min-w-[160px] items-center gap-2.5">{review.avatar_url ? <img src={review.avatar_url} alt="" className="h-9 w-9 shrink-0 rounded-full border border-gray-100 object-cover" /> : <span className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white ${review.source === "google" ? "bg-gradient-to-br from-blue-400 to-blue-600" : "bg-gradient-to-br from-rose-400 to-rose-600"}`}>{review.reviewer_name?.charAt(0).toUpperCase() || "?"}</span>}<div className="min-w-0"><p className="max-w-[190px] truncate font-semibold text-gray-900">{review.reviewer_name || "Anonymous"}</p><p className="max-w-[190px] truncate text-[10px] text-gray-400">{review.reviewer_email || (review.source === "google" ? "External review" : "Customer review")}</p></div></div></td><td className="px-4 py-3.5"><span className="inline-flex max-w-[150px] items-center gap-1.5 truncate rounded-full bg-rose-50 px-2.5 py-1 text-[10px] font-semibold text-rose-700">{review.service_name || "General Review"}</span></td><td className="px-4 py-3.5"><div className="flex items-center gap-0.5 text-amber-400">{Array.from({ length: 5 }).map((_, index) => <Star key={index} size={12} weight={index < review.rating ? "fill" : "regular"} />)}<span className="ml-1 text-[10px] font-semibold text-gray-600">{review.rating}/5</span></div></td><td className="px-4 py-3.5"><p className="max-w-[220px] truncate text-gray-700" title={review.comment}>{review.comment || "—"}</p></td><td className="px-4 py-3.5"><span className={`inline-flex items-center gap-1 rounded-md px-2 py-1 text-[10px] font-semibold ${review.source === "google" ? "bg-blue-50 text-blue-700" : "bg-emerald-50 text-emerald-700"}`}>{review.source === "google" ? <GoogleLogo size={12} /> : <Check size={12} />}{sourceLabel(review.source)}</span></td><td className="px-4 py-3.5"><p className="font-medium text-gray-700">{formatDate(review.created_at)}</p><p className="mt-0.5 text-[10px] text-gray-400">{review.created_at_human || ""}</p></td><td className="px-4 py-3.5 text-right"><div className="flex items-center justify-end gap-1.5"><button type="button" onClick={() => openEdit(review)} className="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-[10px] font-semibold text-gray-700 shadow-2xs transition hover:bg-gray-50"><PencilSimple size={12} />Edit</button><button type="button" onClick={() => setReviewToDelete(review)} aria-label={`Delete review by ${review.reviewer_name}`} title="Delete review" className="rounded-lg border border-rose-200 bg-white p-1.5 text-rose-600 shadow-2xs transition hover:bg-rose-50"><Trash size={13} /></button></div></td></tr>)}
            </tbody></table></div>}

          {!loadError && !loading && meta.total > 0 && <div className="flex flex-col gap-3 border-t border-gray-100 bg-gray-50/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5"><p className="text-[11px] text-gray-500">Showing <span className="font-semibold text-gray-700">{firstResult}–{lastResult}</span> of <span className="font-semibold text-gray-700">{meta.total}</span> reviews</p><div className="flex items-center justify-between gap-2 sm:justify-end"><button type="button" onClick={() => setPage((current) => Math.max(1, current - 1))} disabled={page <= 1} className="rounded-lg border border-gray-200 bg-white px-3 py-2 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40">Previous</button><span className="px-1 text-[11px] tabular-nums text-gray-500">Page {page} of {meta.last_page}</span><button type="button" onClick={() => setPage((current) => Math.min(meta.last_page, current + 1))} disabled={page >= meta.last_page} className="rounded-lg border border-gray-200 bg-white px-3 py-2 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40">Next</button></div></div>}
        </section>
      </div>

      {modalOpen && <div className="fixed inset-0 z-[90] flex items-center justify-center bg-black/50 p-3 backdrop-blur-xs sm:p-4" onMouseDown={(event) => { if (event.target === event.currentTarget && !saving) setModalOpen(false); }}><section role="dialog" aria-modal="true" aria-labelledby="review-form-title" className="max-h-[calc(100dvh-1.5rem)] w-full max-w-2xl overflow-y-auto rounded-2xl border border-gray-200 bg-white shadow-2xl sm:max-h-[calc(100dvh-2rem)]"><div className="sticky top-0 z-10 flex items-center justify-between border-b border-gray-100 bg-gray-50/95 p-4 backdrop-blur sm:px-6"><div className="flex min-w-0 items-center gap-3"><span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-600 text-white">{editingReview ? <PencilSimple size={18} /> : <GoogleLogo size={18} />}</span><div className="min-w-0"><h2 id="review-form-title" className="text-sm font-bold text-gray-900">{editingReview ? "Edit review" : "Add Google review"}</h2><p className="mt-0.5 text-[11px] text-gray-500">{editingReview ? `Update ${sourceLabel(form.source)} review details.` : "Copy a review from your Google Business Profile."}</p></div></div><button type="button" onClick={() => setModalOpen(false)} disabled={saving} aria-label="Close dialog" className="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 disabled:opacity-50"><X size={17} /></button></div>
        <form onSubmit={saveReview} className="space-y-4 p-4 sm:space-y-5 sm:p-6">
          {!editingReview && <div className="flex items-start gap-3 rounded-xl border border-blue-200 bg-blue-50 p-3.5"><GoogleLogo size={19} className="mt-0.5 shrink-0 text-blue-600" /><p className="text-xs leading-relaxed text-blue-900">Copy the reviewer’s details from your <a href="https://business.google.com" target="_blank" rel="noopener noreferrer" className="font-semibold underline">Google Business Profile</a>. This review will also be included in the homepage review feed.</p></div>}
          {editingReview && <div><label htmlFor="review-source" className="mb-1.5 block text-xs font-semibold text-gray-700">Source</label><select id="review-source" value={form.source} onChange={(event) => setForm((current) => ({ ...current, source: event.target.value as ReviewSource }))} className="w-full rounded-lg border border-gray-200 bg-white px-3.5 py-2.5 text-sm outline-none focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10"><option value="google">Google</option><option value="internal">Internal</option></select></div>}
          <div><label htmlFor="reviewer-name" className="mb-1.5 block text-xs font-semibold text-gray-700">Reviewer name <span className="text-rose-500">*</span></label><input id="reviewer-name" required maxLength={255} value={form.reviewer_name} onChange={(event) => setForm((current) => ({ ...current, reviewer_name: event.target.value }))} placeholder="e.g. Jane Uwimana" className="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10" /></div>
          <div><span className="mb-1.5 block text-xs font-semibold text-gray-700">Rating <span className="text-rose-500">*</span></span><div className="flex items-center gap-1" role="radiogroup" aria-label="Review rating">{[1, 2, 3, 4, 5].map((rating) => <button key={rating} type="button" role="radio" aria-checked={form.rating === rating} aria-label={`${rating} star${rating > 1 ? "s" : ""}`} onClick={() => setForm((current) => ({ ...current, rating }))} className="rounded p-1 text-2xl transition hover:scale-110 focus:outline-none focus:ring-2 focus:ring-amber-300"><Star size={25} weight={rating <= form.rating ? "fill" : "regular"} className={rating <= form.rating ? "text-amber-400" : "text-gray-300"} /></button>)}<span className="ml-2 text-xs font-semibold text-gray-500">{form.rating}/5</span></div></div>
          <div><label htmlFor="review-comment" className="mb-1.5 block text-xs font-semibold text-gray-700">Review text <span className="text-rose-500">*</span></label><textarea id="review-comment" required rows={4} maxLength={10000} value={form.comment} onChange={(event) => setForm((current) => ({ ...current, comment: event.target.value }))} placeholder="Paste the review text…" className="w-full resize-y rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10" /></div>
          <div className="grid gap-4 sm:grid-cols-2"><div><label htmlFor="review-avatar" className="mb-1.5 block text-xs font-semibold text-gray-700">Profile photo URL <span className="font-normal text-gray-400">(optional)</span></label><input id="review-avatar" type="url" maxLength={2048} value={form.avatar_url} onChange={(event) => setForm((current) => ({ ...current, avatar_url: event.target.value }))} placeholder="https://…" className="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10" /></div>{!editingReview && <div><label htmlFor="review-date" className="mb-1.5 block text-xs font-semibold text-gray-700">Original review date <span className="font-normal text-gray-400">(optional)</span></label><input id="review-date" type="date" value={form.review_date} onChange={(event) => setForm((current) => ({ ...current, review_date: event.target.value }))} className="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10" /><p className="mt-1 text-[10px] text-gray-400">Used to place the review in the correct date order.</p></div>}</div>
          {formError && <p role="alert" className="text-xs text-rose-700">{formError}</p>}
          <div className="flex flex-col-reverse gap-2 border-t border-gray-100 pt-4 sm:flex-row sm:justify-end"><button type="button" onClick={() => setModalOpen(false)} disabled={saving} className="rounded-lg border border-gray-200 px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-50">Cancel</button><button type="submit" disabled={saving} className="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-rose-700 disabled:opacity-50">{saving ? <ArrowsClockwise size={14} className="animate-spin" /> : editingReview ? <Check size={14} weight="bold" /> : <GoogleLogo size={14} />}{saving ? "Saving…" : editingReview ? "Save changes" : "Add Google review"}</button></div>
        </form></section></div>}

      {reviewToDelete && <div className="fixed inset-0 z-[90] flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs" onMouseDown={(event) => { if (event.target === event.currentTarget && !deleting) setReviewToDelete(null); }}><section role="alertdialog" aria-modal="true" aria-labelledby="delete-review-title" className="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6"><div className="flex items-start gap-3"><span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-600"><Warning size={20} /></span><div className="min-w-0"><h2 id="delete-review-title" className="text-sm font-bold text-gray-900">Delete this review?</h2><p className="mt-1 break-words text-xs leading-relaxed text-gray-600">The review by <strong className="text-gray-900">{reviewToDelete.reviewer_name}</strong> will be removed from the site.</p></div></div><div className="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"><button type="button" onClick={() => setReviewToDelete(null)} disabled={deleting} className="rounded-lg border border-gray-200 px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-50">Cancel</button><button type="button" onClick={() => void deleteReview()} disabled={deleting} className="inline-flex items-center justify-center gap-2 rounded-lg bg-rose-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-rose-700 disabled:opacity-50">{deleting ? <ArrowsClockwise size={14} className="animate-spin" /> : <Trash size={14} />}Delete review</button></div></section></div>}
    </div>
  );
}
