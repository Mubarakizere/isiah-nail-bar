"use client";

import { motion } from "framer-motion";
import { ShieldCheck, Drop, Sparkle, SealCheck } from "@phosphor-icons/react";

const guarantees = [
  {
    icon: ShieldCheck,
    title: "Hospital-Grade Hygiene",
    description: "Multi-stage medical autoclave sterilization & sealed individual pouches opened directly in your presence.",
  },
  {
    icon: Drop,
    title: "Single-Use Disposables",
    description: "Every file, buffer, and cuticle wood stick is dedicated exclusively to you and responsibly recycled after use.",
  },
  {
    icon: SealCheck,
    title: "HEMA-Free European Gels",
    description: "Premium non-toxic hypoallergenic formulations imported from Germany and the UK that safeguard natural nail health.",
  },
  {
    icon: Sparkle,
    title: "Zero Hidden Fees",
    description: "Transparent prices in Rwandan Francs (RWF) with complimentary Rwandan herbal tea or espresso lounge service.",
  },
];

export default function BookingGuarantees() {
  return (
    <section className="py-16 sm:py-20 bg-[#EFE8DD] border-t border-[#DDD2C2]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center max-w-2xl mx-auto mb-12">
          <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-2">
            The Isaiah Standard
          </span>
          <h2 className="font-serif text-2xl sm:text-3xl text-[#221C1A] font-normal mb-3">
            Your Sanctuary Guarantee
          </h2>
          <p className="text-xs sm:text-sm text-[#5C5550] font-light leading-relaxed">
            Every booking is backed by our strict European wellness protocols and attentive Kigali hospitality.
          </p>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {guarantees.map((item, idx) => {
            const Icon = item.icon;
            return (
              <motion.div
                key={item.title}
                initial={{ opacity: 0, y: 15 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.4, delay: idx * 0.1 }}
                className="bg-white/80 backdrop-blur-xs rounded-2xl p-6 border border-[#DDD2C2] hover:border-[#B8975A]/50 transition-all duration-300 shadow-2xs hover:shadow-md flex flex-col justify-between"
              >
                <div>
                  <div className="w-10 h-10 rounded-xl bg-[#FAF7F2] border border-[#B8975A]/25 text-[#B8975A] flex items-center justify-center mb-4">
                    <Icon size={20} weight="light" />
                  </div>
                  <h3 className="font-serif text-base text-[#221C1A] font-semibold mb-2">
                    {item.title}
                  </h3>
                  <p className="text-xs text-[#5C5550] font-light leading-relaxed">
                    {item.description}
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
