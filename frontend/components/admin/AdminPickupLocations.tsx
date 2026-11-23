"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import {
  ArrowsClockwise,
  Car,
  Check,
  CurrencyDollar,
  Eye,
  EyeSlash,
  MagnifyingGlass,
  MapPin,
  PencilSimple,
  Plus,
  Trash,
  UsersThree,
  Warning,
  X,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";

interface PickupLocation {
  id: number;
  name: string;
  fee: number;
  is_active: boolean;
  bookings_count: number;
  created_at: string | null;
}

interface LocationForm {
  name: string;
  fee: string;
  is_active: boolean;
}

const emptyForm: LocationForm = { name: "", fee: "50000", is_active: true };
const fieldClass = "w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10";

async function readResponse(response: Response) {
  const result = await response.json().catch(() => ({}));
  if (!response.ok || result.success === false) {
    const validation = result.errors ? Object.values(result.errors).flat().join(" ") : "";
    throw new Error(validation || result.message || "The request could not be completed.");
  }
  return result;
}

function formatFee(value: number): string {
  return `RWF ${Math.round(Number(value) || 0).toLocaleString("en-US")}`;
}

function formatDate(value: string | null): string {
  if (!value) return "Date unavailable";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "Date unavailable";
  return new Intl.DateTimeFormat("en", { dateStyle: "medium" }).format(date);
}

