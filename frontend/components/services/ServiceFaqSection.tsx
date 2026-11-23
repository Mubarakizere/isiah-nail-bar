"use client";

import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { CaretDown } from "@phosphor-icons/react";

const faqs = [
  {
    q: "What makes a Russian Manicure superior to traditional water manicures?",
    a: "Unlike traditional manicures that soak nails in water (which causes nail plates to swell and shrink, leading to early gel peeling), Russian manicures are 100% dry. We use micro-diamond electric drill bits to gently and cleanly remove dead cuticle skin with microscopic precision. This allows us to apply gel polish directly beneath the proximal nail fold, providing a flawless finish with 4+ weeks of retention without chipping or lifting.",
  },
  {
    q: "How do I choose between BIAB, Sculpted Hard Gel, and Acrylic?",
    a: "BIAB (Builder in a Bottle) is ideal for strengthening your natural nails and helping them grow longer. Sculpted Hard Gel provides lightweight, flexible extension length without glue tips or toxic chemicals. Acrylic extensions are best if you desire maximum structural hardness and dramatic length. Our nail artists will examine your nail structure and recommend the ideal medium during your consultation.",
  },
  {
    q: "How long should I plan for my appointment?",
    a: "A classic or Russian manicure takes approximately 50 to 75 minutes. Extensions (Hard Gel or Acrylic) require between 80 and 95 minutes, and our luxury botanical pedicures take about 60 minutes. Because we do not cut corners on hygiene or cuticle detailing, each service is unhurried and meticulous.",
  },
  {
    q: "How do you ensure sterile hygiene?",
    a: "We adhere strictly to medical hospital standards. All metal implements undergo a 3-step decontamination protocol: ultrasonic disinfectant bath, medical packaging in sealed sterilization pouches, and autoclave pressurized steam sterilization. All nail files, buffers, and pedicure tub liners are strictly 100% single-use and disposed of after your session.",
  },
  {
    q: "Can I book a specific nail artist or private booking?",
    a: "Yes! Our online booking system lets you select your preferred senior nail technician or select 'First Available Master'. For private wedding parties, VIP sessions, or group bookings, you can also contact our concierge directly via WhatsApp.",
  },
];

export default function ServiceFaqSection() {
  const [openIndex, setOpenIndex] = useState<number | null>(0);

  const toggle = (i: number) => {
    setOpenIndex(openIndex === i ? null : i);
  };

  return (
    <section className="py-20 lg:py-24 bg-white">
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-16">
          <span className="text-[11px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-3">
            Client Guidance
          </span>
          <h2 className="font-serif text-3xl sm:text-4xl lg:text-5xl font-normal text-[#221C1A] mb-4">
            Frequently Asked Questions
          </h2>
          <p className="text-sm sm:text-base text-[#5C5550] font-light">
            Everything you need to know about our treatments, procedures, and salon etiquette.
          </p>
        </div>

        <div className="space-y-4">
          {faqs.map((faq, idx) => {
            const isOpen = openIndex === idx;
            return (
              <div
                key={idx}
                className="bg-[#FAF7F2] border border-[#E5DFD5] rounded-2xl overflow-hidden transition-all duration-200"
              >
                <button
                  onClick={() => toggle(idx)}
                  className="w-full px-6 py-5 text-left flex items-center justify-between gap-4 font-serif text-base sm:text-lg text-[#221C1A] hover:text-[#B8975A] transition-colors"
                >
                  <span className="font-medium">{faq.q}</span>
                  <CaretDown
                    size={18}
                    weight="bold"
                    className={`shrink-0 text-[#8E857F] transition-transform duration-300 ${
                      isOpen ? "rotate-180 text-[#B8975A]" : ""
                    }`}
                  />
                </button>

                <AnimatePresence initial={false}>
                  {isOpen && (
                    <motion.div
                      initial={{ height: 0, opacity: 0 }}
                      animate={{ height: "auto", opacity: 1 }}
                      exit={{ height: 0, opacity: 0 }}
                      transition={{ duration: 0.3, ease: "easeInOut" }}
                    >
                      <div className="px-6 pb-6 pt-1 text-xs sm:text-sm text-[#5C5550] font-light leading-relaxed border-t border-[#E5DFD5]/60">
                        {faq.a}
                      </div>
                    </motion.div>
                  )}
                </AnimatePresence>
              </div>
            );
          })}
        </div>
      </div>
    </section>
  );
}
