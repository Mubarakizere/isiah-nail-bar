"use client";

import Link from "next/link";
import Image from "next/image";
import {
  InstagramLogo,
  FacebookLogo,
  WhatsappLogo,
  GoogleLogo,
  MapPin,
  Phone,
  EnvelopeSimple,
  Clock,
  ArrowUpRight,
} from "@phosphor-icons/react";
import { useSiteSettings } from "@/lib/settings-context";

export default function Footer() {
  const { settings } = useSiteSettings();
  return (
    <footer className="bg-[#ECE3D5] text-[#4A423D] border-t border-[#D9CEBC] pt-20 pb-12">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
          {/* Brand Info */}
          <div className="space-y-6 lg:pr-4">
            <div className="flex items-center gap-3.5">
              <div className="relative h-12 w-12 rounded-xl overflow-hidden bg-white/90 p-0.5 border border-[#B8975A]/30 shadow-xs">
                <Image
                  src="/logo.png"
                  alt="Isaiah Nail Bar"
                  fill
                  sizes="48px"
                  className="object-contain"
                />
              </div>
              <div>
                <span className="font-serif text-xl font-bold text-[#221C1A] tracking-tight block leading-tight">
                  Isaiah Nail Bar
                </span>
                <span className="text-[10px] tracking-[0.22em] uppercase text-[#8E857F] font-medium block">
                  Kigali &bull; Rwanda
                </span>
              </div>
            </div>
            <p className="text-sm leading-relaxed text-[#6E6662] font-light">
              Rwanda&apos;s premier boutique nail sanctuary. Dedicated to the craft of exceptional nail artistry, rejuvenating rituals, and bespoke beauty experiences in Kigali.
            </p>
            <div className="flex items-center gap-3 pt-2">
              <a
                href={settings.instagram || "https://www.instagram.com/isaiah_nails_art_kigali_rwanda"}
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Instagram"
                className="w-10 h-10 rounded-full bg-white border border-[#B8975A]/25 flex items-center justify-center text-[#221C1A] hover:bg-[#C4847A] hover:text-white hover:border-[#C4847A] transition-all duration-300 shadow-sm"
              >
                <InstagramLogo size={18} />
              </a>
              <a
                href={settings.facebook || "https://www.facebook.com/isaiahnailbar"}
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Facebook"
                className="w-10 h-10 rounded-full bg-white border border-[#B8975A]/25 flex items-center justify-center text-[#221C1A] hover:bg-[#C4847A] hover:text-white hover:border-[#C4847A] transition-all duration-300 shadow-sm"
              >
                <FacebookLogo size={18} />
              </a>
              <a
                href={settings.whatsapp_url}
                target="_blank"
                rel="noopener noreferrer"
                aria-label="WhatsApp"
                className="w-10 h-10 rounded-full bg-white border border-[#6B7C6E]/30 flex items-center justify-center text-[#6B7C6E] hover:bg-[#6B7C6E] hover:text-white hover:border-[#6B7C6E] transition-all duration-300 shadow-sm"
              >
                <WhatsappLogo size={18} />
              </a>
              <a
                href={settings.google_review_url || "https://g.page/r/CS4QpNuz_MJkEAE/review"}
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Google Review"
                className="w-10 h-10 rounded-full bg-white border border-[#B8975A]/25 flex items-center justify-center text-[#B8975A] hover:bg-[#B8975A] hover:text-white hover:border-[#B8975A] transition-all duration-300 shadow-sm"
              >
                <GoogleLogo size={18} />
              </a>
            </div>
          </div>

          {/* Navigation Links */}
          <div>
            <h4 className="text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-6">
              Explore
            </h4>
            <ul className="space-y-3 text-sm font-light">
              <li>
                <Link href="/services" className="hover:text-[#C4847A] transition-colors flex items-center justify-between group">
                  <span>Our Services</span>
                  <ArrowUpRight size={14} className="opacity-0 group-hover:opacity-100 transition-opacity text-[#B8975A]" />
                </Link>
              </li>
              <li>
                <Link href="/gallery" className="hover:text-[#C4847A] transition-colors flex items-center justify-between group">
                  <span>Inspiration Gallery</span>
                  <ArrowUpRight size={14} className="opacity-0 group-hover:opacity-100 transition-opacity text-[#B8975A]" />
                </Link>
              </li>
              <li>
                <Link href="/about" className="hover:text-[#C4847A] transition-colors flex items-center justify-between group">
                  <span>The Salon & Story</span>
                  <ArrowUpRight size={14} className="opacity-0 group-hover:opacity-100 transition-opacity text-[#B8975A]" />
                </Link>
              </li>
              <li>
                <Link href="/providers" className="hover:text-[#C4847A] transition-colors flex items-center justify-between group">
                  <span>Master Artists</span>
                  <ArrowUpRight size={14} className="opacity-0 group-hover:opacity-100 transition-opacity text-[#B8975A]" />
                </Link>
              </li>
              <li>
                <Link href="/booking" className="hover:text-[#C4847A] transition-colors flex items-center justify-between group">
                  <span>Book Appointment</span>
                  <ArrowUpRight size={14} className="opacity-0 group-hover:opacity-100 transition-opacity text-[#B8975A]" />
                </Link>
              </li>
            </ul>
          </div>

          {/* Salon Hours */}
          <div>
            <h4 className="font-serif text-base text-[#221C1A] tracking-wide mb-6 uppercase text-xs font-semibold flex items-center gap-1.5">
              <Clock size={14} weight="fill" className="text-[#B8975A]" />
              Opening Hours
            </h4>
            <ul className="space-y-3 text-sm font-light">
              <li className="flex items-start gap-3">
                <div className="w-6 h-6 rounded-full bg-[#B8975A]/15 flex items-center justify-center shrink-0 mt-0.5 text-[#B8975A]">
                  <Clock size={13} weight="fill" />
                </div>
                <div>
                  <span className="text-[#221C1A] block font-medium">Monday – Saturday</span>
                  <span className="text-[#8E857F] text-xs">08:00 AM – 08:00 PM</span>
                </div>
              </li>
              <li className="flex items-start gap-3">
                <div className="w-6 h-6 rounded-full bg-[#B8975A]/15 flex items-center justify-center shrink-0 mt-0.5 text-[#B8975A]">
                  <Clock size={13} weight="fill" />
                </div>
                <div>
                  <span className="text-[#221C1A] block font-medium">Sunday</span>
                  <span className="text-[#8E857F] text-xs">10:00 AM – 06:00 PM (By Request)</span>
                </div>
              </li>
              <li className="pt-2 text-xs text-[#8E857F]">
                Home service & private bridal bookings available on request.
              </li>
            </ul>
          </div>

          {/* Sanctuary Contact */}
          <div>
            <h4 className="font-serif text-base text-[#221C1A] tracking-wide mb-6 uppercase text-xs font-semibold flex items-center gap-1.5">
              <MapPin size={14} weight="fill" className="text-[#C4847A]" />
              Sanctuary
            </h4>
            <ul className="space-y-4 text-sm font-light">
              <li className="flex items-start gap-3">
                <MapPin size={18} className="text-[#C4847A] shrink-0 mt-0.5" weight="fill" />
                <span className="text-xs leading-relaxed text-[#6E6662]">
                  {settings.address}
                </span>
              </li>
              <li className="flex items-center gap-3">
                <Phone size={18} className="text-[#C4847A] shrink-0" weight="fill" />
                <a href={settings.tel_url} className="text-xs hover:text-[#C4847A] transition-colors text-[#6E6662]">
                  {settings.phone}
                </a>
              </li>
              <li className="flex items-center gap-3">
                <EnvelopeSimple size={18} className="text-[#C4847A] shrink-0" weight="fill" />
                <a href={`mailto:${settings.email}`} className="text-xs hover:text-[#C4847A] transition-colors text-[#6E6662]">
                  {settings.email}
                </a>
              </li>
            </ul>
          </div>
        </div>

        {/* Bottom Bar */}
        <div className="pt-8 border-t border-[#B8975A]/15 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[#8E857F]">
          <p>© {new Date().getFullYear()} Isaiah Nail Bar. All rights reserved.</p>
          <div className="flex items-center gap-6">
            <Link href="/faq" className="hover:text-[#221C1A] transition-colors">
              FAQ
            </Link>
            <Link href="/contact" className="hover:text-[#221C1A] transition-colors">
              Contact
            </Link>
            <a
              href="https://g.page/r/CS4QpNuz_MJkEAE/review"
              target="_blank"
              rel="noopener noreferrer"
              className="text-[#5C5550] hover:text-[#C4847A] flex items-center gap-1.5 font-medium transition-colors"
            >
              <GoogleLogo size={14} />
              <span>Review on Google</span>
            </a>
          </div>
        </div>
      </div>
    </footer>
  );
}