export default function AdminPickupLocations() {
  const [locations, setLocations] = useState<PickupLocation[]>([]);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [search, setSearch] = useState("");
  const [formOpen, setFormOpen] = useState(false);
  const [editingLocation, setEditingLocation] = useState<PickupLocation | null>(null);
  const [form, setForm] = useState<LocationForm>({ ...emptyForm });
  const [formError, setFormError] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);
  const [locationToDelete, setLocationToDelete] = useState<PickupLocation | null>(null);
  const [deleting, setDeleting] = useState(false);
  const [busyLocationId, setBusyLocationId] = useState<number | null>(null);
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(null);

  const fetchLocations = useCallback(async () => {
    setLoading(true);
    setLoadError(null);
    try {
      const response = await fetch("/api/v1/admin/pickup-locations", {
        headers: { Accept: "application/json" },
        cache: "no-store",
      });
      const result = await readResponse(response);
      setLocations(Array.isArray(result.data) ? result.data : []);
    } catch (error) {
      setLoadError(error instanceof Error ? error.message : "Failed to load pickup locations.");
      setLocations([]);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    void fetchLocations();
  }, [fetchLocations]);

  const modalOpen = formOpen || locationToDelete !== null;
  useEffect(() => {
    if (!modalOpen) return;
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => { document.body.style.overflow = previousOverflow; };
  }, [modalOpen]);

  useEffect(() => {
    if (!modalOpen) return;
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key !== "Escape" || saving || deleting) return;
      setFormOpen(false);
      setLocationToDelete(null);
    };
    window.addEventListener("keydown", onKeyDown);
    return () => window.removeEventListener("keydown", onKeyDown);
  }, [modalOpen, saving, deleting]);

  const notify = (message: string, type: "success" | "error" = "success") => {
    setToast({ message, type });
    window.setTimeout(() => setToast(null), 4000);
  };

  const closeForm = () => {
    if (saving) return;
    setFormOpen(false);
    setFormError(null);
  };

  const openCreate = () => {
    setEditingLocation(null);
    setForm({ ...emptyForm });
    setFormError(null);
    setFormOpen(true);
  };

  const openEdit = (location: PickupLocation) => {
    setEditingLocation(location);
    setForm({ name: location.name, fee: String(location.fee), is_active: location.is_active });
    setFormError(null);
    setFormOpen(true);
  };

  const saveLocation = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setSaving(true);
    setFormError(null);
    try {
      const url = editingLocation
        ? `/api/v1/admin/pickup-locations/${editingLocation.id}`
        : "/api/v1/admin/pickup-locations";
      const response = await fetch(url, {
        method: editingLocation ? "PUT" : "POST",
        headers: { Accept: "application/json", "Content-Type": "application/json" },
        body: JSON.stringify({
          name: form.name.trim(),
          fee: Number(form.fee),
          is_active: form.is_active,
        }),
      });
      const result = await readResponse(response);
      setFormOpen(false);
      notify(result.message || (editingLocation ? "Pickup location updated." : "Pickup location added."));
      void fetchLocations();
    } catch (error) {
      setFormError(error instanceof Error ? error.message : "The pickup location could not be saved.");
    } finally {
      setSaving(false);
    }
  };

  const toggleLocation = async (location: PickupLocation) => {
    setBusyLocationId(location.id);
    try {
      const response = await fetch(`/api/v1/admin/pickup-locations/${location.id}/toggle`, {
        method: "POST",
        headers: { Accept: "application/json" },
      });
      const result = await readResponse(response);
      setLocations((current) => current.map((item) => item.id === location.id ? result.data : item));
      notify(result.message || "Pickup route status updated.");
    } catch (error) {
      notify(error instanceof Error ? error.message : "The route status could not be updated.", "error");
    } finally {
      setBusyLocationId(null);
    }
  };

  const deleteLocation = async () => {
    if (!locationToDelete) return;
    setDeleting(true);
    try {
      const response = await fetch(`/api/v1/admin/pickup-locations/${locationToDelete.id}`, {
        method: "DELETE",
        headers: { Accept: "application/json" },
      });
      const result = await readResponse(response);
      setLocations((current) => current.filter((location) => location.id !== locationToDelete.id));
      setLocationToDelete(null);
      notify(result.message || "Pickup location deleted.");
    } catch (error) {
      notify(error instanceof Error ? error.message : "The pickup location could not be deleted.", "error");
    } finally {
      setDeleting(false);
    }
  };

  const visibleLocations = useMemo(() => {
    const query = search.trim().toLowerCase();
    return query ? locations.filter((location) => location.name.toLowerCase().includes(query)) : locations;
  }, [locations, search]);
  const activeCount = locations.filter((location) => location.is_active).length;
  const bookingCount = locations.reduce((total, location) => total + location.bookings_count, 0);

  return (
    <div className="flex min-w-0 flex-1 flex-col bg-gray-50 pb-16">
      <AdminHeader title="Pickup Locations" subtitle="Manage transport routes and fees for bookings" />

      {toast && <div role="status" className={`fixed bottom-5 right-5 z-[100] max-w-[calc(100vw-2.5rem)] rounded-xl px-4 py-3 text-xs font-semibold text-white shadow-xl ${toast.type === "error" ? "bg-rose-700" : "bg-gray-900"}`}>{toast.message}</div>}

      <div className="mx-auto w-full max-w-7xl space-y-4 p-4 sm:p-6">
        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <p className="text-xs leading-5 text-gray-500">Active routes are offered to customers who select pickup during booking.</p>
          <button type="button" onClick={openCreate} className="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-gray-800"><Plus size={15} weight="bold" />Add location</button>
        </div>

        <section className="grid gap-3 sm:grid-cols-3">
          {[
            { label: "Pickup locations", value: locations.length, tone: "bg-blue-50 text-blue-700", icon: <MapPin size={17} /> },
            { label: "Active routes", value: activeCount, tone: "bg-emerald-50 text-emerald-700", icon: <Car size={17} /> },
            { label: "Bookings using routes", value: bookingCount, tone: "bg-violet-50 text-violet-700", icon: <UsersThree size={17} /> },
          ].map((item) => <div key={item.label} className="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-2xs"><div><p className="text-[10px] font-bold uppercase tracking-wide text-gray-500">{item.label}</p><p className="mt-1 text-2xl font-bold tabular-nums text-gray-900">{loading ? "—" : item.value}</p></div><span className={`rounded-lg p-2.5 ${item.tone}`}>{item.icon}</span></div>)}
        </section>

        <section className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xs">
          <div className="flex flex-col gap-3 border-b border-gray-100 bg-gray-50/60 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div><h2 className="text-sm font-bold text-gray-900">Transport routes</h2><p className="mt-0.5 text-[11px] text-gray-500">Manage pickup areas and their customer fees.</p></div>
            <div className="flex items-center gap-2"><div className="relative flex-1 sm:w-56"><MagnifyingGlass size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" /><input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Search locations…" className="w-full rounded-lg border border-gray-200 bg-white py-2 pl-9 pr-3 text-xs outline-none focus:border-gray-400" /></div><button type="button" onClick={() => void fetchLocations()} disabled={loading} aria-label="Refresh locations" className="rounded-lg border border-gray-200 bg-white p-2 text-gray-600 transition hover:bg-gray-50 disabled:opacity-50"><ArrowsClockwise size={15} className={loading ? "animate-spin" : ""} /></button></div>
          </div>

          {loadError ? (
            <div role="alert" className="m-4 flex flex-col items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 p-8 text-center sm:m-6"><Warning size={28} className="text-rose-500" /><div><h3 className="text-sm font-bold text-rose-900">Pickup locations could not be loaded</h3><p className="mt-1 text-xs text-rose-800">{loadError}</p></div><button type="button" onClick={() => void fetchLocations()} className="rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-800 hover:bg-rose-100">Try again</button></div>
          ) : loading ? (
            <div className="space-y-3 p-4">{Array.from({ length: 4 }).map((_, index) => <div key={index} className="h-14 animate-pulse rounded-xl bg-gray-100" />)}</div>
          ) : visibleLocations.length === 0 ? (
            <div className="px-5 py-14 text-center"><span className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-500"><Car size={27} /></span><h3 className="mt-4 text-base font-bold text-gray-900">{search ? "No matching locations" : "No pickup locations yet"}</h3><p className="mx-auto mt-1 max-w-sm text-xs leading-5 text-gray-500">{search ? `No pickup location matches “${search}”.` : "Add a pickup route and fee to offer transport during booking."}</p>{!search && <button type="button" onClick={openCreate} className="mt-4 inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-xs font-semibold text-white hover:bg-gray-800"><Plus size={14} />Add first location</button>}</div>
          ) : (
            <>
              <div className="hidden overflow-x-auto md:block"><table className="w-full min-w-[720px] text-left"><thead className="border-b border-gray-200 bg-gray-50"><tr className="text-[10px] font-bold uppercase tracking-wider text-gray-500"><th className="px-5 py-3">Pickup location</th><th className="px-5 py-3">Transport fee</th><th className="px-5 py-3">Bookings</th><th className="px-5 py-3">Status</th><th className="px-5 py-3">Added</th><th className="px-5 py-3 text-right">Actions</th></tr></thead><tbody className="divide-y divide-gray-100">{visibleLocations.map((location) => <tr key={location.id} className="transition hover:bg-gray-50/70"><td className="px-5 py-4"><div className="flex items-center gap-3"><span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700"><MapPin size={17} /></span><div className="min-w-0"><p className="truncate text-sm font-semibold text-gray-900">{location.name}</p><p className="mt-0.5 text-[10px] text-gray-400">Route #{location.id}</p></div></div></td><td className="px-5 py-4 text-sm font-bold tabular-nums text-gray-900">{formatFee(location.fee)}</td><td className="px-5 py-4 text-xs tabular-nums text-gray-600">{location.bookings_count}</td><td className="px-5 py-4"><span className={`inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-bold ${location.is_active ? "border-emerald-200 bg-emerald-50 text-emerald-700" : "border-gray-200 bg-gray-100 text-gray-600"}`}><span className={`h-1.5 w-1.5 rounded-full ${location.is_active ? "bg-emerald-500" : "bg-gray-400"}`} />{location.is_active ? "Active" : "Inactive"}</span></td><td className="whitespace-nowrap px-5 py-4 text-[11px] text-gray-500">{formatDate(location.created_at)}</td><td className="px-5 py-4"><div className="flex items-center justify-end gap-1"><button type="button" onClick={() => void toggleLocation(location)} disabled={busyLocationId === location.id} title={location.is_active ? "Deactivate route" : "Activate route"} aria-label={location.is_active ? "Deactivate route" : "Activate route"} className={`rounded-lg p-2 transition disabled:opacity-50 ${location.is_active ? "text-emerald-700 hover:bg-emerald-50" : "text-gray-400 hover:bg-gray-100 hover:text-gray-700"}`}>{busyLocationId === location.id ? <ArrowsClockwise size={15} className="animate-spin" /> : location.is_active ? <Eye size={15} /> : <EyeSlash size={15} />}</button><button type="button" onClick={() => openEdit(location)} title="Edit location" aria-label="Edit location" className="rounded-lg p-2 text-gray-500 transition hover:bg-blue-50 hover:text-blue-700"><PencilSimple size={15} /></button><button type="button" onClick={() => setLocationToDelete(location)} title="Delete location" aria-label="Delete location" className="rounded-lg p-2 text-gray-500 transition hover:bg-rose-50 hover:text-rose-700"><Trash size={15} /></button></div></td></tr>)}</tbody></table></div>

              <div className="divide-y divide-gray-100 md:hidden">{visibleLocations.map((location) => <article key={location.id} className="space-y-3 p-4"><div className="flex items-start justify-between gap-3"><div className="flex min-w-0 items-center gap-2.5"><span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700"><MapPin size={17} /></span><div className="min-w-0"><h3 className="truncate text-sm font-bold text-gray-900">{location.name}</h3><p className="mt-0.5 text-[10px] text-gray-400">Route #{location.id}</p></div></div><span className={`shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold ${location.is_active ? "bg-emerald-50 text-emerald-700" : "bg-gray-100 text-gray-600"}`}>{location.is_active ? "Active" : "Inactive"}</span></div><div className="flex items-center justify-between rounded-xl bg-gray-50 px-3 py-2.5"><div><p className="text-[9px] font-bold uppercase tracking-wide text-gray-400">Transport fee</p><p className="mt-0.5 text-sm font-bold tabular-nums text-gray-900">{formatFee(location.fee)}</p></div><div className="text-right"><p className="text-[9px] font-bold uppercase tracking-wide text-gray-400">Bookings</p><p className="mt-0.5 text-sm font-semibold tabular-nums text-gray-700">{location.bookings_count}</p></div></div><div className="flex gap-2"><button type="button" onClick={() => openEdit(location)} className="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700"><PencilSimple size={14} />Edit</button><button type="button" onClick={() => void toggleLocation(location)} disabled={busyLocationId === location.id} className="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 disabled:opacity-50">{busyLocationId === location.id ? <ArrowsClockwise size={14} className="animate-spin" /> : location.is_active ? <EyeSlash size={14} /> : <Eye size={14} />}{location.is_active ? "Deactivate" : "Activate"}</button><button type="button" onClick={() => setLocationToDelete(location)} aria-label={`Delete ${location.name}`} className="inline-flex items-center justify-center rounded-lg bg-rose-50 px-3 py-2 text-rose-700"><Trash size={14} /></button></div></article>)}</div>
              <div className="border-t border-gray-100 bg-gray-50/60 px-4 py-3 text-[11px] text-gray-500 sm:px-5">Showing {visibleLocations.length} of {locations.length} locations</div>
            </>
          )}
        </section>
      </div>

      {formOpen && <div className="fixed inset-0 z-[80] flex items-center justify-center overflow-y-auto bg-black/50 p-3 backdrop-blur-xs animate-in fade-in duration-200 sm:p-5" onMouseDown={(event) => { if (event.target === event.currentTarget) closeForm(); }}><section role="dialog" aria-modal="true" aria-labelledby="pickup-form-title" className="flex max-h-[94dvh] w-full max-w-lg flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl animate-in zoom-in-95 duration-200 sm:max-h-[90dvh]"><header className="flex shrink-0 items-start justify-between gap-3 border-b border-gray-100 bg-gray-50/70 px-4 py-4 sm:px-5"><div><h2 id="pickup-form-title" className="text-sm font-bold text-gray-900">{editingLocation ? "Edit pickup location" : "Add pickup location"}</h2><p className="mt-1 text-[11px] text-gray-500">Set the route name, fee, and availability for bookings.</p></div><button type="button" onClick={closeForm} disabled={saving} aria-label="Close form" className="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-800 disabled:opacity-50"><X size={17} /></button></header><form onSubmit={saveLocation} className="flex min-h-0 flex-1 flex-col overflow-hidden"><div className="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain p-4 sm:p-5">{formError && <div role="alert" className="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2.5 text-xs text-rose-800">{formError}</div>}<label className="block space-y-1.5"><span className="text-xs font-semibold text-gray-700">Location name <span className="text-rose-600">*</span></span><span className="relative block"><MapPin size={15} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" /><input required maxLength={255} autoFocus value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} className={fieldClass + " pl-9"} placeholder="e.g. Kigali City Center" /></span></label><label className="block space-y-1.5"><span className="text-xs font-semibold text-gray-700">Transport fee <span className="text-rose-600">*</span></span><span className="relative block"><CurrencyDollar size={15} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" /><input type="number" required min="0" step="0.01" value={form.fee} onChange={(event) => setForm((current) => ({ ...current, fee: event.target.value }))} className={fieldClass + " pl-9 pr-14 tabular-nums"} /><span className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-semibold text-gray-400">RWF</span></span><span className="block text-[10px] text-gray-400">Enter the transport amount charged for this pickup route.</span></label><label className="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-3"><input type="checkbox" checked={form.is_active} onChange={(event) => setForm((current) => ({ ...current, is_active: event.target.checked }))} className="h-4 w-4 rounded border-gray-300 accent-gray-900" /><span><span className="block text-xs font-semibold text-gray-800">Offer this route during booking</span><span className="mt-0.5 block text-[10px] text-gray-500">Inactive routes are hidden from new bookings.</span></span></label></div><footer className="flex shrink-0 flex-col-reverse gap-2 border-t border-gray-100 bg-gray-50 p-4 sm:flex-row sm:justify-end"><button type="button" onClick={closeForm} disabled={saving} className="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 disabled:opacity-50">Cancel</button><button type="submit" disabled={saving} className="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-gray-800 disabled:opacity-50">{saving ? <ArrowsClockwise size={14} className="animate-spin" /> : <Check size={14} />}{saving ? "Saving…" : editingLocation ? "Save changes" : "Add location"}</button></footer></form></section></div>}

      {locationToDelete && <div className="fixed inset-0 z-[90] flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs" onMouseDown={(event) => { if (event.target === event.currentTarget && !deleting) setLocationToDelete(null); }}><section role="alertdialog" aria-modal="true" aria-labelledby="delete-pickup-title" className="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6"><div className="flex items-start gap-3"><span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-600"><Warning size={20} /></span><div><h2 id="delete-pickup-title" className="text-sm font-bold text-gray-900">Delete pickup location?</h2><p className="mt-1 text-xs leading-relaxed text-gray-600">This removes <strong className="text-gray-900">{locationToDelete.name}</strong> from new bookings.{locationToDelete.bookings_count > 0 && <> {locationToDelete.bookings_count} existing {locationToDelete.bookings_count === 1 ? "booking will" : "bookings will"} keep their saved fee and pickup address, but will no longer reference this route.</>}</p></div></div><div className="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"><button type="button" onClick={() => setLocationToDelete(null)} disabled={deleting} className="rounded-lg border border-gray-200 px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-50">Cancel</button><button type="button" onClick={() => void deleteLocation()} disabled={deleting} className="inline-flex items-center justify-center gap-2 rounded-lg bg-rose-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-rose-700 disabled:opacity-50">{deleting ? <ArrowsClockwise size={14} className="animate-spin" /> : <Trash size={14} />}Delete location</button></div></section></div>}
    </div>
  );
}
