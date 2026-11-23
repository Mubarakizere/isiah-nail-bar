"use client";

import Link from "next/link";
import { motion, type Variants } from "framer-motion";
import { ArrowRight, Clock, Tag } from "@phosphor-icons/react";
import { formatCurrency } from "@/lib/utils";
import ServiceImage from "@/components/services/ServiceImage";

interface ServiceItem {
  id: number;
  name: string;
  description?: string;
  price: number;
  duration_minutes?: number;
  image?: string;
  category?: {
    name: string;
  };
}

interface FeaturedServicesProps {
  services?: ServiceItem[];
}

const fallbackFeatured: ServiceItem[] = [
  {
    id: 1,
    name: "Russian Gel Manicure",
    description: "Dry cuticular detailing with micro-e-file alignment, apex reinforcement, and zero-lift gel color.",
    price: 35000,
    duration_minutes: 75,
    image: "/services/2fefdFyQnIi6810B9JwmtPspYVvKKFJ7stxSN02Y.jpg",
    category: { name: "Precision Care" },
  },
  {
    id: 2,
    name: "Sculpted Hard Gel Extensions",
    description: "Custom sculpted forms designed to follow your nail structure for durable, lightweight elegance.",
    price: 45000,
    duration_minutes: 90,
    image: "/services/2nktLbUqZnQWq2J7otghlcStfsL4bVepkg6vjTLK.jpg",
    category: { name: "Extensions" },
  },
  {
    id: 3,
    name: "Luxury Botanical Pedicure",
    description: "Herbal foot bath, organic scrub, calluses smoothing, tension release massage, and flawless polish.",
    price: 30000,
    duration_minutes: 60,
    image: "/services/2Q9T51JCvUkDyTfrgfwhLgtl6j3Wf9yXXdgEfuHN.jpg",
    category: { name: "Wellness & Spa" },
  },
];

const containerVariants: Variants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: {
      staggerChildren: 0.15,
    },
  },
};

const cardVariants: Variants = {
  hidden: { opacity: 0, y: 30 },
  visible: {
    opacity: 1,
    y: 0,
    transition: { duration: 0.6 },
  },
};

export default function FeaturedServices({ services }: FeaturedServicesProps) {
  const displayServices = services && services.length > 0 ? services : fallbackFeatured;

  return (
    <section className="py-24 bg-white relative">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        {/* Section Header */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6 }}
          className="text-center max-w-3xl mx-auto mb-16"
        >
          <span className="text-[11px] font-semibold tracking-[0.24em] uppercase text-[#C4847A] block mb-3">
            Signature Rituals &bull; Kigali Atelier
          </span>
          <h2 className="text-4xl md:text-5xl font-serif text-[#221C1A] mb-5 font-normal">
            Curated Treatments
          </h2>
          <p className="text-base sm:text-lg text-[#5C5550] font-light leading-relaxed">
            Discover our signature rituals, designed to rejuvenate your hands and feet with hospital-grade sanitization, non-toxic formulations, and master craftsmanship.
          </p>
        </motion.div>

        {/* Services Grid with Staggered Entrance */}
        <motion.div
          variants={containerVariants}
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true, margin: "-50px" }}
          className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10"
        >
          {displayServices.map((service) => (
            <motion.div
              key={service.id}
              variants={cardVariants}
              whileHover={{ y: -6 }}
              className="bg-[#FAF7F2] rounded-3xl p-4 sm:p-5 border border-[#B8975A]/20 hover:border-[#B8975A]/50 hover:shadow-[0_16px_36px_-6px_rgba(184,151,90,0.18)] transition-all duration-300 flex flex-col justify-between group"
            >
              <div>
                {/* Image Frame */}
                <div className="relative aspect-[4/5] overflow-hidden rounded-2xl mb-5 bg-white border border-black/5">
                  <ServiceImage
                    src={service.image}
                    alt={service.name}
                    sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
                    className="object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
                  />

                  {/* Gradient & Quick Book Overlay */}
                  <div className="absolute inset-0 bg-gradient-to-t from-[#221C1A]/85 via-[#221C1A]/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center p-6">
                    <Link
                      href={`/booking?service_id=${service.id}`}
                      className="w-full text-center py-3 px-6 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white hover:bg-[#C4847A] hover:text-white transition-all transform translate-y-3 group-hover:translate-y-0 duration-300 shadow-md"
                    >
                      Book This Service
                    </Link>
                  </div>

                  {/* Category Pill */}
                  {service.category && (
                    <div className="absolute top-4 left-4 z-10">
                      <span className="px-3 py-1 rounded-full text-[10px] uppercase font-semibold tracking-wider bg-white/95 text-[#221C1A] shadow-sm border border-[#B8975A]/20 flex items-center gap-1">
                        <Tag size={10} className="text-[#B8975A]" />
                        {service.category.name}
                      </span>
                    </div>
                  )}

                  {service.duration_minutes && (
                    <div className="absolute top-4 right-4 z-10">
                      <span className="px-2.5 py-1 rounded-full text-[10px] font-medium tracking-wide bg-white/95 text-[#5C5550] shadow-sm border border-[#B8975A]/20 flex items-center gap-1">
                        <Clock size={12} className="text-[#C4847A]" />
                        {service.duration_minutes}m
                      </span>
                    </div>
                  )}
                </div>

                {/* Details */}
                <div className="px-2">
                  <h3 className="text-xl font-serif text-[#221C1A] mb-2 group-hover:text-[#C4847A] transition-colors">
                    {service.name}
                  </h3>

                  {service.description && (
                    <p className="text-xs text-[#5C5550] font-light line-clamp-2 mb-4 leading-relaxed">
                      {service.description}
                    </p>
                  )}
                </div>
              </div>

              {/* Price & Book Button */}
              <div className="pt-3 px-2 border-t border-[#B8975A]/15 flex items-center justify-between">
                <div>
                  <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block">Pricing</span>
                  <span className="font-semibold text-base text-[#221C1A] font-sans">
                    {formatCurrency(service.price)}
                  </span>
                </div>

                <Link
                  href={`/booking?service_id=${service.id}`}
                  className="px-4 py-2 rounded-full text-xs font-semibold text-[#221C1A] bg-white border border-[#B8975A]/30 hover:bg-[#C4847A] hover:text-white hover:border-[#C4847A] transition-all shadow-sm"
                >
                  Book Ritual
                </Link>
              </div>
            </motion.div>
          ))}
        </motion.div>

        {/* View Full Menu CTA */}
        <motion.div
          initial={{ opacity: 0, y: 15 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.5, delay: 0.3 }}
          className="text-center mt-16"
        >
          <Link
            href="/services"
            className="inline-flex items-center gap-2 px-8 py-3.5 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] border border-[#B8975A]/40 bg-[#FAF7F2] hover:bg-white hover:border-[#B8975A] hover:text-[#B8975A] shadow-sm hover:shadow-md transition-all group"
          >
            <span>View Full Treatment Menu</span>
            <ArrowRight size={15} className="group-hover:translate-x-1 transition-transform" />
          </Link>
        </motion.div>
      </div>
    </section>
  );
}
