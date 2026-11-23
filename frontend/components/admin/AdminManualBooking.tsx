"use client";

import { useEffect, useMemo, useState } from "react";
import type { FormEvent } from "react";
import Link from "next/link";
import {
  ArrowsClockwise,
  CalendarPlus,
  Check,
  CheckCircle,
  Clock,
  EnvelopeSimple,
  Plus,
  Scissors,
  User,
  Warning,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";

interface CustomerOption {
  id: number;
  name: string;
  email: string | null;
  phone: string | null;
}

interface ProviderOption {
  id: number;
  name: string;
  service_ids: number[];
}

interface ServiceOption {
  id: number;
  name: string;
  price: number;
  duration_minutes: number;
}

interface OptionsResponse {
  success: boolean;
  data: {
    customers: CustomerOption[];
    providers: ProviderOption[];
    services: ServiceOption[];
  };
}

interface ManualBookingResult {
  id: number;
  reference: string;
  customer: CustomerOption;
  provider: ProviderOption | null;
  services: ServiceOption[];
  date: string;
  time: string;
  payment_option: "full" | "deposit";
  deposit_amount: number;
  total: number;
  status: string;
  email_sent: boolean | null;
}

interface ManualBookingForm {
  customer_id: string;
  new_customer_name: string;
  new_customer_email: string;
  new_customer_phone: string;
  provider_id: string;
  date: string;
  time: string;
  services: number[];
  payment_option: "full" | "deposit";
  deposit_amount: string;
  notes: string;
  send_email: boolean;
}

const fieldClass = "w-full rounded-lg border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10";

function todayLocal(): string {
  const now = new Date();
  return new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
}

function emptyForm(): ManualBookingForm {
  return {
    customer_id: "",
    new_customer_name: "",
    new_customer_email: "",
    new_customer_phone: "",
    provider_id: "",
    date: todayLocal(),
    time: "",
    services: [],
    payment_option: "full",
    deposit_amount: "0",
    notes: "",
    send_email: true,
  };
}

function formatRwf(value: number): string {
  return `RWF ${new Intl.NumberFormat("en-RW", { maximumFractionDigits: 0 }).format(value || 0)}`;
}

async function readResponse(response: Response) {
  const result = await response.json().catch(() => ({}));
  if (!response.ok || result.success === false) {
    const validation = result.errors ? Object.values(result.errors).flat().join(" ") : "";
    throw new Error(validation || result.message || "The request could not be completed.");
  }
  return result;
}

export default function AdminManualBooking() {
  const [customers, setCustomers] = useState<CustomerOption[]>([]);
  const [providers, setProviders] = useState<ProviderOption[]>([]);
  const [services, setServices] = useState<ServiceOption[]>([]);
  const [optionsLoading, setOptionsLoading] = useState(true);
  const [optionsError, setOptionsError] = useState<string | null>(null);
  const [form, setForm] = useState<ManualBookingForm>(emptyForm);
  const [submitting, setSubmitting] = useState(false);
  const [formError, setFormError] = useState<string | null>(null);
  const [createdBooking, setCreatedBooking] = useState<ManualBookingResult | null>(null);

  useEffect(() => {
    const controller = new AbortController();
    const loadOptions = async () => {
      setOptionsLoading(true);
      setOptionsError(null);
      try {
        const response = await fetch("/api/v1/admin/bookings/manual/options", {
          headers: { Accept: "application/json" },
          cache: "no-store",
          signal: controller.signal,
        });
        const result = (await readResponse(response)) as OptionsResponse;
        if (controller.signal.aborted) return;
        setCustomers(result.data?.customers || []);
        setProviders(result.data?.providers || []);
        setServices(result.data?.services || []);
      } catch (error) {
        if (!controller.signal.aborted) setOptionsError(error instanceof Error ? error.message : "Could not load booking options.");
      } finally {
        if (!controller.signal.aborted) setOptionsLoading(false);
      }
    };
    void loadOptions();
    return () => controller.abort();
  }, []);

  const selectedProvider = providers.find((provider) => String(provider.id) === form.provider_id);
  const availableServices = useMemo(
    () => selectedProvider ? services.filter((service) => selectedProvider.service_ids.includes(service.id)) : services,
    [selectedProvider, services],
  );
  const selectedServices = useMemo(
    () => availableServices.filter((service) => form.services.includes(service.id)),
    [availableServices, form.services],
  );
  const bookingTotal = selectedServices.reduce((total, service) => total + Number(service.price || 0), 0);
  const totalDuration = selectedServices.reduce((total, service) => total + Number(service.duration_minutes || 0), 0);
  const isCreatingCustomer = form.customer_id === "new";

  const updateForm = <K extends keyof ManualBookingForm>(key: K, value: ManualBookingForm[K]) => {
    setForm((current) => ({ ...current, [key]: value }));
    setFormError(null);
  };

  const toggleService = (serviceId: number) => {
    setForm((current) => ({
      ...current,
      services: current.services.includes(serviceId)
        ? current.services.filter((id) => id !== serviceId)
        : [...current.services, serviceId],
    }));
    setFormError(null);
  };

  const submitBooking = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setFormError(null);
    if (!form.customer_id) {
      setFormError("Select a customer or choose Create new customer.");
      return;
    }
    if (form.services.length === 0) {
      setFormError("Select at least one service.");
      return;
    }
    if (form.payment_option === "deposit" && Number(form.deposit_amount) > bookingTotal) {
      setFormError("Deposit cannot be greater than the selected services total.");
      return;
    }

    setSubmitting(true);
    try {
      const response = await fetch("/api/v1/admin/bookings/manual", {
        method: "POST",
        headers: { Accept: "application/json", "Content-Type": "application/json" },
        body: JSON.stringify({
          ...form,
          deposit_amount: form.payment_option === "deposit" ? Number(form.deposit_amount) : 0,
        }),
      });
      const result = await readResponse(response);
      setCreatedBooking(result.data as ManualBookingResult);
    } catch (error) {
      setFormError(error instanceof Error ? error.message : "The booking could not be created.");
    } finally {
      setSubmitting(false);
    }
  };

  const resetForm = () => {
    setCreatedBooking(null);
    setForm(emptyForm());
    setFormError(null);
  };

  return (
    <div className="flex min-w-0 flex-1 flex-col bg-gray-50 pb-16">
      <AdminHeader title="Manual Booking" subtitle="Create a booking on behalf of a customer" />

      <div className="mx-auto w-full max-w-5xl space-y-4 p-4 sm:p-6">
        {optionsError && <div role="alert" className="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800"><Warning size={18} className="mt-0.5 shrink-0 text-rose-500" /><div className="min-w-0 flex-1"><p className="font-semibold">Booking options could not be loaded</p><p className="mt-1">{optionsError}</p></div><button type="button" onClick={() => window.location.reload()} className="shrink-0 rounded-lg border border-rose-200 bg-white px-3 py-2 font-semibold hover:bg-rose-100">Retry</button></div>}

        {createdBooking ? (
          <section className="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-2xs">
            <div className="flex flex-col items-center bg-emerald-50 px-5 py-10 text-center sm:px-8"><span className="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-700"><CheckCircle size={30} weight="fill" /></span><h2 className="mt-4 text-xl font-bold text-gray-900">Booking created</h2><p className="mt-1 max-w-md text-sm text-gray-600">{createdBooking.email_sent === true ? "The customer confirmation email was sent." : createdBooking.email_sent === false ? "The booking was saved, but the confirmation email could not be sent." : "The booking was saved without sending an email."}</p><p className="mt-4 rounded-lg border border-emerald-200 bg-white px-4 py-2 font-mono text-sm font-bold text-gray-900">{createdBooking.reference}</p></div>
            <div className="grid gap-4 p-5 text-sm sm:grid-cols-2 sm:p-7"><div><p className="text-[10px] font-bold uppercase tracking-wider text-gray-500">Customer</p><p className="mt-1 font-semibold text-gray-900">{createdBooking.customer.name}</p><p className="text-xs text-gray-500">{createdBooking.customer.email}</p></div><div><p className="text-[10px] font-bold uppercase tracking-wider text-gray-500">Provider and time</p><p className="mt-1 font-semibold text-gray-900">{createdBooking.provider?.name || "Provider"}</p><p className="text-xs text-gray-500">{createdBooking.date} at {createdBooking.time}</p></div><div><p className="text-[10px] font-bold uppercase tracking-wider text-gray-500">Services</p><p className="mt-1 font-semibold text-gray-900">{createdBooking.services.map((service) => service.name).join(", ")}</p></div><div><p className="text-[10px] font-bold uppercase tracking-wider text-gray-500">Booking total</p><p className="mt-1 font-semibold text-gray-900">{formatRwf(createdBooking.total)}</p><p className="text-xs text-gray-500">{createdBooking.payment_option === "deposit" ? `Deposit ${formatRwf(createdBooking.deposit_amount)}` : "Full payment option"}</p></div></div>
            <div className="flex flex-col-reverse gap-2 border-t border-gray-100 bg-gray-50/60 p-4 sm:flex-row sm:justify-end sm:px-7"><Link href="/admin/bookings" className="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">View bookings</Link><button type="button" onClick={resetForm} className="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-xs font-semibold text-white hover:bg-gray-800"><Plus size={14} weight="bold" />Create another booking</button></div>
          </section>
        ) : (
          <>
            <div className="flex items-center justify-between gap-3"><div><h2 className="text-base font-bold text-gray-900">Create manual booking</h2><p className="mt-1 text-xs text-gray-500">Choose the customer, services, and appointment details.</p></div><Link href="/admin/bookings" className="hidden text-xs font-semibold text-gray-600 hover:text-gray-900 sm:inline-flex">Back to bookings</Link></div>

            {formError && <div role="alert" className="flex items-start gap-2.5 rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-xs text-rose-800"><Warning size={16} className="mt-0.5 shrink-0 text-rose-500" /><span>{formError}</span></div>}

            <form onSubmit={submitBooking} className="space-y-4">
              <section className="space-y-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-2xs sm:p-5">
                <div className="flex items-center gap-2"><span className="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-700"><User size={16} /></span><div><h3 className="text-sm font-bold text-gray-900">Customer</h3><p className="text-[11px] text-gray-500">Select an existing customer or add a new one.</p></div></div>
                <div><label htmlFor="customer_id" className="mb-1.5 block text-xs font-semibold text-gray-700">Customer <span className="text-rose-500">*</span></label><select id="customer_id" required value={form.customer_id} disabled={optionsLoading} onChange={(event) => { const value = event.target.value; setForm((current) => ({ ...current, customer_id: value, ...(value !== "new" ? { new_customer_name: "", new_customer_email: "", new_customer_phone: "" } : {}) })); setFormError(null); }} className={fieldClass}><option value="">{optionsLoading ? "Loading customers…" : "Select a customer"}</option><option value="new">＋ Create new customer</option>{customers.map((customer) => <option key={customer.id} value={customer.id}>{customer.name}{customer.email ? ` (${customer.email})` : customer.phone ? ` (${customer.phone})` : ""}</option>)}</select></div>
                {isCreatingCustomer && <div className="grid gap-3 rounded-xl border border-blue-200 bg-blue-50 p-4 sm:grid-cols-2"><div className="sm:col-span-2"><h4 className="text-xs font-bold text-blue-900">New customer information</h4></div><div><label htmlFor="new_customer_name" className="mb-1.5 block text-xs font-semibold text-gray-700">Name <span className="text-rose-500">*</span></label><input id="new_customer_name" required maxLength={255} value={form.new_customer_name} onChange={(event) => updateForm("new_customer_name", event.target.value)} placeholder="Customer name" className={fieldClass} /></div><div><label htmlFor="new_customer_email" className="mb-1.5 block text-xs font-semibold text-gray-700">Email <span className="text-rose-500">*</span></label><input id="new_customer_email" required type="email" maxLength={255} value={form.new_customer_email} onChange={(event) => updateForm("new_customer_email", event.target.value)} placeholder="name@example.com" className={fieldClass} /></div><div className="sm:col-span-2"><label htmlFor="new_customer_phone" className="mb-1.5 block text-xs font-semibold text-gray-700">Phone <span className="font-normal text-gray-400">(optional)</span></label><input id="new_customer_phone" type="tel" maxLength={50} value={form.new_customer_phone} onChange={(event) => updateForm("new_customer_phone", event.target.value)} placeholder="Phone number" className={fieldClass} /></div></div>}
              </section>

              <section className="space-y-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-2xs sm:p-5">
                <div className="flex items-center gap-2"><span className="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-700"><CalendarPlus size={16} /></span><div><h3 className="text-sm font-bold text-gray-900">Appointment</h3><p className="text-[11px] text-gray-500">Choose an active provider and appointment time.</p></div></div>
                <div className="grid gap-3 sm:grid-cols-3"><div><label htmlFor="provider_id" className="mb-1.5 block text-xs font-semibold text-gray-700">Provider <span className="text-rose-500">*</span></label><select id="provider_id" required value={form.provider_id} disabled={optionsLoading || providers.length === 0} onChange={(event) => { const providerId = event.target.value; const provider = providers.find((item) => String(item.id) === providerId); setForm((current) => ({ ...current, provider_id: providerId, services: current.services.filter((id) => provider?.service_ids.includes(id)) })); setFormError(null); }} className={fieldClass}><option value="">Select provider</option>{providers.map((provider) => <option key={provider.id} value={provider.id}>{provider.name}</option>)}</select>{providers.length === 0 && !optionsLoading && <p className="mt-1 text-[10px] text-amber-700">No active providers are available.</p>}</div><div><label htmlFor="booking_date" className="mb-1.5 block text-xs font-semibold text-gray-700">Date <span className="text-rose-500">*</span></label><input id="booking_date" type="date" required min={todayLocal()} value={form.date} onChange={(event) => updateForm("date", event.target.value)} className={fieldClass} /></div><div><label htmlFor="booking_time" className="mb-1.5 block text-xs font-semibold text-gray-700">Time <span className="text-rose-500">*</span></label><input id="booking_time" type="time" required value={form.time} onChange={(event) => updateForm("time", event.target.value)} className={fieldClass} /></div></div>
              </section>

              <section className="space-y-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-2xs sm:p-5">
                <div className="flex items-center justify-between gap-3"><div className="flex items-center gap-2"><span className="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700"><Scissors size={16} /></span><div><h3 className="text-sm font-bold text-gray-900">Services <span className="text-rose-500">*</span></h3><p className="text-[11px] text-gray-500">Select one or more approved services.</p></div></div>{selectedServices.length > 0 && <span className="shrink-0 rounded-lg bg-gray-100 px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">{selectedServices.length} selected</span>}</div>
                {optionsLoading ? <div className="grid gap-2 sm:grid-cols-2">{[0, 1, 2, 3].map((item) => <div key={item} className="h-16 animate-pulse rounded-xl bg-gray-100" />)}</div> : availableServices.length === 0 ? <p className="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800">{services.length ? "This provider has no services assigned. Update their service assignments first." : "There are no approved services available for booking."}</p> : <div className="grid gap-2 sm:grid-cols-2">{availableServices.map((service) => { const selected = form.services.includes(service.id); return <label key={service.id} className={`flex min-w-0 cursor-pointer items-center gap-3 rounded-xl border p-3 transition ${selected ? "border-blue-300 bg-blue-50/70" : "border-gray-200 hover:border-gray-300 hover:bg-gray-50"}`}><input type="checkbox" checked={selected} onChange={() => toggleService(service.id)} className="h-4 w-4 shrink-0 rounded border-gray-300 text-blue-700 focus:ring-blue-500" /><span className="min-w-0 flex-1"><span className="block truncate text-xs font-semibold text-gray-800">{service.name}</span><span className="mt-1 flex items-center gap-2 text-[10px] text-gray-500"><span className="font-semibold text-emerald-700">{formatRwf(Number(service.price))}</span><span className="inline-flex items-center gap-1"><Clock size={11} />{service.duration_minutes} min</span></span></span>{selected && <Check size={15} className="shrink-0 text-blue-700" weight="bold" />}</label>; })}</div>}
                {selectedServices.length > 0 && <div className="flex flex-col gap-1 rounded-xl bg-gray-50 px-3.5 py-3 sm:flex-row sm:items-center sm:justify-between"><span className="text-[11px] text-gray-500">Estimated duration: <strong className="text-gray-700">{totalDuration} minutes</strong></span><span className="text-sm font-bold text-gray-900">Total {formatRwf(bookingTotal)}</span></div>}
              </section>

              <section className="space-y-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-2xs sm:p-5">
                <div className="flex items-center gap-2"><span className="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-700"><EnvelopeSimple size={16} /></span><div><h3 className="text-sm font-bold text-gray-900">Payment and notes</h3><p className="text-[11px] text-gray-500">Set the payment option and any appointment notes.</p></div></div>
                <div className="grid gap-3 sm:grid-cols-2"><div><label htmlFor="payment_option" className="mb-1.5 block text-xs font-semibold text-gray-700">Payment option <span className="text-rose-500">*</span></label><select id="payment_option" value={form.payment_option} onChange={(event) => { const paymentOption = event.target.value as "full" | "deposit"; setForm((current) => ({ ...current, payment_option: paymentOption, deposit_amount: paymentOption === "full" ? "0" : current.deposit_amount })); setFormError(null); }} className={fieldClass}><option value="full">Full payment</option><option value="deposit">Deposit</option></select></div>{form.payment_option === "deposit" && <div><label htmlFor="deposit_amount" className="mb-1.5 block text-xs font-semibold text-gray-700">Deposit amount <span className="text-rose-500">*</span></label><input id="deposit_amount" required type="number" min="0" max={bookingTotal || undefined} step="1" value={form.deposit_amount} onChange={(event) => updateForm("deposit_amount", event.target.value)} className={fieldClass} /><p className="mt-1 text-[10px] text-gray-500">Remaining balance: {formatRwf(Math.max(0, bookingTotal - Number(form.deposit_amount || 0)))}</p></div>}</div>
                <div><label htmlFor="booking_notes" className="mb-1.5 block text-xs font-semibold text-gray-700">Notes <span className="font-normal text-gray-400">(optional)</span></label><textarea id="booking_notes" rows={3} maxLength={5000} value={form.notes} onChange={(event) => updateForm("notes", event.target.value)} placeholder="Add any relevant appointment notes…" className={`${fieldClass} resize-y`} /></div>
                <label className="flex cursor-pointer items-start gap-3 rounded-xl border border-blue-200 bg-blue-50 p-3.5"><input type="checkbox" checked={form.send_email} onChange={(event) => updateForm("send_email", event.target.checked)} className="mt-0.5 h-4 w-4 shrink-0 rounded border-blue-300 text-blue-700 focus:ring-blue-500" /><span><span className="block text-xs font-semibold text-blue-900">Send confirmation email to customer</span><span className="mt-0.5 block text-[10px] text-blue-700">The customer receives their appointment details by email.</span></span></label>
              </section>

              <div className="flex flex-col-reverse gap-2 border-t border-gray-200 pt-4 sm:flex-row sm:justify-end"><Link href="/admin/bookings" className="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Cancel</Link><button type="submit" disabled={submitting || optionsLoading || services.length === 0 || providers.length === 0} className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-50">{submitting ? <ArrowsClockwise size={14} className="animate-spin" /> : <Check size={14} weight="bold" />}{submitting ? "Creating booking…" : "Create booking"}</button></div>
            </form>
          </>
        )}
      </div>
    </div>
  );
}
