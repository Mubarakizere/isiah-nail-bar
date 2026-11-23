"use client";

import { motion } from "framer-motion";
import {
  Certificate,
  Drop,
  CurrencyCircleDollar,
  ClockCountdown,
  MapPin,
} from "@phosphor-icons/react";

const commitments = [
  {
    icon: Certificate,
    title: "European-Trained Artists",
    desc: "Our nail artists undergo extensive training in Russian dry e-file safety, cuticle biology, and precision apex alignment.",
  },
  {
    icon: Drop,
    title: "10-Free Botanical Formulations",
    desc: "We exclusively curate non-toxic polishes and restorative plant-derived cuticular oils that nourish your natural nail plate.",
  },
  {
    icon: CurrencyCircleDollar,
    title: "Transparent Kigali Pricing",
    desc: "All treatment fees are clear, transparent in RWF, and include full sterilisation and grooming without surprise checkout add-ons.",
  },
  {
    icon: ClockCountdown,
    title: "Unhurried Dedicated Sessions",
    desc: "We never rush through an appointment. Each guest receives ample dedicated time for consultation, shaping, and finish.",
  },
];

export default function AboutAtelierSection() {
  return (
    <section className="py-20 lg:py-28 bg-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
          {/* Left Column: Heading & Location card (5 cols) */}
          <div className="lg:col-span-5 space-y-6">
            <span className="text-[11px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-2">
              The Isaiah Standard
            </span>
            <h2 className="font-serif text-3xl sm:text-4xl lg:text-5xl font-normal text-[#221C1A] leading-tight">
              An Atelier Built on Trust &amp; Craftsmanship
            </h2>
            <p className="text-sm sm:text-base text-[#5C5550] font-light leading-relaxed">
              We founded Isaiah Nail Bar to set a new benchmark for nail salons in East Africa. Our focus is never on volume, but on the enduring quality and hygiene of every ritual.
            </p>

            <div className="p-5 rounded-2xl bg-[#FAF7F2] border border-[#E5DFD5] flex items-start gap-4">
              <div className="w-10 h-10 rounded-xl bg-white border border-[#B8975A]/30 text-[#C4847A] flex items-center justify-center shrink-0 shadow-2xs">
                <MapPin size={20} weight="fill" />
              </div>
              <div>
                <h4 className="font-serif font-semibold text-sm text-[#221C1A]">
                  Convenient Gisementi Location
                </h4>
                <p className="text-xs text-[#5C5550] font-light mt-0.5 leading-relaxed">
                  Located at KG 4 Roundabout, Gisementi, with private parking and sunlit, calm treatment rooms.
                </p>
              </div>
            </div>
          </div>

          {/* Right Column: 4 Commitments Grid (7 cols) */}
          <div className="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-6">
            {commitments.map((item, idx) => {
              const Icon = item.icon;
              return (
                <motion.div
                  key={idx}
                  initial={{ opacity: 0, y: 15 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ duration: 0.4, delay: idx * 0.1 }}
                  className="bg-[#FAF7F2] rounded-3xl p-6 sm:p-7 border border-[#E5DFD5] flex flex-col justify-between"
                >
                  <div>
                    <div className="w-11 h-11 rounded-2xl bg-white border border-[#B8975A]/25 text-[#B8975A] flex items-center justify-center mb-4 shadow-2xs">
                      <Icon size={22} weight="bold" />
                    </div>
                    <h3 className="font-serif text-base font-semibold text-[#221C1A] mb-2">
                      {item.title}
                    </h3>
                    <p className="text-xs text-[#5C5550] font-light leading-relaxed">
                      {item.desc}
                    </p>
                  </div>
                </motion.div>
              );
            })}
          </div>
        </div>
      </div>
    </section>
  );
}
