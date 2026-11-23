"use client";

import { motion } from "framer-motion";
import { MapPin, Armchair, ClockCountdown, ChatCircleDots } from "@phosphor-icons/react";

const arrivalGuidelines = [
  {
    icon: MapPin,
    title: "Guest Parking & Accessibility",
    description:
      "Complimentary dedicated visitor parking is available directly in front of the salon at KG 4 Roundabout, Gisementi. Ground-floor access is step-free for all guests.",
  },
  {
    icon: Armchair,
    title: "Arrival Lounge & Refreshment",
    description:
      "We recommend arriving 10 minutes early. Relax in our quiet sanctuary lounge with complimentary Rwandan organic herbal tea, fresh espresso, or infused water.",
  },
  {
    icon: ClockCountdown,
    title: "Cancellation & Rescheduling",
    description:
      "We kindly ask for at least 2 hours advance notice for any schedule changes so our artisans can accommodate guests on our waitlist.",
  },
  {
    icon: ChatCircleDots,
    title: "Multilingual Concierge",
    description:
      "Our nail technicians and reception team fluently assist guests in English, Français, and Ikinyarwanda for a smooth and comfortable experience.",
  },
];

export default function ContactFaqSection() {
  return (
    <section className="py-20 lg:py-24 bg-[#EFE8DD] border-y border-[#DDD2C2]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center max-w-2xl mx-auto mb-14">
          <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-2">
            Sanctuary Etiquette
          </span>
          <h2 className="font-serif text-2xl sm:text-4xl text-[#221C1A] font-normal mb-4">
            Visiting Our Kigali Atelier
          </h2>
          <p className="text-xs sm:text-sm text-[#5C5550] font-light leading-relaxed">
            Everything you need to know to ensure a tranquil, unhurried wellness experience from the moment you arrive.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {arrivalGuidelines.map((item, idx) => {
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
                  <div className="w-11 h-11 rounded-xl bg-[#FAF7F2] border border-[#B8975A]/25 text-[#B8975A] flex items-center justify-center mb-4">
                    <Icon size={22} weight="light" />
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
