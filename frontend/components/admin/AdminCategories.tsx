"use client";

import React, { useState, useEffect, useMemo, useCallback } from "react";
import Link from "next/link";
import {
  Folder,
  FolderPlus,
  Plus,
  PencilSimple,
  Trash,
  Copy,
  Check,
  MagnifyingGlass,
  ArrowsClockwise,
  Scissors,
  Warning,
  X,
  Sparkle,
  ArrowSquareOut,
  FolderOpen,
  Tag,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";
import type {
  AdminCategoryItem,
  AdminCategoriesSummary,
  AdminCategoriesResponse,
} from "@/types";

export default function AdminCategories() {
  const [categories, setCategories] = useState<AdminCategoryItem[]>([]);
  const [summary, setSummary] = useState<AdminCategoriesSummary | null>(null);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState("");
  const [sort, setSort] = useState<"latest" | "services_desc" | "name_asc" | "oldest">("latest");

  // Modal State (Create / Edit)
  const [modalOpen, setModalOpen] = useState(false);
  const [editingCategory, setEditingCategory] = useState<AdminCategoryItem | null>(null);
  const [formName, setFormName] = useState("");
  const [formSlug, setFormSlug] = useState("");
  const [customSlugActive, setCustomSlugActive] = useState(false);
  const [formSaving, setFormSaving] = useState(false);
  const [formError, setFormError] = useState<string | null>(null);

  // Delete Modal State
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [categoryToDelete, setCategoryToDelete] = useState<AdminCategoryItem | null>(null);
  const [deleting, setDeleting] = useState(false);

  // Toast State
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(null);
  const [copiedSlug, setCopiedSlug] = useState<string | null>(null);

  const showToast = (message: string, type: "success" | "error" = "success") => {
    setToast({ message, type });
    setTimeout(() => setToast(null), 4000);
  };

  // Fetch Categories from API
  const fetchCategories = useCallback(async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams();
      if (search.trim()) params.append("search", search.trim());
      if (sort) params.append("sort", sort);

      const res = await fetch(`/api/v1/admin/categories?${params.toString()}`);
      if (!res.ok) throw new Error("Failed to load categories.");

      const data: AdminCategoriesResponse = await res.json();
      setCategories(data.categories || []);
      setSummary(data.summary || null);
    } catch (err: unknown) {
      console.error("Error fetching categories:", err);
      showToast(err instanceof Error ? err.message : "Failed to load categories", "error");
    } finally {
      setLoading(false);
    }
  }, [search, sort]);

  useEffect(() => {
    fetchCategories();
  }, [fetchCategories]);

  // Handle Slug Auto-generation
  const generateSlug = (text: string) => {
    return text
      .toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, "")
      .replace(/[\s_-]+/g, "-")
      .replace(/^-+|-+$/g, "");
  };

  const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const val = e.target.value;
    setFormName(val);
    if (!customSlugActive) {
      setFormSlug(generateSlug(val));
    }
  };

  // Open Create Modal
  const handleOpenCreate = () => {
    setEditingCategory(null);
    setFormName("");
    setFormSlug("");
    setCustomSlugActive(false);
    setFormError(null);
    setModalOpen(true);
  };

  // Open Edit Modal
  const handleOpenEdit = (cat: AdminCategoryItem) => {
    setEditingCategory(cat);
    setFormName(cat.name);
    setFormSlug(cat.slug);
    setCustomSlugActive(true);
    setFormError(null);
    setModalOpen(true);
  };

  // Save (Create or Update) Category
  const handleSaveCategory = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!formName.trim()) {
      setFormError("Category name is required.");
      return;
    }

    setFormSaving(true);
    setFormError(null);

    try {
      const url = editingCategory
        ? `/api/v1/admin/categories/${editingCategory.id}`
        : `/api/v1/admin/categories`;
      const method = editingCategory ? "PUT" : "POST";

      const res = await fetch(url, {
        method,
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
        },
        body: JSON.stringify({
          name: formName.trim(),
          slug: formSlug.trim() || undefined,
        }),
      });

      const result = await res.json();

      if (!res.ok) {
        throw new Error(result.message || "Failed to save category.");
      }

      showToast(result.message || "Category saved successfully!", "success");
      setModalOpen(false);
      fetchCategories();
    } catch (err: unknown) {
      console.error("Save error:", err);
      setFormError(err instanceof Error ? err.message : "Failed to save category.");
    } finally {
      setFormSaving(false);
    }
  };

  // Open Delete Modal
  const handleOpenDelete = (cat: AdminCategoryItem) => {
    setCategoryToDelete(cat);
    setDeleteModalOpen(true);
  };

  // Confirm Delete
  const handleConfirmDelete = async () => {
    if (!categoryToDelete) return;
    setDeleting(true);

    try {
      const res = await fetch(`/api/v1/admin/categories/${categoryToDelete.id}`, {
        method: "DELETE",
        headers: { "Accept": "application/json" },
      });
      const result = await res.json();

      if (!res.ok) {
        throw new Error(result.message || "Failed to delete category.");
      }

      showToast(result.message || "Category deleted successfully.", "success");
      setDeleteModalOpen(false);
      setCategoryToDelete(null);
      fetchCategories();
    } catch (err: unknown) {
      console.error("Delete error:", err);
      showToast(err instanceof Error ? err.message : "Failed to delete category.", "error");
    } finally {
      setDeleting(false);
    }
  };

  // Copy slug helper
  const handleCopySlug = (slug: string) => {
    navigator.clipboard.writeText(slug);
    setCopiedSlug(slug);
    setTimeout(() => setCopiedSlug(null), 2000);
  };

  // Calculate total services across visible
  const totalCatalogServices = useMemo(() => {
    return summary?.total_services_linked || 1;
  }, [summary]);

  return (
    <div className="flex-1 flex flex-col min-w-0 bg-gray-50 pb-16">
      {/* Top Header */}
      <AdminHeader
        title="Treatment Categories"
        subtitle="Organize luxury nail artistry, spa pedicures, and beauty services into client-facing salon collections."
      />

      {/* Toast Alert */}
      {toast && (
        <div className="fixed bottom-6 right-6 z-50 animate-in fade-in slide-in-from-bottom-3 duration-200">
          <div
            className={`flex items-center gap-2.5 px-4 py-3 rounded-xl shadow-lg border text-xs font-semibold ${
              toast.type === "success"
                ? "bg-gray-900 text-white border-gray-800"
                : "bg-red-50 text-red-800 border-red-200"
            }`}
          >
            {toast.type === "success" ? (
              <Check size={16} className="text-emerald-400 shrink-0" weight="bold" />
            ) : (
              <Warning size={16} className="text-red-500 shrink-0" weight="bold" />
            )}
            <span>{toast.message}</span>
          </div>
        </div>
      )}

      <div className="p-4 sm:p-6 max-w-7xl w-full mx-auto space-y-4">
        {/* Top Control Bar */}
        <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-gray-200 shadow-2xs">
          {/* Left search */}
          <div className="relative flex-1 max-w-md">
            <MagnifyingGlass
              size={15}
              className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
            />
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search category name or slug..."
              className="w-full pl-9 pr-8 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded-lg text-gray-900 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition"
            />
            {search && (
              <button
                onClick={() => setSearch("")}
                className="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-0.5"
              >
                <X size={12} />
              </button>
            )}
          </div>

          {/* Right Action buttons */}
          <div className="flex items-center gap-2 self-end sm:self-auto">
            {/* Sort Dropdown */}
            <select
              value={sort}
              onChange={(e) => setSort(e.target.value as typeof sort)}
              className="px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-medium text-gray-700 hover:border-gray-300 focus:outline-none focus:ring-1 focus:ring-gray-900 cursor-pointer shadow-2xs"
            >
              <option value="latest">Latest First</option>
              <option value="services_desc">Most Treatments</option>
              <option value="name_asc">Alphabetical (A-Z)</option>
              <option value="oldest">Oldest First</option>
            </select>

            {/* Refresh */}
            <button
              onClick={() => fetchCategories()}
              disabled={loading}
              title="Refresh collections"
              className="p-1.5 border border-gray-200 bg-white rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition shadow-2xs disabled:opacity-50"
            >
              <ArrowsClockwise size={15} className={loading ? "animate-spin" : ""} />
            </button>

            {/* Add Category Trigger */}
            <button
              onClick={handleOpenCreate}
              className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-semibold hover:bg-gray-800 transition shadow-xs shrink-0"
            >
              <Plus size={13} weight="bold" />
              <span>Add Category</span>
            </button>
          </div>
        </div>

        {/* 4 Summary KPI Cards */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          {/* Card 1: Total Categories */}
          <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs flex flex-col justify-between">
            <div className="flex items-center justify-between text-gray-500 mb-1.5">
              <span className="text-xs font-bold uppercase tracking-wider">Collections</span>
              <div className="w-7 h-7 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center">
                <Folder size={16} weight="bold" />
              </div>
            </div>
            <div>
              <div className="text-xl sm:text-2xl font-bold tracking-tight tabular-nums text-gray-900">
                {summary?.total_categories || categories.length}
              </div>
              <p className="text-[11px] text-gray-400 mt-1">
                Active menu groupings for online booking
              </p>
            </div>
          </div>

          {/* Card 2: Catalog Treatments Linked */}
          <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs flex flex-col justify-between">
            <div className="flex items-center justify-between text-gray-500 mb-1.5">
              <span className="text-xs font-bold uppercase tracking-wider">Assigned Services</span>
              <div className="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                <Scissors size={16} weight="bold" />
              </div>
            </div>
            <div>
              <div className="text-xl sm:text-2xl font-bold tracking-tight tabular-nums text-gray-900">
                {summary?.total_services_linked ?? 0}
              </div>
              <p className="text-[11px] text-gray-400 mt-1">
                Treatments categorized in collections
              </p>
            </div>
          </div>

          {/* Card 3: Top Collection */}
          <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs flex flex-col justify-between">
            <div className="flex items-center justify-between text-gray-500 mb-1.5">
              <span className="text-xs font-bold uppercase tracking-wider">Top Collection</span>
              <div className="w-7 h-7 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
                <Sparkle size={16} weight="bold" />
              </div>
            </div>
            <div>
              <div className="text-base font-bold text-gray-900 truncate flex items-center gap-1.5">
                <span>{summary?.top_category?.name || "None yet"}</span>
              </div>
              <p className="text-[11px] text-gray-500 font-medium tabular-nums mt-1">
                {summary?.top_category?.services_count ?? 0} treatments assigned
              </p>
            </div>
          </div>

          {/* Card 4: Uncategorized Services */}
          <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs flex flex-col justify-between">
            <div className="flex items-center justify-between text-gray-500 mb-1.5">
              <span className="text-xs font-bold uppercase tracking-wider">Unassigned Services</span>
              <div
                className={`w-7 h-7 rounded-lg flex items-center justify-center ${
                  (summary?.uncategorized_services_count ?? 0) > 0
                    ? "bg-amber-100 text-amber-800"
                    : "bg-blue-50 text-blue-700"
                }`}
              >
                <Tag size={16} weight="bold" />
              </div>
            </div>
            <div>
              <div className="text-xl sm:text-2xl font-bold tracking-tight tabular-nums text-gray-900">
                {summary?.uncategorized_services_count ?? 0}
              </div>
              <p className="text-[11px] text-gray-400 mt-1">
                {(summary?.uncategorized_services_count ?? 0) > 0 ? (
                  <Link
                    href="/admin/services"
                    className="text-amber-700 font-semibold hover:underline inline-flex items-center gap-1"
                  >
                    Assign categories to services &rarr;
                  </Link>
                ) : (
                  "All catalog treatments organized"
                )}
              </p>
            </div>
          </div>
        </div>

        {/* Categories Table Card */}
        <div className="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
          {/* Header Bar */}
          <div className="p-3.5 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-gray-50/50">
            <div>
              <h2 className="text-xs font-bold text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                <FolderOpen size={14} className="text-gray-700" />
                Category Directory ({categories.length})
              </h2>
              <p className="text-[11px] text-gray-500 mt-0.5">
                Categories define service tabs on public booking and customer portal.
              </p>
            </div>

            <div className="text-xs text-gray-400 font-medium">
              Click <span className="text-gray-600 font-semibold">View Treatments</span> to audit services in a category.
            </div>
          </div>

          {/* Table Container */}
          <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse text-xs">
              <thead>
                <tr className="bg-gray-50/80 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-600">
                  <th className="px-4 py-3">Category & Slug</th>
                  <th className="px-4 py-3">Treatments Included</th>
                  <th className="px-4 py-3">Catalog Share</th>
                  <th className="px-4 py-3">Date Created</th>
                  <th className="px-4 py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {loading ? (
                  Array.from({ length: 3 }).map((_, i) => (
                    <tr key={i} className="animate-pulse">
                      <td colSpan={5} className="px-4 py-4">
                        <div className="h-4 bg-gray-200 rounded w-full"></div>
                      </td>
                    </tr>
                  ))
                ) : categories.length === 0 ? (
                  <tr>
                    <td colSpan={5} className="px-4 py-12 text-center">
                      <FolderPlus size={36} className="mx-auto text-gray-300 mb-2" />
                      <p className="text-sm font-semibold text-gray-700">No categories found</p>
                      <p className="text-xs text-gray-400 mt-1">
                        {search
                          ? `No categories match "${search}". Try clearing your search filter.`
                          : "Create your first category collection to begin organizing salon services."}
                      </p>
                      {!search && (
                        <button
                          onClick={handleOpenCreate}
                          className="mt-3.5 inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-semibold hover:bg-gray-800 transition"
                        >
                          <Plus size={13} weight="bold" />
                          <span>Add First Category</span>
                        </button>
                      )}
                    </td>
                  </tr>
                ) : (
                  categories.map((cat) => {
                    const sharePercent =
                      totalCatalogServices > 0
                        ? Math.round((cat.services_count / totalCatalogServices) * 100)
                        : 0;

                    return (
                      <tr key={cat.id} className="hover:bg-gray-50/70 transition-colors">
                        {/* 1. Name & Slug */}
                        <td className="px-4 py-3.5 align-middle">
                          <div className="flex items-start gap-2.5">
                            <div className="w-8 h-8 rounded-lg bg-rose-50/70 border border-rose-100 text-rose-800 flex items-center justify-center shrink-0 mt-0.5">
                              <Folder size={16} weight="duotone" />
                            </div>
                            <div className="min-w-0">
                              <div className="font-bold text-gray-900 text-sm tracking-tight flex items-center gap-1.5">
                                <span>{cat.name}</span>
                              </div>
                              <div className="flex items-center gap-1.5 mt-0.5">
                                <span className="text-[11px] font-mono text-gray-400 bg-gray-100 px-1.5 py-0.2 rounded border border-gray-200">
                                  {cat.slug}
                                </span>
                                <button
                                  type="button"
                                  onClick={() => handleCopySlug(cat.slug)}
                                  title="Copy slug link"
                                  className="text-gray-400 hover:text-gray-700 transition p-0.5"
                                >
                                  {copiedSlug === cat.slug ? (
                                    <Check size={11} className="text-emerald-600" />
                                  ) : (
                                    <Copy size={11} />
                                  )}
                                </button>
                              </div>
                            </div>
                          </div>
                        </td>

                        {/* 2. Treatments Included */}
                        <td className="px-4 py-3.5 align-middle">
                          <div>
                            <div className="flex items-center gap-1.5 mb-1">
                              <span className="font-bold text-xs text-gray-900">
                                {cat.services_count}
                              </span>
                              <span className="text-gray-400 text-xs">
                                {cat.services_count === 1 ? "treatment" : "treatments"}
                              </span>
                              {cat.active_services_count > 0 && (
                                <span className="px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-100">
                                  {cat.active_services_count} active
                                </span>
                              )}
                            </div>

                            {/* Sample treatment pills */}
                            <div className="flex flex-wrap items-center gap-1 max-w-xs">
                              {cat.sample_services.length === 0 ? (
                                <span className="text-[11px] text-gray-400 italic">
                                  No treatments yet
                                </span>
                              ) : (
                                cat.sample_services.map((s) => (
                                  <span
                                    key={s.id}
                                    className="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700 border border-gray-200"
                                  >
                                    <span className="truncate max-w-[110px]">{s.name}</span>
                                    <span className="text-gray-400 tabular-nums">
                                      · {s.price.toLocaleString()}
                                    </span>
                                  </span>
                                ))
                              )}
                              {cat.services_count > cat.sample_services.length && (
                                <span className="text-[10px] font-semibold text-gray-400">
                                  +{cat.services_count - cat.sample_services.length} more
                                </span>
                              )}
                            </div>
                          </div>
                        </td>

                        {/* 3. Catalog Share */}
                        <td className="px-4 py-3.5 align-middle">
                          <div className="max-w-[110px] space-y-1">
                            <div className="flex items-center justify-between text-[11px]">
                              <span className="font-bold text-gray-700 tabular-nums">
                                {sharePercent}%
                              </span>
                              <span className="text-gray-400 text-[10px]">of salon menu</span>
                            </div>
                            <div className="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                              <div
                                style={{ width: `${Math.max(4, sharePercent)}%` }}
                                className="h-full bg-gray-900 rounded-full transition-all duration-300"
                              />
                            </div>
                          </div>
                        </td>

                        {/* 4. Created Date */}
                        <td className="px-4 py-3.5 align-middle text-gray-600">
                          <div className="font-medium text-gray-900 text-xs">
                            {cat.created_at || "—"}
                          </div>
                          <div className="text-[11px] text-gray-400 mt-0.5">
                            {cat.created_at_human || ""}
                          </div>
                        </td>

                        {/* 5. Actions */}
                        <td className="px-4 py-3.5 align-middle text-right whitespace-nowrap">
                          <div className="flex items-center justify-end gap-1.5">
                            {/* View Services */}
                            <Link
                              href={`/admin/services?category=${cat.id}`}
                              className="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-2xs"
                              title="View & manage services in this collection"
                            >
                              <Scissors size={12} />
                              <span className="hidden sm:inline">Services</span>
                              <ArrowSquareOut size={10} className="text-gray-400" />
                            </Link>

                            {/* Edit */}
                            <button
                              onClick={() => handleOpenEdit(cat)}
                              className="p-1.5 text-gray-600 bg-white border border-gray-300 rounded-lg hover:text-gray-900 hover:bg-gray-100 transition shadow-2xs"
                              title="Edit Category"
                            >
                              <PencilSimple size={13} />
                            </button>

                            {/* Delete */}
                            <button
                              onClick={() => handleOpenDelete(cat)}
                              className="p-1.5 text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 hover:border-red-300 transition shadow-2xs"
                              title="Delete Category"
                            >
                              <Trash size={13} />
                            </button>
                          </div>
                        </td>
                      </tr>
                    );
                  })
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {/* CREATE / EDIT MODAL */}
      {modalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs animate-in fade-in duration-200">
          <div className="bg-white rounded-2xl max-w-md w-full overflow-hidden shadow-2xl border border-gray-200 animate-in zoom-in-95 duration-200">
            {/* Header */}
            <div className="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 rounded-lg bg-gray-900 text-white flex items-center justify-center">
                  {editingCategory ? <PencilSimple size={15} /> : <FolderPlus size={15} />}
                </div>
                <div>
                  <h3 className="text-sm font-bold text-gray-900">
                    {editingCategory ? "Edit Category Collection" : "Add New Category"}
                  </h3>
                  <p className="text-[11px] text-gray-500">
                    {editingCategory
                      ? "Update collection display name or URL slug."
                      : "Define a new service category for client bookings."}
                  </p>
                </div>
              </div>
              <button
                onClick={() => setModalOpen(false)}
                className="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
              >
                <X size={16} />
              </button>
            </div>

            {/* Form */}
            <form onSubmit={handleSaveCategory}>
              <div className="p-5 space-y-4 text-xs">
                {formError && (
                  <div className="p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-xs flex items-center gap-2">
                    <Warning size={14} className="shrink-0" />
                    <span>{formError}</span>
                  </div>
                )}

                {/* Name */}
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    Category Name <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    required
                    value={formName}
                    onChange={handleNameChange}
                    placeholder="e.g. Russian & Precision, Gel Extensions, Spa Pedicures"
                    className="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900 text-xs"
                    autoFocus
                  />
                  <p className="text-[11px] text-gray-400 mt-1">
                    Displayed prominently in the client booking tab navigation.
                  </p>
                </div>

                {/* Slug */}
                <div>
                  <div className="flex items-center justify-between mb-1">
                    <label className="block text-xs font-bold text-gray-700">
                      URL Slug <span className="text-red-500">*</span>
                    </label>
                    <button
                      type="button"
                      onClick={() => setCustomSlugActive(!customSlugActive)}
                      className="text-[11px] text-gray-500 hover:text-gray-900 font-semibold"
                    >
                      {customSlugActive ? "Auto-generate" : "Customize"}
                    </button>
                  </div>
                  <div className="relative">
                    <input
                      type="text"
                      required
                      value={formSlug}
                      onChange={(e) => {
                        setFormSlug(e.target.value);
                        setCustomSlugActive(true);
                      }}
                      placeholder="e.g. russian-precision"
                      className="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 font-mono text-xs focus:bg-white focus:outline-none focus:ring-1 focus:ring-gray-900"
                    />
                  </div>
                  <div className="text-[10px] text-gray-400 mt-1 flex items-center gap-1 font-mono">
                    <span>Preview:</span>
                    <span className="text-gray-600 truncate">
                      isaiahnailbar.com/booking?category={formSlug || "your-slug"}
                    </span>
                  </div>
                </div>
              </div>

              {/* Footer */}
              <div className="p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5">
                <button
                  type="button"
                  onClick={() => setModalOpen(false)}
                  className="px-3.5 py-1.5 text-xs font-semibold text-gray-700 hover:text-gray-900 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition shadow-2xs"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={formSaving}
                  className="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition shadow-xs disabled:opacity-50"
                >
                  {formSaving ? (
                    <>
                      <ArrowsClockwise size={13} className="animate-spin" />
                      <span>Saving...</span>
                    </>
                  ) : (
                    <>
                      <Check size={13} weight="bold" />
                      <span>{editingCategory ? "Update Category" : "Create Category"}</span>
                    </>
                  )}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* DELETE CONFIRMATION MODAL */}
      {deleteModalOpen && categoryToDelete && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs animate-in fade-in duration-200">
          <div className="bg-white rounded-2xl max-w-md w-full overflow-hidden shadow-2xl border border-gray-200 animate-in zoom-in-95 duration-200">
            <div className="p-5">
              <div className="flex items-start gap-3 mb-4">
                <div className="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                  <Warning size={20} weight="bold" />
                </div>
                <div>
                  <h3 className="text-sm font-bold text-gray-900">
                    Delete &ldquo;{categoryToDelete.name}&rdquo;?
                  </h3>
                  <p className="text-xs text-gray-500 mt-0.5">
                    This action will permanently delete this category collection.
                  </p>
                </div>
              </div>

              {categoryToDelete.services_count > 0 && (
                <div className="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-xs space-y-1">
                  <div className="font-bold flex items-center gap-1 text-amber-800">
                    <Warning size={14} />
                    <span>Attached Treatments Notice:</span>
                  </div>
                  <p className="text-[11px] text-amber-700">
                    This collection currently has{" "}
                    <strong>{categoryToDelete.services_count} treatments</strong> assigned to it.
                    Deleting it will safely detach these treatments (they will remain in your catalog as unassigned) without deleting the services themselves.
                  </p>
                </div>
              )}

              <p className="text-xs text-gray-600">
                Are you sure you want to proceed?
              </p>
            </div>

            <div className="p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5">
              <button
                type="button"
                onClick={() => setDeleteModalOpen(false)}
                disabled={deleting}
                className="px-3.5 py-1.5 text-xs font-semibold text-gray-700 hover:text-gray-900 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition shadow-2xs"
              >
                Keep Category
              </button>
              <button
                type="button"
                onClick={handleConfirmDelete}
                disabled={deleting}
                className="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition shadow-xs disabled:opacity-50"
              >
                {deleting ? (
                  <>
                    <ArrowsClockwise size={13} className="animate-spin" />
                    <span>Deleting...</span>
                  </>
                ) : (
                  <>
                    <Trash size={13} weight="bold" />
                    <span>Delete Category</span>
                  </>
                )}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
