"use client";

import { useCallback, useEffect, useState } from "react";
import type { FormEvent } from "react";
import {
  ArrowsClockwise,
  Check,
  MagnifyingGlass,
  PencilSimple,
  Plus,
  Tag as TagIcon,
  Trash,
  Warning,
  X,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";

interface AdminTag {
  id: number;
  name: string;
  created_at: string | null;
  created_ago: string | null;
}

interface Pagination {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
}

interface TagListResponse {
  success: boolean;
  data: AdminTag[];
  pagination: Pagination;
}

const EMPTY_PAGINATION: Pagination = {
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: null,
  to: null,
};

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

export default function AdminTags() {
  const [tags, setTags] = useState<AdminTag[]>([]);
  const [pagination, setPagination] = useState<Pagination>(EMPTY_PAGINATION);
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState("");
  const [refreshKey, setRefreshKey] = useState(0);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);

  const [modalOpen, setModalOpen] = useState(false);
  const [editingTag, setEditingTag] = useState<AdminTag | null>(null);
  const [formName, setFormName] = useState("");
  const [formError, setFormError] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);

  const [tagToDelete, setTagToDelete] = useState<AdminTag | null>(null);
  const [deleting, setDeleting] = useState(false);
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(null);

  const showToast = useCallback((message: string, type: "success" | "error" = "success") => {
    setToast({ message, type });
    window.setTimeout(() => setToast(null), 4000);
  }, []);

  const fetchTags = useCallback(async (signal?: AbortSignal) => {
    setLoading(true);
    setLoadError(null);
    const params = new URLSearchParams({ page: String(page) });
    if (search.trim()) params.set("search", search.trim());

    try {
      const response = await fetch(`/api/v1/admin/tags?${params.toString()}`, {
        headers: { Accept: "application/json" },
        cache: "no-store",
        signal,
      });
      const result = (await readResponse(response)) as TagListResponse;
      if (signal?.aborted) return;

      if (result.pagination?.last_page && page > result.pagination.last_page) {
        setPage(result.pagination.last_page);
        return;
      }

      setTags(Array.isArray(result.data) ? result.data : []);
      setPagination(result.pagination || EMPTY_PAGINATION);
    } catch (error) {
      if (signal?.aborted) return;
      setLoadError(error instanceof Error ? error.message : "Failed to load tags.");
      setTags([]);
    } finally {
      if (!signal?.aborted) setLoading(false);
    }
  }, [page, search]);

  useEffect(() => {
    const controller = new AbortController();
    const timer = window.setTimeout(() => void fetchTags(controller.signal), search ? 250 : 0);
    return () => {
      window.clearTimeout(timer);
      controller.abort();
    };
  }, [fetchTags, refreshKey, search]);

  useEffect(() => {
    if (!modalOpen && !tagToDelete) return;
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key !== "Escape" || saving || deleting) return;
      setModalOpen(false);
      setTagToDelete(null);
    };
    window.addEventListener("keydown", onKeyDown);
    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener("keydown", onKeyDown);
    };
  }, [modalOpen, tagToDelete, saving, deleting]);

  const openCreateModal = () => {
    setEditingTag(null);
    setFormName("");
    setFormError(null);
    setModalOpen(true);
  };

  const openEditModal = (tag: AdminTag) => {
    setEditingTag(tag);
    setFormName(tag.name);
    setFormError(null);
    setModalOpen(true);
  };

  const saveTag = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (!formName.trim()) {
      setFormError("Tag name is required.");
      return;
    }

    setSaving(true);
    setFormError(null);
    try {
      const response = await fetch(
        editingTag ? `/api/v1/admin/tags/${editingTag.id}` : "/api/v1/admin/tags",
        {
          method: editingTag ? "PUT" : "POST",
          headers: { Accept: "application/json", "Content-Type": "application/json" },
          body: JSON.stringify({ name: formName.trim() }),
        },
      );
      const result = await readResponse(response);
      showToast(result.message || "Tag saved successfully.");
      setModalOpen(false);
      setEditingTag(null);
      if (!editingTag) {
        setSearch("");
        setPage(1);
      }
      setRefreshKey((current) => current + 1);
    } catch (error) {
      setFormError(error instanceof Error ? error.message : "Failed to save tag.");
    } finally {
      setSaving(false);
    }
  };

  const deleteTag = async () => {
    if (!tagToDelete) return;
    setDeleting(true);
    try {
      const response = await fetch(`/api/v1/admin/tags/${tagToDelete.id}`, {
        method: "DELETE",
        headers: { Accept: "application/json" },
      });
      const result = await readResponse(response);
      showToast(result.message || "Tag deleted successfully.");
      setTagToDelete(null);
      if (tags.length === 1 && page > 1) setPage((current) => current - 1);
      else setRefreshKey((current) => current + 1);
    } catch (error) {
      showToast(error instanceof Error ? error.message : "Failed to delete tag.", "error");
    } finally {
      setDeleting(false);
    }
  };

  const handleSearch = (value: string) => {
    setSearch(value);
    setPage(1);
  };

  return (
    <div className="flex min-w-0 flex-1 flex-col bg-gray-50 pb-16">
      <AdminHeader title="Tags" subtitle="Manage saved service tag names" />

      {toast && (
        <div role="status" className={`fixed bottom-5 right-5 z-[100] flex max-w-[calc(100vw-2.5rem)] items-center gap-2 rounded-xl px-4 py-3 text-xs font-semibold shadow-xl ${toast.type === "error" ? "bg-rose-50 text-rose-800 ring-1 ring-rose-200" : "bg-gray-900 text-white"}`}>
          {toast.type === "success" ? <Check size={15} className="shrink-0 text-emerald-400" weight="bold" /> : <Warning size={15} className="shrink-0 text-rose-500" />}
          <span>{toast.message}</span>
        </div>
      )}

      <div className="mx-auto w-full max-w-7xl space-y-4 p-4 sm:p-6">
        <section className="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-3.5 shadow-2xs sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-center gap-3">
            <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700"><TagIcon size={19} weight="duotone" /></span>
            <div><p className="text-[10px] font-bold uppercase tracking-wider text-gray-500">Saved tags</p><p className="mt-0.5 text-lg font-bold tabular-nums text-gray-900">{loading ? "—" : pagination.total}</p></div>
          </div>
          <button type="button" onClick={openCreateModal} className="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-gray-800 sm:w-auto"><Plus size={14} weight="bold" />Add Tag</button>
        </section>

        <section className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xs">
          <div className="flex flex-col gap-3 border-b border-gray-100 bg-gray-50/60 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div><h2 className="flex items-center gap-1.5 text-sm font-bold text-gray-900"><TagIcon size={15} />Tag directory</h2><p className="mt-0.5 text-[11px] text-gray-500">Create, rename, or remove saved tag names.</p></div>
            <div className="flex items-center gap-2">
              <div className="relative min-w-0 flex-1 sm:w-64"><MagnifyingGlass size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" /><input value={search} onChange={(event) => handleSearch(event.target.value)} placeholder="Search tags…" aria-label="Search tags" className="w-full rounded-lg border border-gray-200 bg-white py-2 pl-9 pr-3 text-xs outline-none focus:border-gray-400" /></div>
              <button type="button" onClick={() => setRefreshKey((current) => current + 1)} disabled={loading} aria-label="Refresh tags" title="Refresh" className="rounded-lg border border-gray-200 bg-white p-2 text-gray-600 transition hover:bg-gray-50 disabled:opacity-50"><ArrowsClockwise size={15} className={loading ? "animate-spin" : ""} /></button>
            </div>
          </div>

          {loadError ? (
            <div role="alert" className="m-4 flex flex-col items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 p-8 text-center sm:m-6"><Warning size={28} className="text-rose-500" /><div><h3 className="text-sm font-bold text-rose-900">Tags could not be loaded</h3><p className="mt-1 text-xs text-rose-800">{loadError}</p></div><button type="button" onClick={() => setRefreshKey((current) => current + 1)} className="rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-800 hover:bg-rose-100">Try again</button></div>
          ) : (
            <>
              <div className="overflow-x-auto">
                <table className="w-full min-w-[560px] text-left text-xs">
                  <thead><tr className="border-b border-gray-200 bg-gray-50/80 text-[10px] font-bold uppercase tracking-wider text-gray-600"><th className="px-5 py-3">#</th><th className="px-5 py-3">Tag name</th><th className="px-5 py-3">Created</th><th className="px-5 py-3 text-right">Actions</th></tr></thead>
                  <tbody className="divide-y divide-gray-100">
                    {loading ? Array.from({ length: 4 }).map((_, index) => <tr key={index} className="animate-pulse"><td colSpan={4} className="px-5 py-4"><div className="h-5 rounded bg-gray-100" /></td></tr>)
                      : tags.length === 0 ? <tr><td colSpan={4} className="px-5 py-14 text-center"><TagIcon size={34} className="mx-auto mb-2 text-gray-300" /><p className="text-sm font-semibold text-gray-700">{search ? "No matching tags" : "No tags found"}</p><p className="mt-1 text-xs text-gray-400">{search ? `No tag matches “${search}”. Try another search.` : "Create your first tag to get started."}</p>{!search && <button type="button" onClick={openCreateModal} className="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-800"><Plus size={13} weight="bold" />Add first tag</button>}</td></tr>
                      : tags.map((tag, index) => <tr key={tag.id} className="transition-colors hover:bg-gray-50/70"><td className="px-5 py-3.5 tabular-nums text-gray-400">{(page - 1) * pagination.per_page + index + 1}</td><td className="px-5 py-3.5"><span className="inline-flex max-w-full items-center gap-1.5 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-800"><TagIcon size={12} className="shrink-0" /><span className="truncate">{tag.name}</span></span></td><td className="px-5 py-3.5"><p className="font-medium text-gray-800">{formatDate(tag.created_at)}</p><p className="mt-0.5 text-[10px] text-gray-400">{tag.created_ago || ""}</p></td><td className="px-5 py-3.5 text-right"><div className="flex items-center justify-end gap-1.5"><button type="button" onClick={() => openEditModal(tag)} aria-label={`Edit ${tag.name}`} title="Edit tag" className="rounded-lg border border-gray-200 bg-white p-2 text-gray-600 shadow-2xs transition hover:bg-gray-100 hover:text-gray-900"><PencilSimple size={13} /></button><button type="button" onClick={() => setTagToDelete(tag)} aria-label={`Delete ${tag.name}`} title="Delete tag" className="rounded-lg border border-rose-200 bg-white p-2 text-rose-600 shadow-2xs transition hover:bg-rose-50"><Trash size={13} /></button></div></td></tr>)}
                  </tbody>
                </table>
              </div>

              {!loading && pagination.total > 0 && <div className="flex flex-col gap-3 border-t border-gray-100 bg-gray-50/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5"><p className="text-[11px] text-gray-500">Showing <span className="font-semibold text-gray-700">{pagination.from}–{pagination.to}</span> of <span className="font-semibold text-gray-700">{pagination.total}</span> tags</p><div className="flex items-center justify-between gap-2 sm:justify-end"><button type="button" onClick={() => setPage((current) => Math.max(1, current - 1))} disabled={page <= 1} className="rounded-lg border border-gray-200 bg-white px-3 py-2 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40">Previous</button><span className="px-1 text-[11px] tabular-nums text-gray-500">Page {page} of {pagination.last_page}</span><button type="button" onClick={() => setPage((current) => Math.min(pagination.last_page, current + 1))} disabled={page >= pagination.last_page} className="rounded-lg border border-gray-200 bg-white px-3 py-2 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40">Next</button></div></div>}
            </>
          )}
        </section>
      </div>

      {modalOpen && <div className="fixed inset-0 z-[90] flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs" onMouseDown={(event) => { if (event.target === event.currentTarget && !saving) setModalOpen(false); }}><section role="dialog" aria-modal="true" aria-labelledby="tag-form-title" className="w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl"><div className="flex items-center justify-between border-b border-gray-100 bg-gray-50/50 p-4 sm:p-5"><div className="flex min-w-0 items-center gap-2.5"><span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gray-900 text-white">{editingTag ? <PencilSimple size={16} /> : <TagIcon size={17} />}</span><div className="min-w-0"><h2 id="tag-form-title" className="text-sm font-bold text-gray-900">{editingTag ? "Edit tag" : "Add new tag"}</h2><p className="mt-0.5 text-[11px] text-gray-500">{editingTag ? "Update the saved tag name." : "Create a saved service tag name."}</p></div></div><button type="button" onClick={() => setModalOpen(false)} disabled={saving} aria-label="Close dialog" className="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 disabled:opacity-50"><X size={17} /></button></div><form onSubmit={saveTag} className="p-4 sm:p-5"><label htmlFor="tag-name" className="mb-2 block text-xs font-semibold text-gray-700">Tag name</label><input id="tag-name" type="text" required maxLength={255} autoFocus value={formName} onChange={(event) => { setFormName(event.target.value); setFormError(null); }} placeholder="Enter tag name" className="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none transition placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10" />{formError && <p role="alert" className="mt-2 text-xs text-rose-700">{formError}</p>}<div className="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"><button type="button" onClick={() => setModalOpen(false)} disabled={saving} className="rounded-lg border border-gray-200 px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-50">Cancel</button><button type="submit" disabled={saving} className="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-xs font-semibold text-white hover:bg-gray-800 disabled:opacity-50">{saving ? <ArrowsClockwise size={14} className="animate-spin" /> : <Check size={14} weight="bold" />}{saving ? "Saving…" : editingTag ? "Save changes" : "Save tag"}</button></div></form></section></div>}

      {tagToDelete && <div className="fixed inset-0 z-[90] flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs" onMouseDown={(event) => { if (event.target === event.currentTarget && !deleting) setTagToDelete(null); }}><section role="alertdialog" aria-modal="true" aria-labelledby="delete-tag-title" className="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6"><div className="flex items-start gap-3"><span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-600"><Warning size={20} /></span><div className="min-w-0"><h2 id="delete-tag-title" className="text-sm font-bold text-gray-900">Delete this tag?</h2><p className="mt-1 break-words text-xs leading-relaxed text-gray-600">You are deleting <strong className="text-gray-900">{tagToDelete.name}</strong>. This action cannot be undone.</p></div></div><div className="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"><button type="button" onClick={() => setTagToDelete(null)} disabled={deleting} className="rounded-lg border border-gray-200 px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-50">Cancel</button><button type="button" onClick={() => void deleteTag()} disabled={deleting} className="inline-flex items-center justify-center gap-2 rounded-lg bg-rose-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-rose-700 disabled:opacity-50">{deleting ? <ArrowsClockwise size={14} className="animate-spin" /> : <Trash size={14} />}Delete tag</button></div></section></div>}
    </div>
  );
}
