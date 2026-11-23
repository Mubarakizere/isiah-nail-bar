"use client";

import { motion } from "framer-motion";
import { ShieldCheck, DiamondsFour, Armchair } from "@phosphor-icons/react";

const philosophies = [
  {
    icon: ShieldCheck,
    title: "Health & Sterilization First",
    subtitle: "Medical Standards",
    desc: "We utilize hospital-grade pressurized autoclave sterilization for all metal instruments. Every file, buffer, and pedicure tub liner is strictly 100% single-use and opened fresh before your eyes.",
  },
  {
    icon: DiamondsFour,
    title: "Precision Artistry",
    subtitle: "Master Techniques",
    desc: "Our nail technicians specialize in European dry e-file manicure methods. We take pride in clean cuticles, perfectly balanced apex structures, and zero-lift retention lasting 4+ weeks.",
  },
  {
    icon: Armchair,
    title: "Absolute Sanctuary Comfort",
    subtitle: "Unhurried Leisure",
    desc: "Step away from crowded, rushed nail salons. Relax into ergonomic chairs with calming music, natural herbal aromatherapy, complimentary refreshments, and undivided master artisan focus.",
  },
];

export default function AboutPhilosophySection() {
  return (
    <section className="py-20 lg:py-28 bg-[#EFE8DD] border-y border-[#DDD2C2] relative">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center max-w-2xl mx-auto mb-16">
          <span className="text-[11px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-3">
            Our Guiding Creed
          </span>
          <h2 className="font-serif text-3xl sm:text-4xl lg:text-5xl font-normal text-[#221C1A] mb-4">
            Our Core Philosophy
          </h2>
          <p className="text-sm sm:text-base text-[#5C5550] font-light leading-relaxed">
            &ldquo;We don&apos;t just paint nails&mdash;we restore and elevate them.&rdquo;
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {philosophies.map((item, idx) => {
            const Icon = item.icon;
            return (
              <motion.div
                key={idx}
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.5, delay: idx * 0.12 }}
                className="bg-white rounded-3xl p-8 sm:p-10 border border-[#DDD3C2] shadow-xs flex flex-col justify-between hover:shadow-lg hover:-translate-y-1 transition-all duration-300"
              >
                <div>
                  <div className="w-14 h-14 rounded-2xl bg-[#FAF7F2] border border-[#B8975A]/25 text-[#B8975A] flex items-center justify-center mb-6 shadow-2xs">
                    <Icon size={28} weight="bold" />
                  </div>
                  <span className="text-[10px] font-semibold uppercase tracking-wider text-[#C4847A] block mb-1.5">
                    {item.subtitle}
                  </span>
                  <h3 className="font-serif text-xl sm:text-2xl text-[#221C1A] font-semibold mb-3">
                    {item.title}
                  </h3>
                  <p className="text-xs sm:text-sm text-[#5C5550] font-light leading-relaxed">
                    {item.desc}
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
