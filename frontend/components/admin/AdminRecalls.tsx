"use client";

import { useCallback, useEffect, useState } from "react";
import Link from "next/link";
import {
  Phone,
  PhoneCall,
  ChatCircleDots,
  Clock,
  Calendar,
  CheckCircle,
  WarningCircle,
  MagnifyingGlass,
  ArrowsClockwise,
  EnvelopeSimple,
  User,
  PlusCircle,
  Funnel,
  X,
  FileText,
  Sparkle,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";

interface RecallItem {
  customer_id: number;
  customer_name: string;
  customer_phone: string;
  customer_email: string;
  whatsapp_url: string | null;
  last_booking_id: number;
  last_booking_date: string;
  days_since_visit: number;
  last_services: string[];
  last_provider_id: number | null;
  last_provider_name: string;
  recall_status: "pending" | "spoke" | "no_answer" | "booked" | "do_not_contact";
  notes: string | null;
  called_at: string | null;
  called_by_name: string | null;
  email_reminder_sent_at: string | null;
}

interface RecallMetrics {
  due_3w_count: number;
  overdue_count: number;
  contacted_today_count: number;
  booked_count: number;
  total_eligible: number;
}

export default function AdminRecalls() {
  const [items, setItems] = useState<RecallItem[]>([]);
  const [metrics, setMetrics] = useState<RecallMetrics>({
    due_3w_count: 0,
    overdue_count: 0,
    contacted_today_count: 0,
    booked_count: 0,
    total_eligible: 0,
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Filters
  const [bracket, setBracket] = useState<string>("all");
  const [status, setStatus] = useState<string>("all");
  const [search, setSearch] = useState<string>("");

  // Modal state
  const [activeModalItem, setActiveModalItem] = useState<RecallItem | null>(null);
  const [modalStatus, setModalStatus] = useState<RecallItem["recall_status"]>("spoke");
  const [modalNotes, setModalNotes] = useState<string>("");
  const [savingLog, setSavingLog] = useState(false);

  // Email sending state
  const [sendingEmailId, setSendingEmailId] = useState<number | null>(null);
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(null);

  const showToast = (message: string, type: "success" | "error" = "success") => {
    setToast({ message, type });
    setTimeout(() => setToast(null), 4000);
  };

  const fetchRecalls = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const params = new URLSearchParams();
      if (bracket !== "all") params.append("bracket", bracket);
      if (status !== "all") params.append("status", status);
      if (search.trim()) params.append("search", search.trim());

      const res = await fetch(`/api/v1/admin/recalls?${params.toString()}`, {
        headers: { Accept: "application/json" },
        cache: "no-store",
      });

      if (!res.ok) {
        throw new Error(`Failed to load recalls (${res.status})`);
      }

      const json = await res.json();
      if (json.success) {
        setItems(json.data || []);
        if (json.metrics) setMetrics(json.metrics);
      }
    } catch (err: any) {
      setError(err?.message || "Could not fetch client call list.");
    } finally {
      setLoading(false);
    }
  }, [bracket, status, search]);

  useEffect(() => {
    const timer = setTimeout(() => {
      fetchRecalls();
    }, 250);
    return () => clearTimeout(timer);
  }, [fetchRecalls]);

  const openLogModal = (item: RecallItem) => {
    setActiveModalItem(item);
    setModalStatus(item.recall_status === "pending" ? "spoke" : item.recall_status);
    setModalNotes(item.notes || "");
  };

  const closeLogModal = () => {
    setActiveModalItem(null);
    setModalNotes("");
  };

  const handleSaveContactLog = async () => {
    if (!activeModalItem) return;
    setSavingLog(true);

    try {
      const res = await fetch(`/api/v1/admin/recalls/${activeModalItem.customer_id}/log-contact`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          status: modalStatus,
          notes: modalNotes,
        }),
      });

      const json = await res.json();
      if (!res.ok || !json.success) {
        throw new Error(json.message || "Failed to save contact log.");
      }

      // Update local item
      setItems((prev) =>
        prev.map((i) =>
          i.customer_id === activeModalItem.customer_id
            ? {
                ...i,
                recall_status: modalStatus,
                notes: modalNotes,
                called_at: new Date().toISOString(),
                called_by_name: json.recall?.caller?.name || "Staff",
              }
            : i
        )
      );

      showToast("Call log updated successfully.");
      closeLogModal();
    } catch (err: any) {
      alert(err?.message || "Failed to save call log.");
    } finally {
      setSavingLog(false);
    }
  };

  const handleSendReminderEmail = async (customerId: number, customerName: string) => {
    if (!confirm(`Send 3-week re-booking reminder email to ${customerName}?`)) return;

    setSendingEmailId(customerId);
    try {
      const res = await fetch(`/api/v1/admin/recalls/${customerId}/send-email`, {
        method: "POST",
        headers: { Accept: "application/json" },
      });

      const json = await res.json();
      if (!res.ok || !json.success) {
        throw new Error(json.message || "Failed to send email.");
      }

      setItems((prev) =>
        prev.map((i) =>
          i.customer_id === customerId
            ? { ...i, email_reminder_sent_at: json.email_reminder_sent_at || new Date().toISOString() }
            : i
        )
      );

      showToast(`Reminder email sent to ${customerName}.`);
    } catch (err: any) {
      showToast(err?.message || "Failed to send email reminder.", "error");
    } finally {
      setSendingEmailId(null);
    }
  };

  const getStatusBadge = (status: RecallItem["recall_status"]) => {
    switch (status) {
      case "spoke":
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
            <CheckCircle size={13} weight="fill" /> Spoke - Booking Soon
          </span>
        );
      case "no_answer":
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
            <Clock size={13} /> No Answer / Left Msg
          </span>
        );
      case "booked":
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
            <CheckCircle size={13} weight="fill" /> Booked
          </span>
        );
      case "do_not_contact":
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">
            <WarningCircle size={13} weight="fill" /> Do Not Contact
          </span>
        );
      case "pending":
      default:
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
            <Clock size={13} /> Needs Call
          </span>
        );
    }
  };

  return (
    <div className="min-h-screen bg-[#FBF9F7] text-gray-900 flex flex-col font-sans">
      <AdminHeader
        title="Client Call List & Retention"
        subtitle="3-week return reminders, outreach call list, and re-booking follow-ups"
      />

      {toast && (
        <div
          className={`fixed bottom-6 right-6 z-50 px-4 py-3 rounded-xl shadow-lg border text-sm font-medium flex items-center gap-2 transition-all ${
            toast.type === "success"
              ? "bg-emerald-900 text-white border-emerald-700"
              : "bg-rose-900 text-white border-rose-700"
          }`}
        >
          {toast.type === "success" ? <CheckCircle size={16} /> : <WarningCircle size={16} />}
          <span>{toast.message}</span>
        </div>
      )}

      <main className="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full space-y-6">
        {/* ========================================================= */}
        {/* TOP METRIC CARDS */}
        {/* ========================================================= */}
        <div className="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
          <div className="bg-white p-4 sm:p-5 rounded-2xl border border-gray-200 shadow-xs flex items-center justify-between">
            <div>
              <p className="text-xs uppercase tracking-wider font-semibold text-gray-500">Due for Refill (3 Wks)</p>
              <p className="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">{metrics.due_3w_count}</p>
              <p className="text-[11px] text-[#A67C52] font-medium mt-0.5">21 – 28 days since visit</p>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-[#A67C52]/10 text-[#A67C52] flex items-center justify-center shrink-0">
              <Sparkle size={22} weight="fill" />
            </div>
          </div>

          <div className="bg-white p-4 sm:p-5 rounded-2xl border border-gray-200 shadow-xs flex items-center justify-between">
            <div>
              <p className="text-xs uppercase tracking-wider font-semibold text-gray-500">Overdue (4+ Wks)</p>
              <p className="text-2xl sm:text-3xl font-bold text-amber-700 mt-1">{metrics.overdue_count}</p>
              <p className="text-[11px] text-gray-500 font-medium mt-0.5">29+ days since visit</p>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0 border border-amber-100">
              <Clock size={22} />
            </div>
          </div>

          <div className="bg-white p-4 sm:p-5 rounded-2xl border border-gray-200 shadow-xs flex items-center justify-between">
            <div>
              <p className="text-xs uppercase tracking-wider font-semibold text-gray-500">Contacted Today</p>
              <p className="text-2xl sm:text-3xl font-bold text-blue-700 mt-1">{metrics.contacted_today_count}</p>
              <p className="text-[11px] text-gray-500 font-medium mt-0.5">Calls or messages logged</p>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0 border border-blue-100">
              <PhoneCall size={22} />
            </div>
          </div>

          <div className="bg-white p-4 sm:p-5 rounded-2xl border border-gray-200 shadow-xs flex items-center justify-between">
            <div>
              <p className="text-xs uppercase tracking-wider font-semibold text-gray-500">Re-Booked</p>
              <p className="text-2xl sm:text-3xl font-bold text-emerald-700 mt-1">{metrics.booked_count}</p>
              <p className="text-[11px] text-gray-500 font-medium mt-0.5">Secured repeat clients</p>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-100">
              <CheckCircle size={22} weight="fill" />
            </div>
          </div>
        </div>

        {/* ========================================================= */}
        {/* FILTER & SEARCH CONTROLS */}
        {/* ========================================================= */}
        <div className="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs space-y-3">
          <div className="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
            {/* Search Input */}
            <div className="relative flex-1">
              <MagnifyingGlass size={16} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" />
              <input
                type="text"
                placeholder="Search by client name, phone number, or email..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="w-full pl-10 pr-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#A67C52]/30 focus:border-[#A67C52] transition-colors"
              />
              {search && (
                <button
                  type="button"
                  onClick={() => setSearch("")}
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                >
                  <X size={14} />
                </button>
              )}
            </div>

            {/* Refresh Button */}
            <button
              type="button"
              onClick={() => fetchRecalls()}
              disabled={loading}
              className="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl transition-colors cursor-pointer disabled:opacity-50"
            >
              <ArrowsClockwise size={15} className={loading ? "animate-spin" : ""} />
              <span>Refresh</span>
            </button>
          </div>

          {/* Time Bracket & Status Selector Tabs */}
          <div className="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100 text-xs font-medium">
            <span className="text-gray-400 flex items-center gap-1 mr-1">
              <Funnel size={13} /> Timeframe:
            </span>
            {[
              { id: "all", label: `All Eligible (${metrics.total_eligible})` },
              { id: "3_weeks", label: `3-Week Refill (${metrics.due_3w_count})` },
              { id: "4_weeks", label: "4–6 Weeks" },
              { id: "overdue", label: `Overdue (${metrics.overdue_count})` },
            ].map((tab) => (
              <button
                key={tab.id}
                type="button"
                onClick={() => setBracket(tab.id)}
                className={`px-3 py-1.5 rounded-lg transition-colors cursor-pointer ${
                  bracket === tab.id
                    ? "bg-[#A67C52] text-white shadow-xs font-semibold"
                    : "bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-900"
                }`}
              >
                {tab.label}
              </button>
            ))}

            <span className="text-gray-300 mx-1">|</span>

            <span className="text-gray-400">Status:</span>
            {[
              { id: "all", label: "All" },
              { id: "pending", label: "Needs Call" },
              { id: "spoke", label: "Spoke" },
              { id: "no_answer", label: "No Answer" },
              { id: "booked", label: "Booked" },
            ].map((s) => (
              <button
                key={s.id}
                type="button"
                onClick={() => setStatus(s.id)}
                className={`px-2.5 py-1.5 rounded-lg transition-colors cursor-pointer ${
                  status === s.id
                    ? "bg-gray-900 text-white font-semibold"
                    : "bg-gray-50 text-gray-600 hover:bg-gray-200"
                }`}
              >
                {s.label}
              </button>
            ))}
          </div>
        </div>

        {/* ========================================================= */}
        {/* CLIENT CALL LIST */}
        {/* ========================================================= */}
        {error && (
          <div className="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 text-sm flex items-center gap-3">
            <WarningCircle size={18} />
            <span>{error}</span>
          </div>
        )}

        {loading && items.length === 0 ? (
          <div className="bg-white rounded-2xl border border-gray-200 p-12 text-center text-gray-500 space-y-3">
            <ArrowsClockwise size={28} className="animate-spin text-[#A67C52] mx-auto" />
            <p className="text-sm">Scanning for clients due for repeat visit...</p>
          </div>
        ) : items.length === 0 ? (
          <div className="bg-white rounded-2xl border border-gray-200 p-12 text-center text-gray-500 space-y-3">
            <CheckCircle size={36} weight="fill" className="text-emerald-600 mx-auto" />
            <h3 className="font-semibold text-gray-800 text-base">All Caught Up!</h3>
            <p className="text-sm max-w-md mx-auto text-gray-500">
              There are no clients currently overdue or matching your selected filters. Automated 3-week reminders run daily.
            </p>
          </div>
        ) : (
          <div className="space-y-3">
            <div className="flex items-center justify-between text-xs text-gray-500 px-1 font-medium">
              <span>Showing {items.length} client{items.length === 1 ? "" : "s"} due for outreach</span>
              <span>Sorted by longest since last visit</span>
            </div>

            {items.map((item) => {
              const isOverdue = item.days_since_visit > 28;
              const hasEmail = Boolean(item.customer_email);
              const initials = item.customer_name
                .split(" ")
                .map((n) => n[0])
                .slice(0, 2)
                .join("")
                .toUpperCase() || "C";

              return (
                <div
                  key={item.customer_id}
                  className="bg-white rounded-2xl border border-gray-200 p-4 sm:p-5 shadow-xs hover:border-[#A67C52]/40 transition-all flex flex-col lg:flex-row lg:items-center justify-between gap-4"
                >
                  {/* Left: Client info & last visit */}
                  <div className="flex items-start gap-3.5 min-w-0">
                    <div className="w-11 h-11 rounded-2xl bg-[#F6F2EE] text-[#A67C52] font-semibold text-sm flex items-center justify-center shrink-0 border border-[#EADBCC]">
                      {initials}
                    </div>

                    <div className="min-w-0 space-y-1">
                      <div className="flex flex-wrap items-center gap-2">
                        <h4 className="text-sm sm:text-base font-bold text-gray-900 truncate">
                          {item.customer_name}
                        </h4>
                        {getStatusBadge(item.recall_status)}
                        <span
                          className={`text-xs px-2.5 py-0.5 rounded-full font-semibold ${
                            isOverdue
                              ? "bg-rose-50 text-rose-700 border border-rose-200"
                              : "bg-[#FBF6F0] text-[#A67C52] border border-[#EADBCC]"
                          }`}
                        >
                          {item.days_since_visit} days ago
                        </span>
                      </div>

                      <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500">
                        <span className="font-mono text-gray-700 font-semibold">{item.customer_phone || "No phone"}</span>
                        {item.customer_email && (
                          <span className="truncate max-w-[200px] text-gray-400">{item.customer_email}</span>
                        )}
                        <span>•</span>
                        <span>
                          Stylist: <strong className="text-gray-700 font-medium">{item.last_provider_name}</strong>
                        </span>
                        <span>•</span>
                        <span>
                          Visit: {item.last_booking_date}
                        </span>
                      </div>

                      {/* Last Services tags */}
                      {item.last_services && item.last_services.length > 0 && (
                        <div className="flex flex-wrap items-center gap-1.5 pt-1">
                          <span className="text-[11px] text-gray-400">Previous:</span>
                          {item.last_services.map((srv, idx) => (
                            <span
                              key={idx}
                              className="text-[11px] px-2 py-0.5 rounded-md bg-gray-50 text-gray-700 border border-gray-200 font-medium"
                            >
                              {srv}
                            </span>
                          ))}
                        </div>
                      )}

                      {/* Staff note / Call history */}
                      {(item.notes || item.called_at) && (
                        <div className="pt-2 text-xs text-gray-600 flex items-start gap-1.5 bg-gray-50/70 p-2 rounded-lg border border-gray-100 max-w-xl">
                          <FileText size={14} className="text-[#A67C52] shrink-0 mt-0.5" />
                          <div className="min-w-0">
                            {item.notes && <p className="italic text-gray-700 font-sans">“{item.notes}”</p>}
                            {item.called_at && (
                              <p className="text-[11px] text-gray-400 mt-0.5">
                                Logged {new Date(item.called_at).toLocaleDateString()} by {item.called_by_name || "Staff"}
                              </p>
                            )}
                          </div>
                        </div>
                      )}

                      {/* Automated email delivery indicator */}
                      <div className="pt-1 text-[11px] text-gray-400 flex items-center gap-1.5">
                        <EnvelopeSimple size={12} className="text-gray-400" />
                        {item.email_reminder_sent_at ? (
                          <span className="text-emerald-700 font-medium">
                            3-week rebooking email sent on {new Date(item.email_reminder_sent_at).toLocaleDateString()}
                          </span>
                        ) : (
                          <span>Automated 3-week email not yet sent</span>
                        )}
                      </div>
                    </div>
                  </div>

                  {/* Right: Quick Outreach Action Buttons */}
                  <div className="flex flex-wrap sm:flex-nowrap items-center gap-2 shrink-0 pt-2 lg:pt-0 border-t lg:border-t-0 border-gray-100">
                    {/* One-click Call button */}
                    {item.customer_phone ? (
                      <a
                        href={`tel:${item.customer_phone}`}
                        className="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-900 border border-gray-200 transition-colors cursor-pointer"
                        title="Call Customer"
                      >
                        <Phone size={14} className="text-gray-700" />
                        <span>Call</span>
                      </a>
                    ) : null}

                    {/* Direct WhatsApp button with pre-filled luxury invite message */}
                    {item.whatsapp_url ? (
                      <a
                        href={item.whatsapp_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl bg-[#25D366]/10 hover:bg-[#25D366]/20 text-[#128C7E] border border-[#25D366]/30 transition-colors cursor-pointer"
                        title="Open WhatsApp Chat with Pre-filled Invite"
                      >
                        <ChatCircleDots size={14} className="text-[#25D366]" />
                        <span>WhatsApp</span>
                      </a>
                    ) : null}

                    {/* Manual Send Re-Booking Email button */}
                    {hasEmail ? (
                      <button
                        type="button"
                        onClick={() => handleSendReminderEmail(item.customer_id, item.customer_name)}
                        disabled={sendingEmailId === item.customer_id}
                        className="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-700 border border-gray-200 transition-colors cursor-pointer disabled:opacity-50"
                        title="Send Rebooking Email Reminder"
                      >
                        <EnvelopeSimple size={14} className={sendingEmailId === item.customer_id ? "animate-spin" : ""} />
                        <span>Email</span>
                      </button>
                    ) : null}

                    {/* Log Call Modal Trigger */}
                    <button
                      type="button"
                      onClick={() => openLogModal(item)}
                      className="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl bg-[#A67C52] hover:bg-[#8D6640] text-white transition-colors cursor-pointer shadow-xs"
                      title="Log Call Outcome & Notes"
                    >
                      <PhoneCall size={14} />
                      <span>Log Call</span>
                    </button>

                    {/* Book Appointment Shortcut */}
                    <Link
                      href={`/admin/bookings/manual?customer_id=${item.customer_id}`}
                      className="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-xl bg-gray-900 hover:bg-black text-white transition-colors cursor-pointer"
                      title="Create Booking for this Client"
                    >
                      <Calendar size={14} />
                      <span>Book</span>
                    </Link>
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </main>

      {/* ========================================================= */}
      {/* LOG CALL OUTCOME MODAL */}
      {/* ========================================================= */}
      {activeModalItem && (
        <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-gray-100 space-y-5 animate-in fade-in zoom-in-95 duration-150">
            <div className="flex items-center justify-between border-b border-gray-100 pb-4">
              <div>
                <h3 className="text-lg font-bold text-gray-900">Log Customer Contact</h3>
                <p className="text-xs text-gray-500 mt-0.5">
                  Client: <strong className="text-gray-800">{activeModalItem.customer_name}</strong> • {activeModalItem.customer_phone}
                </p>
              </div>
              <button
                type="button"
                onClick={closeLogModal}
                className="p-2 rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition-colors"
              >
                <X size={18} />
              </button>
            </div>

            {/* Status selection */}
            <div className="space-y-2">
              <label className="text-xs font-semibold text-gray-700 uppercase tracking-wider block">
                Call / Message Outcome
              </label>
              <div className="grid grid-cols-2 gap-2 text-xs font-medium">
                {[
                  { id: "spoke", label: "Spoke - Booking Soon", desc: "Interested in returning" },
                  { id: "booked", label: "Booked Appointment", desc: "Appointment secured" },
                  { id: "no_answer", label: "No Answer / Left Msg", desc: "Follow up later" },
                  { id: "do_not_contact", label: "Do Not Contact", desc: "Remove from recalls" },
                ].map((opt) => (
                  <button
                    key={opt.id}
                    type="button"
                    onClick={() => setModalStatus(opt.id as any)}
                    className={`p-3 rounded-xl border text-left transition-all cursor-pointer ${
                      modalStatus === opt.id
                        ? "border-[#A67C52] bg-[#FBF6F0] text-gray-900 ring-2 ring-[#A67C52]/20 font-semibold"
                        : "border-gray-200 bg-white text-gray-600 hover:bg-gray-50"
                    }`}
                  >
                    <p className="font-bold text-gray-900">{opt.label}</p>
                    <p className="text-[11px] text-gray-500 font-normal mt-0.5">{opt.desc}</p>
                  </button>
                ))}
              </div>
            </div>

            {/* Notes textarea */}
            <div className="space-y-1.5">
              <label className="text-xs font-semibold text-gray-700 uppercase tracking-wider block">
                Call Notes & Preferences
              </label>
              <textarea
                rows={3}
                placeholder="e.g. Asked to call back on Friday; wants French manicure with Aline; prefers 2 PM slots..."
                value={modalNotes}
                onChange={(e) => setModalNotes(e.target.value)}
                className="w-full p-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#A67C52]/30 focus:border-[#A67C52] resize-none"
              />
            </div>

            {/* Modal actions */}
            <div className="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
              <button
                type="button"
                onClick={closeLogModal}
                disabled={savingLog}
                className="px-4 py-2.5 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-100 transition-colors"
              >
                Cancel
              </button>
              <button
                type="button"
                onClick={handleSaveContactLog}
                disabled={savingLog}
                className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold bg-[#A67C52] hover:bg-[#8D6640] text-white transition-colors cursor-pointer shadow-xs disabled:opacity-50"
              >
                {savingLog ? <ArrowsClockwise size={14} className="animate-spin" /> : <CheckCircle size={14} weight="fill" />}
                <span>Save Log</span>
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
