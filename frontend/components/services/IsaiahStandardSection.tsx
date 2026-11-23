"use client";

import { motion } from "framer-motion";
import { ShieldCheck, Drop, SealCheck, ClockCountdown } from "@phosphor-icons/react";

const standards = [
  {
    icon: ShieldCheck,
    title: "Hospital Autoclave Sterilization",
    description: "Every metal drill bit and implement undergoes medical-grade pouch sterilization. Files and buffers are 100% single-use.",
  },
  {
    icon: Drop,
    title: "10-Free & Vegan Formulations",
    description: "We strictly avoid harsh formaldehyde, DBP, toluene, and camphor, nourishing the biological health of your natural nails.",
  },
  {
    icon: SealCheck,
    title: "Zero-Lifting 4-Week Retention",
    description: "Through microscopic Russian cuticle dry preparation and apex balancing, your gel or extensions stay secure for weeks.",
  },
  {
    icon: ClockCountdown,
    title: "Unhurried, Dedicated Craft",
    description: "No rushed assembly-line treatments. Each guest receives undivided artist focus in our serene, sunlit Gisementi sanctuary.",
  },
];

export default function IsaiahStandardSection() {
  return (
    <section className="py-20 lg:py-24 bg-[#EFE8DD] border-y border-[#DDD2C2] relative">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center max-w-2xl mx-auto mb-16">
          <span className="text-[11px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-3">
            The Isaiah Atelier Standard
          </span>
          <h2 className="font-serif text-3xl sm:text-4xl lg:text-5xl font-normal text-[#221C1A] mb-4">
            Uncompromising Excellence
          </h2>
          <p className="text-sm sm:text-base text-[#5C5550] font-light leading-relaxed">
            Every service at Isaiah Nail Bar is performed in strict adherence to European hygiene standards, biological nail care, and precision aesthetics.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {standards.map((std, idx) => {
            const Icon = std.icon;
            return (
              <motion.div
                key={idx}
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.5, delay: idx * 0.1 }}
                className="bg-white rounded-3xl p-6 sm:p-7 border border-[#DDD3C2] shadow-xs flex flex-col justify-between"
              >
                <div>
                  <div className="w-12 h-12 rounded-2xl bg-[#FAF7F2] border border-[#B8975A]/25 text-[#B8975A] flex items-center justify-center mb-5 shadow-2xs">
                    <Icon size={24} weight="bold" />
                  </div>
                  <h3 className="font-serif text-lg text-[#221C1A] font-semibold mb-2">
                    {std.title}
                  </h3>
                  <p className="text-xs sm:text-sm text-[#5C5550] font-light leading-relaxed">
                    {std.description}
                  </p>
                </div>
              </motion.div>
            );
          })}
        </div>
      </div>
    </section>
  );
}
