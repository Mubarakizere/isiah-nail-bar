"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import Script from "next/script";
import {
  ArrowsClockwise,
  ArrowSquareOut,
  Check,
  InstagramLogo,
  MagnifyingGlass,
  Plus,
  Trash,
  Warning,
  X,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";

interface GalleryPost {
  id: number;
  url: string;
  created_at: string | null;
}

declare global {
  interface Window {
    instgrm?: { Embeds?: { process: () => void } };
  }
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

export default function AdminGallery() {
  const [posts, setPosts] = useState<GalleryPost[]>([]);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [url, setUrl] = useState("");
  const [adding, setAdding] = useState(false);
  const [formError, setFormError] = useState<string | null>(null);
  const [search, setSearch] = useState("");
  const [postToDelete, setPostToDelete] = useState<GalleryPost | null>(null);
  const [deleting, setDeleting] = useState(false);
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(null);

  const fetchPosts = useCallback(async () => {
    setLoading(true);
    setLoadError(null);
    try {
      const response = await fetch("/api/v1/admin/gallery", {
        headers: { Accept: "application/json" },
        cache: "no-store",
      });
      const result = await readResponse(response);
      setPosts(Array.isArray(result.data) ? result.data : []);
    } catch (error) {
      setLoadError(error instanceof Error ? error.message : "Failed to load gallery posts.");
      setPosts([]);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    void fetchPosts();
  }, [fetchPosts]);

  useEffect(() => {
    if (posts.length > 0) window.instgrm?.Embeds?.process();
  }, [posts]);

  useEffect(() => {
    if (!postToDelete) return;
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape" && !deleting) setPostToDelete(null);
    };
    window.addEventListener("keydown", onKeyDown);
    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener("keydown", onKeyDown);
    };
  }, [postToDelete, deleting]);

  const notify = (message: string, type: "success" | "error" = "success") => {
    setToast({ message, type });
    window.setTimeout(() => setToast(null), 4000);
  };

  const addPost = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setAdding(true);
    setFormError(null);
    try {
      const response = await fetch("/api/v1/admin/gallery", {
        method: "POST",
        headers: { Accept: "application/json", "Content-Type": "application/json" },
        body: JSON.stringify({ url: url.trim() }),
      });
      const result = await readResponse(response);
      setPosts((current) => [result.data as GalleryPost, ...current]);
      setUrl("");
      notify(result.message || "Instagram post added to the gallery.");
    } catch (error) {
      setFormError(error instanceof Error ? error.message : "The Instagram post could not be added.");
    } finally {
      setAdding(false);
    }
  };

  const deletePost = async () => {
    if (!postToDelete) return;
    setDeleting(true);
    try {
      const response = await fetch(`/api/v1/admin/gallery/${postToDelete.id}`, {
        method: "DELETE",
        headers: { Accept: "application/json" },
      });
      const result = await readResponse(response);
      setPosts((current) => current.filter((post) => post.id !== postToDelete.id));
      setPostToDelete(null);
      notify(result.message || "Instagram post removed.");
    } catch (error) {
      notify(error instanceof Error ? error.message : "The Instagram post could not be removed.", "error");
    } finally {
      setDeleting(false);
    }
  };

  const visiblePosts = useMemo(() => {
    const query = search.trim().toLowerCase();
    return query ? posts.filter((post) => post.url.toLowerCase().includes(query)) : posts;
  }, [posts, search]);

  return (
    <div className="flex min-w-0 flex-1 flex-col bg-gray-50 pb-16">
      <AdminHeader title="Instagram Gallery" subtitle="Manage Instagram posts displayed on your website" />
      {toast && <div role="status" className={`fixed bottom-5 right-5 z-[100] max-w-[calc(100vw-2.5rem)] rounded-xl px-4 py-3 text-xs font-semibold text-white shadow-xl ${toast.type === "error" ? "bg-rose-700" : "bg-gray-900"}`}>{toast.message}</div>}
      {posts.length > 0 && <Script src="https://www.instagram.com/embed.js" strategy="lazyOnload" onLoad={() => window.instgrm?.Embeds?.process()} />}

      <div className="mx-auto w-full max-w-7xl space-y-4 p-4 sm:p-6">
        <section className="grid gap-3 sm:grid-cols-2">
          <div className="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-2xs"><div><p className="text-[10px] font-bold uppercase tracking-wide text-gray-500">Instagram posts</p><p className="mt-1 text-2xl font-bold tabular-nums text-gray-900">{loading ? "—" : posts.length}</p></div><span className="rounded-lg bg-pink-50 p-2.5 text-pink-700"><InstagramLogo size={18} /></span></div>
          <div className="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-2xs"><div><p className="text-[10px] font-bold uppercase tracking-wide text-gray-500">Public gallery</p><p className="mt-1 text-sm font-bold text-gray-900">{loading ? "Loading…" : posts.length ? "Displaying managed posts" : "No managed posts"}</p></div><span className="rounded-lg bg-blue-50 p-2.5 text-blue-700"><ArrowSquareOut size={18} /></span></div>
        </section>

        <section className="rounded-2xl border border-gray-200 bg-white p-4 shadow-2xs sm:p-5">
          <div className="mb-3"><h2 className="text-sm font-bold text-gray-900">Add Instagram post</h2><p className="mt-1 text-[11px] text-gray-500">Paste a public post or reel link from the Isaiah Nail Bar Instagram account.</p></div>
          <form onSubmit={addPost} className="flex flex-col gap-2 sm:flex-row">
            <label className="min-w-0 flex-1"><span className="sr-only">Instagram URL</span><input type="url" required maxLength={255} value={url} onChange={(event) => { setUrl(event.target.value); setFormError(null); }} placeholder="https://www.instagram.com/p/POST_ID/" className="w-full rounded-lg border border-gray-200 bg-white px-3.5 py-2.5 text-sm outline-none transition placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10" /></label>
            <button type="submit" disabled={adding} className="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-pink-600 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-pink-700 disabled:opacity-50">{adding ? <ArrowsClockwise size={14} className="animate-spin" /> : <Plus size={14} weight="bold" />}{adding ? "Adding…" : "Add post"}</button>
          </form>
          {formError && <p role="alert" className="mt-2 text-xs text-rose-700">{formError}</p>}
          <p className="mt-2 text-[10px] text-gray-400">Use the full URL beginning with https://www.instagram.com/</p>
        </section>

        <section className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xs">
          <div className="flex flex-col gap-3 border-b border-gray-100 bg-gray-50/60 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div><h2 className="text-sm font-bold text-gray-900">Managed posts</h2><p className="mt-0.5 text-[11px] text-gray-500">Newest posts appear first in the public gallery.</p></div>
            <div className="flex items-center gap-2"><div className="relative flex-1 sm:w-56"><MagnifyingGlass size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" /><input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Search Instagram URLs…" className="w-full rounded-lg border border-gray-200 bg-white py-2 pl-9 pr-3 text-xs outline-none focus:border-gray-400" /></div><button type="button" onClick={() => void fetchPosts()} disabled={loading} aria-label="Refresh gallery" className="rounded-lg border border-gray-200 bg-white p-2 text-gray-600 transition hover:bg-gray-50 disabled:opacity-50"><ArrowsClockwise size={15} className={loading ? "animate-spin" : ""} /></button></div>
          </div>

          {loadError ? <div role="alert" className="m-4 flex flex-col items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 p-8 text-center sm:m-6"><Warning size={28} className="text-rose-500" /><div><h3 className="text-sm font-bold text-rose-900">Gallery posts could not be loaded</h3><p className="mt-1 text-xs text-rose-800">{loadError}</p></div><button type="button" onClick={() => void fetchPosts()} className="rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-800 hover:bg-rose-100">Try again</button></div>
            : loading ? <div className="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-3 sm:p-5">{Array.from({ length: 3 }).map((_, index) => <div key={index} className="overflow-hidden rounded-xl border border-gray-100"><div className="h-[420px] animate-pulse bg-gray-100" /><div className="h-12 animate-pulse border-t border-gray-100 bg-gray-50" /></div>)}</div>
              : visiblePosts.length === 0 ? <div className="px-5 py-14 text-center"><span className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-pink-50 text-pink-500"><InstagramLogo size={28} /></span><h3 className="mt-4 text-base font-bold text-gray-900">{search ? "No matching posts" : "No Instagram posts yet"}</h3><p className="mx-auto mt-1 max-w-sm text-xs leading-5 text-gray-500">{search ? `No post URL matches “${search}”.` : "Add your first post to showcase your work on the website gallery."}</p></div>
                : <div className="grid items-start gap-4 p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-3">{visiblePosts.map((post) => <article key={post.id} className="min-w-0 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-2xs"><div className="flex min-h-[410px] justify-center overflow-hidden rounded-t-xl bg-gray-50 p-2"><blockquote className="instagram-media" data-instgrm-permalink={post.url} data-instgrm-version="14" style={{ background: "#fff", border: 0, borderRadius: 12, boxShadow: "none", margin: "0 auto", maxWidth: 540, minWidth: 260, padding: 0, width: "100%" }}><div className="flex min-h-[390px] flex-col items-center justify-center p-8 text-center"><InstagramLogo size={38} className="mb-3 text-pink-500" /><p className="text-xs font-semibold text-gray-700">Instagram post preview</p><a href={post.url} target="_blank" rel="noopener noreferrer" className="mt-2 text-[11px] text-blue-700 hover:underline">Open this post on Instagram</a></div></blockquote></div><div className="flex items-center justify-between gap-3 border-t border-gray-100 p-3"><div className="min-w-0"><a href={post.url} target="_blank" rel="noopener noreferrer" className="block truncate text-[11px] font-semibold text-blue-700 hover:text-blue-900">{post.url}</a><p className="mt-1 text-[10px] text-gray-400">Added {formatDate(post.created_at)}</p></div><button type="button" onClick={() => setPostToDelete(post)} aria-label="Remove Instagram post" title="Remove post" className="shrink-0 rounded-lg bg-rose-50 p-2 text-rose-700 transition hover:bg-rose-100"><Trash size={14} /></button></div></article>)}</div>}
        </section>
      </div>

      {postToDelete && <div className="fixed inset-0 z-[90] flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs" onMouseDown={(event) => { if (event.target === event.currentTarget && !deleting) setPostToDelete(null); }}><section role="alertdialog" aria-modal="true" aria-labelledby="delete-gallery-title" className="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6"><div className="flex items-start gap-3"><span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-600"><Warning size={20} /></span><div><h2 id="delete-gallery-title" className="text-sm font-bold text-gray-900">Remove Instagram post?</h2><p className="mt-1 break-all text-xs leading-relaxed text-gray-600">This removes the post from your managed website gallery. The original Instagram post will not be affected.</p></div></div><div className="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"><button type="button" onClick={() => setPostToDelete(null)} disabled={deleting} className="rounded-lg border border-gray-200 px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-50">Cancel</button><button type="button" onClick={() => void deletePost()} disabled={deleting} className="inline-flex items-center justify-center gap-2 rounded-lg bg-rose-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-rose-700 disabled:opacity-50">{deleting ? <ArrowsClockwise size={14} className="animate-spin" /> : <Trash size={14} />}Remove post</button></div></section></div>}
    </div>
  );
}
