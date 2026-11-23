"use client";

import { useEffect, useState } from "react";
import type { FormEvent } from "react";
import {
  ArrowsClockwise,
  Check,
  EnvelopeSimple,
  Warning,
  Phone,
  WhatsappLogo,
  MapPin,
  Clock,
  Globe,
  NavigationArrow,
  CheckCircle,
  Eye,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";

interface SettingsData {
  salon_name?: string;
  phone?: string;
  phone_raw?: string;
  whatsapp?: string;
  email?: string;
  address?: string;
  location_landmark?: string;
  opening_hours?: string;
  opening_hours_sunday?: string;
  google_map_embed_url?: string;
  google_map_link?: string;
  instagram?: string;
  facebook?: string;
  google_review_url?: string;
  booking_alert_emails?: string;
  email_addresses?: string[];
}

export default function AdminSettings() {
  const [formData, setFormData] = useState<SettingsData>({
    salon_name: "Isaiah Nail Bar",
    phone: "+250 790 395 169",
    phone_raw: "0790395169",
    whatsapp: "+250790395169",
    email: "info@isaiahnailbar.com",
    address: "KG 4 Roundabout, Gisementi, Kigali, Rwanda",
    location_landmark: "Opposite Sonatubes direction, with dedicated client parking",
    opening_hours: "Monday – Saturday: 08:00 AM – 08:00 PM",
    opening_hours_sunday: "Sunday: Closed (Private bookings upon request)",
    google_map_embed_url: "",
    google_map_link: "",
    instagram: "",
    facebook: "",
    google_review_url: "",
    booking_alert_emails: "",
    email_addresses: [],
  });

  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [formError, setFormError] = useState<string | null>(null);
  const [toast, setToast] = useState<string | null>(null);

  // Extract clean URL from pasted <iframe src="..."> tag if present
  const handleMapInput = (value: string) => {
    let clean = value.trim();
    const match = clean.match(/<iframe[^>]*\s+src=["']([^"']+)["'][^>]*>/i);
    if (match && match[1]) {
      clean = match[1];
    }
    setFormData((prev) => ({ ...prev, google_map_embed_url: clean }));
  };

  const loadSettings = async () => {
    setLoading(true);
    setLoadError(null);
    try {
      const response = await fetch("/api/v1/admin/settings", {
        headers: { Accept: "application/json" },
        cache: "no-store",
      });
      const result = await response.json();
      if (!response.ok || result.success === false) {
        throw new Error(result.message || "Failed to load site settings.");
      }
      if (result.data) {
        setFormData((prev) => ({
          ...prev,
          ...result.data,
        }));
      }
    } catch (error) {
      setLoadError(error instanceof Error ? error.message : "Could not load settings.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadSettings();
  }, []);

  const saveSettings = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setSaving(true);
    setFormError(null);
    setToast(null);

    try {
      const response = await fetch("/api/v1/admin/settings", {
        method: "PUT",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify(formData),
      });

      const result = await response.json();
      if (!response.ok || result.success === false) {
        const validation = result.errors
          ? Object.values(result.errors).flat().join(" ")
          : "";
        throw new Error(validation || result.message || "Could not save settings.");
      }

      if (result.data) {
        setFormData((prev) => ({ ...prev, ...result.data }));
      }
      setToast("Site settings, contact info, and Google Maps updated successfully.");
      window.setTimeout(() => setToast(null), 4000);
    } catch (error) {
      setFormError(error instanceof Error ? error.message : "Could not save settings.");
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="flex min-w-0 flex-1 flex-col bg-[#FAF7F2] pb-16 text-[#221C1A]">
      <AdminHeader
        title="Site Settings & Salon Profile"
        subtitle="Manage phone numbers, physical address, Google Maps location, and booking alerts"
      />

      <div className="mx-auto w-full max-w-5xl space-y-6 p-4 sm:p-6 lg:p-8">
        {/* Toast Notification */}
        {toast && (
          <div
            role="status"
            className="flex items-center gap-2 rounded-2xl border border-[#34A853]/30 bg-[#E6F4EA] px-4 py-3 text-xs font-semibold text-[#2D8E47] shadow-sm"
          >
            <CheckCircle size={18} weight="fill" />
            <span>{toast}</span>
          </div>
        )}

        {/* Form Error Alert */}
        {formError && (
          <div
            role="alert"
            className="flex items-center gap-2 rounded-2xl border border-[#F5B7B1] bg-[#FDEDEC] px-4 py-3 text-xs font-semibold text-[#C0392B] shadow-sm"
          >
            <Warning size={18} weight="fill" />
            <span>{formError}</span>
          </div>
        )}

        {loadError ? (
          <div
            role="alert"
            className="flex flex-col items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-10 text-center"
          >
            <Warning size={28} className="text-rose-500" />
            <div>
              <h2 className="text-sm font-bold text-rose-900">Settings could not be loaded</h2>
              <p className="mt-1 text-xs text-rose-800">{loadError}</p>
            </div>
            <button
              type="button"
              onClick={loadSettings}
              className="rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-800 hover:bg-rose-100"
            >
              Try again
            </button>
          </div>
        ) : loading ? (
          <div className="p-16 flex flex-col items-center justify-center gap-3 rounded-2xl bg-white border border-[#EAE4D9]">
            <ArrowsClockwise size={28} className="animate-spin text-[#B8975A]" />
            <p className="font-serif text-sm text-[#6E6660]">Loading salon settings...</p>
          </div>
        ) : (
          <form onSubmit={saveSettings} className="space-y-6">
            {/* ========================================================= */}
            {/* CARD 1: SALON CONTACT & TELEPHONE HOTLINES               */}
            {/* ========================================================= */}
            <div className="rounded-3xl border border-[#EAE4D9] bg-white p-6 sm:p-8 shadow-xs space-y-5">
              <div className="flex items-start gap-3 border-b border-[#FAF7F2] pb-4">
                <div className="w-10 h-10 rounded-2xl bg-[#FAF0E4] text-[#B8975A] flex items-center justify-center shrink-0">
                  <Phone size={20} weight="fill" />
                </div>
                <div>
                  <h3 className="font-serif text-lg text-[#221C1A]">Salon Contact & Hotlines</h3>
                  <p className="text-xs text-[#6E6660] mt-0.5">
                    Phone numbers and WhatsApp displayed on the website header, footer, and booking reminders.
                  </p>
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                    Display Phone Number (Public View)
                  </label>
                  <div className="relative">
                    <Phone size={16} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8E857F]" />
                    <input
                      type="text"
                      value={formData.phone || ""}
                      onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                      placeholder="+250 790 395 169"
                      className="w-full pl-10 pr-4 py-2.5 bg-[#FAF7F2] border border-[#EAE4D9] focus:border-[#221C1A] rounded-xl text-xs font-medium text-[#221C1A] outline-none transition-colors"
                    />
                  </div>
                  <span className="text-[10px] text-[#8E857F] block mt-1">
                    Formatted for client reading in header and footer.
                  </span>
                </div>

                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                    Quick Dial Number (tel: link)
                  </label>
                  <input
                    type="text"
                    value={formData.phone_raw || ""}
                    onChange={(e) => setFormData({ ...formData, phone_raw: e.target.value })}
                    placeholder="0790395169"
                    className="w-full px-4 py-2.5 bg-[#FAF7F2] border border-[#EAE4D9] focus:border-[#221C1A] rounded-xl text-xs font-mono text-[#221C1A] outline-none transition-colors"
                  />
                  <span className="text-[10px] text-[#8E857F] block mt-1">
                    Phone dial protocol number (e.g. 0790395169 or +250790395169).
                  </span>
                </div>

                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                    WhatsApp Concierge Hotline
                  </label>
                  <div className="relative">
                    <WhatsappLogo size={16} weight="fill" className="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#34A853]" />
                    <input
                      type="text"
                      value={formData.whatsapp || ""}
                      onChange={(e) => setFormData({ ...formData, whatsapp: e.target.value })}
                      placeholder="+250790395169"
                      className="w-full pl-10 pr-4 py-2.5 bg-[#FAF7F2] border border-[#EAE4D9] focus:border-[#221C1A] rounded-xl text-xs font-mono text-[#221C1A] outline-none transition-colors"
                    />
                  </div>
                  <span className="text-[10px] text-[#8E857F] block mt-1">
                    Opens WhatsApp chat with concierge (e.g. +250790395169).
                  </span>
                </div>

                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                    Public Concierge Email
                  </label>
                  <div className="relative">
                    <EnvelopeSimple size={16} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8E857F]" />
                    <input
                      type="email"
                      value={formData.email || ""}
                      onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                      placeholder="info@isaiahnailbar.com"
                      className="w-full pl-10 pr-4 py-2.5 bg-[#FAF7F2] border border-[#EAE4D9] focus:border-[#221C1A] rounded-xl text-xs text-[#221C1A] outline-none transition-colors"
                    />
                  </div>
                  <span className="text-[10px] text-[#8E857F] block mt-1">
                    Displayed on contact cards and footer.
                  </span>
                </div>
              </div>
            </div>

            {/* ========================================================= */}
            {/* CARD 2: PHYSICAL ADDRESS & OPERATING HOURS               */}
            {/* ========================================================= */}
            <div className="rounded-3xl border border-[#EAE4D9] bg-white p-6 sm:p-8 shadow-xs space-y-5">
              <div className="flex items-start gap-3 border-b border-[#FAF7F2] pb-4">
                <div className="w-10 h-10 rounded-2xl bg-[#FAF0E4] text-[#B8975A] flex items-center justify-center shrink-0">
                  <MapPin size={20} weight="fill" />
                </div>
                <div>
                  <h3 className="font-serif text-lg text-[#221C1A]">Sanctuary Address & Hours</h3>
                  <p className="text-xs text-[#6E6660] mt-0.5">
                    Location text, arrival landmarks, and visiting hours shown on the Homepage and Contact page.
                  </p>
                </div>
              </div>

              <div className="space-y-4">
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                    Physical Street Address
                  </label>
                  <input
                    type="text"
                    value={formData.address || ""}
                    onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                    placeholder="KG 4 Roundabout, Gisementi, Kigali, Rwanda"
                    className="w-full px-4 py-2.5 bg-[#FAF7F2] border border-[#EAE4D9] focus:border-[#221C1A] rounded-xl text-xs font-medium text-[#221C1A] outline-none transition-colors"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                    Landmark & Arrival Directions
                  </label>
                  <input
                    type="text"
                    value={formData.location_landmark || ""}
                    onChange={(e) => setFormData({ ...formData, location_landmark: e.target.value })}
                    placeholder="Opposite Sonatubes direction, with dedicated client parking"
                    className="w-full px-4 py-2.5 bg-[#FAF7F2] border border-[#EAE4D9] focus:border-[#221C1A] rounded-xl text-xs text-[#221C1A] outline-none transition-colors"
                  />
                  <span className="text-[10px] text-[#8E857F] block mt-1">
                    Additional hints to help guests locate the salon entrance and parking.
                  </span>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5 flex items-center gap-1.5">
                      <Clock size={13} weight="fill" className="text-[#B8975A]" />
                      Regular Operating Hours (Mon – Sat)
                    </label>
                    <input
                      type="text"
                      value={formData.opening_hours || ""}
                      onChange={(e) => setFormData({ ...formData, opening_hours: e.target.value })}
                      placeholder="Monday – Saturday: 08:00 AM – 08:00 PM"
                      className="w-full px-4 py-2.5 bg-[#FAF7F2] border border-[#EAE4D9] focus:border-[#221C1A] rounded-xl text-xs text-[#221C1A] outline-none transition-colors"
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5 flex items-center gap-1.5">
                      <Clock size={13} className="text-[#8E857F]" />
                      Sunday / Special Schedule
                    </label>
                    <input
                      type="text"
                      value={formData.opening_hours_sunday || ""}
                      onChange={(e) => setFormData({ ...formData, opening_hours_sunday: e.target.value })}
                      placeholder="Sunday: Closed (Private bookings upon request)"
                      className="w-full px-4 py-2.5 bg-[#FAF7F2] border border-[#EAE4D9] focus:border-[#221C1A] rounded-xl text-xs text-[#221C1A] outline-none transition-colors"
                    />
                  </div>
                </div>
              </div>
            </div>

            {/* ========================================================= */}
            {/* CARD 3: GOOGLE MAPS & NAVIGATION                         */}
            {/* ========================================================= */}
            <div className="rounded-3xl border border-[#EAE4D9] bg-white p-6 sm:p-8 shadow-xs space-y-5">
              <div className="flex items-start gap-3 border-b border-[#FAF7F2] pb-4">
                <div className="w-10 h-10 rounded-2xl bg-[#FAF0E4] text-[#B8975A] flex items-center justify-center shrink-0">
                  <NavigationArrow size={20} weight="fill" />
                </div>
                <div>
                  <h3 className="font-serif text-lg text-[#221C1A]">Google Maps Location & Embed</h3>
                  <p className="text-xs text-[#6E6660] mt-0.5">
                    Embed Google Maps directly on the homepage and contact page. You can paste either the clean embed URL or the full Google Maps &lt;iframe&gt; code.
                  </p>
                </div>
              </div>

              <div className="space-y-4">
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                    Google Maps Embed URL or &lt;iframe&gt; Tag (Smart Paste)
                  </label>
                  <textarea
                    rows={3}
                    value={formData.google_map_embed_url || ""}
                    onChange={(e) => handleMapInput(e.target.value)}
                    placeholder='https://www.google.com/maps/embed?pb=... or <iframe src="https://www.google.com/maps/embed?pb=..." ...></iframe>'
                    className="w-full px-4 py-2.5 bg-[#FAF7F2] border border-[#EAE4D9] focus:border-[#221C1A] rounded-xl text-xs font-mono text-[#221C1A] outline-none transition-colors"
                  />
                  <p className="text-[10px] text-[#8E857F] mt-1">
                    Tip: In Google Maps, click <strong>Share</strong> &rarr; <strong>Embed a map</strong> &rarr; <strong>Copy HTML</strong>, then paste it here directly. The URL is extracted automatically.
                  </p>
                </div>

                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5 flex items-center gap-1.5">
                    <Globe size={13} className="text-[#B8975A]" />
                    Direct Google Maps Directions Link (Mobile & Web)
                  </label>
                  <input
                    type="url"
                    value={formData.google_map_link || ""}
                    onChange={(e) => setFormData({ ...formData, google_map_link: e.target.value })}
                    placeholder="https://maps.google.com/?q=Isaiah+Nail+Bar+Gisementi+Kigali"
                    className="w-full px-4 py-2.5 bg-[#FAF7F2] border border-[#EAE4D9] focus:border-[#221C1A] rounded-xl text-xs font-mono text-[#221C1A] outline-none transition-colors"
                  />
                  <span className="text-[10px] text-[#8E857F] block mt-1">
                    Used for "Get Directions" buttons to open Google Maps app on smartphones.
                  </span>
                </div>

                {/* Live Map Preview Box */}
                <div className="pt-2">
                  <div className="flex items-center justify-between mb-2">
                    <span className="text-xs font-semibold uppercase tracking-wider text-[#221C1A] flex items-center gap-1.5">
                      <Eye size={14} className="text-[#B8975A]" />
                      Live Map Preview
                    </span>
                    <span className="text-[10px] text-[#8E857F]">
                      Confirm your pin location before saving
                    </span>
                  </div>

                  {formData.google_map_embed_url ? (
                    <div className="h-64 sm:h-80 w-full rounded-2xl overflow-hidden border border-[#EAE4D9] shadow-inner relative bg-[#FAF7F2]">
                      <iframe
                        src={formData.google_map_embed_url}
                        width="100%"
                        height="100%"
                        style={{ border: 0 }}
                        allowFullScreen={false}
                        loading="lazy"
                        referrerPolicy="no-referrer-when-downgrade"
                        title="Google Map Live Preview"
                      />
                    </div>
                  ) : (
                    <div className="h-44 rounded-2xl border-2 border-dashed border-[#EAE4D9] bg-[#FAF7F2] flex flex-col items-center justify-center p-6 text-center text-[#8E857F] space-y-1.5">
                      <NavigationArrow size={24} className="text-[#B8975A]" />
                      <p className="text-xs font-medium text-[#221C1A]">No Google Map URL Configured</p>
                      <p className="text-[11px] max-w-sm">
                        Paste your embed link above to preview your pinned salon location.
                      </p>
                    </div>
                  )}
                </div>
              </div>
            </div>

            {/* ========================================================= */}
            {/* CARD 4: BOOKING NOTIFICATION ALERT RECIPIENTS             */}
            {/* ========================================================= */}
            <div className="rounded-3xl border border-[#EAE4D9] bg-white p-6 sm:p-8 shadow-xs space-y-5">
              <div className="flex items-start gap-3 border-b border-[#FAF7F2] pb-4">
                <div className="w-10 h-10 rounded-2xl bg-[#FAF0E4] text-[#B8975A] flex items-center justify-center shrink-0">
                  <EnvelopeSimple size={20} weight="fill" />
                </div>
                <div>
                  <h3 className="font-serif text-lg text-[#221C1A]">Booking Alert Notification Emails</h3>
                  <p className="text-xs text-[#6E6660] mt-0.5">
                    Additional staff or management email addresses that receive automated booking confirmation copies.
                  </p>
                </div>
              </div>

              <div className="space-y-4">
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                    Alert Email Recipients (Comma Separated)
                  </label>
                  <textarea
                    rows={3}
                    value={formData.booking_alert_emails || ""}
                    onChange={(e) => setFormData({ ...formData, booking_alert_emails: e.target.value })}
                    placeholder="manager@isaiahnailbar.com, desk@isaiahnailbar.com"
                    className="w-full px-4 py-2.5 bg-[#FAF7F2] border border-[#EAE4D9] focus:border-[#221C1A] rounded-xl text-xs font-mono text-[#221C1A] outline-none transition-colors"
                  />
                  <span className="text-[10px] text-[#8E857F] block mt-1">
                    Separate multiple emails with commas.
                  </span>
                </div>

                {formData.email_addresses && formData.email_addresses.length > 0 && (
                  <div className="p-4 rounded-2xl bg-[#FAF7F2] border border-[#EAE4D9] space-y-2">
                    <p className="text-[10px] font-bold uppercase tracking-wider text-[#8E857F]">
                      Active Recipient Addresses
                    </p>
                    <div className="flex flex-wrap gap-1.5">
                      {formData.email_addresses.map((email) => (
                        <span
                          key={email}
                          className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white border border-[#EAE4D9] text-xs font-mono text-[#221C1A]"
                        >
                          <EnvelopeSimple size={12} className="text-[#B8975A]" />
                          <span>{email}</span>
                        </span>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            </div>

            {/* ========================================================= */}
            {/* SUBMIT BUTTON BAR                                         */}
            {/* ========================================================= */}
            <div className="flex items-center justify-end gap-3 pt-2">
              <button
                type="submit"
                disabled={saving}
                className="px-8 py-3.5 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-all shadow-md flex items-center gap-2 cursor-pointer disabled:opacity-50"
              >
                {saving ? (
                  <>
                    <ArrowsClockwise size={16} className="animate-spin" />
                    <span>Saving Settings...</span>
                  </>
                ) : (
                  <>
                    <Check size={16} weight="bold" />
                    <span>Save All Changes</span>
                  </>
                )}
              </button>
            </div>
          </form>
        )}
      </div>
    </div>
  );
}
