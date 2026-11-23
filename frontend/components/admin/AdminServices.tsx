"use client";

import { useCallback, useEffect, useState } from "react";
import type { FormEvent } from "react";
import Image from "next/image";
import Link from "next/link";
import {
  ArrowsClockwise,
  Check,
  Clock,
  MagnifyingGlass,
  PencilSimple,
  Plus,
  Scissors,
  Trash,
  Warning,
  X,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";

type ServiceStatus = "pending" | "approved" | "rejected";

interface CategoryOption {
  id: number;
  name: string;
}

interface AdminService {
  id: number;
  name: string;
  description: string | null;
  price: number;
  duration_minutes: number;
  category_id: number | null;
  category: CategoryOption | null;
  image_url: string | null;
  tags: string[];
  status: ServiceStatus;
  created_ago: string | null;
}

interface Pagination {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

interface Filters {
  search: string;
  category_id: string;
  min_price: string;
  max_price: string;
  status: string;
}

interface ServiceForm {
  name: string;
  description: string;
  price: string;
  duration_minutes: string;
  category_id: string;
  tags: string;
  image: File | null;
}

interface ConfirmAction {
  service: AdminService;
  action: "delete" | "reject";
}

const EMPTY_FORM: ServiceForm = {
  name: "",
  description: "",
  price: "",
  duration_minutes: "60",
  category_id: "",
  tags: "",
  image: null,
};

const DEFAULT_FILTERS: Filters = {
  search: "",
  category_id: "",
  min_price: "",
  max_price: "",
  status: "all",
};

const inputClass =
  "w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10";

async function responseMessage(response: Response): Promise<string> {
  try {
    const body = await response.json();
    if (body.errors) {
      const validation = Object.values(body.errors).flat().join(" ");
      if (validation) return validation;
    }
    return body.message || "The request could not be completed.";
  } catch {
    return "The request could not be completed.";
  }
}

function formatRwf(value: number): string {
  return new Intl.NumberFormat("en-RW", { maximumFractionDigits: 0 }).format(value);
}

function statusClass(status: ServiceStatus): string {
  if (status === "approved") return "bg-emerald-50 text-emerald-700 ring-emerald-200";
  if (status === "pending") return "bg-amber-50 text-amber-700 ring-amber-200";
  return "bg-rose-50 text-rose-700 ring-rose-200";
}

export default function AdminServices() {
  const [services, setServices] = useState<AdminService[]>([]);
  const [categories, setCategories] = useState<CategoryOption[]>([]);
  const [pagination, setPagination] = useState<Pagination>({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
  });
  const [page, setPage] = useState(1);
  const [draftFilters, setDraftFilters] = useState<Filters>(DEFAULT_FILTERS);
  const [filters, setFilters] = useState<Filters>(DEFAULT_FILTERS);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [busyId, setBusyId] = useState<number | null>(null);
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(
    null
  );

  const [formOpen, setFormOpen] = useState(false);
  const [editingService, setEditingService] = useState<AdminService | null>(null);
  const [form, setForm] = useState<ServiceForm>(EMPTY_FORM);
  const [formError, setFormError] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);
  const [confirmAction, setConfirmAction] = useState<ConfirmAction | null>(null);
  const modalOpen = formOpen || confirmAction !== null;

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
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key !== "Escape") return;
      if (formOpen && !saving) setFormOpen(false);
      else if (confirmAction && busyId === null) setConfirmAction(null);
    };
    window.addEventListener("keydown", handleKeyDown);
    return () => window.removeEventListener("keydown", handleKeyDown);
  }, [modalOpen, formOpen, saving, confirmAction, busyId]);

  const notify = (message: string, type: "success" | "error" = "success") => {
    setToast({ message, type });
    window.setTimeout(() => setToast(null), 4000);
  };

  const fetchServices = useCallback(
    async (targetPage = page) => {
      setLoading(true);
      setLoadError(null);
      try {
        const params = new URLSearchParams();
        Object.entries({ ...filters, page: String(targetPage) }).forEach(([key, value]) => {
          if (value && value !== "all") params.set(key, value);
        });

        const response = await fetch("/api/v1/admin/services?" + params.toString(), {
          headers: { Accept: "application/json" },
        });

        if (response.status === 401) {
          throw new Error("Your session is unavailable. Refresh the page and try again.");
        }
        if (response.status === 403) {
          throw new Error("This page is available to administrator accounts only.");
        }
        if (!response.ok) throw new Error(await responseMessage(response));

        const result = await response.json();
        setServices(Array.isArray(result.data) ? result.data : []);
        setCategories(Array.isArray(result.categories) ? result.categories : []);
        if (result.meta) {
          setPagination(result.meta);
          if (targetPage > result.meta.last_page && result.meta.last_page > 0) {
            setPage(result.meta.last_page);
          }
        }
      } catch (error) {
        const message = error instanceof Error ? error.message : "Failed to load services.";
        setLoadError(message);
        setServices([]);
      } finally {
        setLoading(false);
      }
    },
    [filters, page]
  );

  useEffect(() => {
    void fetchServices(page);
  }, [fetchServices, page]);

  const handleFilterSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setPage(1);
    setFilters({
      ...draftFilters,
      search: draftFilters.search.trim(),
      min_price: draftFilters.min_price.trim(),
      max_price: draftFilters.max_price.trim(),
    });
  };

  const handleClearFilters = () => {
    setDraftFilters(DEFAULT_FILTERS);
    setFilters(DEFAULT_FILTERS);
    setPage(1);
  };

  const openCreate = () => {
    setEditingService(null);
    setForm({
      ...EMPTY_FORM,
      category_id: categories[0] ? String(categories[0].id) : "",
    });
    setFormError(null);
    setFormOpen(true);
  };

  const openEdit = (service: AdminService) => {
    setEditingService(service);
    setForm({
      name: service.name,
      description: service.description || "",
      price: String(service.price),
      duration_minutes: String(service.duration_minutes),
      category_id: service.category_id ? String(service.category_id) : "",
      tags: service.tags.join(", "),
      image: null,
    });
    setFormError(null);
    setFormOpen(true);
  };

  const handleSave = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setSaving(true);
    setFormError(null);

    const payload = new FormData();
    payload.set("name", form.name.trim());
    payload.set("description", form.description.trim());
    payload.set("price", form.price);
    payload.set("duration_minutes", form.duration_minutes);
    payload.set("category_id", form.category_id);
    payload.set("tags", form.tags);
    if (form.image) payload.set("image", form.image);
    if (editingService) payload.set("_method", "PUT");

    try {
      const url = editingService
        ? "/api/v1/admin/services/" + editingService.id
        : "/api/v1/admin/services";
      const response = await fetch(url, {
        method: "POST",
        headers: { Accept: "application/json" },
        body: payload,
      });

      if (!response.ok) throw new Error(await responseMessage(response));
      const result = await response.json();
      setFormOpen(false);
      notify(result.message || "Service saved successfully.");
      if (!editingService) setPage(1);
      await fetchServices(editingService ? page : 1);
    } catch (error) {
      setFormError(error instanceof Error ? error.message : "Failed to save service.");
    } finally {
      setSaving(false);
    }
  };

  const runAction = async (service: AdminService, action: "approve" | "reject" | "delete") => {
    setBusyId(service.id);
    try {
      const url =
        action === "delete"
          ? "/api/v1/admin/services/" + service.id
          : "/api/v1/admin/services/" + service.id + "/" + action;
      const response = await fetch(url, {
        method: action === "delete" ? "DELETE" : "POST",
        headers: { Accept: "application/json" },
      });
      if (!response.ok) throw new Error(await responseMessage(response));
      const result = await response.json();
      setConfirmAction(null);
      notify(result.message || "Service updated.");
      await fetchServices(page);
    } catch (error) {
      notify(error instanceof Error ? error.message : "The service could not be updated.", "error");
    } finally {
      setBusyId(null);
    }
  };

  const shownFrom = pagination.total === 0 ? 0 : (pagination.current_page - 1) * pagination.per_page + 1;
  const shownTo = Math.min(pagination.current_page * pagination.per_page, pagination.total);

  return (
    <div className="flex min-w-0 flex-1 flex-col bg-gray-50 pb-12">
      <AdminHeader
        title="Services"
        subtitle="Manage the menu, pricing, and provider submissions."
      />

      {toast && (
        <div className="fixed bottom-5 right-5 z-[70]">
          <div
            role="status"
            className={
              "flex max-w-sm items-center gap-2.5 rounded-xl border px-4 py-3 text-xs font-semibold shadow-lg " +
              (toast.type === "success"
                ? "border-gray-800 bg-gray-900 text-white"
                : "border-rose-200 bg-rose-50 text-rose-800")
            }
          >
            {toast.type === "success" ? (
              <Check size={16} weight="bold" className="shrink-0 text-emerald-400" />
            ) : (
              <Warning size={16} weight="bold" className="shrink-0 text-rose-600" />
            )}
            <span>{toast.message}</span>
          </div>
        </div>
      )}

      <div className="mx-auto w-full max-w-7xl space-y-4 p-4 sm:p-6">
        <form
          onSubmit={handleFilterSubmit}
          className="space-y-3 rounded-xl border border-gray-200 bg-white p-3.5 shadow-2xs"
        >
          <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
            <div className="relative min-w-0 flex-1 sm:max-w-md">
              <MagnifyingGlass
                size={15}
                className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
              />
              <input
                type="search"
                value={draftFilters.search}
                onChange={(event) =>
                  setDraftFilters((current) => ({ ...current, search: event.target.value }))
                }
                placeholder="Search services..."
                aria-label="Search services"
                className="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-9 pr-3 text-xs text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-gray-900 focus:bg-white focus:ring-1 focus:ring-gray-900"
              />
            </div>
            <div className="flex flex-wrap items-center gap-2 sm:ml-auto">
              <button
                type="button"
                onClick={() => void fetchServices(page)}
                disabled={loading}
                title="Refresh services"
                className="rounded-lg border border-gray-200 bg-white p-2 text-gray-600 shadow-2xs transition hover:bg-gray-50 hover:text-gray-900 disabled:opacity-50"
              >
                <ArrowsClockwise size={15} className={loading ? "animate-spin" : ""} />
              </button>
              <button
                type="button"
                onClick={openCreate}
                className="inline-flex items-center justify-center gap-1.5 rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-gray-800"
              >
                <Plus size={14} weight="bold" />
                Add Service
              </button>
            </div>
          </div>

          <div className="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6">
            <select
              value={draftFilters.category_id}
              onChange={(event) =>
                setDraftFilters((current) => ({ ...current, category_id: event.target.value }))
              }
              aria-label="Filter by category"
              className={inputClass + " col-span-2 sm:col-span-1"}
            >
              <option value="">All Categories</option>
              {categories.map((category) => (
                <option key={category.id} value={category.id}>
                  {category.name}
                </option>
              ))}
            </select>
            <input
              type="number"
              min="0"
              value={draftFilters.min_price}
              onChange={(event) =>
                setDraftFilters((current) => ({ ...current, min_price: event.target.value }))
              }
              placeholder="Min price"
              aria-label="Minimum price"
              className={inputClass}
            />
            <input
              type="number"
              min="0"
              value={draftFilters.max_price}
              onChange={(event) =>
                setDraftFilters((current) => ({ ...current, max_price: event.target.value }))
              }
              placeholder="Max price"
              aria-label="Maximum price"
              className={inputClass}
            />
            <select
              value={draftFilters.status}
              onChange={(event) =>
                setDraftFilters((current) => ({ ...current, status: event.target.value }))
              }
              aria-label="Filter by status"
              className={inputClass}
            >
              <option value="all">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
            </select>
            <button
              type="submit"
              className="rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-gray-800"
            >
              Apply Filters
            </button>
            <button
              type="button"
              onClick={handleClearFilters}
              className="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-600 transition hover:bg-gray-50 hover:text-gray-900"
            >
              Clear
            </button>
          </div>
        </form>

        <section className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-2xs">
          <div className="flex flex-col gap-1 border-b border-gray-200 bg-gray-50/60 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h2 className="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-gray-900">
                <Scissors size={15} className="text-gray-600" />
                Service Directory
              </h2>
              <p className="mt-0.5 text-[11px] text-gray-500">
                {pagination.total} {pagination.total === 1 ? "service" : "services"} in the catalog
              </p>
            </div>
            <span className="text-[11px] font-medium text-gray-400">
              Approve provider submissions from this list.
            </span>
          </div>

          {loadError ? (
            <div className="flex flex-col items-center px-5 py-14 text-center">
              <div className="mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-amber-50 text-amber-700">
                <Warning size={20} />
              </div>
              <p className="max-w-lg text-sm font-semibold text-gray-900">{loadError}</p>
              {loadError.toLowerCase().includes("sign in") && (
                <Link
                  href="/login?redirect=/admin/services"
                  className="mt-4 rounded-lg bg-gray-900 px-4 py-2 text-xs font-semibold text-white hover:bg-gray-800"
                >
                  Sign in
                </Link>
              )}
              <button
                type="button"
                onClick={() => void fetchServices(page)}
                className="mt-3 text-xs font-semibold text-gray-500 hover:text-gray-900"
              >
                Try again
              </button>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full min-w-[820px] border-collapse text-left text-xs">
                <thead>
                  <tr className="border-b border-gray-200 bg-gray-50/80 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                    <th className="px-4 py-3">Service</th>
                    <th className="px-4 py-3">Category</th>
                    <th className="px-4 py-3">Price</th>
                    <th className="px-4 py-3">Status</th>
                    <th className="px-4 py-3">Created</th>
                    <th className="px-4 py-3 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {loading ? (
                    Array.from({ length: 5 }).map((_, index) => (
                      <tr key={"loading-" + index} className="animate-pulse">
                        <td colSpan={6} className="px-4 py-4">
                          <div className="h-8 rounded-md bg-gray-100" />
                        </td>
                      </tr>
                    ))
                  ) : services.length === 0 ? (
                    <tr>
                      <td colSpan={6} className="px-4 py-14 text-center">
                        <Scissors size={30} className="mx-auto mb-2 text-gray-300" />
                        <p className="text-sm font-semibold text-gray-700">No services found</p>
                        <p className="mt-1 text-xs text-gray-400">
                          Try changing the filters or add a service to the menu.
                        </p>
                      </td>
                    </tr>
                  ) : (
                    services.map((service) => (
                      <tr key={service.id} className="transition hover:bg-gray-50/70">
                        <td className="px-4 py-3.5">
                          <div className="flex min-w-0 items-center gap-3">
                            {service.image_url ? (
                              <Image
                                src={service.image_url}
                                alt=""
                                width={40}
                                height={40}
                                unoptimized
                                className="h-10 w-10 shrink-0 rounded-lg border border-gray-200 object-cover"
                              />
                            ) : (
                              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500">
                                <Scissors size={17} />
                              </div>
                            )}
                            <div className="min-w-0">
                              <p className="truncate font-semibold text-gray-900">{service.name}</p>
                              <p className="mt-0.5 max-w-xs truncate text-[11px] text-gray-400">
                                {service.description || "No description"}
                              </p>
                            </div>
                          </div>
                        </td>
                        <td className="px-4 py-3.5 text-gray-600">
                          {service.category?.name || "—"}
                        </td>
                        <td className="whitespace-nowrap px-4 py-3.5 font-semibold tabular-nums text-emerald-700">
                          RWF {formatRwf(service.price)}
                        </td>
                        <td className="px-4 py-3.5">
                          <span
                            className={
                              "inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold capitalize ring-1 ring-inset " +
                              statusClass(service.status)
                            }
                          >
                            {service.status}
                          </span>
                        </td>
                        <td className="whitespace-nowrap px-4 py-3.5 text-gray-500">
                          <span className="inline-flex items-center gap-1">
                            <Clock size={13} className="text-gray-400" />
                            {service.created_ago || "—"}
                          </span>
                        </td>
                        <td className="whitespace-nowrap px-4 py-3.5">
                          <div className="flex items-center justify-end gap-1.5">
                            <button
                              type="button"
                              onClick={() => openEdit(service)}
                              title="Edit service"
                              aria-label={"Edit " + service.name}
                              className="rounded-md border border-gray-200 bg-white p-2 text-gray-600 transition hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900"
                            >
                              <PencilSimple size={14} />
                            </button>
                            {service.status === "pending" && (
                              <>
                                <button
                                  type="button"
                                  onClick={() => void runAction(service, "approve")}
                                  disabled={busyId === service.id}
                                  title="Approve service"
                                  aria-label={"Approve " + service.name}
                                  className="rounded-md border border-emerald-200 bg-emerald-50 p-2 text-emerald-700 transition hover:bg-emerald-100 disabled:opacity-50"
                                >
                                  <Check size={14} weight="bold" />
                                </button>
                                <button
                                  type="button"
                                  onClick={() => setConfirmAction({ service, action: "reject" })}
                                  disabled={busyId === service.id}
                                  title="Decline service"
                                  aria-label={"Decline " + service.name}
                                  className="rounded-md border border-rose-200 bg-rose-50 p-2 text-rose-700 transition hover:bg-rose-100 disabled:opacity-50"
                                >
                                  <X size={14} weight="bold" />
                                </button>
                              </>
                            )}
                            <button
                              type="button"
                              onClick={() => setConfirmAction({ service, action: "delete" })}
                              disabled={busyId === service.id}
                              title="Delete service"
                              aria-label={"Delete " + service.name}
                              className="rounded-md border border-rose-200 bg-rose-50 p-2 text-rose-700 transition hover:bg-rose-100 disabled:opacity-50"
                            >
                              <Trash size={14} />
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          )}

          {!loadError && !loading && pagination.total > 0 && (
            <div className="flex flex-col gap-3 border-t border-gray-200 bg-gray-50/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
              <p className="text-[11px] text-gray-500">
                Showing <span className="font-semibold text-gray-700">{shownFrom}–{shownTo}</span> of{" "}
                <span className="font-semibold text-gray-700">{pagination.total}</span>
              </p>
              <div className="flex items-center gap-2">
                <button
                  type="button"
                  onClick={() => setPage((current) => Math.max(1, current - 1))}
                  disabled={page <= 1}
                  className="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-[11px] font-medium text-gray-600 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
                >
                  Previous
                </button>
                <span className="text-[11px] tabular-nums text-gray-500">
                  Page {pagination.current_page} of {Math.max(1, pagination.last_page)}
                </span>
                <button
                  type="button"
                  onClick={() =>
                    setPage((current) => Math.min(pagination.last_page, current + 1))
                  }
                  disabled={page >= pagination.last_page}
                  className="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-[11px] font-medium text-gray-600 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
                >
                  Next
                </button>
              </div>
            </div>
          )}
        </section>
      </div>

      {formOpen && (
        <div
          className="fixed inset-0 z-[80] flex items-center justify-center overflow-y-auto bg-black/50 p-4 backdrop-blur-xs animate-in fade-in duration-200"
          onMouseDown={(event) => {
            if (event.target === event.currentTarget && !saving) setFormOpen(false);
          }}
        >
          <section
            role="dialog"
            aria-modal="true"
            aria-labelledby="service-form-title"
            className="flex max-h-[90dvh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl animate-in zoom-in-95 duration-200"
          >
            <div className="flex shrink-0 items-start justify-between border-b border-gray-100 bg-gray-50/50 px-4 py-4 sm:px-5">
              <div>
                <h2 id="service-form-title" className="text-base font-bold text-gray-900">
                  {editingService ? "Edit Service" : "Add Service"}
                </h2>
                <p className="mt-1 text-xs text-gray-500">
                  Set the client-facing service details and category.
                </p>
              </div>
              <button
                type="button"
                onClick={() => setFormOpen(false)}
                disabled={saving}
                aria-label="Close form"
                className="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-800 disabled:opacity-50"
              >
                <X size={18} />
              </button>
            </div>

            <form onSubmit={handleSave} className="flex min-h-0 flex-1 flex-col overflow-hidden">
              <div className="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain p-5 text-xs">
              {formError && (
                <div role="alert" className="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2.5 text-xs text-rose-800">
                  {formError}
                </div>
              )}
              <div className="grid gap-4 sm:grid-cols-2">
                <label className="space-y-1.5 sm:col-span-2">
                  <span className="text-xs font-semibold text-gray-700">Service name</span>
                  <input
                    required
                    maxLength={255}
                    value={form.name}
                    onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))}
                    className={inputClass}
                    placeholder="e.g. Signature Russian Manicure"
                  />
                </label>
                <label className="space-y-1.5 sm:col-span-2">
                  <span className="text-xs font-semibold text-gray-700">Description</span>
                  <textarea
                    required
                    rows={3}
                    value={form.description}
                    onChange={(event) => setForm((current) => ({ ...current, description: event.target.value }))}
                    className={inputClass + " resize-y"}
                    placeholder="Describe what is included in the service."
                  />
                </label>
                <label className="space-y-1.5">
                  <span className="text-xs font-semibold text-gray-700">Price (RWF)</span>
                  <input
                    required
                    type="number"
                    min="0"
                    step="1"
                    value={form.price}
                    onChange={(event) => setForm((current) => ({ ...current, price: event.target.value }))}
                    className={inputClass}
                    placeholder="25000"
                  />
                </label>
                <label className="space-y-1.5">
                  <span className="text-xs font-semibold text-gray-700">Duration (minutes)</span>
                  <input
                    required
                    type="number"
                    min="0"
                    step="1"
                    value={form.duration_minutes}
                    onChange={(event) =>
                      setForm((current) => ({ ...current, duration_minutes: event.target.value }))
                    }
                    className={inputClass}
                    placeholder="60"
                  />
                </label>
                <label className="space-y-1.5">
                  <span className="text-xs font-semibold text-gray-700">Category</span>
                  <select
                    required
                    value={form.category_id}
                    onChange={(event) =>
                      setForm((current) => ({ ...current, category_id: event.target.value }))
                    }
                    className={inputClass}
                  >
                    <option value="" disabled>Select a category</option>
                    {categories.map((category) => (
                      <option key={category.id} value={category.id}>
                        {category.name}
                      </option>
                    ))}
                  </select>
                </label>
                <label className="space-y-1.5">
                  <span className="text-xs font-semibold text-gray-700">Tags</span>
                  <input
                    value={form.tags}
                    onChange={(event) => setForm((current) => ({ ...current, tags: event.target.value }))}
                    className={inputClass}
                    placeholder="Russian, manicure, signature"
                  />
                </label>
                <label className="space-y-1.5 sm:col-span-2">
                  <span className="text-xs font-semibold text-gray-700">Service image</span>
                  <input
                    type="file"
                    accept="image/jpeg,image/png,image/webp,image/bmp,image/gif,image/tiff"
                    onChange={(event) =>
                      setForm((current) => ({ ...current, image: event.target.files?.[0] || null }))
                    }
                    className="block w-full rounded-lg border border-gray-200 bg-white text-xs text-gray-600 file:mr-3 file:border-0 file:border-r file:border-gray-200 file:bg-gray-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-gray-700 hover:file:bg-gray-100"
                  />
                  <span className="block text-[11px] text-gray-400">
                    Optional. Choose a JPG, PNG, WebP, GIF, BMP, or TIFF image up to 20 MB.
                  </span>
                </label>
              </div>
              </div>
              <div className="flex shrink-0 items-center justify-end gap-2.5 border-t border-gray-100 bg-gray-50 p-4">
                <button
                  type="button"
                  onClick={() => setFormOpen(false)}
                  disabled={saving}
                  className="rounded-lg border border-gray-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-gray-700 shadow-2xs transition hover:bg-gray-100 disabled:opacity-50"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={saving || categories.length === 0}
                  className="rounded-lg bg-gray-900 px-4 py-1.5 text-xs font-semibold text-white shadow-xs transition hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50"
                >
                  {saving ? "Saving..." : editingService ? "Save Changes" : "Create Service"}
                </button>
              </div>
            </form>
          </section>
        </div>
      )}

      {confirmAction && (
        <div
          className="fixed inset-0 z-[90] flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs animate-in fade-in duration-200"
          onMouseDown={(event) => {
            if (event.target === event.currentTarget && busyId === null) setConfirmAction(null);
          }}
        >
          <section
            role="dialog"
            aria-modal="true"
            aria-labelledby="service-confirm-title"
            className="w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl animate-in zoom-in-95 duration-200 sm:p-6"
          >
            <div className="flex items-start gap-3">
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-600">
                <Warning size={20} />
              </div>
              <div>
                <h2 id="service-confirm-title" className="text-sm font-bold text-gray-900">
                  {confirmAction.action === "delete" ? "Delete service?" : "Decline service?"}
                </h2>
                <p className="mt-1 text-xs leading-relaxed text-gray-600">
                  {confirmAction.action === "delete"
                    ? "This removes " + confirmAction.service.name + " from the service catalog."
                    : "This marks " + confirmAction.service.name + " as rejected and notifies its provider."}
                </p>
              </div>
            </div>
            <div className="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
              <button
                type="button"
                onClick={() => setConfirmAction(null)}
                disabled={busyId !== null}
                className="rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-50 disabled:opacity-50"
              >
                Cancel
              </button>
              <button
                type="button"
                onClick={() =>
                  void runAction(confirmAction.service, confirmAction.action)
                }
                disabled={busyId !== null}
                className="rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-rose-700 disabled:opacity-50"
              >
                {busyId !== null
                  ? "Working..."
                  : confirmAction.action === "delete"
                    ? "Delete Service"
                    : "Decline Service"}
              </button>
            </div>
          </section>
        </div>
      )}
    </div>
  );
}
