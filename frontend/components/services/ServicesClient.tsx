"use client";

import { useState, useMemo, useEffect } from "react";
import Link from "next/link";
import { motion, AnimatePresence } from "framer-motion";
import {
  MagnifyingGlass,
  Clock,
  ArrowRight,
  Eye,
  ShareNetwork,
  X,
  CheckCircle,
  ShieldCheck,
  SealCheck,
  CalendarCheck,
  ArrowSquareOut,
} from "@phosphor-icons/react";
import { ServiceItem, ServiceCategory } from "@/lib/services-data";
import { formatCurrency } from "@/lib/utils";
import ServiceImage from "@/components/services/ServiceImage";

interface ServicesClientProps {
  initialServices: ServiceItem[];
  categories: ServiceCategory[];
}

export default function ServicesClient({
  initialServices,
  categories,
}: ServicesClientProps) {
  const [searchQuery, setSearchQuery] = useState("");
  const [activeModalService, setActiveModalService] = useState<ServiceItem | null>(null);
  const [copiedSlug, setCopiedSlug] = useState<string | null>(null);

  // Close modal on Escape key
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === "Escape") setActiveModalService(null);
    };
    window.addEventListener("keydown", handleKeyDown);
    return () => window.removeEventListener("keydown", handleKeyDown);
  }, []);

  // Prevent background scroll when modal is open
  useEffect(() => {
    if (activeModalService) {
      document.body.style.overflow = "hidden";
    } else {
      document.body.style.overflow = "unset";
    }
    return () => {
      document.body.style.overflow = "unset";
    };
  }, [activeModalService]);

  const isSearching = searchQuery.trim().length > 0;

  // Filtered search results
  const searchResults = useMemo(() => {
    if (!isSearching) return [];
    const q = searchQuery.toLowerCase().trim();
    return initialServices.filter(
      (s) =>
        s.name.toLowerCase().includes(q) ||
        s.description.toLowerCase().includes(q) ||
        s.category_name.toLowerCase().includes(q)
    );
  }, [initialServices, isSearching, searchQuery]);

  // Grouped by category
  const categorizedServices = useMemo(() => {
    return categories
      .map((cat) => ({
        category: cat,
        items: initialServices.filter((s) => s.category_id === cat.id),
      }))
      .filter((group) => group.items.length > 0);
  }, [categories, initialServices]);

  // Generate clean slug from service name
  const getServiceSlug = (service: ServiceItem): string => {
    if (service.slug) return service.slug;
    return service.name
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/(^-|-$)+/g, "");
  };

  // Share using service NAME / SLUG (not ID)
  const handleShare = async (e: React.MouseEvent, service: ServiceItem) => {
    e.stopPropagation();
    const slug = getServiceSlug(service);
    const shareUrl = `${window.location.origin}/services/${slug}`;

    if (navigator.share) {
      try {
        await navigator.share({
          title: `${service.name} | Isaiah Nail Bar Kigali`,
          text: `${service.name} (${formatCurrency(service.price)}) - ${service.description}`,
          url: shareUrl,
        });
        return;
      } catch {
        // Fallback to clipboard
      }
    }

    try {
      await navigator.clipboard.writeText(shareUrl);
      setCopiedSlug(slug);
      setTimeout(() => setCopiedSlug(null), 2500);
    } catch {
      // Ignored
    }
  };

  return (
    <div className="w-full bg-white">
      {/* Clean, Refined Search Bar */}
      <div className="py-8 bg-white border-b border-[#E5DFD5]">
        <div className="max-w-3xl mx-auto px-4 sm:px-6">
          <div className="relative">
            <MagnifyingGlass
              size={20}
              className="absolute left-4 top-1/2 -translate-y-1/2 text-[#8E857F]"
            />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Search treatments (e.g. Russian Gel, BIAB, Pedicure, Extensions...)"
              className="w-full pl-12 pr-10 py-3.5 bg-[#FAF7F2] border border-[#E5DFD5] hover:border-[#B8975A]/60 focus:border-[#221C1A] rounded-full text-sm text-[#221C1A] placeholder-[#8E857F] focus:outline-none transition-all shadow-2xs"
            />
            {searchQuery && (
              <button
                onClick={() => setSearchQuery("")}
                className="absolute right-4 top-1/2 -translate-y-1/2 text-[#8E857F] hover:text-[#221C1A]"
                aria-label="Clear search"
              >
                <X size={16} />
              </button>
            )}
          </div>

          {isSearching && (
            <div className="mt-3 text-xs text-[#5C5550] flex items-center justify-between px-2">
              <span>
                Found <strong>{searchResults.length}</strong> treatment
                {searchResults.length === 1 ? "" : "s"} matching &ldquo;{searchQuery}&rdquo;
              </span>
              <button
                onClick={() => setSearchQuery("")}
                className="text-[#B8975A] hover:underline font-medium"
              >
                Clear Search
              </button>
            </div>
          )}
        </div>
      </div>

      {/* Main Content: Either Search Results OR Organized by Category */}
      <section className="py-14 sm:py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          {isSearching ? (
            /* Search Results View */
            <div>
              {searchResults.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                  {searchResults.map((service) => (
                    <ServiceCard
                      key={service.id}
                      service={service}
                      getServiceSlug={getServiceSlug}
                      onOpenModal={setActiveModalService}
                      onShare={handleShare}
                    />
                  ))}
                </div>
              ) : (
                <div className="text-center py-20 bg-[#FAF7F2] rounded-3xl border border-[#E5DFD5] max-w-lg mx-auto">
                  <MagnifyingGlass size={36} className="mx-auto text-[#8E857F] mb-3" />
                  <h3 className="font-serif text-xl text-[#221C1A] mb-2">No treatments found</h3>
                  <p className="text-xs sm:text-sm text-[#5C5550] max-w-sm mx-auto mb-6">
                    We couldn&apos;t find any service matching &ldquo;{searchQuery}&rdquo;.
                  </p>
                  <button
                    onClick={() => setSearchQuery("")}
                    className="px-6 py-2.5 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-colors"
                  >
                    View All Services
                  </button>
                </div>
              )}
            </div>
          ) : (
            /* Organized Category Sections (Editorial, Clean Luxury Style) */
            <div className="space-y-20 sm:space-y-24">
              {categorizedServices.map((group, groupIdx) => (
                <div key={group.category.id} className="relative">
                  {/* Category Header */}
                  <div className="mb-10 pb-4 border-b border-[#E5DFD5]">
                    <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-1">
                      0{groupIdx + 1} &bull; Ritual Collection
                    </span>
                    <h2 className="font-serif text-2xl sm:text-4xl text-[#221C1A] font-normal mb-2">
                      {group.category.name}
                    </h2>
                    {group.category.description && (
                      <p className="text-xs sm:text-sm text-[#5C5550] font-light max-w-2xl">
                        {group.category.description}
                      </p>
                    )}
                  </div>

                  {/* Category Cards Grid */}
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    {group.items.map((service) => (
                      <ServiceCard
                        key={service.id}
                        service={service}
                        getServiceSlug={getServiceSlug}
                        onOpenModal={setActiveModalService}
                        onShare={handleShare}
                      />
                    ))}
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </section>

      {/* Copy Notification Toast */}
      <AnimatePresence>
        {copiedSlug && (
          <motion.div
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: 20 }}
            className="fixed bottom-6 right-6 z-50 px-4 py-3 bg-[#221C1A] text-white rounded-2xl shadow-2xl flex items-center gap-2 text-xs"
          >
            <CheckCircle size={16} weight="fill" className="text-[#34A853]" />
            <span>Service link copied to clipboard!</span>
          </motion.div>
        )}
      </AnimatePresence>

      {/* ========================================================= */}
      {/* LUXURY QUICK VIEW MODAL (Responsive 2-Col on Desktop, Smooth Bottom-Sheet on Mobile) */}
      {/* ========================================================= */}
      <AnimatePresence>
        {activeModalService && (
          <div
            className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 lg:p-8 bg-black/65 backdrop-blur-md overflow-y-auto"
            onClick={() => setActiveModalService(null)}
          >
            <motion.div
              initial={{ opacity: 0, scale: 0.94, y: 24 }}
              animate={{ opacity: 1, scale: 1, y: 0 }}
              exit={{ opacity: 0, scale: 0.94, y: 20 }}
              transition={{ duration: 0.28, ease: "easeOut" }}
              onClick={(e) => e.stopPropagation()}
              className="bg-white rounded-[24px] sm:rounded-[32px] max-w-4xl w-full overflow-hidden shadow-[0_25px_60px_-15px_rgba(0,0,0,0.35)] border border-[#E5DFD5] max-h-[92vh] flex flex-col md:flex-row relative my-auto"
            >
              {/* Close Button - Floats Top-Right */}
              <button
                onClick={() => setActiveModalService(null)}
                aria-label="Close modal"
                className="absolute top-4 right-4 z-20 w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/90 hover:bg-[#221C1A] text-[#221C1A] hover:text-white flex items-center justify-center backdrop-blur-md shadow-md border border-black/5 transition-all duration-200"
              >
                <X size={18} weight="bold" />
              </button>

              {/* LEFT COLUMN: Authentic Photography & Visual Highlights */}
              <div className="md:w-5/12 relative min-h-[220px] sm:min-h-[260px] md:min-h-full bg-[#181514] flex flex-col justify-between shrink-0">
                <ServiceImage
                  src={activeModalService.image}
                  alt={activeModalService.name}
                  sizes="(max-width: 768px) 100vw, 40vw"
                  className="object-cover object-center"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-black/10 z-10" />

                {/* Top Overlay Badges */}
                <div className="relative z-10 p-4 sm:p-5 flex items-center gap-2">
                  <span className="px-3 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-white/90 text-[#221C1A] backdrop-blur-md shadow-xs">
                    {activeModalService.category_name}
                  </span>
                  {activeModalService.featured && (
                    <span className="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-[#B8975A] text-white shadow-xs">
                      Atelier Signature
                    </span>
                  )}
                </div>

                {/* Bottom Assurance Badges on Left Photo */}
                <div className="relative z-10 p-5 sm:p-6 text-white space-y-2 hidden sm:block">
                  <div className="flex items-center gap-2 text-xs text-white/90">
                    <ShieldCheck size={16} weight="bold" className="text-[#86EFAC] shrink-0" />
                    <span>Hospital Autoclave Sterile</span>
                  </div>
                  <div className="flex items-center gap-2 text-xs text-white/90">
                    <SealCheck size={16} weight="bold" className="text-[#B8975A] shrink-0" />
                    <span>4+ Weeks Retention Guarantee</span>
                  </div>
                </div>
              </div>

              {/* RIGHT COLUMN: Service Details, Inclusions & Actions */}
              <div className="md:w-7/12 flex flex-col justify-between overflow-y-auto p-5 sm:p-8 lg:p-10 bg-white">
                <div className="space-y-6">
                  {/* Category Eyebrow & Title */}
                  <div className="pr-8">
                    <span className="text-[10px] font-semibold uppercase tracking-widest text-[#B8975A] block mb-1.5">
                      Kigali Atelier &bull; {activeModalService.category_name}
                    </span>
                    <h3 className="font-serif text-2xl sm:text-3xl text-[#221C1A] font-normal leading-tight">
                      {activeModalService.name}
                    </h3>
                  </div>

                  {/* Price & Duration Feature Box */}
                  <div className="p-4 sm:p-5 rounded-2xl bg-[#FAF7F2] border border-[#E5DFD5] flex items-center justify-between">
                    <div>
                      <span className="text-[10px] text-[#8E857F] uppercase tracking-wider block mb-0.5">
                        Investment
                      </span>
                      <span className="text-xl sm:text-2xl font-serif font-bold text-[#221C1A]">
                        {formatCurrency(activeModalService.price)}
                      </span>
                    </div>

                    <div className="text-right">
                      <span className="text-[10px] text-[#8E857F] uppercase tracking-wider block mb-0.5">
                        Duration
                      </span>
                      <div className="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#221C1A] justify-end">
                        <Clock size={16} className="text-[#B8975A]" />
                        <span>{activeModalService.duration_minutes} mins</span>
                      </div>
                    </div>
                  </div>

                  {/* Description */}
                  <div>
                    <h4 className="text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-2">
                      Ritual Details
                    </h4>
                    <p className="text-xs sm:text-sm text-[#5C5550] font-light leading-relaxed">
                      {activeModalService.description}
                    </p>
                  </div>

                  {/* Benefits */}
                  {activeModalService.benefits && activeModalService.benefits.length > 0 && (
                    <div>
                      <h4 className="text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-2.5">
                        Guest Highlights
                      </h4>
                      <div className="space-y-2">
                        {activeModalService.benefits.map((b, i) => (
                          <div key={i} className="flex items-center gap-2.5 text-xs text-[#5C5550]">
                            <CheckCircle size={15} weight="fill" className="text-[#34A853] shrink-0" />
                            <span>{b}</span>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}

                  {/* Included Steps */}
                  {activeModalService.includes && activeModalService.includes.length > 0 && (
                    <div>
                      <h4 className="text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-2">
                        What&apos;s Included
                      </h4>
                      <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        {activeModalService.includes.map((step, i) => (
                          <div
                            key={i}
                            className="px-3 py-2 rounded-xl bg-[#FAF7F2] border border-[#E5DFD5] text-[11px] text-[#5C5550] leading-snug"
                          >
                            &bull; {step}
                          </div>
                        ))}
                      </div>
                    </div>
                  )}
                </div>

                {/* Bottom Action Footer */}
                <div className="pt-6 sm:pt-8 mt-6 border-t border-[#E5DFD5] space-y-3">
                  <div className="flex items-center gap-3">
                    <Link
                      href={`/booking?service=${activeModalService.id}`}
                      className="group flex-1 py-3.5 sm:py-4 bg-[#221C1A] hover:bg-[#B8975A] text-white text-center font-semibold text-xs uppercase tracking-wider rounded-full shadow-lg transition-all duration-300 flex items-center justify-center gap-2"
                    >
                      <CalendarCheck size={16} weight="bold" />
                      <span>Reserve Appointment</span>
                      <ArrowRight size={14} className="group-hover:translate-x-1 transition-transform" />
                    </Link>

                    <button
                      onClick={(e) => handleShare(e, activeModalService)}
                      aria-label="Share Treatment"
                      className="w-11 h-11 sm:w-12 sm:h-12 rounded-full border border-[#E5DFD5] hover:border-[#221C1A] hover:bg-[#FAF7F2] text-[#221C1A] flex items-center justify-center transition-all duration-200 shrink-0"
                      title="Share by name"
                    >
                      <ShareNetwork size={18} />
                    </button>
                  </div>

                  {/* Dedicated Page Link */}
                  <div className="text-center">
                    <Link
                      href={`/services/${getServiceSlug(activeModalService)}`}
                      className="inline-flex items-center gap-1.5 text-xs text-[#8E857F] hover:text-[#221C1A] transition-colors"
                    >
                      <span>View full dedicated page & ritual breakdown</span>
                      <ArrowSquareOut size={13} />
                    </Link>
                  </div>
                </div>
              </div>
            </motion.div>
          </div>
        )}
      </AnimatePresence>
    </div>
  );
}

// Subcomponent: Individual Treatment Card
interface ServiceCardProps {
  service: ServiceItem;
  getServiceSlug: (s: ServiceItem) => string;
  onOpenModal: (s: ServiceItem) => void;
  onShare: (e: React.MouseEvent, s: ServiceItem) => void;
}

function ServiceCard({
  service,
  getServiceSlug,
  onOpenModal,
  onShare,
}: ServiceCardProps) {
  const slug = getServiceSlug(service);

  return (
    <div className="group bg-[#FAF7F2] rounded-3xl p-5 border border-[#B8975A]/20 hover:border-[#B8975A]/60 hover:shadow-[0_16px_36px_-6px_rgba(184,151,90,0.18)] transition-all duration-300 flex flex-col justify-between">
      <div>
        {/* Image Container with Hover Quick View */}
        <div
          onClick={() => onOpenModal(service)}
          className="relative aspect-[4/3] rounded-2xl overflow-hidden mb-5 bg-white border border-black/5 cursor-pointer"
        >
          <ServiceImage
            src={service.image}
            alt={service.name}
            sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
            className="object-cover object-center group-hover:scale-105 transition-transform duration-500"
          />

          {/* Top Badges */}
          <div className="absolute top-3 left-3 flex items-center gap-1.5 z-10">
            <span className="px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-white/95 text-[#221C1A] backdrop-blur-md shadow-xs border border-black/5">
              {service.category_name}
            </span>
            {service.featured && (
              <span className="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-[#221C1A] text-white shadow-xs">
                Signature
              </span>
            )}
          </div>

          {/* Share Button (Top Right) - Uses Service Name */}
          <button
            onClick={(e) => onShare(e, service)}
            aria-label={`Share ${service.name}`}
            className="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 hover:bg-white text-[#221C1A] flex items-center justify-center backdrop-blur-md shadow-xs opacity-90 group-hover:opacity-100 transition-opacity z-10"
            title="Share Treatment"
          >
            <ShareNetwork size={15} />
          </button>

          {/* Quick View Hover Overlay */}
          <div className="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
            <span className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white text-[#221C1A] text-xs font-semibold shadow-lg">
              <Eye size={15} />
              Quick View
            </span>
          </div>
        </div>

        {/* Service Info */}
        <div className="space-y-2 mb-6">
          <Link href={`/services/${slug}`}>
            <h3 className="font-serif text-xl text-[#221C1A] font-medium leading-snug hover:text-[#B8975A] transition-colors cursor-pointer">
              {service.name}
            </h3>
          </Link>

          <p className="text-xs sm:text-sm text-[#5C5550] font-light leading-relaxed line-clamp-2">
            {service.description}
          </p>

          {/* Duration Indicator */}
          <div className="flex items-center gap-1.5 text-xs text-[#8E857F] pt-1">
            <Clock size={14} />
            <span>{service.duration_minutes} minutes duration</span>
          </div>
        </div>
      </div>

      {/* Price & Book Action Bottom Bar */}
      <div className="pt-4 border-t border-[#E5DFD5] flex items-center justify-between gap-3">
        <div>
          <span className="text-[10px] text-[#8E857F] uppercase tracking-wider block">
            Treatment Fee
          </span>
          <span className="text-lg font-serif font-bold text-[#221C1A]">
            {formatCurrency(service.price)}
          </span>
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={() => onOpenModal(service)}
            className="px-3.5 py-2.5 rounded-full text-xs font-medium text-[#221C1A] bg-white border border-[#E5DFD5] hover:border-[#221C1A] transition-colors cursor-pointer"
          >
            Quick View
          </button>

          <Link
            href={`/booking?service=${service.id}`}
            className="group/btn inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-colors shadow-xs"
          >
            <span>Book</span>
            <ArrowRight size={13} className="group-hover/btn:translate-x-0.5 transition-transform" />
          </Link>
        </div>
      </div>
    </div>
  );
}
