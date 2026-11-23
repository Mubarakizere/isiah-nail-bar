"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import {
  ArrowsClockwise,
  ArrowLeft,
  CaretLeft,
  CaretRight,
  Check,
  EnvelopeOpen,
  EnvelopeSimple,
  Paperclip,
  PaperPlaneTilt,
  Trash,
  Warning,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";

interface MailAttachment {
  id: string;
  name: string;
  size: number;
  content_type: string | null;
}

interface MailItem {
  uid: string;
  sender_name: string;
  sender_email: string;
  subject: string;
  preview: string;
  date: string | null;
  seen: boolean;
  answered: boolean;
  attachments: MailAttachment[];
  body?: string;
}

interface MailListResponse {
  success: boolean;
  data: MailItem[];
  meta: { current_page: number; last_page: number; per_page: number; total: number };
}

function requestHeaders(jsonBody = false): HeadersInit {
  const token = typeof window !== "undefined" ? window.localStorage.getItem("token") : null;
  return {
    Accept: "application/json",
    ...(jsonBody ? { "Content-Type": "application/json" } : {}),
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
  };
}

async function readResponse(response: Response) {
  const result = await response.json().catch(() => ({}));
  if (!response.ok || result.success === false) {
    const validation = result.errors ? Object.values(result.errors).flat().join(" ") : "";
    throw new Error(validation || result.message || "The request could not be completed.");
  }
  return result;
}

function formatDate(value: string | null, options: Intl.DateTimeFormatOptions = {}): string {
  if (!value) return "Date unavailable";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "Date unavailable";
  return new Intl.DateTimeFormat("en", {
    month: "short",
    day: "numeric",
    year: "numeric",
    hour: "numeric",
    minute: "2-digit",
    ...options,
  }).format(date);
}

function formatSize(size: number): string {
  if (size < 1024) return `${size} B`;
  if (size < 1024 * 1024) return `${(size / 1024).toFixed(1)} KB`;
  return `${(size / (1024 * 1024)).toFixed(1)} MB`;
}

