"use client";

import Link from "next/link";
import { ArrowRight, WhatsappLogo, CalendarCheck, PhoneCall } from "@phosphor-icons/react";

export default function ServiceBookingCta() {
  return (
    <section className="py-20 lg:py-24 bg-[#F5EFEB] border-t border-[#DFD6C7] relative">
      <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span className="text-[11px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-3">
          Reserve Your Ritual
        </span>
        <h2 className="font-serif text-3xl sm:text-5xl font-normal text-[#221C1A] mb-6 leading-tight">
          Ready for Bespoke Nail Artistry in Kigali?
        </h2>
        <p className="text-base sm:text-lg text-[#5C5550] font-light max-w-2xl mx-auto mb-10 leading-relaxed">
          Book online in under 60 seconds with instant confirmation, or chat directly with our concierge team for custom appointments.
        </p>

        <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
          <Link
            href="/booking"
            className="group w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-0.5"
          >
            <CalendarCheck size={18} weight="bold" />
            <span>Book Online Now</span>
            <ArrowRight size={15} className="group-hover:translate-x-1 transition-transform" />
          </Link>

          <a
            href="https://wa.me/250790395169?text=Hello%20Isaiah%20Nail%20Bar,%20I%20would%20like%20to%20inquire%20about%20booking%20a%20nail%20service."
            target="_blank"
            rel="noopener noreferrer"
            className="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white border border-[#DFD6C7] hover:border-[#221C1A] hover:bg-[#FAF7F2] transition-all duration-300 shadow-xs"
          >
            <WhatsappLogo size={18} weight="fill" className="text-[#25D366]" />
            <span>Chat on WhatsApp</span>
          </a>
        </div>

        <div className="mt-8 flex items-center justify-center gap-2 text-xs text-[#8E857F]">
          <PhoneCall size={14} />
          <span>Concierge Hotline: <strong>+250 790 395 169</strong> &bull; Open Mon&ndash;Sat 08:00 AM &ndash; 08:00 PM</span>
        </div>
      </div>
    </section>
  );
}
