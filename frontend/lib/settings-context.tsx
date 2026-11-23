"use client";

import React, { createContext, useContext, useEffect, useState } from "react";

export interface SiteSettings {
  salon_name: string;
  phone: string;
  phone_raw: string;
  whatsapp: string;
  email: string;
  address: string;
  location_landmark: string;
  opening_hours: string;
  opening_hours_sunday: string;
  google_map_embed_url: string;
  google_map_link: string;
  currency: string;
  instagram: string;
  facebook: string;
  google_review_url: string;
  tel_url: string;
  whatsapp_url: string;
}

export const DEFAULT_SITE_SETTINGS: SiteSettings = {
  salon_name: "Isaiah Nail Bar",
  phone: "+250 790 395 169",
  phone_raw: "0790395169",
  whatsapp: "+250790395169",
  email: "info@isaiahnailbar.com",
  address: "KG 4 Roundabout, Gisementi, Kigali, Rwanda",
  location_landmark: "Opposite Sonatubes direction, with dedicated client parking",
  opening_hours: "Monday – Saturday: 08:00 AM – 08:00 PM",
  opening_hours_sunday: "Sunday: Closed (Private bookings upon request)",
  google_map_embed_url: "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15950.019185121285!2d30.108678!3d-1.954736!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x19dca6ee46244f67%3A0x6291244078657662!2sKisimenti!5e0!3m2!1sen!2srw!4v1683838383838!5m2!1sen!2srw",
  google_map_link: "https://maps.google.com/?q=Isaiah+Nail+Bar+Gisementi+Kigali",
  currency: "RWF",
  instagram: "https://www.instagram.com/isaiah_nails_art_kigali_rwanda",
  facebook: "https://www.facebook.com/isaiahnailbar",
  google_review_url: "https://g.page/r/CS4QpNuz_MJkEAE/review",
  tel_url: "tel:0790395169",
  whatsapp_url: "https://wa.me/250790395169",
};

interface SettingsContextValue {
  settings: SiteSettings;
  loading: boolean;
  refreshSettings: () => Promise<void>;
}

const SettingsContext = createContext<SettingsContextValue>({
  settings: DEFAULT_SITE_SETTINGS,
  loading: false,
  refreshSettings: async () => {},
});

export function SettingsProvider({ children }: { children: React.ReactNode }) {
  const [settings, setSettings] = useState<SiteSettings>(DEFAULT_SITE_SETTINGS);
  const [loading, setLoading] = useState(true);

  const fetchSettings = async () => {
    try {
      const res = await fetch("/api/v1/settings", { cache: "no-store" });
      if (!res.ok) return;
      const json = await res.json();
      if (json.success && json.data) {
        const raw = json.data;
        const cleanPhoneRaw = (raw.phone_raw || raw.phone || DEFAULT_SITE_SETTINGS.phone_raw).replace(/[^\d+]/g, "");
        const cleanWa = (raw.whatsapp || DEFAULT_SITE_SETTINGS.whatsapp).replace(/[^\d]/g, "");

        setSettings({
          ...DEFAULT_SITE_SETTINGS,
          ...raw,
          tel_url: `tel:${cleanPhoneRaw}`,
          whatsapp_url: `https://wa.me/${cleanWa}`,
        });
      }
    } catch {
      // Retain defaults gracefully
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchSettings();
  }, []);

  return (
    <SettingsContext.Provider value={{ settings, loading, refreshSettings: fetchSettings }}>
      {children}
    </SettingsContext.Provider>
  );
}

export function useSiteSettings() {
  return useContext(SettingsContext);
}
