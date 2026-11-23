"use client";

import { motion, type Variants } from "framer-motion";
import {
  DiamondsFour,
  Drop,
  ShieldCheck,
  Armchair,
  CheckCircle,
} from "@phosphor-icons/react";

const features = [
  {
    icon: DiamondsFour,
    title: "Precision Artistry",
    desc: "Master technicians dedicated to meticulous precision, clean cuticle lines, and durable nail artistry.",
  },
  {
    icon: Drop,
    title: "Nourishing Formulations",
    desc: "We exclusively curate non-toxic polishes, restorative cuticle botanicals, and gentle removal rituals.",
  },
  {
    icon: ShieldCheck,
    title: "Hospital-Grade Hygiene",
    desc: "Full autoclave sterilization for metal instruments, single-use buffers, and sanitized pedicure baths.",
  },
  {
    icon: Armchair,
    title: "Unhurried Sanctuary",
    desc: "A calm space in Gisementi crafted with ergonomic seating, peaceful soundscapes, and herbal refreshments.",
  },
];

const containerVariants: Variants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: { staggerChildren: 0.12 },
  },
};

const itemVariants: Variants = {
  hidden: { opacity: 0, y: 25 },
  visible: {
    opacity: 1,
    y: 0,
    transition: { duration: 0.6 },
  },
};

export default function SanctuaryExperience() {
  return (
    <section className="py-24 bg-[#EFE8DD] border-y border-[#DDD2C2] relative">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
          {/* 4 Feature Cards with Staggered Entrance */}
          <motion.div
            variants={containerVariants}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true, margin: "-50px" }}
            className="order-2 lg:order-1"
          >
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
              {features.map((feat, idx) => {
                const IconComponent = feat.icon;
                return (
                  <motion.div
                    key={idx}
                    variants={itemVariants}
                    whileHover={{ y: -5, scale: 1.01 }}
                    className="bg-white p-8 rounded-3xl border border-[#DDD3C2] shadow-xs hover:shadow-[0_12px_32px_-4px_rgba(34,28,26,0.08)] hover:border-[#B8975A]/50 transition-all duration-300 group"
                  >
                    <div className="w-12 h-12 bg-[#FAF7F2] rounded-2xl flex items-center justify-center mb-5 text-[#C4847A] group-hover:bg-[#C4847A] group-hover:text-white transition-colors duration-300">
                      <IconComponent size={24} weight="light" />
                    </div>
                    <h4 className="text-base font-serif font-semibold text-[#221C1A] mb-2">
                      {feat.title}
                    </h4>
                    <p className="text-xs text-[#5C5550] leading-relaxed font-light">
                      {feat.desc}
                    </p>
                  </motion.div>
                );
              })}
            </div>
          </motion.div>

          {/* Narrative & Stats */}
          <motion.div
            initial={{ opacity: 0, x: 25 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.7, ease: "easeOut" }}
            className="order-1 lg:order-2 lg:pl-6"
          >
            <div className="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white border border-[#B8975A]/25 text-[#C4847A] text-xs font-semibold tracking-[0.2em] uppercase mb-4 shadow-sm">
              <CheckCircle size={13} weight="fill" />
              <span>Why Choose Us</span>
            </div>

            <h2 className="text-4xl md:text-5xl font-serif text-[#221C1A] mb-6 leading-tight font-normal">
              Beyond Just a <br className="hidden sm:inline" />
              Nail Service
            </h2>

            <p className="text-base sm:text-lg text-[#5C5550] font-light leading-relaxed mb-8">
              At Isaiah Nail Bar, self-care is honored as an intentional art form. Every detail of our salon in Gisementi is crafted to provide a genuine refuge of tranquility in Kigali. From anatomical seating to personal consultations for your nail wellness, your visit is unhurried and restorative.
            </p>

            {/* Stat Counters Bar */}
            <div className="flex flex-wrap items-center gap-6 sm:gap-8 pt-6 border-t border-[#DDD2C2]">
              <div>
                <span className="text-3xl font-serif text-[#221C1A] block font-semibold">
                  500+
                </span>
                <span className="text-xs text-[#8E857F] uppercase tracking-wider font-light">
                  Happy Clients
                </span>
              </div>

              <div className="h-10 w-px bg-[#B8975A]/25" />

              <div>
                <span className="text-3xl font-serif text-[#B8975A] block font-semibold">
                  4.9 / 5.0
                </span>
                <span className="text-xs text-[#8E857F] uppercase tracking-wider font-light">
                  Google Rating
                </span>
              </div>

              <div className="h-10 w-px bg-[#B8975A]/25" />

              <div>
                <span className="text-3xl font-serif text-[#221C1A] block font-semibold">
                  100%
                </span>
                <span className="text-xs text-[#8E857F] uppercase tracking-wider font-light">
                  Autoclave Sterile
                </span>
              </div>
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}
