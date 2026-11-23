"use client";

import Link from "next/link";
import { motion } from "framer-motion";
import { MapPin, Clock, WhatsappLogo, CalendarCheck, NavigationArrow } from "@phosphor-icons/react";
import { useSiteSettings } from "@/lib/settings-context";

export default function VisitUsSection() {
  const { settings } = useSiteSettings();
  return (
    <section className="relative bg-[#F5EFEB] border-t border-[#DFD6C7]">
      <div className="grid grid-cols-1 lg:grid-cols-2">
        {/* Left Column: Details & CTAs */}
        <motion.div
          initial={{ opacity: 0, x: -20 }}
          whileInView={{ opacity: 1, x: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.7, ease: "easeOut" }}
          className="bg-[#F5EFEB] px-8 py-20 lg:p-24 flex flex-col justify-center border-b lg:border-b-0 lg:border-r border-[#DFD6C7]"
        >
          <div className="max-w-md">
            <span className="text-[11px] font-semibold tracking-[0.24em] uppercase text-[#C4847A] block mb-3">
              Kigali Salon &bull; Gisementi
            </span>

            <h2 className="text-3xl sm:text-4xl md:text-5xl font-serif text-[#221C1A] mb-8 font-normal leading-tight">
              Your Sanctuary <br />
              Awaits You
            </h2>

            <div className="space-y-6 mb-10">
              {/* Location */}
              <div className="flex items-start gap-4">
                <div className="w-11 h-11 rounded-2xl bg-[#FAF7F2] border border-[#B8975A]/25 flex items-center justify-center shadow-sm shrink-0 text-[#C4847A]">
                  <MapPin size={20} weight="fill" />
                </div>
                <div>
                  <h4 className="font-serif font-semibold text-sm text-[#221C1A]">
                    Sanctuary Location
                  </h4>
                  <p className="text-xs text-[#5C5550] font-light mt-0.5 leading-relaxed">
                    {settings.address}
                  </p>
                  {settings.location_landmark && (
                    <p className="text-[11px] text-[#8E857F] mt-0.5">
                      {settings.location_landmark}
                    </p>
                  )}
                  {settings.google_map_link && (
                    <a
                      href={settings.google_map_link}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center gap-1 text-[11px] font-semibold text-[#B8975A] hover:underline mt-1.5"
                    >
                      <NavigationArrow size={12} weight="fill" />
                      <span>Get Directions on Google Maps</span>
                    </a>
                  )}
                </div>
              </div>

              {/* Hours */}
              <div className="flex items-start gap-4">
                <div className="w-11 h-11 rounded-2xl bg-[#FAF7F2] border border-[#B8975A]/25 flex items-center justify-center shadow-sm shrink-0 text-[#B8975A]">
                  <Clock size={20} weight="fill" />
                </div>
                <div>
                  <h4 className="font-serif font-semibold text-sm text-[#221C1A]">
                    Opening Hours
                  </h4>
                  <p className="text-xs text-[#5C5550] font-light mt-0.5">
                    {settings.opening_hours}
                  </p>
                  {settings.opening_hours_sunday && (
                    <p className="text-xs text-[#8E857F] font-light">
                      {settings.opening_hours_sunday}
                    </p>
                  )}
                </div>
              </div>
            </div>

            {/* CTAs */}
            <div className="flex flex-col sm:flex-row gap-4">
              <Link
                href="/booking"
                className="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#C4847A] transition-all duration-300 shadow-md hover:shadow-[0_8px_25px_rgba(196,132,122,0.4)] hover:-translate-y-0.5"
              >
                <CalendarCheck size={16} />
                <span>Book Appointment</span>
              </Link>

              <a
                href={settings.whatsapp_url}
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] border border-[#B8975A]/40 hover:border-[#6B7C6E] hover:text-[#6B7C6E] transition-all bg-[#FAF7F2] hover:bg-white shadow-sm"
              >
                <WhatsappLogo size={18} weight="fill" className="text-[#6B7C6E]" />
                <span>WhatsApp Us</span>
              </a>
            </div>
          </div>
        </motion.div>

        {/* Right Column: Google Maps Embed */}
        <div className="h-96 lg:h-auto min-h-[420px] bg-[#FAF7F2] relative">
          <iframe
            src={settings.google_map_embed_url}
            width="100%"
            height="100%"
            style={{ border: 0 }}
            allowFullScreen={false}
            loading="lazy"
            referrerPolicy="no-referrer-when-downgrade"
            className="absolute inset-0 opacity-95 transition-opacity"
            title="Isaiah Nail Bar Sanctuary Map"
          />
        </div>
      </div>
    </section>
  );
}
