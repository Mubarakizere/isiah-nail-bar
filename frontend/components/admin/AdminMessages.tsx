"use client";

import { useCallback, useEffect, useState } from "react";
import {
  ArrowsClockwise,
  CalendarBlank,
  CaretLeft,
  CaretRight,
  EnvelopeSimple,
  Eye,
  Trash,
  Warning,
  X,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";

interface ContactMessage {
  id: number;
  name: string;
  email: string;
  message: string;
  created_at: string | null;
  is_read: boolean;
}

interface MessageResponse {
  data: ContactMessage[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    unread_count: number;
  };
}

async function readResponse(response: Response) {
  const result = await response.json().catch(() => ({}));
  if (!response.ok) {
    const validation = result.errors ? Object.values(result.errors).flat().join(" ") : "";
    throw new Error(validation || result.message || "The request could not be completed.");
  }
  return result;
}

function relativeDate(value: string | null): string {
  if (!value) return "Date unavailable";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "Date unavailable";

  const seconds = Math.round((date.getTime() - Date.now()) / 1000);
  const units: [Intl.RelativeTimeFormatUnit, number][] = [
    ["year", 60 * 60 * 24 * 365],
    ["month", 60 * 60 * 24 * 30],
    ["week", 60 * 60 * 24 * 7],
    ["day", 60 * 60 * 24],
    ["hour", 60 * 60],
    ["minute", 60],
  ];

  const formatter = new Intl.RelativeTimeFormat("en", { numeric: "auto" });
  for (const [unit, size] of units) {
    if (Math.abs(seconds) >= size) return formatter.format(Math.round(seconds / size), unit);
  }
  return formatter.format(seconds, "second");
}

function fullDate(value: string | null): string {
  if (!value) return "Date unavailable";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "Date unavailable";
  return new Intl.DateTimeFormat("en", {
    dateStyle: "medium",
    timeStyle: "short",
  }).format(date);
}

export default function AdminMessages() {
  const [messages, setMessages] = useState<ContactMessage[]>([]);
  const [page, setPage] = useState(1);
  const [meta, setMeta] = useState<MessageResponse["meta"]>({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
    unread_count: 0,
  });
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [selectedMessage, setSelectedMessage] = useState<ContactMessage | null>(null);
  const [messageToDelete, setMessageToDelete] = useState<ContactMessage | null>(null);
  const [deleting, setDeleting] = useState(false);
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(null);

  const fetchMessages = useCallback(async () => {
    setLoading(true);
    setLoadError(null);
    try {
      const response = await fetch(`/api/v1/admin/messages?page=${page}`, {
        headers: { Accept: "application/json" },
        cache: "no-store",
      });
      const result: MessageResponse = await readResponse(response);
      setMessages(Array.isArray(result.data) ? result.data : []);
      setMeta(result.meta || { current_page: 1, last_page: 1, per_page: 10, total: 0, unread_count: 0 });
    } catch (error) {
      setLoadError(error instanceof Error ? error.message : "Failed to load messages.");
      setMessages([]);
    } finally {
      setLoading(false);
    }
  }, [page]);

  useEffect(() => {
    void fetchMessages();
  }, [fetchMessages]);

  useEffect(() => {
    if (!selectedMessage && !messageToDelete) return;

    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape" && !deleting) {
        setSelectedMessage(null);
        setMessageToDelete(null);
      }
    };
    window.addEventListener("keydown", onKeyDown);
    return () => window.removeEventListener("keydown", onKeyDown);
  }, [selectedMessage, messageToDelete, deleting]);

  const notify = (message: string, type: "success" | "error" = "success") => {
    setToast({ message, type });
    window.setTimeout(() => setToast(null), 4000);
  };

  const openMessage = async (message: ContactMessage) => {
    setSelectedMessage(message);
    if (message.is_read) return;

    try {
      const response = await fetch(`/api/v1/admin/messages/${message.id}/read`, {
        method: "POST",
        headers: { Accept: "application/json" },
      });
      await readResponse(response);
      setMessages((current) => current.map((item) => item.id === message.id ? { ...item, is_read: true } : item));
      setSelectedMessage((current) => current?.id === message.id ? { ...current, is_read: true } : current);
      window.dispatchEvent(new Event("admin-sidebar-refresh"));
    } catch (error) {
      notify(error instanceof Error ? error.message : "The message could not be marked as read.", "error");
    }
  };

  const confirmDelete = async () => {
    if (!messageToDelete) return;
    setDeleting(true);
    try {
      const response = await fetch(`/api/v1/admin/messages/${messageToDelete.id}`, {
        method: "DELETE",
        headers: { Accept: "application/json" },
      });
      const result = await readResponse(response);
      const deletingLastItemOnPage = messages.length === 1 && page > 1;
      setMessageToDelete(null);
      notify(result.message || "Message deleted successfully.");
      window.dispatchEvent(new Event("admin-sidebar-refresh"));

      if (deletingLastItemOnPage) {
        setPage((current) => current - 1);
      } else {
        void fetchMessages();
      }
    } catch (error) {
      notify(error instanceof Error ? error.message : "The message could not be deleted.", "error");
    } finally {
      setDeleting(false);
    }
  };

  const firstResult = meta.total === 0 ? 0 : (meta.current_page - 1) * meta.per_page + 1;
  const lastResult = Math.min(meta.current_page * meta.per_page, meta.total);

  return (
    <div className="flex min-w-0 flex-1 flex-col bg-gray-50 pb-16">
      <AdminHeader title="Messages" subtitle="Read and manage customer inquiries" />

      {toast && (
        <div role="status" className={`fixed bottom-5 right-5 z-[100] max-w-[calc(100vw-2.5rem)] rounded-xl px-4 py-3 text-xs font-semibold text-white shadow-xl ${toast.type === "error" ? "bg-rose-700" : "bg-gray-900"}`}>
          {toast.message}
        </div>
      )}

      <div className="mx-auto w-full max-w-7xl space-y-4 p-4 sm:p-6">
        <div className="flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-2xs sm:flex-row sm:items-center sm:justify-between sm:px-5">
          <div className="flex items-center gap-3">
            <span className="rounded-xl bg-blue-50 p-2.5 text-blue-700"><EnvelopeSimple size={20} /></span>
            <div>
              <h2 className="text-sm font-bold text-gray-900">Customer inquiries</h2>
              <p className="mt-0.5 text-xs text-gray-500">{loading ? "Loading messages…" : `${meta.total} ${meta.total === 1 ? "message" : "messages"} received`}</p>
            </div>
          </div>
          <button type="button" onClick={() => void fetchMessages()} disabled={loading} className="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 px-3.5 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 disabled:opacity-50">
            <ArrowsClockwise size={14} className={loading ? "animate-spin" : ""} /> Refresh
          </button>
        </div>

        {loadError ? (
          <div role="alert" className="flex flex-col items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-10 text-center">
            <Warning size={28} className="text-rose-500" />
            <div><h2 className="text-sm font-bold text-rose-900">Messages could not be loaded</h2><p className="mt-1 text-xs text-rose-800">{loadError}</p></div>
            <button type="button" onClick={() => void fetchMessages()} className="rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-800 transition hover:bg-rose-100">Try again</button>
          </div>
        ) : !loading && messages.length === 0 ? (
          <div className="rounded-2xl border border-blue-200 bg-blue-50 px-6 py-14 text-center">
            <EnvelopeSimple size={34} className="mx-auto text-blue-300" />
            <h2 className="mt-3 text-base font-bold text-blue-950">No messages received yet</h2>
            <p className="mt-1 text-xs text-blue-800">Customer inquiries will appear here.</p>
          </div>
        ) : (
          <section className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xs">
            <div className="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 sm:px-5">
              <div>
                <h2 className="text-sm font-bold text-gray-900">Inbox</h2>
                <p className="mt-0.5 text-[11px] text-gray-500">Showing {firstResult}–{lastResult} of {meta.total}</p>
              </div>
              {loading && <ArrowsClockwise size={16} className="animate-spin text-gray-400" />}
            </div>

            <div className="hidden overflow-x-auto md:block">
              <table className="w-full min-w-[760px]">
                <thead className="border-b border-gray-200 bg-gray-50">
                  <tr>
                    <th className="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500">Sender</th>
                    <th className="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500">Email</th>
                    <th className="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500">Message</th>
                    <th className="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500">Received</th>
                    <th className="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-gray-500">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {loading && messages.length === 0 ? (
                    Array.from({ length: 5 }).map((_, index) => <tr key={index}><td colSpan={5} className="px-5 py-5"><div className="h-4 animate-pulse rounded bg-gray-100" /></td></tr>)
                  ) : messages.map((message) => (
                    <tr key={message.id} className={`transition hover:bg-gray-50 ${message.is_read ? "" : "bg-blue-50/35"}`}>
                      <td className="px-5 py-4"><div className="flex items-center gap-2"><div className={`max-w-[170px] truncate text-sm text-gray-900 ${message.is_read ? "font-medium" : "font-bold"}`}>{message.name}</div>{!message.is_read && <span className="rounded-full bg-blue-100 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-blue-800">New</span>}</div><div className="mt-1 text-[10px] text-gray-400">#{message.id}</div></td>
                      <td className="px-5 py-4"><a href={`mailto:${message.email}`} className="inline-flex max-w-[190px] items-center gap-1.5 truncate text-xs text-blue-700 hover:text-blue-900"><EnvelopeSimple size={13} className="shrink-0" />{message.email}</a></td>
                      <td className="max-w-sm px-5 py-4"><p className="line-clamp-2 text-xs leading-5 text-gray-600">{message.message}</p></td>
                      <td className="whitespace-nowrap px-5 py-4"><div className="text-xs font-medium text-gray-700">{relativeDate(message.created_at)}</div><div className="mt-1 text-[10px] text-gray-400">{fullDate(message.created_at)}</div></td>
                      <td className="whitespace-nowrap px-5 py-4"><div className="flex items-center justify-end gap-2">
                        <button type="button" onClick={() => void openMessage(message)} aria-label={`View message from ${message.name}`} className="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-2 text-[11px] font-semibold text-blue-800 transition hover:bg-blue-100"><Eye size={14} />View</button>
                        <button type="button" onClick={() => setMessageToDelete(message)} aria-label={`Delete message from ${message.name}`} className="inline-flex items-center justify-center rounded-lg bg-rose-50 p-2 text-rose-700 transition hover:bg-rose-100"><Trash size={14} /></button>
                      </div></td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <div className="divide-y divide-gray-100 md:hidden">
              {loading && messages.length === 0 ? (
                Array.from({ length: 4 }).map((_, index) => <div key={index} className="space-y-3 p-4"><div className="h-4 w-2/5 animate-pulse rounded bg-gray-100" /><div className="h-3 animate-pulse rounded bg-gray-100" /></div>)
              ) : messages.map((message) => (
                <article key={message.id} className="space-y-3 p-4">
                  <div className="flex items-start justify-between gap-3">
                    <div className="min-w-0"><div className="flex items-center gap-2"><h3 className="truncate text-sm font-bold text-gray-900">{message.name}</h3>{!message.is_read && <span className="rounded-full bg-blue-100 px-2 py-0.5 text-[9px] font-bold uppercase text-blue-800">New</span>}</div><a href={`mailto:${message.email}`} className="mt-1 block truncate text-xs text-blue-700">{message.email}</a></div>
                    <span className="shrink-0 rounded-full bg-gray-100 px-2.5 py-1 text-[10px] font-medium text-gray-600">{relativeDate(message.created_at)}</span>
                  </div>
                  <p className="line-clamp-3 text-xs leading-5 text-gray-600">{message.message}</p>
                  <div className="flex gap-2">
                    <button type="button" onClick={() => void openMessage(message)} className="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-800"><Eye size={14} />View message</button>
                    <button type="button" onClick={() => setMessageToDelete(message)} aria-label={`Delete message from ${message.name}`} className="inline-flex items-center justify-center rounded-lg bg-rose-50 px-3 py-2 text-rose-700"><Trash size={14} /></button>
                  </div>
                </article>
              ))}
            </div>

            <div className="flex flex-col gap-3 border-t border-gray-100 bg-gray-50/70 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
              <p className="text-[11px] text-gray-500">Page {meta.current_page} of {meta.last_page}</p>
              <div className="flex items-center gap-2 self-end sm:self-auto">
                <button type="button" onClick={() => setPage((current) => Math.max(1, current - 1))} disabled={loading || meta.current_page <= 1} className="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"><CaretLeft size={14} />Previous</button>
                <button type="button" onClick={() => setPage((current) => Math.min(meta.last_page, current + 1))} disabled={loading || meta.current_page >= meta.last_page} className="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40">Next<CaretRight size={14} /></button>
              </div>
            </div>
          </section>
        )}
      </div>

      {selectedMessage && (
        <div role="presentation" onMouseDown={(event) => { if (event.target === event.currentTarget) setSelectedMessage(null); }} className="fixed inset-0 z-[80] flex items-center justify-center bg-gray-950/50 p-3 backdrop-blur-[2px] sm:p-6">
          <section role="dialog" aria-modal="true" aria-labelledby="message-dialog-title" className="flex max-h-[calc(100dvh-1.5rem)] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl sm:max-h-[calc(100dvh-3rem)]">
            <header className="flex items-start justify-between gap-4 border-b border-gray-100 p-4 sm:p-6">
              <div className="min-w-0">
                <p className="text-[10px] font-bold uppercase tracking-wider text-blue-700">Customer message</p>
                <h2 id="message-dialog-title" className="mt-1 break-words text-lg font-bold text-gray-900 sm:text-xl">{selectedMessage.name}</h2>
                <a href={`mailto:${selectedMessage.email}`} className="mt-1 inline-flex max-w-full items-center gap-1.5 break-all text-xs text-blue-700 hover:text-blue-900"><EnvelopeSimple size={13} className="shrink-0" />{selectedMessage.email}</a>
              </div>
              <button type="button" onClick={() => setSelectedMessage(null)} aria-label="Close message" className="shrink-0 rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700"><X size={18} /></button>
            </header>
            <div className="overflow-y-auto p-4 sm:p-6">
              <div className="mb-4 flex items-center gap-2 text-[11px] text-gray-500"><CalendarBlank size={14} />Received {fullDate(selectedMessage.created_at)}</div>
              <p className="whitespace-pre-wrap break-words text-sm leading-7 text-gray-700">{selectedMessage.message}</p>
            </div>
            <footer className="flex flex-col-reverse gap-2 border-t border-gray-100 bg-gray-50 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
              <a href={`mailto:${selectedMessage.email}`} className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-blue-800"><EnvelopeSimple size={14} />Reply by email</a>
              <button type="button" onClick={() => setSelectedMessage(null)} className="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">Close</button>
            </footer>
          </section>
        </div>
      )}

      {messageToDelete && (
        <div role="presentation" onMouseDown={(event) => { if (event.target === event.currentTarget && !deleting) setMessageToDelete(null); }} className="fixed inset-0 z-[90] flex items-center justify-center bg-gray-950/50 p-3 backdrop-blur-[2px] sm:p-6">
          <section role="alertdialog" aria-modal="true" aria-labelledby="delete-dialog-title" className="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl sm:p-6">
            <div className="flex items-start gap-3">
              <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-700"><Warning size={21} /></span>
              <div><h2 id="delete-dialog-title" className="text-base font-bold text-gray-900">Delete this message?</h2><p className="mt-1 text-xs leading-5 text-gray-600">This will permanently remove the inquiry from <strong className="text-gray-900">{messageToDelete.name}</strong>.</p></div>
            </div>
            <div className="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
              <button type="button" onClick={() => setMessageToDelete(null)} disabled={deleting} className="rounded-lg border border-gray-200 px-4 py-2.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 disabled:opacity-50">Cancel</button>
              <button type="button" onClick={() => void confirmDelete()} disabled={deleting} className="inline-flex items-center justify-center gap-2 rounded-lg bg-rose-600 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-rose-700 disabled:opacity-50">{deleting ? <ArrowsClockwise size={14} className="animate-spin" /> : <Trash size={14} />}Delete message</button>
            </div>
          </section>
        </div>
      )}
    </div>
  );
}
