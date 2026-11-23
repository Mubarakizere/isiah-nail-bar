"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import Image from "next/image";
import {
  ArrowsClockwise,
  Check,
  Eye,
  EyeSlash,
  ImageSquare,
  PencilSimple,
  Plus,
  Slideshow,
  Trash,
  Warning,
  X,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";

interface HeroSlide {
  id: number;
  title: string;
  subtitle: string | null;
  description: string | null;
  image: string;
  image_url: string;
  button_text: string | null;
  button_url: string | null;
  secondary_button_text: string | null;
  secondary_button_url: string | null;
  is_active: boolean;
  sort_order: number;
  created_at: string | null;
}

interface SlideForm {
  title: string;
  subtitle: string;
  description: string;
  button_text: string;
  button_url: string;
  secondary_button_text: string;
  secondary_button_url: string;
  sort_order: string;
  is_active: boolean;
}

const emptyForm: SlideForm = {
  title: "",
  subtitle: "",
  description: "",
  button_text: "Book Appointment",
  button_url: "/booking",
  secondary_button_text: "",
  secondary_button_url: "",
  sort_order: "0",
  is_active: true,
};

const fieldClass = "w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10";

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

function freshForm(slide?: HeroSlide): SlideForm {
  if (!slide) return { ...emptyForm };
  return {
    title: slide.title,
    subtitle: slide.subtitle || "",
    description: slide.description || "",
    button_text: slide.button_text || "Book Appointment",
    button_url: slide.button_url || "/booking",
    secondary_button_text: slide.secondary_button_text || "",
    secondary_button_url: slide.secondary_button_url || "",
    sort_order: String(slide.sort_order),
    is_active: slide.is_active,
  };
}

export default function AdminHeroSlides() {
  const [slides, setSlides] = useState<HeroSlide[]>([]);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [formOpen, setFormOpen] = useState(false);
  const [editingSlide, setEditingSlide] = useState<HeroSlide | null>(null);
  const [form, setForm] = useState<SlideForm>({ ...emptyForm });
  const [imageFile, setImageFile] = useState<File | null>(null);
  const [imagePreview, setImagePreview] = useState<string | null>(null);
  const [formError, setFormError] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);
  const [slideToDelete, setSlideToDelete] = useState<HeroSlide | null>(null);
  const [deleting, setDeleting] = useState(false);
  const [busySlideId, setBusySlideId] = useState<number | null>(null);
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(null);
  const [search, setSearch] = useState("");

  const fetchSlides = useCallback(async () => {
    setLoading(true);
    setLoadError(null);
    try {
      const response = await fetch("/api/v1/admin/hero-slides", {
        headers: { Accept: "application/json" },
        cache: "no-store",
      });
      const result = await readResponse(response);
      setSlides(Array.isArray(result.data) ? result.data : []);
    } catch (error) {
      setLoadError(error instanceof Error ? error.message : "Failed to load hero slides.");
      setSlides([]);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    void fetchSlides();
  }, [fetchSlides]);

  useEffect(() => {
    if (!formOpen && !slideToDelete) return;
    const handleEscape = (event: KeyboardEvent) => {
      if (event.key !== "Escape" || saving || deleting) return;
      setFormOpen(false);
      setSlideToDelete(null);
    };
    window.addEventListener("keydown", handleEscape);
    return () => window.removeEventListener("keydown", handleEscape);
  }, [formOpen, slideToDelete, saving, deleting]);

  const notify = (message: string, type: "success" | "error" = "success") => {
    setToast({ message, type });
    window.setTimeout(() => setToast(null), 4000);
  };

  const closeForm = () => {
    if (imagePreview?.startsWith("blob:")) URL.revokeObjectURL(imagePreview);
    setFormOpen(false);
    setImageFile(null);
    setImagePreview(null);
    setFormError(null);
  };

  const openCreate = () => {
    setEditingSlide(null);
    setForm(freshForm());
    setImageFile(null);
    setImagePreview(null);
    setFormError(null);
    setFormOpen(true);
  };

  const openEdit = (slide: HeroSlide) => {
    setEditingSlide(slide);
    setForm(freshForm(slide));
    setImageFile(null);
    setImagePreview(slide.image_url);
    setFormError(null);
    setFormOpen(true);
  };

  const onChooseImage = (file: File | undefined) => {
    if (!file) return;
    setFormError(null);
    if (!/^image\/(jpeg|png|webp)$/.test(file.type)) {
      setFormError("Choose a JPG, PNG, or WebP image.");
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      setFormError("The image must be 5 MB or smaller.");
      return;
    }
    if (imagePreview?.startsWith("blob:")) URL.revokeObjectURL(imagePreview);
    setImageFile(file);
    setImagePreview(URL.createObjectURL(file));
  };

  const clearChosenImage = () => {
    if (imagePreview?.startsWith("blob:")) URL.revokeObjectURL(imagePreview);
    setImageFile(null);
    setImagePreview(editingSlide?.image_url || null);
  };

  const saveSlide = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (!editingSlide && !imageFile) {
      setFormError("Choose an image for the hero slide.");
      return;
    }
    setSaving(true);
    setFormError(null);
    try {
      const payload = new FormData();
      payload.set("title", form.title.trim());
      payload.set("subtitle", form.subtitle.trim());
      payload.set("description", form.description.trim());
      payload.set("button_text", form.button_text.trim());
      payload.set("button_url", form.button_url.trim());
      payload.set("secondary_button_text", form.secondary_button_text.trim());
      payload.set("secondary_button_url", form.secondary_button_url.trim());
      payload.set("sort_order", form.sort_order || "0");
      payload.set("is_active", form.is_active ? "1" : "0");
      if (imageFile) payload.set("image", imageFile);

      const url = editingSlide
        ? `/api/v1/admin/hero-slides/${editingSlide.id}`
        : "/api/v1/admin/hero-slides";
      const response = await fetch(url, {
        method: "POST",
        headers: { Accept: "application/json" },
        body: payload,
      });
      const result = await readResponse(response);
      closeForm();
      notify(result.message || (editingSlide ? "Hero slide updated." : "Hero slide created."));
      void fetchSlides();
    } catch (error) {
      setFormError(error instanceof Error ? error.message : "The slide could not be saved.");
    } finally {
      setSaving(false);
    }
  };

  const toggleSlide = async (slide: HeroSlide) => {
    setBusySlideId(slide.id);
    try {
      const response = await fetch(`/api/v1/admin/hero-slides/${slide.id}/toggle`, {
        method: "POST",
        headers: { Accept: "application/json" },
      });
      const result = await readResponse(response);
      setSlides((current) => current.map((item) => item.id === slide.id ? result.data : item));
      notify(result.message || "Slide status updated.");
    } catch (error) {
      notify(error instanceof Error ? error.message : "Slide status could not be updated.", "error");
    } finally {
      setBusySlideId(null);
    }
  };

  const deleteSlide = async () => {
    if (!slideToDelete) return;
    setDeleting(true);
    try {
      const response = await fetch(`/api/v1/admin/hero-slides/${slideToDelete.id}`, {
        method: "DELETE",
        headers: { Accept: "application/json" },
      });
      const result = await readResponse(response);
      setSlides((current) => current.filter((slide) => slide.id !== slideToDelete.id));
      setSlideToDelete(null);
      notify(result.message || "Hero slide deleted.");
    } catch (error) {
      notify(error instanceof Error ? error.message : "The slide could not be deleted.", "error");
    } finally {
      setDeleting(false);
    }
  };

  const visibleSlides = useMemo(() => {
    const query = search.trim().toLowerCase();
    return query
      ? slides.filter((slide) => [slide.title, slide.subtitle, slide.description].some((value) => value?.toLowerCase().includes(query)))
      : slides;
  }, [slides, search]);
  const activeCount = slides.filter((slide) => slide.is_active).length;
  const inactiveCount = slides.length - activeCount;

  return (
    <div className="flex min-w-0 flex-1 flex-col bg-gray-50 pb-16">
      <AdminHeader title="Hero Slides" subtitle="Manage homepage carousel images and content" />

      {toast && <div role="status" className={`fixed bottom-5 right-5 z-[100] max-w-[calc(100vw-2.5rem)] rounded-xl px-4 py-3 text-xs font-semibold text-white shadow-xl ${toast.type === "error" ? "bg-rose-700" : "bg-gray-900"}`}>{toast.message}</div>}

      <div className="mx-auto w-full max-w-7xl space-y-4 p-4 sm:p-6">
        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <p className="text-xs leading-5 text-gray-500">Add, edit, and order the slides shown in the homepage carousel.</p>
          <button type="button" onClick={openCreate} className="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-gray-800"><Plus size={15} weight="bold" />Add new slide</button>
        </div>

        <section className="grid gap-3 sm:grid-cols-3">
          {[
            { label: "Total slides", value: slides.length, tone: "bg-blue-50 text-blue-700", icon: <Slideshow size={17} /> },
            { label: "Active on homepage", value: activeCount, tone: "bg-emerald-50 text-emerald-700", icon: <Eye size={17} /> },
            { label: "Inactive", value: inactiveCount, tone: "bg-gray-100 text-gray-600", icon: <EyeSlash size={17} /> },
          ].map((item) => <div key={item.label} className="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-2xs"><div><p className="text-[10px] font-bold uppercase tracking-wide text-gray-500">{item.label}</p><p className="mt-1 text-2xl font-bold tabular-nums text-gray-900">{loading ? "—" : item.value}</p></div><span className={`rounded-lg p-2.5 ${item.tone}`}>{item.icon}</span></div>)}
        </section>

        <section className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xs">
          <div className="flex flex-col gap-3 border-b border-gray-100 bg-gray-50/60 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div><h2 className="text-sm font-bold text-gray-900">Homepage carousel</h2><p className="mt-0.5 text-[11px] text-gray-500">Slides display in sort order, with the lowest number first.</p></div>
            <div className="flex items-center gap-2">
              <input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Search slides…" className="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs outline-none focus:border-gray-400 sm:w-48" />
              <button type="button" onClick={() => void fetchSlides()} disabled={loading} aria-label="Refresh slides" className="rounded-lg border border-gray-200 bg-white p-2 text-gray-600 transition hover:bg-gray-50 disabled:opacity-50"><ArrowsClockwise size={15} className={loading ? "animate-spin" : ""} /></button>
            </div>
          </div>

          {loadError ? (
            <div role="alert" className="m-4 flex flex-col items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 p-8 text-center sm:m-6"><Warning size={28} className="text-rose-500" /><div><h3 className="text-sm font-bold text-rose-900">Hero slides could not be loaded</h3><p className="mt-1 text-xs text-rose-800">{loadError}</p></div><button type="button" onClick={() => void fetchSlides()} className="rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-800 hover:bg-rose-100">Try again</button></div>
          ) : loading ? (
            <div className="grid gap-4 p-4 sm:grid-cols-2 sm:p-5 xl:grid-cols-3">{Array.from({ length: 3 }).map((_, index) => <div key={index} className="overflow-hidden rounded-xl border border-gray-100"><div className="aspect-[16/9] animate-pulse bg-gray-100" /><div className="space-y-2 p-4"><div className="h-4 w-2/3 animate-pulse rounded bg-gray-100" /><div className="h-3 animate-pulse rounded bg-gray-100" /></div></div>)}</div>
          ) : visibleSlides.length === 0 ? (
            <div className="px-5 py-14 text-center sm:py-16"><span className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-rose-400"><ImageSquare size={27} /></span><h3 className="mt-4 text-base font-bold text-gray-900">{search ? "No matching slides" : "No hero slides yet"}</h3><p className="mx-auto mt-1 max-w-sm text-xs leading-5 text-gray-500">{search ? `No slide matches “${search}”. Try a different search.` : "Add your first hero slide to build the homepage carousel."}</p>{!search && <button type="button" onClick={openCreate} className="mt-4 inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-xs font-semibold text-white hover:bg-gray-800"><Plus size={14} />Add first slide</button>}</div>
          ) : (
            <div className="grid gap-4 p-4 sm:grid-cols-2 sm:p-5 xl:grid-cols-3">
              {visibleSlides.map((slide) => <article key={slide.id} className="group overflow-hidden rounded-xl border border-gray-200 bg-white transition hover:shadow-md">
                <div className="relative aspect-[16/9] overflow-hidden bg-gray-100">
                  <Image src={slide.image_url} alt={slide.title} fill unoptimized sizes="(max-width: 640px) 100vw, (max-width: 1280px) 50vw, 33vw" className="object-cover transition duration-500 group-hover:scale-[1.03]" />
                  <div className="absolute inset-0 bg-gradient-to-t from-black/85 via-black/25 to-transparent" />
                  <div className="absolute left-3 top-3 flex items-center gap-2"><span className={`rounded-full px-2.5 py-1 text-[10px] font-bold shadow-sm backdrop-blur ${slide.is_active ? "bg-emerald-500/90 text-white" : "bg-gray-900/70 text-gray-200"}`}>{slide.is_active ? "Active" : "Inactive"}</span><span className="rounded-full bg-black/50 px-2.5 py-1 text-[10px] font-bold text-white backdrop-blur">#{slide.sort_order}</span></div>
                  <div className="absolute inset-x-0 bottom-0 p-4 sm:p-5">{slide.subtitle && <p className="mb-1 text-[10px] font-semibold uppercase tracking-[0.15em] text-rose-200">{slide.subtitle}</p>}<h3 className="line-clamp-2 font-serif text-lg font-bold leading-tight text-white sm:text-xl">{slide.title}</h3>{slide.description && <p className="mt-1 line-clamp-2 text-[11px] leading-4 text-gray-200">{slide.description}</p>}</div>
                </div>
                <div className="flex items-center justify-between gap-3 border-t border-gray-100 px-3 py-3 sm:px-4">
                  <div className="min-w-0"><p className="truncate text-[11px] font-semibold text-gray-800">{slide.button_text || "No primary button"}</p><p className="mt-0.5 truncate text-[10px] text-gray-400">{slide.button_url || "No button link"} · Order {slide.sort_order}</p></div>
                  <div className="flex shrink-0 items-center gap-1">
                    <button type="button" onClick={() => void toggleSlide(slide)} disabled={busySlideId === slide.id} title={slide.is_active ? "Deactivate slide" : "Activate slide"} aria-label={slide.is_active ? "Deactivate slide" : "Activate slide"} className={`rounded-lg p-2 transition disabled:opacity-50 ${slide.is_active ? "text-emerald-700 hover:bg-emerald-50" : "text-gray-400 hover:bg-gray-100 hover:text-gray-700"}`}>{busySlideId === slide.id ? <ArrowsClockwise size={15} className="animate-spin" /> : slide.is_active ? <Eye size={15} /> : <EyeSlash size={15} />}</button>
                    <button type="button" onClick={() => openEdit(slide)} title="Edit slide" aria-label="Edit slide" className="rounded-lg p-2 text-gray-500 transition hover:bg-blue-50 hover:text-blue-700"><PencilSimple size={15} /></button>
                    <button type="button" onClick={() => setSlideToDelete(slide)} title="Delete slide" aria-label="Delete slide" className="rounded-lg p-2 text-gray-500 transition hover:bg-rose-50 hover:text-rose-700"><Trash size={15} /></button>
                  </div>
                </div>
                <p className="border-t border-gray-50 px-4 py-2 text-[10px] text-gray-400">Added {formatDate(slide.created_at)}</p>
              </article>)}
            </div>
          )}
        </section>
      </div>

      {formOpen && (
        <div className="fixed inset-0 z-[80] flex items-center justify-center overflow-y-auto bg-black/50 p-3 backdrop-blur-xs animate-in fade-in duration-200 sm:p-5" onMouseDown={(event) => { if (event.target === event.currentTarget && !saving) closeForm(); }}>
          <section role="dialog" aria-modal="true" aria-labelledby="hero-slide-form-title" className="flex max-h-[94dvh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl animate-in zoom-in-95 duration-200 sm:max-h-[90dvh]">
            <header className="flex shrink-0 items-start justify-between gap-3 border-b border-gray-100 bg-gray-50/70 px-4 py-4 sm:px-5">
              <div><h2 id="hero-slide-form-title" className="text-sm font-bold text-gray-900">{editingSlide ? "Edit hero slide" : "Add hero slide"}</h2><p className="mt-1 text-[11px] text-gray-500">Manage its image, homepage text, buttons, and display order.</p></div>
              <button type="button" onClick={closeForm} disabled={saving} aria-label="Close form" className="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-800 disabled:opacity-50"><X size={17} /></button>
            </header>
            <form onSubmit={saveSlide} className="flex min-h-0 flex-1 flex-col overflow-hidden">
              <div className="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain p-4 text-xs sm:space-y-5 sm:p-5">
                {formError && <div role="alert" className="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2.5 text-xs text-rose-800">{formError}</div>}
                <div className="grid gap-4 sm:grid-cols-2">
                  <div className="space-y-2 sm:col-span-2">
                    <label className="block text-xs font-semibold text-gray-700">Slide image <span className="text-rose-600">*</span></label>
                    {imagePreview ? <div className="relative aspect-[16/9] overflow-hidden rounded-xl border border-gray-200 bg-gray-100"><div role="img" aria-label="Hero slide image preview" className="absolute inset-0 bg-cover bg-center" style={{ backgroundImage: `url("${imagePreview}")` }} /><div className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/65 to-transparent p-3 text-[10px] font-medium text-white">{imageFile?.name || "Current slide image"}</div></div> : <label className="relative flex aspect-[16/9] cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 text-center transition hover:border-rose-300 hover:bg-rose-50/30"><ImageSquare size={30} className="text-rose-400" /><span className="mt-2 text-xs font-semibold text-gray-800">Choose a slide image</span><span className="mt-1 text-[10px] text-gray-400">JPG, PNG, or WebP · Up to 5 MB · Recommended 1920 × 1080</span><input type="file" accept="image/jpeg,image/png,image/webp" required={!editingSlide && !imageFile} onChange={(event) => onChooseImage(event.target.files?.[0])} className="absolute inset-0 cursor-pointer opacity-0" /></label>}
                    {imagePreview && <div className="flex flex-wrap items-center gap-2"><label className="inline-flex cursor-pointer items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-[11px] font-semibold text-gray-700 transition hover:bg-gray-50">Choose another image<input type="file" accept="image/jpeg,image/png,image/webp" onChange={(event) => { onChooseImage(event.target.files?.[0]); event.target.value = ""; }} className="sr-only" /></label>{imageFile && <button type="button" onClick={clearChosenImage} className="rounded-lg px-3 py-2 text-[11px] font-semibold text-gray-500 hover:bg-gray-100">{editingSlide ? "Use current image" : "Remove image"}</button>}</div>}
                    <p className="text-[10px] text-gray-400">The image is stored in Laravel’s public storage.</p>
                  </div>
                  <label className="space-y-1.5"><span className="text-xs font-semibold text-gray-700">Title <span className="text-rose-600">*</span></span><input required maxLength={255} value={form.title} onChange={(event) => setForm((current) => ({ ...current, title: event.target.value }))} className={fieldClass} placeholder="Artistry at Your Fingertips" /></label>
                  <label className="space-y-1.5"><span className="text-xs font-semibold text-gray-700">Tagline / subtitle</span><input maxLength={255} value={form.subtitle} onChange={(event) => setForm((current) => ({ ...current, subtitle: event.target.value }))} className={fieldClass} placeholder="Kigali’s Premier Nail Salon" /></label>
                  <label className="space-y-1.5 sm:col-span-2"><span className="text-xs font-semibold text-gray-700">Description</span><textarea rows={3} maxLength={500} value={form.description} onChange={(event) => setForm((current) => ({ ...current, description: event.target.value }))} className={fieldClass + " resize-y"} placeholder="A short description displayed over the hero image." /><span className="block text-right text-[10px] text-gray-400">{form.description.length}/500</span></label>
                </div>

                <div className="border-t border-gray-100 pt-4">
                  <h3 className="mb-3 text-xs font-bold text-gray-900">Primary button</h3>
                  <div className="grid gap-4 sm:grid-cols-2"><label className="space-y-1.5"><span className="text-xs font-semibold text-gray-700">Button text</span><input maxLength={100} value={form.button_text} onChange={(event) => setForm((current) => ({ ...current, button_text: event.target.value }))} className={fieldClass} placeholder="Book Appointment" /></label><label className="space-y-1.5"><span className="text-xs font-semibold text-gray-700">Button URL</span><input maxLength={255} value={form.button_url} onChange={(event) => setForm((current) => ({ ...current, button_url: event.target.value }))} className={fieldClass} placeholder="/booking" /></label></div>
                </div>

                <div className="border-t border-gray-100 pt-4">
                  <h3 className="mb-3 text-xs font-bold text-gray-900">Secondary button <span className="font-normal text-gray-400">(optional)</span></h3>
                  <div className="grid gap-4 sm:grid-cols-2"><label className="space-y-1.5"><span className="text-xs font-semibold text-gray-700">Button text</span><input maxLength={100} value={form.secondary_button_text} onChange={(event) => setForm((current) => ({ ...current, secondary_button_text: event.target.value }))} className={fieldClass} placeholder="Explore Services" /></label><label className="space-y-1.5"><span className="text-xs font-semibold text-gray-700">Button URL</span><input maxLength={255} value={form.secondary_button_url} onChange={(event) => setForm((current) => ({ ...current, secondary_button_url: event.target.value }))} className={fieldClass} placeholder="/services" /></label></div>
                </div>

                <div className="grid gap-4 border-t border-gray-100 pt-4 sm:grid-cols-2 sm:items-end">
                  <label className="space-y-1.5"><span className="text-xs font-semibold text-gray-700">Sort order</span><input type="number" min={0} step={1} value={form.sort_order} onChange={(event) => setForm((current) => ({ ...current, sort_order: event.target.value }))} className={fieldClass} /><span className="block text-[10px] text-gray-400">Lower numbers appear first in the carousel.</span></label>
                  <label className="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-3"><input type="checkbox" checked={form.is_active} onChange={(event) => setForm((current) => ({ ...current, is_active: event.target.checked }))} className="h-4 w-4 rounded border-gray-300 accent-gray-900" /><span><span className="block text-xs font-semibold text-gray-800">Show on homepage</span><span className="mt-0.5 block text-[10px] text-gray-500">Inactive slides are hidden from visitors.</span></span></label>
                </div>
              </div>
              <footer className="flex shrink-0 flex-col-reverse gap-2 border-t border-gray-100 bg-gray-50 p-4 sm:flex-row sm:justify-end"><button type="button" onClick={closeForm} disabled={saving} className="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 disabled:opacity-50">Cancel</button><button type="submit" disabled={saving} className="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-gray-800 disabled:opacity-50">{saving ? <ArrowsClockwise size={14} className="animate-spin" /> : <Check size={14} />}{saving ? "Saving…" : editingSlide ? "Save changes" : "Create slide"}</button></footer>
            </form>
          </section>
        </div>
      )}

      {slideToDelete && <div className="fixed inset-0 z-[90] flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs" onMouseDown={(event) => { if (event.target === event.currentTarget && !deleting) setSlideToDelete(null); }}><section role="alertdialog" aria-modal="true" aria-labelledby="delete-slide-title" className="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6"><div className="flex items-start gap-3"><span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-600"><Warning size={20} /></span><div><h2 id="delete-slide-title" className="text-sm font-bold text-gray-900">Delete hero slide?</h2><p className="mt-1 text-xs leading-relaxed text-gray-600">This permanently removes <strong className="text-gray-900">{slideToDelete.title}</strong> and its stored image.</p></div></div><div className="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"><button type="button" onClick={() => setSlideToDelete(null)} disabled={deleting} className="rounded-lg border border-gray-200 px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-50">Cancel</button><button type="button" onClick={() => void deleteSlide()} disabled={deleting} className="inline-flex items-center justify-center gap-2 rounded-lg bg-rose-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-rose-700 disabled:opacity-50">{deleting ? <ArrowsClockwise size={14} className="animate-spin" /> : <Trash size={14} />}Delete slide</button></div></section></div>}
    </div>
  );
}
