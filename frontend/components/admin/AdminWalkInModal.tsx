"use client";

import { useCallback, useEffect, useMemo, useState, type FormEvent } from "react";
import { ArrowsClockwise, Check, Clock, Scissors, User, X } from "@phosphor-icons/react";

type WalkInCustomer = { id: number; name: string; email: string | null; phone: string | null };
type WalkInProvider = { id: number; name: string; service_ids: number[] };
type WalkInService = { id: number; name: string; price: number; duration_minutes: number | null };

type WalkInOptions = {
  customers: WalkInCustomer[];
  providers: WalkInProvider[];
  services: WalkInService[];
};

type Props = {
  onClose: () => void;
  onCreated: (message: string, reference: string) => void;
  initialDate?: string;
  initialTime?: string;
  initialProviderId?: number | null;
};

const fieldClass = "w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10";

function getLocalDateTime() {
  const now = new Date();
  const localDate = new Date(now.getTime() - now.getTimezoneOffset() * 60000);
  const value = localDate.toISOString();
  return { date: value.slice(0, 10), time: value.slice(11, 16) };
}

async function readResponse(response: Response): Promise<any> {
  const body = await response.text();
  let result: any;
  try {
    result = body ? JSON.parse(body) : {};
  } catch {
    throw new Error(response.status >= 500
      ? "The server could not complete the request. Please try again."
      : "The server returned an unreadable response.");
  }

  if (!response.ok || result.success === false) {
    const validation = result.errors ? Object.values(result.errors).flat().join(" ") : "";
    throw new Error(validation || result.message || "The request could not be completed.");
  }

  return result;
}

