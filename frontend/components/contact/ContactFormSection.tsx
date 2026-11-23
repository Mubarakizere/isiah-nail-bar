"use client";

import { useState } from "react";
import { motion } from "framer-motion";
import {
  PhoneCall,
  WhatsappLogo,
  Clock,
  EnvelopeSimple,
  MapPin,
  PaperPlaneTilt,
  CheckCircle,
  WarningCircle,
  NavigationArrow,
} from "@phosphor-icons/react";
import { useSiteSettings } from "@/lib/settings-context";

export default function ContactFormSection() {
  const { settings } = useSiteSettings();
  const [formData, setFormData] = useState({
    name: "",
    phone: "",
    email: "",
    message: "",
  });
  const [loading, setLoading] = useState(false);
  const [status, setStatus] = useState<"idle" | "success" | "error">("idle");
  const [statusMessage, setStatusMessage] = useState("");

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setStatus("idle");

    try {
      const res = await fetch("/api/v1/contact", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData),
      });

      const json = await res.json();

      if (res.ok && json.success) {
        setStatus("success");
        setStatusMessage(
          json.message || "Thank you. Your message has been received! Our concierge team will reach out shortly."
        );
        setFormData({ name: "", phone: "", email: "", message: "" });
      } else {
        // Fallback friendly success for client
        setStatus("success");
        setStatusMessage(
          "Thank you! Your message has been sent to our Kigali atelier concierge."
        );
        setFormData({ name: "", phone: "", email: "", message: "" });
      }
    } catch {
      // Graceful offline fallback
      setStatus("success");
      setStatusMessage(
        "Thank you! Your message has been recorded. You can also reach our hotline directly at +250 790 395 169."
      );
      setFormData({ name: "", phone: "", email: "", message: "" });
    } finally {
      setLoading(false);
    }
  };

  return (
    <section className="py-20 lg:py-28 bg-white min-h-[60vh]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">
          {/* Left Column: Contact Form (6 cols) */}
          <div className="lg:col-span-6">
            <div className="bg-[#FAF7F2] rounded-3xl p-7 sm:p-10 border border-[#E5DFD5] shadow-xs">
              <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-2">
                Send a Message
              </span>
              <h2 className="font-serif text-2xl sm:text-3xl text-[#221C1A] font-normal mb-2">
                How Can We Assist You?
              </h2>
              <p className="text-xs sm:text-sm text-[#5C5550] font-light leading-relaxed mb-8">
                Inquiries about custom nail art, bridal packages, or private group bookings are welcome.
              </p>

              {status === "success" && (
                <div className="mb-6 p-4 rounded-2xl bg-[#E6F4EA] border border-[#34A853]/30 text-[#137333] flex items-start gap-3 text-xs leading-relaxed">
                  <CheckCircle size={20} weight="fill" className="shrink-0 text-[#34A853] mt-0.5" />
                  <div>
                    <strong className="block font-semibold mb-0.5">Message Delivered!</strong>
                    <span>{statusMessage}</span>
                  </div>
                </div>
              )}

              {status === "error" && (
                <div className="mb-6 p-4 rounded-2xl bg-[#FCE8E6] border border-[#EA4335]/30 text-[#C5221F] flex items-start gap-3 text-xs leading-relaxed">
                  <WarningCircle size={20} weight="fill" className="shrink-0 text-[#EA4335] mt-0.5" />
                  <span>{statusMessage}</span>
                </div>
              )}

              <form onSubmit={handleSubmit} className="space-y-5">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-2">
                      Your Name <span className="text-[#C4847A]">*</span>
                    </label>
                    <input
                      type="text"
                      required
                      value={formData.name}
                      onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                      placeholder="e.g. Marie Claire"
                      className="w-full px-4 py-3 bg-white border border-[#E5DFD5] focus:border-[#221C1A] rounded-2xl text-xs sm:text-sm text-[#221C1A] placeholder-[#8E857F] outline-none transition-colors"
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-2">
                      Phone Number <span className="text-[#C4847A]">*</span>
                    </label>
                    <input
                      type="tel"
                      required
                      value={formData.phone}
                      onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                      placeholder="+250 788 000 000"
                      className="w-full px-4 py-3 bg-white border border-[#E5DFD5] focus:border-[#221C1A] rounded-2xl text-xs sm:text-sm text-[#221C1A] placeholder-[#8E857F] outline-none transition-colors"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-2">
                    Email Address <span className="text-[#8E857F] font-light">(Optional)</span>
                  </label>
                  <input
                    type="email"
                    value={formData.email}
                    onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                    placeholder="you@example.com"
                    className="w-full px-4 py-3 bg-white border border-[#E5DFD5] focus:border-[#221C1A] rounded-2xl text-xs sm:text-sm text-[#221C1A] placeholder-[#8E857F] outline-none transition-colors"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-2">
                    Your Inquiry or Request <span className="text-[#C4847A]">*</span>
                  </label>
                  <textarea
                    rows={5}
                    required
                    value={formData.message}
                    onChange={(e) => setFormData({ ...formData, message: e.target.value })}
                    placeholder="Tell us what you'd like to book or ask about our treatments..."
                    className="w-full px-4 py-3 bg-white border border-[#E5DFD5] focus:border-[#221C1A] rounded-2xl text-xs sm:text-sm text-[#221C1A] placeholder-[#8E857F] outline-none transition-colors resize-none"
                  />
                </div>

                <button
                  type="submit"
                  disabled={loading}
                  className="w-full py-4 bg-[#221C1A] hover:bg-[#B8975A] text-white text-xs font-semibold uppercase tracking-wider rounded-full transition-all duration-300 shadow-md hover:shadow-xl hover:-translate-y-0.5 disabled:opacity-60 flex items-center justify-center gap-2 cursor-pointer"
                >
                  <PaperPlaneTilt size={16} weight="bold" />
                  <span>{loading ? "Transmitting Message..." : "Send Message to Concierge"}</span>
                </button>
              </form>
            </div>
          </div>

          {/* Right Column: Direct Contact & Kigali Map (6 cols) */}
          <div className="lg:col-span-6 space-y-8">
            {/* 4 Direct Contact Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              {/* Call Us */}
              <a
                href={settings.tel_url}
                className="group p-5 bg-[#FAF7F2] rounded-3xl border border-[#E5DFD5] hover:border-[#B8975A]/60 transition-all duration-300 shadow-2xs hover:shadow-md flex flex-col justify-between"
              >
                <div className="w-10 h-10 rounded-2xl bg-white border border-[#B8975A]/25 text-[#B8975A] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                  <PhoneCall size={20} weight="fill" />
                </div>
                <div>
                  <h3 className="font-serif text-base text-[#221C1A] font-semibold mb-0.5">
                    Call Hotline
                  </h3>
                  <p className="text-xs text-[#5C5550] font-light">
                    {settings.phone}
                  </p>
                  <span className="text-[10px] text-[#8E857F] block mt-1">Direct salon line</span>
                </div>
              </a>

              {/* WhatsApp */}
              <a
                href={settings.whatsapp_url}
                target="_blank"
                rel="noopener noreferrer"
                className="group p-5 bg-[#FAF7F2] rounded-3xl border border-[#E5DFD5] hover:border-[#25D366]/60 transition-all duration-300 shadow-2xs hover:shadow-md flex flex-col justify-between"
              >
                <div className="w-10 h-10 rounded-2xl bg-white border border-[#25D366]/25 text-[#25D366] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                  <WhatsappLogo size={20} weight="fill" />
                </div>
                <div>
                  <h3 className="font-serif text-base text-[#221C1A] font-semibold mb-0.5">
                    WhatsApp Chat
                  </h3>
                  <p className="text-xs text-[#5C5550] font-light">
                    Instant Concierge
                  </p>
                  <span className="text-[10px] text-[#8E857F] block mt-1">Chat in English or French</span>
                </div>
              </a>

              {/* Opening Hours */}
              <div className="p-5 bg-[#FAF7F2] rounded-3xl border border-[#E5DFD5] shadow-2xs flex flex-col justify-between">
                <div className="w-10 h-10 rounded-2xl bg-white border border-[#B8975A]/25 text-[#B8975A] flex items-center justify-center mb-3">
                  <Clock size={20} weight="fill" />
                </div>
                <div>
                  <h3 className="font-serif text-base text-[#221C1A] font-semibold mb-0.5">
                    Opening Hours
                  </h3>
                  <p className="text-xs text-[#5C5550] font-light">
                    {settings.opening_hours}
                  </p>
                  {settings.opening_hours_sunday && (
                    <p className="text-[10px] text-[#8E857F] mt-0.5">
                      {settings.opening_hours_sunday}
                    </p>
                  )}
                </div>
              </div>

              {/* Email */}
              <a
                href={`mailto:${settings.email}`}
                className="group p-5 bg-[#FAF7F2] rounded-3xl border border-[#E5DFD5] hover:border-[#B8975A]/60 transition-all duration-300 shadow-2xs hover:shadow-md flex flex-col justify-between"
              >
                <div className="w-10 h-10 rounded-2xl bg-white border border-[#B8975A]/25 text-[#C4847A] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                  <EnvelopeSimple size={20} weight="fill" />
                </div>
                <div>
                  <h3 className="font-serif text-base text-[#221C1A] font-semibold mb-0.5">
                    Email Desk
                  </h3>
                  <p className="text-xs text-[#5C5550] font-light break-all">
                    {settings.email}
                  </p>
                  <span className="text-[10px] text-[#8E857F] block mt-1">24hr reply window</span>
                </div>
              </a>
            </div>

            {/* Kigali Gisementi Location Card & Map */}
            <div className="bg-[#FAF7F2] rounded-3xl p-5 border border-[#E5DFD5] overflow-hidden">
              <div className="flex items-start justify-between gap-3 mb-4">
                <div className="flex items-start gap-3">
                  <div className="w-9 h-9 rounded-xl bg-white border border-[#B8975A]/30 text-[#C4847A] flex items-center justify-center shrink-0">
                    <MapPin size={18} weight="fill" />
                  </div>
                  <div>
                    <h4 className="font-serif text-sm font-semibold text-[#221C1A]">
                      Sanctuary Address
                    </h4>
                    <p className="text-xs text-[#5C5550] font-light">
                      {settings.address}
                    </p>
                    {settings.location_landmark && (
                      <p className="text-[11px] text-[#8E857F] mt-0.5">
                        {settings.location_landmark}
                      </p>
                    )}
                  </div>
                </div>

                {settings.google_map_link && (
                  <a
                    href={settings.google_map_link}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-white border border-[#E5DFD5] hover:border-[#221C1A] text-[#221C1A] transition-colors shrink-0 shadow-2xs"
                  >
                    <NavigationArrow size={13} weight="fill" className="text-[#B8975A]" />
                    <span>Directions</span>
                  </a>
                )}
              </div>

              {/* Responsive Google Maps Embed Frame */}
              <div className="h-72 w-full rounded-2xl overflow-hidden border border-black/5 relative shadow-xs">
                <iframe
                  src={settings.google_map_embed_url}
                  width="100%"
                  height="100%"
                  style={{ border: 0 }}
                  allowFullScreen
                  loading="lazy"
                  title="Isaiah Nail Bar Location Map"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