export default function AdminWebmail() {
  const [messages, setMessages] = useState<MailItem[]>([]);
  const [page, setPage] = useState(1);
  const [meta, setMeta] = useState<MailListResponse["meta"]>({ current_page: 1, last_page: 1, per_page: 15, total: 0 });
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [selectedMessage, setSelectedMessage] = useState<MailItem | null>(null);
  const [detailLoading, setDetailLoading] = useState(false);
  const [detailError, setDetailError] = useState<string | null>(null);
  const [replyBody, setReplyBody] = useState("");
  const [sendingReply, setSendingReply] = useState(false);
  const [deleting, setDeleting] = useState(false);
  const [confirmDelete, setConfirmDelete] = useState(false);
  const [downloadingId, setDownloadingId] = useState<string | null>(null);
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(null);

  const fetchMessages = useCallback(async () => {
    setLoading(true);
    setLoadError(null);
    try {
      const response = await fetch(`/api/v1/admin/webmail?page=${page}`, {
        headers: requestHeaders(),
        credentials: "same-origin",
        cache: "no-store",
      });
      const result: MailListResponse = await readResponse(response);
      setMessages(Array.isArray(result.data) ? result.data : []);
      setMeta(result.meta || { current_page: 1, last_page: 1, per_page: 15, total: 0 });
    } catch (error) {
      setLoadError(error instanceof Error ? error.message : "Could not connect to the email server.");
      setMessages([]);
    } finally {
      setLoading(false);
    }
  }, [page]);

  useEffect(() => {
    void fetchMessages();
  }, [fetchMessages]);

  const notify = (message: string, type: "success" | "error" = "success") => {
    setToast({ message, type });
    window.setTimeout(() => setToast(null), 4000);
  };

  const selectMessage = async (item: MailItem) => {
    setSelectedMessage(item);
    setReplyBody("");
    setDetailLoading(true);
    setDetailError(null);
    setConfirmDelete(false);
    try {
      const response = await fetch(`/api/v1/admin/webmail/${encodeURIComponent(item.uid)}`, {
        headers: requestHeaders(),
        credentials: "same-origin",
        cache: "no-store",
      });
      const result = await readResponse(response);
      setSelectedMessage(result.data as MailItem);
      setMessages((current) => current.map((message) => message.uid === item.uid ? { ...message, seen: true } : message));
    } catch (error) {
      setDetailError(error instanceof Error ? error.message : "Could not load this email.");
    } finally {
      setDetailLoading(false);
    }
  };

  const sendReply = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (!selectedMessage || !replyBody.trim()) return;
    setSendingReply(true);
    try {
      const response = await fetch(`/api/v1/admin/webmail/${encodeURIComponent(selectedMessage.uid)}/reply`, {
        method: "POST",
        headers: requestHeaders(true),
        credentials: "same-origin",
        body: JSON.stringify({ body: replyBody.trim() }),
      });
      const result = await readResponse(response);
      setReplyBody("");
      setSelectedMessage((current) => current ? { ...current, answered: true } : current);
      setMessages((current) => current.map((message) => message.uid === selectedMessage.uid ? { ...message, answered: true } : message));
      notify(result.message || "Reply sent successfully.");
    } catch (error) {
      notify(error instanceof Error ? error.message : "The reply could not be sent.", "error");
    } finally {
      setSendingReply(false);
    }
  };

  const deleteMessage = async () => {
    if (!selectedMessage) return;
    setDeleting(true);
    try {
      const response = await fetch(`/api/v1/admin/webmail/${encodeURIComponent(selectedMessage.uid)}`, {
        method: "DELETE",
        headers: requestHeaders(),
        credentials: "same-origin",
      });
      const result = await readResponse(response);
      const currentUid = selectedMessage.uid;
      const remaining = messages.filter((message) => message.uid !== currentUid);
      setSelectedMessage(null);
      setConfirmDelete(false);
      notify(result.message || "Email deleted.");
      if (remaining.length === 0 && page > 1) setPage((current) => current - 1);
      else void fetchMessages();
    } catch (error) {
      notify(error instanceof Error ? error.message : "The email could not be deleted.", "error");
    } finally {
      setDeleting(false);
    }
  };

  const downloadAttachment = async (attachment: MailAttachment) => {
    if (!selectedMessage) return;
    setDownloadingId(attachment.id);
    try {
      const response = await fetch(`/api/v1/admin/webmail/${encodeURIComponent(selectedMessage.uid)}/attachments/${encodeURIComponent(attachment.id)}`, {
        headers: requestHeaders(),
        credentials: "same-origin",
      });
      if (!response.ok) {
        const error = await readResponse(response);
        throw new Error(error.message || "Could not download the attachment.");
      }
      const blob = await response.blob();
      const url = URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = url;
      link.download = attachment.name;
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.setTimeout(() => URL.revokeObjectURL(url), 1000);
    } catch (error) {
      notify(error instanceof Error ? error.message : "Could not download the attachment.", "error");
    } finally {
      setDownloadingId(null);
    }
  };

  const unreadCount = useMemo(() => messages.filter((message) => !message.seen).length, [messages]);
  const firstResult = meta.total === 0 ? 0 : (meta.current_page - 1) * meta.per_page + 1;
  const lastResult = Math.min(meta.current_page * meta.per_page, meta.total);

  return (
    <div className="flex min-w-0 flex-1 flex-col bg-gray-50 pb-16">
      <AdminHeader title="Webmail" subtitle="Manage your business inbox" />

      {toast && <div role="status" className={`fixed bottom-5 right-5 z-[100] max-w-[calc(100vw-2.5rem)] rounded-xl px-4 py-3 text-xs font-semibold text-white shadow-xl ${toast.type === "error" ? "bg-rose-700" : "bg-gray-900"}`}>{toast.message}</div>}

      <div className="mx-auto flex w-full max-w-[1500px] min-w-0 flex-1 flex-col p-3 sm:p-5">
        <section className="flex min-h-[calc(100dvh-8rem)] min-w-0 flex-1 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xs md:flex-row">
          <aside className={`${selectedMessage ? "hidden md:flex" : "flex"} w-full min-w-0 flex-col border-gray-200 md:w-[360px] md:shrink-0 md:border-r xl:w-[410px]`}>
            <div className="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-4 sm:px-5">
              <div>
                <h2 className="text-base font-bold text-gray-900">Inbox</h2>
                <p className="mt-1 text-[11px] text-gray-500">{loading ? "Connecting to mailbox…" : `${meta.total} emails${unreadCount ? ` · ${unreadCount} unread on this page` : ""}`}</p>
              </div>
              <button type="button" onClick={() => void fetchMessages()} disabled={loading} aria-label="Refresh inbox" className="rounded-lg border border-gray-200 p-2 text-gray-500 transition hover:bg-gray-50 hover:text-gray-900 disabled:opacity-50"><ArrowsClockwise size={16} className={loading ? "animate-spin" : ""} /></button>
            </div>

            {loadError ? (
              <div role="alert" className="m-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-center">
                <Warning size={24} className="mx-auto text-rose-500" />
                <h3 className="mt-2 text-sm font-bold text-rose-900">Inbox unavailable</h3>
                <p className="mt-1 text-xs leading-5 text-rose-800">{loadError}</p>
                <button type="button" onClick={() => void fetchMessages()} className="mt-3 rounded-lg bg-white px-3 py-2 text-xs font-semibold text-rose-800 ring-1 ring-rose-200 hover:bg-rose-100">Try again</button>
              </div>
            ) : loading && messages.length === 0 ? (
              <div className="space-y-3 p-4">{Array.from({ length: 6 }).map((_, index) => <div key={index} className="space-y-2 rounded-xl border border-gray-100 p-3"><div className="h-3 w-2/5 animate-pulse rounded bg-gray-100" /><div className="h-3 animate-pulse rounded bg-gray-100" /><div className="h-3 w-3/4 animate-pulse rounded bg-gray-100" /></div>)}</div>
            ) : messages.length === 0 ? (
              <div className="flex flex-1 flex-col items-center justify-center p-8 text-center">
                <EnvelopeOpen size={36} className="text-gray-300" />
                <h3 className="mt-3 text-sm font-bold text-gray-800">Inbox is empty</h3>
                <p className="mt-1 text-xs text-gray-500">New business emails will appear here.</p>
              </div>
            ) : (
              <div className="min-h-0 flex-1 divide-y divide-gray-100 overflow-y-auto">
                {messages.map((message) => {
                  const active = selectedMessage?.uid === message.uid;
                  return (
                    <button key={message.uid} type="button" onClick={() => void selectMessage(message)} className={`w-full border-l-2 px-4 py-4 text-left transition hover:bg-gray-50 sm:px-5 ${active ? "border-l-rose-500 bg-rose-50/40" : message.seen ? "border-l-transparent bg-white" : "border-l-blue-500 bg-blue-50/40"}`}>
                      <div className="flex min-w-0 items-start gap-3">
                        <span className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold ${message.seen ? "bg-gray-100 text-gray-600" : "bg-blue-100 text-blue-800"}`}>{(message.sender_name || message.sender_email || "?").slice(0, 1).toUpperCase()}</span>
                        <div className="min-w-0 flex-1">
                          <div className="flex items-center justify-between gap-2"><span className={`truncate text-xs ${message.seen ? "font-medium text-gray-700" : "font-bold text-gray-900"}`}>{message.sender_name}</span><time className="shrink-0 text-[10px] text-gray-400">{formatDate(message.date, { year: undefined, month: "short", day: "numeric" })}</time></div>
                          <p className={`mt-1 truncate text-xs ${message.seen ? "font-medium text-gray-700" : "font-bold text-gray-900"}`}>{message.subject}</p>
                          <p className="mt-1 line-clamp-2 text-[11px] leading-4 text-gray-500">{message.preview}</p>
                          <div className="mt-2 flex items-center gap-2">
                            {message.answered && <span className="inline-flex items-center gap-1 text-[9px] font-semibold text-emerald-700"><Check size={11} />Replied</span>}
                            {message.attachments?.length > 0 && <span className="inline-flex items-center gap-1 text-[9px] text-gray-400"><Paperclip size={11} />{message.attachments.length}</span>}
                            {!message.seen && <span className="ml-auto h-1.5 w-1.5 rounded-full bg-blue-500" aria-label="Unread" />}
                          </div>
                        </div>
                      </div>
                    </button>
                  );
                })}
              </div>
            )}

            <div className="flex items-center justify-between border-t border-gray-100 bg-gray-50/70 px-4 py-3 sm:px-5">
              <span className="text-[10px] text-gray-500">{firstResult}–{lastResult} of {meta.total}</span>
              <div className="flex gap-1.5">
                <button type="button" aria-label="Previous page" onClick={() => { setSelectedMessage(null); setPage((current) => Math.max(1, current - 1)); }} disabled={loading || meta.current_page <= 1} className="rounded-md border border-gray-200 bg-white p-1.5 text-gray-600 hover:bg-gray-50 disabled:opacity-40"><CaretLeft size={14} /></button>
                <button type="button" aria-label="Next page" onClick={() => { setSelectedMessage(null); setPage((current) => Math.min(meta.last_page, current + 1)); }} disabled={loading || meta.current_page >= meta.last_page} className="rounded-md border border-gray-200 bg-white p-1.5 text-gray-600 hover:bg-gray-50 disabled:opacity-40"><CaretRight size={14} /></button>
              </div>
            </div>
          </aside>

          <section className={`${selectedMessage ? "flex" : "hidden md:flex"} min-w-0 flex-1 flex-col bg-white`}>
            {selectedMessage ? (
              <>
                <div className="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 sm:px-6">
                  <button type="button" onClick={() => { setSelectedMessage(null); setConfirmDelete(false); }} className="inline-flex items-center gap-1.5 rounded-lg px-2 py-1.5 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 md:hidden"><ArrowLeft size={15} />Inbox</button>
                  <span className="hidden text-[10px] font-semibold uppercase tracking-wider text-gray-400 md:block">Email details</span>
                  <div className="ml-auto flex items-center gap-2">
                    {selectedMessage.answered && <span className="hidden items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold text-emerald-700 sm:inline-flex"><Check size={12} />Replied</span>}
                    <button type="button" onClick={() => setConfirmDelete(true)} aria-label="Delete email" className="inline-flex items-center gap-1.5 rounded-lg border border-rose-100 px-3 py-2 text-[11px] font-semibold text-rose-700 transition hover:bg-rose-50"><Trash size={14} /><span className="hidden sm:inline">Delete</span></button>
                  </div>
                </div>

                <div className="min-h-0 flex-1 overflow-y-auto">
                  <article className="border-b border-gray-100 px-4 py-5 sm:px-6 sm:py-6">
                    <h1 className="break-words text-lg font-bold leading-snug text-gray-900 sm:text-xl">{selectedMessage.subject}</h1>
                    <div className="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                      <div className="flex min-w-0 items-center gap-3">
                        <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-100 text-sm font-bold text-rose-700">{(selectedMessage.sender_name || "?").slice(0, 1).toUpperCase()}</span>
                        <div className="min-w-0"><p className="truncate text-sm font-bold text-gray-900">{selectedMessage.sender_name}</p><a href={`mailto:${selectedMessage.sender_email}`} className="block break-all text-xs text-gray-500 hover:text-blue-700">{selectedMessage.sender_email}</a></div>
                      </div>
                      <time className="pl-[52px] text-[11px] text-gray-500 sm:pl-0">{formatDate(selectedMessage.date)}</time>
                    </div>
                  </article>

                  {detailError && <div role="alert" className="m-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-xs text-rose-800 sm:m-6">{detailError}</div>}
                  {detailLoading ? (
                    <div className="space-y-3 p-6"><div className="h-4 w-1/3 animate-pulse rounded bg-gray-100" /><div className="h-3 animate-pulse rounded bg-gray-100" /><div className="h-3 animate-pulse rounded bg-gray-100" /><div className="h-3 w-2/3 animate-pulse rounded bg-gray-100" /></div>
                  ) : (
                    <div className="px-4 py-5 sm:px-6 sm:py-6">
                      <p className="whitespace-pre-wrap break-words text-sm leading-7 text-gray-700">{selectedMessage.body || "(This email has no text content.)"}</p>
                      {(selectedMessage.attachments?.length ?? 0) > 0 && (
                        <section className="mt-7 border-t border-gray-100 pt-5">
                          <h2 className="mb-3 text-xs font-bold text-gray-800">Attachments ({selectedMessage.attachments.length})</h2>
                          <div className="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                            {selectedMessage.attachments.map((attachment) => <button key={attachment.id} type="button" onClick={() => void downloadAttachment(attachment)} disabled={downloadingId !== null} className="inline-flex min-w-0 items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-left transition hover:bg-white hover:shadow-sm disabled:opacity-50"><Paperclip size={15} className="shrink-0 text-gray-400" /><span className="min-w-0 flex-1"><span className="block max-w-[220px] truncate text-[11px] font-semibold text-gray-800">{attachment.name}</span><span className="block text-[10px] text-gray-400">{formatSize(attachment.size)}</span></span>{downloadingId === attachment.id && <ArrowsClockwise size={13} className="animate-spin" />}</button>)}
                          </div>
                        </section>
                      )}
                    </div>
                  )}

                  <form onSubmit={sendReply} className="border-t border-gray-100 bg-gray-50/60 px-4 py-5 sm:px-6">
                    <label htmlFor="webmail-reply" className="mb-2 block text-xs font-bold text-gray-900">Reply to {selectedMessage.sender_name}</label>
                    <textarea id="webmail-reply" value={replyBody} onChange={(event) => setReplyBody(event.target.value)} rows={5} maxLength={50000} placeholder="Write your reply…" disabled={detailLoading || sendingReply} className="w-full resize-y rounded-xl border border-gray-200 bg-white px-3.5 py-3 text-sm leading-6 text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-gray-400 focus:ring-2 focus:ring-gray-900/5 disabled:bg-gray-50" />
                    <div className="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"><p className="text-[10px] text-gray-400">Sent from your configured business email address.</p><button type="submit" disabled={!replyBody.trim() || sendingReply || detailLoading} className="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-45">{sendingReply ? <ArrowsClockwise size={14} className="animate-spin" /> : <PaperPlaneTilt size={14} />}Send reply</button></div>
                  </form>
                </div>
              </>
            ) : (
              <div className="flex flex-1 flex-col items-center justify-center p-8 text-center">
                <span className="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400"><EnvelopeSimple size={26} /></span>
                <h2 className="mt-4 text-sm font-bold text-gray-900">Select an email to read it</h2>
                <p className="mt-1 max-w-xs text-xs leading-5 text-gray-500">Choose a message from your inbox to read, reply, or download its attachments.</p>
              </div>
            )}
          </section>
        </section>
      </div>

      {confirmDelete && selectedMessage && (
        <div role="presentation" onMouseDown={(event) => { if (event.target === event.currentTarget && !deleting) setConfirmDelete(false); }} className="fixed inset-0 z-[90] flex items-center justify-center bg-gray-950/50 p-3 backdrop-blur-[2px] sm:p-6">
          <section role="alertdialog" aria-modal="true" aria-labelledby="webmail-delete-title" className="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl sm:p-6">
            <div className="flex items-start gap-3"><span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-700"><Warning size={21} /></span><div><h2 id="webmail-delete-title" className="text-base font-bold text-gray-900">Delete this email?</h2><p className="mt-1 text-xs leading-5 text-gray-600">This will remove “{selectedMessage.subject}” from your mailbox.</p></div></div>
            <div className="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"><button type="button" onClick={() => setConfirmDelete(false)} disabled={deleting} className="rounded-lg border border-gray-200 px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-50">Cancel</button><button type="button" onClick={() => void deleteMessage()} disabled={deleting} className="inline-flex items-center justify-center gap-2 rounded-lg bg-rose-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-rose-700 disabled:opacity-50">{deleting ? <ArrowsClockwise size={14} className="animate-spin" /> : <Trash size={14} />}Delete email</button></div>
          </section>
        </div>
      )}
    </div>
  );
}
