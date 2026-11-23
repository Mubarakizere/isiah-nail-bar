"use client";

import { useCallback, useEffect, useState } from "react";
import type { FormEvent } from "react";
import Image from "next/image";
import Link from "next/link";
import {
  ArrowsClockwise,
  CalendarBlank,
  ChartLineUp,
  Check,
  Clock,
  EnvelopeSimple,
  MagnifyingGlass,
  PencilSimple,
  Phone,
  Plus,
  Scissors,
  Trash,
  UsersThree,
  Warning,
  X,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";
import ProviderWorkingHoursModal from "@/components/admin/ProviderWorkingHoursModal";

interface ProviderServiceOption {
  id: number;
  name: string;
  price: number;
}

interface ProviderItem {
  id: number;
  name: string;
  email: string | null;
  phone: string | null;
  bio: string | null;
  photo_url: string | null;
  active: boolean;
  services: { id: number; name: string }[];
  services_count: number;
  bookings_count: number;
  created_at: string | null;
}

interface ProviderPageData {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

interface ProviderFormState {
  name: string;
  email: string;
  phone: string;
  bio: string;
  serviceIds: number[];
  photo: File | null;
}

interface ProviderConfirmAction {
  provider: ProviderItem;
  action: "delete" | "decline";
}

const EMPTY_FORM: ProviderFormState = {
  name: "",
  email: "",
  phone: "",
  bio: "",
  serviceIds: [],
  photo: null,
};

const inputClass =
  "w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10";

async function readResponse(response: Response) {
  const result = await response.json().catch(() => ({}));
  if (!response.ok) {
    const validation = result.errors ? Object.values(result.errors).flat().join(" ") : "";
    throw new Error(validation || result.message || "The request could not be completed.");
  }
  return result;
}

function initials(name: string): string {
  return name
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part.charAt(0).toUpperCase())
    .join("");
}

function formatDate(value: string | null): string {
  if (!value) return "—";
  const date = new Date(value);
  return Number.isNaN(date.getTime())
    ? "—"
    : new Intl.DateTimeFormat("en", { month: "short", day: "numeric", year: "numeric" }).format(date);
}

export default function AdminProviders() {
  const [providers, setProviders] = useState<ProviderItem[]>([]);
  const [services, setServices] = useState<ProviderServiceOption[]>([]);
  const [stats, setStats] = useState({ total: 0, active: 0, pending: 0 });
  const [pagination, setPagination] = useState<ProviderPageData>({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
  });
  const [page, setPage] = useState(1);
  const [draftSearch, setDraftSearch] = useState("");
  const [search, setSearch] = useState("");
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(null);
  const [formOpen, setFormOpen] = useState(false);
  const [editingProvider, setEditingProvider] = useState<ProviderItem | null>(null);
  const [form, setForm] = useState<ProviderFormState>(EMPTY_FORM);
  const [formError, setFormError] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);
  const [busyId, setBusyId] = useState<number | null>(null);
  const [confirmAction, setConfirmAction] = useState<ProviderConfirmAction | null>(null);
  const [workingHoursProvider, setWorkingHoursProvider] = useState<ProviderItem | null>(null);

  const modalOpen = formOpen || confirmAction !== null || workingHoursProvider !== null;

  useEffect(() => {
    if (!modalOpen) return;
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => {
      document.body.style.overflow = previousOverflow;
    };
  }, [modalOpen]);

  useEffect(() => {
    if (!modalOpen) return;
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key !== "Escape") return;
      if (formOpen && !saving) setFormOpen(false);
      else if (confirmAction && busyId === null) setConfirmAction(null);
      else if (workingHoursProvider) setWorkingHoursProvider(null);
    };
    window.addEventListener("keydown", onKeyDown);
    return () => window.removeEventListener("keydown", onKeyDown);
  }, [modalOpen, formOpen, saving, confirmAction, busyId, workingHoursProvider]);

  const notify = (message: string, type: "success" | "error" = "success") => {
    setToast({ message, type });
    window.setTimeout(() => setToast(null), 4000);
  };

  const loadProviders = useCallback(
    async (targetPage: number, targetSearch = search) => {
      setLoading(true);
      setLoadError(null);
      try {
        const params = new URLSearchParams({ page: String(targetPage) });
        if (targetSearch.trim()) params.set("search", targetSearch.trim());

        const response = await fetch("/api/v1/admin/providers?" + params.toString(), {
          headers: { Accept: "application/json" },
          cache: "no-store",
        });
        const result = await readResponse(response);
        setProviders(Array.isArray(result.data) ? result.data : []);
        setServices(Array.isArray(result.services) ? result.services : []);
        setStats(result.stats || { total: 0, active: 0, pending: 0 });
        if (result.meta) {
          setPagination(result.meta);
          if (targetPage > result.meta.last_page && result.meta.last_page > 0) {
            setPage(result.meta.last_page);
          }
        }
      } catch (error) {
        setLoadError(error instanceof Error ? error.message : "Failed to load providers.");
        setProviders([]);
      } finally {
        setLoading(false);
      }
    },
    [search]
  );

  useEffect(() => {
    void loadProviders(page);
  }, [loadProviders, page]);

  const openCreate = () => {
    setEditingProvider(null);
    setForm(EMPTY_FORM);
    setFormError(null);
    setFormOpen(true);
  };

  const openEdit = (provider: ProviderItem) => {
    setEditingProvider(provider);
    setForm({
      name: provider.name,
      email: provider.email || "",
      phone: provider.phone || "",
      bio: provider.bio || "",
      serviceIds: provider.services.map((service) => service.id),
      photo: null,
    });
    setFormError(null);
    setFormOpen(true);
  };

  const handleSearch = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setPage(1);
    setSearch(draftSearch.trim());
  };

  const handleSave = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setSaving(true);
    setFormError(null);

    const payload = new FormData();
    payload.set("name", form.name.trim());
    payload.set("email", form.email.trim());
    payload.set("phone", form.phone.trim());
    payload.set("bio", form.bio.trim());
    form.serviceIds.forEach((serviceId) => payload.append("services[]", String(serviceId)));
    if (form.photo) payload.set("photo", form.photo);
    if (editingProvider) payload.set("_method", "PUT");

    try {
      const response = await fetch(
        editingProvider
          ? `/api/v1/admin/providers/${editingProvider.id}`
          : "/api/v1/admin/providers",
        {
          method: "POST",
          headers: { Accept: "application/json" },
          body: payload,
        }
      );
      const result = await readResponse(response);
      setFormOpen(false);
      notify(result.message || "Provider saved successfully.");
      await loadProviders(page);
    } catch (error) {
      setFormError(error instanceof Error ? error.message : "Failed to save provider.");
    } finally {
      setSaving(false);
    }
  };

  const performAction = async (provider: ProviderItem, action: "approve" | "decline" | "delete") => {
    setBusyId(provider.id);
    try {
      const response = await fetch(
        action === "delete"
          ? `/api/v1/admin/providers/${provider.id}`
          : `/api/v1/admin/providers/${provider.id}/${action}`,
        {
          method: action === "delete" ? "DELETE" : "POST",
          headers: { Accept: "application/json" },
        }
      );
      const result = await readResponse(response);
      setConfirmAction(null);
      notify(result.message || "Provider updated successfully.");
      await loadProviders(page);
    } catch (error) {
      notify(error instanceof Error ? error.message : "The action could not be completed.", "error");
    } finally {
      setBusyId(null);
    }
  };

  return (
    <div className="flex min-w-0 flex-1 flex-col bg-gray-50 pb-16">
      <AdminHeader title="Providers" subtitle="Manage provider profiles; online booking shows active providers." />

      <div className="mx-auto w-full max-w-7xl space-y-5 p-4 sm:p-6">
        <section className="grid gap-3 sm:grid-cols-3">
          {[
            { label: "All Providers", value: stats.total, tone: "bg-gray-100 text-gray-700" },
            { label: "Active", value: stats.active, tone: "bg-emerald-50 text-emerald-700" },
            { label: "Pending", value: stats.pending, tone: "bg-amber-50 text-amber-700" },
          ].map((item) => (
            <div key={item.label} className="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-2xs">
              <div>
                <p className="text-[11px] font-bold uppercase tracking-wide text-gray-500">{item.label}</p>
                <p className="mt-1 text-2xl font-bold tabular-nums text-gray-900">{item.value}</p>
              </div>
              <span className={`rounded-lg p-2.5 ${item.tone}`}><UsersThree size={18} weight="duotone" /></span>
            </div>
          ))}
        </section>

        <section className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xs">
          <div className="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div>
              <h2 className="text-sm font-bold text-gray-900">Provider directory</h2>
              <p className="mt-1 text-xs text-gray-500">Contact details, service assignments, and provider status.</p>
            </div>
            <div className="flex flex-col gap-2 sm:flex-row">
              <form onSubmit={handleSearch} className="flex min-w-0 gap-2 sm:w-72">
                <label className="relative min-w-0 flex-1">
                  <MagnifyingGlass size={15} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                  <input
                    value={draftSearch}
                    onChange={(event) => setDraftSearch(event.target.value)}
                    className={inputClass + " pl-9"}
                    placeholder="Search providers..."
                  />
                </label>
                <button type="submit" className="rounded-lg border border-gray-200 bg-white px-3 text-xs font-semibold text-gray-600 transition hover:bg-gray-50">
                  Search
                </button>
              </form>
              <button type="button" onClick={openCreate} className="inline-flex items-center justify-center gap-1.5 rounded-lg bg-gray-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-gray-800">
                <Plus size={14} weight="bold" /> Add Provider
              </button>
            </div>
          </div>

          {loadError && (
            <div role="alert" className="m-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2.5 text-xs text-rose-800">
              {loadError}
            </div>
          )}

          <div className="overflow-x-auto">
            <table className="w-full min-w-[800px] text-left">
              <thead>
                <tr className="border-b border-gray-100 bg-gray-50/70 text-[10px] font-bold uppercase tracking-wide text-gray-500">
                  <th className="px-4 py-3">Provider</th>
                  <th className="px-4 py-3">Contact</th>
                  <th className="px-4 py-3">Services</th>
                  <th className="px-4 py-3">Status</th>
                  <th className="px-4 py-3">Added</th>
                  <th className="px-4 py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {loading ? (
                  Array.from({ length: 5 }).map((_, index) => (
                    <tr key={index} className="animate-pulse">
                      <td colSpan={6} className="px-4 py-4"><div className="h-9 rounded-lg bg-gray-100" /></td>
                    </tr>
                  ))
                ) : providers.length === 0 ? (
                  <tr>
                    <td colSpan={6} className="px-4 py-14 text-center">
                      <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-500"><UsersThree size={22} /></div>
                      <h3 className="mt-3 text-sm font-bold text-gray-900">{search ? "No providers found" : "No providers yet"}</h3>
                      <p className="mt-1 text-xs text-gray-500">{search ? "Try a different search term." : "Add your first provider to start managing your team."}</p>
                      {!search && <button type="button" onClick={openCreate} className="mt-3 rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white">Add Provider</button>}
                    </td>
                  </tr>
                ) : (
                  providers.map((provider) => (
                    <tr key={provider.id} className="group hover:bg-gray-50/70">
                      <td className="px-4 py-3.5">
                        <div className="flex items-center gap-3">
                          <div className="relative h-10 w-10 shrink-0">
                            {provider.photo_url ? (
                              <Image src={provider.photo_url} alt={provider.name} fill sizes="40px" className="rounded-xl border border-gray-100 object-cover" />
                            ) : (
                              <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-gray-800 to-gray-600 text-sm font-bold text-white">{initials(provider.name) || "P"}</div>
                            )}
                            <span className={`absolute -bottom-1 -right-1 h-3 w-3 rounded-full border-2 border-white ${provider.active ? "bg-emerald-500" : "bg-amber-500"}`} />
                          </div>
                          <div className="min-w-0">
                            <p className="truncate text-xs font-bold text-gray-900">{provider.name}</p>
                            <p className="mt-0.5 text-[10px] text-gray-400">ID #{provider.id}</p>
                          </div>
                        </div>
                      </td>
                      <td className="px-4 py-3.5">
                        <div className="space-y-1 text-[11px] text-gray-600">
                          {provider.email ? <p className="flex items-center gap-1.5"><EnvelopeSimple size={12} className="shrink-0 text-gray-400" /><span className="max-w-[190px] truncate">{provider.email}</span></p> : <p className="text-gray-400">No email provided</p>}
                          {provider.phone ? <p className="flex items-center gap-1.5"><Phone size={12} className="shrink-0 text-gray-400" />{provider.phone}</p> : null}
                        </div>
                      </td>
                      <td className="px-4 py-3.5">
                        <div className="flex max-w-[220px] flex-wrap gap-1">
                          {provider.services.length ? provider.services.slice(0, 3).map((service) => (
                            <span key={service.id} className="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-gray-50 px-1.5 py-1 text-[10px] text-gray-600"><Scissors size={10} />{service.name}</span>
                          )) : <span className="text-[11px] text-gray-400">No assigned services</span>}
                          {provider.services_count > 3 && <span className="rounded-md bg-gray-100 px-1.5 py-1 text-[10px] text-gray-500">+{provider.services_count - 3}</span>}
                        </div>
                      </td>
                      <td className="px-4 py-3.5">
                        <span className={`inline-flex items-center gap-1 rounded-full border px-2 py-1 text-[10px] font-bold ${provider.active ? "border-emerald-200 bg-emerald-50 text-emerald-700" : "border-amber-200 bg-amber-50 text-amber-700"}`}>
                          <span className={`h-1.5 w-1.5 rounded-full ${provider.active ? "bg-emerald-500" : "bg-amber-500"}`} />{provider.active ? "Active" : "Pending"}
                        </span>
                      </td>
                      <td className="px-4 py-3.5 text-[11px] text-gray-500">{formatDate(provider.created_at)}</td>
                      <td className="px-4 py-3.5">
                        <div className="flex items-center justify-end gap-1.5">
                          <button type="button" title="Edit details" onClick={() => openEdit(provider)} className="rounded-lg bg-gray-50 p-2 text-gray-500 transition hover:bg-indigo-50 hover:text-indigo-700"><PencilSimple size={15} /></button>
                          <button type="button" title="Manage working hours" aria-label={`Manage working hours for ${provider.name}`} onClick={() => setWorkingHoursProvider(provider)} className="rounded-lg bg-gray-50 p-2 text-gray-500 transition hover:bg-blue-50 hover:text-blue-700"><Clock size={15} /></button>
                          <Link title="Open provider calendar" aria-label={`Open calendar for ${provider.name}`} href={`/admin/calendar?provider_id=${provider.id}`} className="rounded-lg bg-gray-50 p-2 text-gray-500 transition hover:bg-blue-50 hover:text-blue-700"><CalendarBlank size={15} /></Link>
                          <Link title="View provider performance" href={`/admin/performance?provider_id=${provider.id}`} className="rounded-lg bg-gray-50 p-2 text-gray-500 transition hover:bg-purple-50 hover:text-purple-700"><ChartLineUp size={15} /></Link>
                          {provider.active ? (
                            <button type="button" title="Decline provider" onClick={() => setConfirmAction({ provider, action: "decline" })} disabled={busyId === provider.id} className="rounded-lg bg-gray-50 p-2 text-gray-500 transition hover:bg-amber-50 hover:text-amber-700 disabled:opacity-50"><X size={15} /></button>
                          ) : (
                            <button type="button" title="Approve provider" onClick={() => void performAction(provider, "approve")} disabled={busyId === provider.id} className="rounded-lg bg-gray-50 p-2 text-gray-500 transition hover:bg-emerald-50 hover:text-emerald-700 disabled:opacity-50"><Check size={15} /></button>
                          )}
                          <button type="button" title="Delete provider" onClick={() => setConfirmAction({ provider, action: "delete" })} disabled={busyId === provider.id} className="rounded-lg bg-gray-50 p-2 text-gray-500 transition hover:bg-rose-50 hover:text-rose-700 disabled:opacity-50"><Trash size={15} /></button>
                        </div>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>

          {!loading && providers.length > 0 && (
            <div className="flex flex-col gap-3 border-t border-gray-100 bg-gray-50/40 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
              <p className="text-[11px] text-gray-500">Showing {providers.length} of {pagination.total} providers</p>
              <div className="flex items-center justify-between gap-3 sm:justify-end">
                <button type="button" onClick={() => setPage((current) => Math.max(1, current - 1))} disabled={page <= 1} className="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-[11px] font-medium text-gray-600 hover:bg-gray-50 disabled:opacity-40">Previous</button>
                <span className="text-[11px] tabular-nums text-gray-500">Page {pagination.current_page} of {Math.max(1, pagination.last_page)}</span>
                <button type="button" onClick={() => setPage((current) => Math.min(pagination.last_page, current + 1))} disabled={page >= pagination.last_page} className="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-[11px] font-medium text-gray-600 hover:bg-gray-50 disabled:opacity-40">Next</button>
              </div>
            </div>
          )}
        </section>
      </div>

      {formOpen && (
        <div className="fixed inset-0 z-[80] flex items-center justify-center overflow-y-auto bg-black/50 p-4 backdrop-blur-xs animate-in fade-in duration-200" onMouseDown={(event) => { if (event.target === event.currentTarget && !saving) setFormOpen(false); }}>
          <section role="dialog" aria-modal="true" aria-labelledby="provider-form-title" className="flex max-h-[90dvh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl animate-in zoom-in-95 duration-200">
            <div className="flex shrink-0 items-start justify-between border-b border-gray-100 bg-gray-50/50 px-4 py-4 sm:px-5">
              <div>
                <h2 id="provider-form-title" className="text-sm font-bold text-gray-900">{editingProvider ? "Edit Provider" : "Add New Provider"}</h2>
                <p className="mt-1 text-[11px] text-gray-500">{editingProvider ? "Update contact details and service assignments." : "Add a service professional to your salon team."}</p>
              </div>
              <button type="button" onClick={() => setFormOpen(false)} disabled={saving} aria-label="Close form" className="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-800 disabled:opacity-50"><X size={17} /></button>
            </div>

            <form onSubmit={handleSave} className="flex min-h-0 flex-1 flex-col overflow-hidden">
              <div className="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain p-5 text-xs">
                {formError && <div role="alert" className="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2.5 text-xs text-rose-800">{formError}</div>}
                <div className="grid gap-4 sm:grid-cols-2">
                  <label className="space-y-1.5">
                    <span className="text-xs font-semibold text-gray-700">Full name</span>
                    <input required maxLength={255} value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} className={inputClass} placeholder="e.g. Jane Doe" />
                  </label>
                  <label className="space-y-1.5">
                    <span className="text-xs font-semibold text-gray-700">Phone <span className="font-normal text-gray-400">(optional)</span></span>
                    <input value={form.phone} onChange={(event) => setForm((current) => ({ ...current, phone: event.target.value }))} className={inputClass} placeholder="+250 7xx xxx xxx" />
                  </label>
                  <label className="space-y-1.5 sm:col-span-2">
                    <span className="text-xs font-semibold text-gray-700">Email <span className="font-normal text-gray-400">(optional)</span></span>
                    <input type="email" value={form.email} onChange={(event) => setForm((current) => ({ ...current, email: event.target.value }))} className={inputClass} placeholder="provider@isaiahnailbar.com" />
                    <span className="block text-[11px] text-gray-400">If provided, Laravel creates the provider login and emails credentials.</span>
                  </label>
                  <label className="space-y-1.5 sm:col-span-2">
                    <span className="text-xs font-semibold text-gray-700">Bio</span>
                    <textarea rows={3} value={form.bio} onChange={(event) => setForm((current) => ({ ...current, bio: event.target.value }))} className={inputClass + " resize-y"} placeholder="Brief introduction about the provider..." />
                  </label>
                  <label className="space-y-1.5 sm:col-span-2">
                    <span className="text-xs font-semibold text-gray-700">Profile photo</span>
                    {editingProvider?.photo_url && !form.photo && <span className="flex items-center gap-2 text-[11px] text-gray-500"><Image src={editingProvider.photo_url} alt="Current provider photo" width={36} height={36} className="h-9 w-9 rounded-lg object-cover" />Current photo</span>}
                    <input type="file" accept="image/*" onChange={(event) => setForm((current) => ({ ...current, photo: event.target.files?.[0] || null }))} className="block w-full rounded-lg border border-gray-200 bg-white text-xs text-gray-600 file:mr-3 file:border-0 file:border-r file:border-gray-200 file:bg-gray-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-gray-700 hover:file:bg-gray-100" />
                    <span className="block text-[11px] text-gray-400">PNG, JPG, GIF, or another image format up to 30 MB.</span>
                  </label>
                </div>

                <div className="border-t border-gray-100 pt-4">
                  <div className="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <div>
                      <h3 className="text-xs font-bold text-gray-900">Assign services</h3>
                      <p className="mt-0.5 text-[11px] text-gray-500">Select the services this provider can perform.</p>
                    </div>
                    {services.length > 0 && <div className="flex gap-2"><button type="button" onClick={() => setForm((current) => ({ ...current, serviceIds: services.map((service) => service.id) }))} className="text-[11px] font-semibold text-gray-500 hover:text-gray-900">Select all</button><button type="button" onClick={() => setForm((current) => ({ ...current, serviceIds: [] }))} className="text-[11px] font-semibold text-gray-500 hover:text-gray-900">Clear</button></div>}
                  </div>
                  {services.length === 0 ? <p className="rounded-lg bg-gray-50 px-3 py-3 text-[11px] text-gray-500">No services are available to assign yet.</p> : (
                    <div className="grid gap-2 sm:grid-cols-2">
                      {services.map((service) => {
                        const selected = form.serviceIds.includes(service.id);
                        return <label key={service.id} className={`flex cursor-pointer items-center gap-2 rounded-lg border p-3 transition ${selected ? "border-gray-900 bg-gray-50" : "border-gray-200 hover:bg-gray-50"}`}>
                          <input type="checkbox" checked={selected} onChange={(event) => setForm((current) => ({ ...current, serviceIds: event.target.checked ? [...current.serviceIds, service.id] : current.serviceIds.filter((id) => id !== service.id) }))} className="h-4 w-4 rounded border-gray-300 accent-gray-900" />
                          <span className="min-w-0 flex-1 truncate text-xs font-medium text-gray-800">{service.name}</span>
                        </label>;
                      })}
                    </div>
                  )}
                </div>
              </div>

              <div className="flex shrink-0 flex-col-reverse gap-2 border-t border-gray-100 bg-gray-50 p-4 sm:flex-row sm:justify-end">
                <button type="button" onClick={() => setFormOpen(false)} disabled={saving} className="rounded-lg border border-gray-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-100 disabled:opacity-50">Cancel</button>
                <button type="submit" disabled={saving} className="inline-flex items-center justify-center gap-1.5 rounded-lg bg-gray-900 px-4 py-1.5 text-xs font-semibold text-white transition hover:bg-gray-800 disabled:opacity-50">{saving && <ArrowsClockwise size={13} className="animate-spin" />}{saving ? "Saving..." : editingProvider ? "Save Changes" : "Create Provider"}</button>
              </div>
            </form>
          </section>
        </div>
      )}

      {confirmAction && (
        <div className="fixed inset-0 z-[90] flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs animate-in fade-in duration-200" onMouseDown={(event) => { if (event.target === event.currentTarget && busyId === null) setConfirmAction(null); }}>
          <section role="dialog" aria-modal="true" aria-labelledby="provider-confirm-title" className="w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl animate-in zoom-in-95 duration-200 sm:p-6">
            <div className="flex items-start gap-3">
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-600"><Warning size={20} /></div>
              <div>
                <h2 id="provider-confirm-title" className="text-sm font-bold text-gray-900">{confirmAction.action === "delete" ? "Delete provider?" : "Decline provider?"}</h2>
                <p className="mt-1 text-xs leading-relaxed text-gray-600">{confirmAction.action === "delete" ? `This removes ${confirmAction.provider.name} from the provider directory.` : `This marks ${confirmAction.provider.name} as inactive.`}</p>
              </div>
            </div>
            <div className="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
              <button type="button" onClick={() => setConfirmAction(null)} disabled={busyId !== null} className="rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-50">Cancel</button>
              <button type="button" onClick={() => void performAction(confirmAction.provider, confirmAction.action)} disabled={busyId !== null} className="rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-700 disabled:opacity-50">{busyId !== null ? "Working..." : confirmAction.action === "delete" ? "Delete Provider" : "Decline Provider"}</button>
            </div>
          </section>
        </div>
      )}

      {workingHoursProvider && (
        <ProviderWorkingHoursModal
          provider={workingHoursProvider}
          onClose={() => setWorkingHoursProvider(null)}
          onSaved={() => notify(workingHoursProvider.services_count === 0
            ? "Hours saved. Assign services before offering this provider in booking."
            : "Working hours saved. Booking availability is updated.")}
        />
      )}

      {toast && <div role="status" className={`fixed bottom-5 right-5 z-[100] max-w-[calc(100vw-2.5rem)] rounded-xl px-4 py-3 text-xs font-semibold text-white shadow-xl ${toast.type === "error" ? "bg-rose-700" : "bg-gray-900"}`}>{toast.message}</div>}
    </div>
  );
}