export default function AdminWalkInModal({ onClose, onCreated, initialDate, initialTime, initialProviderId }: Props) {
  const nowLocal = getLocalDateTime();
  const [options, setOptions] = useState<WalkInOptions>({ customers: [], providers: [], services: [] });
  const [loadingOptions, setLoadingOptions] = useState(true);
  const [optionsError, setOptionsError] = useState("");
  const [customerId, setCustomerId] = useState("new");
  const [customerName, setCustomerName] = useState("");
  const [customerEmail, setCustomerEmail] = useState("");
  const [customerPhone, setCustomerPhone] = useState("");
  const [providerId, setProviderId] = useState(initialProviderId ? String(initialProviderId) : "");
  const [serviceIds, setServiceIds] = useState<number[]>([]);
  const [visitDate, setVisitDate] = useState(() => initialDate || getLocalDateTime().date);
  const [visitTime, setVisitTime] = useState(() => initialTime || getLocalDateTime().time);
  const [visitStatus, setVisitStatus] = useState<"in_progress" | "completed">(() =>
    (initialDate || getLocalDateTime().date) < getLocalDateTime().date ? "completed" : "in_progress",
  );
  const [paymentAmount, setPaymentAmount] = useState("");
  const [paymentMethod, setPaymentMethod] = useState("cash");
  const [notes, setNotes] = useState("");
  const [error, setError] = useState("");
  const [submitting, setSubmitting] = useState(false);

  const fetchOptions = useCallback(async () => {
    setLoadingOptions(true);
    setOptionsError("");
    try {
      const response = await fetch("/api/v1/admin/bookings/walk-in/options", { cache: "no-store", headers: { Accept: "application/json" } });
      const result = await readResponse(response);
      setOptions(result.data);
    } catch (requestError) {
      setOptionsError(requestError instanceof Error ? requestError.message : "Walk-in options could not be loaded.");
    } finally {
      setLoadingOptions(false);
    }
  }, []);

  useEffect(() => {
    void fetchOptions();
  }, [fetchOptions]);

  useEffect(() => {
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape" && !submitting) onClose();
    };
    window.addEventListener("keydown", handleKeyDown);
    return () => window.removeEventListener("keydown", handleKeyDown);
  }, [onClose, submitting]);

  const selectedServices = useMemo(
    () => options.services.filter((service) => serviceIds.includes(service.id)),
    [options.services, serviceIds],
  );
  const availableServices = useMemo(() => {
    const selectedProvider = options.providers.find((provider) => String(provider.id) === providerId);
    return selectedProvider
      ? options.services.filter((service) => selectedProvider.service_ids.includes(service.id))
      : options.services;
  }, [options.providers, options.services, providerId]);
  const total = useMemo(
    () => selectedServices.reduce((sum, service) => sum + Number(service.price || 0), 0),
    [selectedServices],
  );
  const selectedCustomer = options.customers.find((customer) => customer.id.toString() === customerId);

  const toggleService = (id: number) => {
    setServiceIds((current) => current.includes(id) ? current.filter((serviceId) => serviceId !== id) : [...current, id]);
  };

  const submit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setError("");
    if (serviceIds.length === 0) {
      setError("Choose at least one service for this visit.");
      return;
    }
    if (Number(paymentAmount || 0) > total) {
      setError("Payment cannot be greater than the selected services total.");
      return;
    }

    setSubmitting(true);
    try {
      const response = await fetch("/api/v1/admin/bookings/walk-in", {
        method: "POST",
        headers: { Accept: "application/json", "Content-Type": "application/json" },
        body: JSON.stringify({
          customer_id: customerId,
          customer_name: customerId === "new" ? customerName : undefined,
          customer_email: customerId === "new" ? customerEmail || null : undefined,
          customer_phone: customerId === "new" ? customerPhone || null : undefined,
          provider_id: Number(providerId),
          services: serviceIds,
          date: visitDate,
          time: visitTime,
          status: visitStatus,
          payment_amount: Number(paymentAmount || 0),
          payment_method: paymentAmount ? paymentMethod : undefined,
          notes: notes || null,
        }),
      });
      const result = await readResponse(response);
      onCreated(result.message || "Walk-in visit recorded successfully.", result.data.reference);
    } catch (requestError) {
      setError(requestError instanceof Error ? requestError.message : "The walk-in visit could not be recorded.");
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div
      className="fixed inset-0 z-[70] flex items-end justify-center bg-black/50 p-0 backdrop-blur-[2px] sm:items-center sm:p-4"
      onMouseDown={(event) => { if (event.target === event.currentTarget && !submitting) onClose(); }}
    >
      <section
        role="dialog"
        aria-modal="true"
        aria-labelledby="walk-in-title"
        className="flex max-h-[96dvh] w-full max-w-3xl flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl animate-in slide-in-from-bottom-3 duration-200 sm:max-h-[92vh] sm:rounded-2xl sm:animate-in sm:fade-in sm:zoom-in-95"
      >
        <header className="flex shrink-0 items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3.5 sm:px-6">
          <div className="flex min-w-0 items-center gap-3">
            <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700"><User size={20} weight="bold" /></span>
            <div className="min-w-0"><h2 id="walk-in-title" className="text-base font-bold text-gray-900">Record walk-in visit</h2><p className="mt-0.5 text-xs text-gray-500">Record who served the customer and the services they chose.</p></div>
          </div>
          <button type="button" onClick={onClose} disabled={submitting} aria-label="Close walk-in form" className="rounded-lg p-2 text-gray-500 transition hover:bg-gray-200 hover:text-gray-900 disabled:opacity-50"><X size={18} /></button>
        </header>

        {optionsError && <div role="alert" className="mx-4 mt-4 flex items-center justify-between gap-3 rounded-lg border border-rose-200 bg-rose-50 p-3 text-xs text-rose-800 sm:mx-6"><span>{optionsError}</span><button type="button" onClick={() => void fetchOptions()} className="shrink-0 font-semibold underline">Retry</button></div>}
        {error && <div role="alert" className="mx-4 mt-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-xs text-rose-800 sm:mx-6">{error}</div>}

        <form onSubmit={submit} className="flex min-h-0 flex-1 flex-col">
          <div className="min-h-0 flex-1 space-y-5 overflow-y-auto px-4 py-4 sm:px-6 sm:py-5">
            <section className="space-y-3">
              <div><label htmlFor="walkin-customer" className="mb-1.5 block text-xs font-semibold text-gray-700">Customer</label>
                <select id="walkin-customer" value={customerId} onChange={(event) => setCustomerId(event.target.value)} disabled={loadingOptions} className={fieldClass}>
                  <option value="new">New walk-in customer</option>
                  {options.customers.map((customer) => <option key={customer.id} value={customer.id}>{customer.name}{customer.phone ? ` · ${customer.phone}` : ""}</option>)}
                </select>
              </div>
              {customerId === "new" ? (
                <div className="grid gap-3 sm:grid-cols-3">
                  <div><label htmlFor="walkin-name" className="mb-1.5 block text-xs font-semibold text-gray-700">Name <span className="text-rose-600">*</span></label><input id="walkin-name" required maxLength={255} value={customerName} onChange={(event) => setCustomerName(event.target.value)} placeholder="Customer name" className={fieldClass} /></div>
                  <div><label htmlFor="walkin-phone" className="mb-1.5 block text-xs font-semibold text-gray-700">Phone <span className="font-normal text-gray-400">(optional)</span></label><input id="walkin-phone" maxLength={50} value={customerPhone} onChange={(event) => setCustomerPhone(event.target.value)} placeholder="Phone number" className={fieldClass} /></div>
                  <div><label htmlFor="walkin-email" className="mb-1.5 block text-xs font-semibold text-gray-700">Email <span className="font-normal text-gray-400">(optional)</span></label><input id="walkin-email" type="email" maxLength={255} value={customerEmail} onChange={(event) => setCustomerEmail(event.target.value)} placeholder="Email address" className={fieldClass} /></div>
                </div>
              ) : selectedCustomer ? <p className="rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-600">{selectedCustomer.name}{selectedCustomer.phone ? ` · ${selectedCustomer.phone}` : ""}{selectedCustomer.email ? ` · ${selectedCustomer.email}` : ""}</p> : null}
            </section>

            <section className="grid gap-3 sm:grid-cols-2">
              <div><label htmlFor="walkin-provider" className="mb-1.5 block text-xs font-semibold text-gray-700">Served by <span className="text-rose-600">*</span></label><select id="walkin-provider" required value={providerId} onChange={(event) => { const nextProviderId = event.target.value; setProviderId(nextProviderId); const nextProvider = options.providers.find((provider) => String(provider.id) === nextProviderId); setServiceIds((current) => current.filter((serviceId) => nextProvider?.service_ids.includes(serviceId))); }} disabled={loadingOptions} className={fieldClass}><option value="">Choose staff member</option>{options.providers.map((provider) => <option key={provider.id} value={provider.id}>{provider.name}</option>)}</select></div>
              <div className="grid grid-cols-2 gap-2">
                <div><label htmlFor="walkin-date" className="mb-1.5 block text-xs font-semibold text-gray-700">Visit date</label><input id="walkin-date" type="date" required max={nowLocal.date} value={visitDate} onChange={(event) => { const nextDate = event.target.value; setVisitDate(nextDate); if (nextDate < nowLocal.date) setVisitStatus("completed"); }} className={fieldClass} /></div>
                <div><label htmlFor="walkin-time" className="mb-1.5 block text-xs font-semibold text-gray-700">Time</label><input id="walkin-time" type="time" required max={visitDate === nowLocal.date ? nowLocal.time : undefined} value={visitTime} onChange={(event) => setVisitTime(event.target.value)} className={fieldClass} /></div>
              </div>
            </section>

            <section>
              <div className="mb-2 flex items-center justify-between gap-2"><div className="flex items-center gap-2"><Scissors size={16} className="text-gray-500" /><h3 className="text-xs font-bold text-gray-800">Services chosen <span className="text-rose-600">*</span></h3></div><span className="text-[11px] text-gray-500">Select all that apply</span></div>
              {availableServices.length === 0 && !loadingOptions ? <p className="rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">{options.services.length ? "This provider has no services assigned. Update their service assignments first." : "No approved services are available."}</p> : <div className="grid gap-2 sm:grid-cols-2">{availableServices.map((service) => {
                const selected = serviceIds.includes(service.id);
                return <label key={service.id} className={`flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition ${selected ? "border-emerald-400 bg-emerald-50" : "border-gray-200 bg-white hover:border-gray-300"}`}><input type="checkbox" checked={selected} onChange={() => toggleService(service.id)} className="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-emerald-700 focus:ring-emerald-600" /><span className="min-w-0 flex-1"><span className="flex items-start justify-between gap-2"><span className="text-xs font-semibold text-gray-900">{service.name}</span><span className="shrink-0 text-xs font-bold tabular-nums text-gray-900">{Number(service.price).toLocaleString()} RWF</span></span><span className="mt-1 flex items-center gap-1 text-[10px] text-gray-500"><Clock size={11} />{service.duration_minutes || 60} min</span></span></label>;
              })}</div>}
              <div className="mt-2 flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2"><span className="text-[11px] text-gray-500">{selectedServices.length} service{selectedServices.length === 1 ? "" : "s"} · {selectedServices.reduce((sum, service) => sum + (service.duration_minutes || 60), 0)} min</span><span className="text-sm font-bold text-gray-900">Total {total.toLocaleString()} RWF</span></div>
            </section>

            <section className="grid gap-3 sm:grid-cols-2">
              <div><label htmlFor="walkin-status" className="mb-1.5 block text-xs font-semibold text-gray-700">Visit status</label><select id="walkin-status" value={visitStatus} onChange={(event) => setVisitStatus(event.target.value as "in_progress" | "completed")} className={fieldClass}><option value="in_progress" disabled={visitDate !== nowLocal.date}>In progress</option><option value="completed">Already completed</option></select><p className="mt-1 text-[10px] text-gray-500">Walk-ins are recorded after arrival. Use Manual Booking for future appointments.</p></div>
              <div><label htmlFor="walkin-payment" className="mb-1.5 block text-xs font-semibold text-gray-700">Payment received <span className="font-normal text-gray-400">(optional)</span></label><div className="flex gap-2"><input id="walkin-payment" type="number" min="0" max={total || undefined} step="1" value={paymentAmount} onChange={(event) => setPaymentAmount(event.target.value)} placeholder="Amount in RWF" className={`${fieldClass} min-w-0`} /><select aria-label="Payment method" value={paymentMethod} onChange={(event) => setPaymentMethod(event.target.value)} disabled={!paymentAmount} className="w-28 shrink-0 rounded-lg border border-gray-300 bg-white px-2 py-2 text-xs text-gray-800 disabled:bg-gray-100"><option value="cash">Cash</option><option value="card">Card / POS</option><option value="momo">MoMo</option></select></div>{paymentAmount && <p className="mt-1 text-[10px] text-gray-500">Balance after payment: {Math.max(0, total - Number(paymentAmount || 0)).toLocaleString()} RWF</p>}</div>
            </section>

            <section><label htmlFor="walkin-notes" className="mb-1.5 block text-xs font-semibold text-gray-700">Visit notes <span className="font-normal text-gray-400">(optional)</span></label><textarea id="walkin-notes" rows={2} maxLength={5000} value={notes} onChange={(event) => setNotes(event.target.value)} placeholder="Add details about this salon visit" className={`${fieldClass} resize-y`} /></section>
          </div>

          <footer className="flex shrink-0 flex-col-reverse gap-2 border-t border-gray-200 bg-gray-50 px-4 py-3.5 sm:flex-row sm:justify-end sm:px-6">
            <button type="button" onClick={onClose} disabled={submitting} className="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-100 disabled:opacity-50">Cancel</button>
            <button type="submit" disabled={submitting || loadingOptions || !providerId || serviceIds.length === 0 || (customerId === "new" && !customerName.trim())} className="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-xs font-semibold text-white hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50">{submitting ? <ArrowsClockwise size={14} className="animate-spin" /> : <Check size={14} weight="bold" />}{submitting ? "Saving visit…" : "Record visit"}</button>
          </footer>
        </form>
      </section>
    </div>
  );
}
