"use client";

import { useCallback, useEffect, useState } from "react";
import type { FormEvent } from "react";
import Image from "next/image";
import {
  ArrowsClockwise,
  PencilSimple,
  Plus,
  Trash,
  UsersThree,
  Warning,
  X,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";

interface TeamMember {
  id: number;
  name: string;
  role: string;
  bio: string | null;
  photo_url: string | null;
  active: boolean;
  display_order: number;
  created_at: string | null;
}

interface Pagination {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

interface TeamMemberForm {
  name: string;
  role: string;
  bio: string;
  active: boolean;
  display_order: string;
  photo: File | null;
}

const EMPTY_FORM: TeamMemberForm = {
  name: "",
  role: "",
  bio: "",
  active: true,
  display_order: "0",
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

export default function AdminTeamMembers() {
  const [members, setMembers] = useState<TeamMember[]>([]);
  const [stats, setStats] = useState({ total: 0, active: 0, inactive: 0 });
  const [pagination, setPagination] = useState<Pagination>({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
  });
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(null);
  const [formOpen, setFormOpen] = useState(false);
  const [editingMember, setEditingMember] = useState<TeamMember | null>(null);
  const [form, setForm] = useState<TeamMemberForm>(EMPTY_FORM);
  const [formError, setFormError] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);
  const [photoPreview, setPhotoPreview] = useState<string | null>(null);
  const [deleteMember, setDeleteMember] = useState<TeamMember | null>(null);
  const [deleting, setDeleting] = useState(false);

  const modalOpen = formOpen || deleteMember !== null;

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
      else if (deleteMember && !deleting) setDeleteMember(null);
    };
    window.addEventListener("keydown", onKeyDown);
    return () => window.removeEventListener("keydown", onKeyDown);
  }, [modalOpen, formOpen, saving, deleteMember, deleting]);

  useEffect(() => {
    if (!form.photo) {
      setPhotoPreview(null);
      return;
    }
    const preview = URL.createObjectURL(form.photo);
    setPhotoPreview(preview);
    return () => URL.revokeObjectURL(preview);
  }, [form.photo]);

  const notify = (message: string, type: "success" | "error" = "success") => {
    setToast({ message, type });
    window.setTimeout(() => setToast(null), 4000);
  };

  const loadMembers = useCallback(async (targetPage = page) => {
    setLoading(true);
    setLoadError(null);
    try {
      const response = await fetch(`/api/v1/admin/team-members?page=${targetPage}`, {
        headers: { Accept: "application/json" },
        cache: "no-store",
      });
      const result = await readResponse(response);
      setMembers(Array.isArray(result.data) ? result.data : []);
      setStats(result.stats || { total: 0, active: 0, inactive: 0 });
      if (result.meta) {
        setPagination(result.meta);
        if (targetPage > result.meta.last_page && result.meta.last_page > 0) {
          setPage(result.meta.last_page);
        }
      }
    } catch (error) {
      setLoadError(error instanceof Error ? error.message : "Failed to load team members.");
      setMembers([]);
    } finally {
      setLoading(false);
    }
  }, [page]);

  useEffect(() => {
    void loadMembers(page);
  }, [loadMembers, page]);

  const openCreate = () => {
    setEditingMember(null);
    setForm(EMPTY_FORM);
    setFormError(null);
    setFormOpen(true);
  };

  const openEdit = (member: TeamMember) => {
    setEditingMember(member);
    setForm({
      name: member.name,
      role: member.role,
      bio: member.bio || "",
      active: member.active,
      display_order: String(member.display_order),
      photo: null,
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
    payload.set("role", form.role.trim());
    payload.set("bio", form.bio.trim());
    payload.set("active", form.active ? "1" : "0");
    payload.set("display_order", form.display_order);
    if (form.photo) payload.set("photo", form.photo);
    if (editingMember) payload.set("_method", "PUT");

    try {
      const response = await fetch(
        editingMember
          ? `/api/v1/admin/team-members/${editingMember.id}`
          : "/api/v1/admin/team-members",
        {
          method: "POST",
          headers: { Accept: "application/json" },
          body: payload,
        }
      );
      const result = await readResponse(response);
      setFormOpen(false);
      notify(result.message || "Team member saved successfully.");
      await loadMembers(page);
    } catch (error) {
      setFormError(error instanceof Error ? error.message : "Failed to save team member.");
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async () => {
    if (!deleteMember) return;
    setDeleting(true);
    try {
      const response = await fetch(`/api/v1/admin/team-members/${deleteMember.id}`, {
        method: "DELETE",
        headers: { Accept: "application/json" },
      });
      const result = await readResponse(response);
      setDeleteMember(null);
      notify(result.message || "Team member deleted.");
      await loadMembers(page);
    } catch (error) {
      notify(error instanceof Error ? error.message : "Failed to delete team member.", "error");
    } finally {
      setDeleting(false);
    }
  };

  const currentPhoto = photoPreview || editingMember?.photo_url || null;

  return (
    <div className="flex min-w-0 flex-1 flex-col bg-gray-50 pb-16">
      <AdminHeader title="Team Members" subtitle="Manage non-provider staff such as receptionists and managers" />

      <div className="mx-auto w-full max-w-7xl space-y-5 p-4 sm:p-6">
        <section className="grid gap-3 sm:grid-cols-3">
          {[
            { label: "All Team Members", value: stats.total, tone: "bg-gray-100 text-gray-700" },
            { label: "Active", value: stats.active, tone: "bg-emerald-50 text-emerald-700" },
            { label: "Inactive", value: stats.inactive, tone: "bg-gray-100 text-gray-600" },
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
              <h2 className="text-sm font-bold text-gray-900">Staff directory</h2>
              <p className="mt-1 text-xs text-gray-500">Profiles appear in display order on the public Team page.</p>
            </div>
            <button type="button" onClick={openCreate} className="inline-flex items-center justify-center gap-1.5 rounded-lg bg-gray-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-gray-800">
              <Plus size={14} weight="bold" /> Add Team Member
            </button>
          </div>

          {loadError && <div role="alert" className="m-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2.5 text-xs text-rose-800">{loadError}</div>}

          <div className="overflow-x-auto">
            <table className="w-full min-w-[640px] text-left">
              <thead>
                <tr className="border-b border-gray-100 bg-gray-50/70 text-[10px] font-bold uppercase tracking-wide text-gray-500">
                  <th className="px-4 py-3">Name</th>
                  <th className="px-4 py-3">Role</th>
                  <th className="px-4 py-3">Status</th>
                  <th className="px-4 py-3">Display order</th>
                  <th className="px-4 py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {loading ? (
                  Array.from({ length: 5 }).map((_, index) => <tr key={index} className="animate-pulse"><td colSpan={5} className="px-4 py-4"><div className="h-10 rounded-lg bg-gray-100" /></td></tr>)
                ) : members.length === 0 ? (
                  <tr>
                    <td colSpan={5} className="px-4 py-14 text-center">
                      <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-500"><UsersThree size={22} /></div>
                      <h3 className="mt-3 text-sm font-bold text-gray-900">No team members yet</h3>
                      <p className="mt-1 text-xs text-gray-500">Add your first team member to display on the public Team page.</p>
                      <button type="button" onClick={openCreate} className="mt-3 rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white">Add Team Member</button>
                    </td>
                  </tr>
                ) : (
                  members.map((member) => (
                    <tr key={member.id} className="group hover:bg-gray-50/70">
                      <td className="px-4 py-3.5">
                        <div className="flex items-center gap-3">
                          <div className="relative h-10 w-10 shrink-0 overflow-hidden rounded-full bg-gray-100">
                            {member.photo_url ? <Image src={member.photo_url} alt={member.name} fill sizes="40px" className="object-cover" /> : <div className="flex h-full w-full items-center justify-center text-xs font-bold text-gray-500">{member.name.charAt(0).toUpperCase()}</div>}
                          </div>
                          <div className="min-w-0">
                            <p className="truncate text-xs font-bold text-gray-900">{member.name}</p>
                            {member.bio && <p className="mt-0.5 max-w-xs truncate text-[10px] text-gray-400">{member.bio}</p>}
                          </div>
                        </div>
                      </td>
                      <td className="px-4 py-3.5 text-xs text-gray-600">{member.role}</td>
                      <td className="px-4 py-3.5">
                        <span className={`inline-flex items-center gap-1 rounded-full border px-2 py-1 text-[10px] font-bold ${member.active ? "border-emerald-200 bg-emerald-50 text-emerald-700" : "border-gray-200 bg-gray-100 text-gray-600"}`}>
                          <span className={`h-1.5 w-1.5 rounded-full ${member.active ? "bg-emerald-500" : "bg-gray-400"}`} />{member.active ? "Active" : "Inactive"}
                        </span>
                      </td>
                      <td className="px-4 py-3.5 text-xs tabular-nums text-gray-600">{member.display_order}</td>
                      <td className="px-4 py-3.5">
                        <div className="flex items-center justify-end gap-2">
                          <button type="button" title="Edit team member" onClick={() => openEdit(member)} className="rounded-lg bg-gray-50 p-2 text-gray-500 transition hover:bg-indigo-50 hover:text-indigo-700"><PencilSimple size={15} /></button>
                          <button type="button" title="Delete team member" onClick={() => setDeleteMember(member)} className="rounded-lg bg-gray-50 p-2 text-gray-500 transition hover:bg-rose-50 hover:text-rose-700"><Trash size={15} /></button>
                        </div>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>

          {!loading && members.length > 0 && (
            <div className="flex flex-col gap-3 border-t border-gray-100 bg-gray-50/40 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
              <p className="text-[11px] text-gray-500">Showing {members.length} of {pagination.total} team members</p>
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
          <section role="dialog" aria-modal="true" aria-labelledby="team-member-form-title" className="flex max-h-[90dvh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl animate-in zoom-in-95 duration-200">
            <div className="flex shrink-0 items-start justify-between border-b border-gray-100 bg-gray-50/50 px-4 py-4 sm:px-5">
              <div>
                <h2 id="team-member-form-title" className="text-sm font-bold text-gray-900">{editingMember ? "Edit Team Member" : "Add Team Member"}</h2>
                <p className="mt-1 text-[11px] text-gray-500">Manage the profile shown on the public Team page.</p>
              </div>
              <button type="button" onClick={() => setFormOpen(false)} disabled={saving} aria-label="Close form" className="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-800 disabled:opacity-50"><X size={17} /></button>
            </div>
            <form onSubmit={handleSave} className="flex min-h-0 flex-1 flex-col overflow-hidden">
              <div className="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain p-5 text-xs">
                {formError && <div role="alert" className="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2.5 text-xs text-rose-800">{formError}</div>}
                <div className="grid gap-4 sm:grid-cols-2">
                  <label className="space-y-1.5"><span className="text-xs font-semibold text-gray-700">Full name</span><input required maxLength={255} value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} className={inputClass} placeholder="e.g. Jordan N." /></label>
                  <label className="space-y-1.5"><span className="text-xs font-semibold text-gray-700">Role / title</span><input required maxLength={255} value={form.role} onChange={(event) => setForm((current) => ({ ...current, role: event.target.value }))} className={inputClass} placeholder="Receptionist, Manager..." /></label>
                  <label className="space-y-1.5 sm:col-span-2"><span className="text-xs font-semibold text-gray-700">Short bio</span><textarea rows={4} value={form.bio} onChange={(event) => setForm((current) => ({ ...current, bio: event.target.value }))} className={inputClass + " resize-y"} placeholder="A short introduction..." /></label>
                  <label className="space-y-1.5"><span className="text-xs font-semibold text-gray-700">Display order</span><input required type="number" step="1" value={form.display_order} onChange={(event) => setForm((current) => ({ ...current, display_order: event.target.value }))} className={inputClass} /><span className="block text-[11px] text-gray-400">Lower numbers appear first.</span></label>
                  <label className="space-y-1.5"><span className="text-xs font-semibold text-gray-700">Status</span><select value={form.active ? "1" : "0"} onChange={(event) => setForm((current) => ({ ...current, active: event.target.value === "1" }))} className={inputClass}><option value="1">Active</option><option value="0">Inactive</option></select></label>
                  <label className="space-y-1.5 sm:col-span-2"><span className="text-xs font-semibold text-gray-700">Photo</span>
                    {currentPhoto && <span className="flex items-center gap-2 text-[11px] text-gray-500"><span className="relative h-12 w-12 overflow-hidden rounded-lg"><Image src={currentPhoto} alt="Team member preview" fill sizes="48px" unoptimized className="object-cover" /></span>{form.photo ? form.photo.name : "Current photo"}</span>}
                    <input type="file" accept="image/*" onChange={(event) => setForm((current) => ({ ...current, photo: event.target.files?.[0] || null }))} className="block w-full rounded-lg border border-gray-200 bg-white text-xs text-gray-600 file:mr-3 file:border-0 file:border-r file:border-gray-200 file:bg-gray-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-gray-700 hover:file:bg-gray-100" />
                    <span className="block text-[11px] text-gray-400">Optional. Choose an image up to 10 MB.</span>
                  </label>
                </div>
              </div>
              <div className="flex shrink-0 flex-col-reverse gap-2 border-t border-gray-100 bg-gray-50 p-4 sm:flex-row sm:justify-end">
                <button type="button" onClick={() => setFormOpen(false)} disabled={saving} className="rounded-lg border border-gray-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-100 disabled:opacity-50">Cancel</button>
                <button type="submit" disabled={saving} className="inline-flex items-center justify-center gap-1.5 rounded-lg bg-gray-900 px-4 py-1.5 text-xs font-semibold text-white transition hover:bg-gray-800 disabled:opacity-50">{saving && <ArrowsClockwise size={13} className="animate-spin" />}{saving ? "Saving..." : editingMember ? "Save Changes" : "Create Team Member"}</button>
              </div>
            </form>
          </section>
        </div>
      )}

      {deleteMember && (
        <div className="fixed inset-0 z-[90] flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs animate-in fade-in duration-200" onMouseDown={(event) => { if (event.target === event.currentTarget && !deleting) setDeleteMember(null); }}>
          <section role="dialog" aria-modal="true" aria-labelledby="delete-team-member-title" className="w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl animate-in zoom-in-95 duration-200 sm:p-6">
            <div className="flex items-start gap-3">
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-600"><Warning size={20} /></div>
              <div><h2 id="delete-team-member-title" className="text-sm font-bold text-gray-900">Delete team member?</h2><p className="mt-1 text-xs leading-relaxed text-gray-600">This removes {deleteMember.name} from the public team directory.</p></div>
            </div>
            <div className="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
              <button type="button" onClick={() => setDeleteMember(null)} disabled={deleting} className="rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-50">Cancel</button>
              <button type="button" onClick={() => void handleDelete()} disabled={deleting} className="inline-flex items-center justify-center gap-1.5 rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-700 disabled:opacity-50">{deleting && <ArrowsClockwise size={13} className="animate-spin" />}{deleting ? "Deleting..." : <><Trash size={13} /> Delete Member</>}</button>
            </div>
          </section>
        </div>
      )}

      {toast && <div role="status" className={`fixed bottom-5 right-5 z-[100] max-w-[calc(100vw-2.5rem)] rounded-xl px-4 py-3 text-xs font-semibold text-white shadow-xl ${toast.type === "error" ? "bg-rose-700" : "bg-gray-900"}`}>{toast.message}</div>}
    </div>
  );
}
