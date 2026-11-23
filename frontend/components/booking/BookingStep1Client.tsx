"use client";

import { useState, useMemo, useEffect } from "react";
import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { motion, AnimatePresence } from "framer-motion";
import {
  MagnifyingGlass,
  X,
  Check,
  Clock,
  ArrowRight,
  Trash,
  Basket,
  Info,
  WhatsappLogo,
  CalendarCheck,
  Plus,
  CaretUp,
  CaretDown,
} from "@phosphor-icons/react";
import { ServiceItem, ServiceCategory } from "@/lib/services-data";
import ServiceImage from "@/components/services/ServiceImage";

interface BookingStep1ClientProps {
  services: ServiceItem[];
  categories: ServiceCategory[];
  initialSelectedIds?: number[];
}

export default function BookingStep1Client({
  services,
  categories,
  initialSelectedIds = [],
}: BookingStep1ClientProps) {
  const router = useRouter();
  const searchParams = useSearchParams();

  // Selection state
  const [selectedIds, setSelectedIds] = useState<number[]>(initialSelectedIds);
  const [searchQuery, setSearchQuery] = useState("");
  const [activeCategory, setActiveCategory] = useState<string>("all");
  const [activeModalService, setActiveModalService] = useState<ServiceItem | null>(null);
  const [isMobileDrawerOpen, setIsMobileDrawerOpen] = useState(false);

  // Initialize selection from URL or localStorage
  useEffect(() => {
    // 1. Check URL params
    const serviceParam = searchParams.get("service") || searchParams.get("services");
    if (serviceParam) {
      const idsFromUrl = serviceParam
        .split(",")
        .map((s) => parseInt(s.trim(), 10))
        .filter((n) => !isNaN(n));
      if (idsFromUrl.length > 0) {
        setSelectedIds(idsFromUrl);
        return;
      }
    }

    // 2. Check localStorage
    try {
      const saved = localStorage.getItem("isaiah_booking_selection");
      if (saved) {
        const parsed = JSON.parse(saved);
        if (Array.isArray(parsed) && parsed.length > 0) {
          setSelectedIds(parsed);
        }
      }
    } catch {
      // Local storage unavailable
    }
  }, [searchParams]);

  // Persist selection to localStorage
  useEffect(() => {
    try {
      localStorage.setItem("isaiah_booking_selection", JSON.stringify(selectedIds));
    } catch {
      // Ignore
    }
  }, [selectedIds]);

  // Toggle selection
  const toggleService = (id: number) => {
    setSelectedIds((prev) =>
      prev.includes(id) ? prev.filter((item) => item !== id) : [...prev, id]
    );
  };

  const removeService = (id: number, e?: React.MouseEvent) => {
    if (e) e.stopPropagation();
    setSelectedIds((prev) => prev.filter((item) => item !== id));
  };

  const clearAll = () => {
    setSelectedIds([]);
  };

  // Selected services list
  const selectedServices = useMemo(() => {
    return services.filter((s) => selectedIds.includes(s.id));
  }, [services, selectedIds]);

  // Calculated totals
  const totalPrice = useMemo(() => {
    return selectedServices.reduce((sum, s) => sum + s.price, 0);
  }, [selectedServices]);

  const totalDurationMinutes = useMemo(() => {
    return selectedServices.reduce((sum, s) => sum + (s.duration_minutes || 60), 0);
  }, [selectedServices]);

  const formattedDuration = useMemo(() => {
    const hours = Math.floor(totalDurationMinutes / 60);
    const mins = totalDurationMinutes % 60;
    if (hours > 0 && mins > 0) return `${hours} hr ${mins} min`;
    if (hours > 0) return `${hours} hr`;
    return `${mins} min`;
  }, [totalDurationMinutes]);

  // Filtered services
  const filteredServices = useMemo(() => {
    return services.filter((service) => {
      // Category filter
      if (activeCategory !== "all") {
        if (String(service.category_id) !== activeCategory) return false;
      }
      // Search filter
      if (searchQuery.trim()) {
        const query = searchQuery.toLowerCase().trim();
        const matchName = service.name.toLowerCase().includes(query);
        const matchDesc = service.description.toLowerCase().includes(query);
        const matchCat = service.category_name.toLowerCase().includes(query);
        if (!matchName && !matchDesc && !matchCat) return false;
      }
      return true;
    });
  }, [services, activeCategory, searchQuery]);

  // Group filtered services by category
  const groupedServices = useMemo(() => {
    const map = new Map<number, { category: ServiceCategory; items: ServiceItem[] }>();

    filteredServices.forEach((service) => {
      if (!map.has(service.category_id)) {
        const cat = categories.find((c) => c.id === service.category_id) || {
          id: service.category_id,
          name: service.category_name,
          slug: `category-${service.category_id}`,
        };
        map.set(service.category_id, { category: cat, items: [] });
      }
      map.get(service.category_id)!.items.push(service);
    });

    return Array.from(map.values());
  }, [filteredServices, categories]);

  // Format currency
  const formatRwf = (amount: number) => {
    return new Intl.NumberFormat("en-RW", {
      style: "decimal",
      maximumFractionDigits: 0,
    }).format(amount);
  };

  // Continue to Step 2
  const handleProceedToStep2 = () => {
    if (selectedIds.length === 0) return;
    router.push(`/booking/step2?services=${selectedIds.join(",")}`);
  };

  return (
    <div className="bg-[#FAF7F2] min-h-screen">
      {/* Sticky Top Filter Toolbar */}
      <div className="sticky top-0 z-30 bg-[#FAF7F2]/95 backdrop-blur-md border-b border-[#E5DFD5] shadow-2xs py-4 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
          {/* Search Box */}
          <div className="relative flex-1 max-w-md">
            <MagnifyingGlass
              size={18}
              weight="bold"
              className="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8E857F]"
            />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Search treatments (e.g. Russian, BIAB, Gel, Pedicure)..."
              className="w-full pl-10 pr-9 py-2.5 bg-white border border-[#E5DFD5] focus:border-[#221C1A] rounded-full text-xs sm:text-sm text-[#221C1A] placeholder-[#8E857F] outline-none transition-colors shadow-2xs"
            />
            {searchQuery && (
              <button
                type="button"
                onClick={() => setSearchQuery("")}
                className="absolute right-3.5 top-1/2 -translate-y-1/2 text-[#8E857F] hover:text-[#221C1A]"
              >
                <X size={14} weight="bold" />
              </button>
            )}
          </div>

          {/* Category Tabs */}
          <div className="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0 scrollbar-none">
            <button
              type="button"
              onClick={() => setActiveCategory("all")}
              className={`px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all duration-200 cursor-pointer ${
                activeCategory === "all"
                  ? "bg-[#221C1A] text-white shadow-xs"
                  : "bg-white text-[#5C5550] border border-[#E5DFD5] hover:border-[#221C1A]/40"
              }`}
            >
              All Treatments ({services.length})
            </button>
            {categories.map((cat) => {
              const count = services.filter((s) => s.category_id === cat.id).length;
              if (count === 0) return null;
              const isActive = activeCategory === String(cat.id);
              return (
                <button
                  key={cat.id}
                  type="button"
                  onClick={() => setActiveCategory(String(cat.id))}
                  className={`px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all duration-200 cursor-pointer ${
                    isActive
                      ? "bg-[#221C1A] text-white shadow-xs"
                      : "bg-white text-[#5C5550] border border-[#E5DFD5] hover:border-[#221C1A]/40"
                  }`}
                >
                  {cat.name} ({count})
                </button>
              );
            })}
          </div>
        </div>
      </div>

      {/* Main Booking Stage: Left Services List + Right Sticky Sidebar */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-16">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
          {/* ========================================================= */}
          {/* Left Column: Grouped Services Menu (7 or 8 cols) */}
          {/* ========================================================= */}
          <div className="lg:col-span-8 space-y-12">
            {groupedServices.length === 0 ? (
              <div className="bg-white rounded-3xl p-12 text-center border border-[#E5DFD5]">
                <div className="w-16 h-16 rounded-full bg-[#FAF7F2] text-[#8E857F] flex items-center justify-center mx-auto mb-4">
                  <Basket size={28} weight="light" />
                </div>
                <h3 className="font-serif text-xl text-[#221C1A] font-normal mb-2">
                  No treatments match your search
                </h3>
                <p className="text-xs sm:text-sm text-[#5C5550] font-light max-w-md mx-auto mb-6">
                  Try adjusting your search query or selecting a different category from the top tabs.
                </p>
                <button
                  type="button"
                  onClick={() => {
                    setSearchQuery("");
                    setActiveCategory("all");
                  }}
                  className="px-6 py-2.5 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-colors"
                >
                  Reset Filter
                </button>
              </div>
            ) : (
              groupedServices.map((group) => (
                <section key={group.category.id} className="scroll-mt-32">
                  {/* Category Section Header */}
                  <div className="flex items-center justify-between pb-3 mb-6 border-b border-[#E5DFD5]">
                    <div>
                      <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block">
                        Treatment Collection
                      </span>
                      <h2 className="font-serif text-2xl sm:text-3xl text-[#221C1A] font-normal">
                        {group.category.name}
                      </h2>
                    </div>
                    <span className="text-xs text-[#8E857F] font-light">
                      {group.items.length} {group.items.length === 1 ? "treatment" : "treatments"}
                    </span>
                  </div>

                  {/* Treatments Grid */}
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {group.items.map((service) => {
                      const isSelected = selectedIds.includes(service.id);
                      return (
                        <div
                          key={service.id}
                          onClick={() => toggleService(service.id)}
                          className={`group relative bg-white rounded-3xl overflow-hidden border transition-all duration-300 cursor-pointer flex flex-col justify-between ${
                            isSelected
                              ? "border-[#B8975A] ring-2 ring-[#B8975A]/25 shadow-lg bg-gradient-to-b from-[#FAF7F2] to-white"
                              : "border-[#E5DFD5] hover:border-[#221C1A]/40 hover:shadow-md"
                          }`}
                        >
                          <div>
                            {/* Card Header Image with Overlays */}
                            <div className="relative h-44 sm:h-48 w-full bg-[#E5DFD5] overflow-hidden">
                              <ServiceImage
                                src={service.image}
                                alt={service.name}
                                sizes="(max-width: 768px) 100vw, 50vw"
                                className="object-cover object-center group-hover:scale-105 transition-transform duration-500"
                              />
                              <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent" />

                              {/* Duration Pill */}
                              <div className="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-black/60 backdrop-blur-md text-white text-[11px] font-light flex items-center gap-1.5 border border-white/10">
                                <Clock size={12} weight="bold" />
                                <span>{service.duration_minutes || 60} mins</span>
                              </div>

                              {/* Selection Indicator Badge */}
                              <div
                                className={`absolute top-3 right-3 w-7 h-7 rounded-full flex items-center justify-center transition-all duration-300 ${
                                  isSelected
                                    ? "bg-[#B8975A] text-white shadow-md scale-100"
                                    : "bg-white/90 text-[#8E857F] border border-black/10 scale-95 group-hover:scale-100"
                                }`}
                              >
                                {isSelected ? (
                                  <Check size={14} weight="bold" />
                                ) : (
                                  <Plus size={14} weight="bold" />
                                )}
                              </div>

                              {/* Price Overlay on Image Bottom */}
                              <div className="absolute bottom-3 left-3 right-3 flex items-end justify-between text-white">
                                <span className="font-serif text-lg font-semibold drop-shadow-sm">
                                  {formatRwf(service.price)}{" "}
                                  <span className="text-[11px] font-sans font-normal opacity-90">
                                    RWF
                                  </span>
                                </span>
                                {service.featured && (
                                  <span className="px-2 py-0.5 rounded-md bg-[#B8975A] text-[10px] font-semibold uppercase tracking-wider text-white shadow-xs">
                                    Signature
                                  </span>
                                )}
                              </div>
                            </div>

                            {/* Card Body */}
                            <div className="p-5">
                              <h3
                                className={`font-serif text-base sm:text-lg mb-1.5 transition-colors line-clamp-1 ${
                                  isSelected
                                    ? "text-[#B8975A] font-semibold"
                                    : "text-[#221C1A] group-hover:text-[#B8975A]"
                                }`}
                              >
                                {service.name}
                              </h3>
                              <p className="text-xs text-[#5C5550] font-light leading-relaxed line-clamp-2 mb-4">
                                {service.description}
                              </p>
                            </div>
                          </div>

                          {/* Card Footer: Quick View & Add/Remove Button */}
                          <div className="px-5 pb-5 pt-0 flex items-center justify-between gap-3">
                            <button
                              type="button"
                              onClick={(e) => {
                                e.stopPropagation();
                                setActiveModalService(service);
                              }}
                              className="inline-flex items-center gap-1 text-[11px] font-medium text-[#8E857F] hover:text-[#221C1A] transition-colors"
                            >
                              <Info size={14} />
                              <span>View Ritual Details</span>
                            </button>

                            <button
                              type="button"
                              onClick={(e) => {
                                e.stopPropagation();
                                toggleService(service.id);
                              }}
                              className={`px-4 py-2 rounded-full text-xs font-semibold tracking-wide transition-all duration-200 flex items-center gap-1.5 ${
                                isSelected
                                  ? "bg-[#B8975A] text-white shadow-xs hover:bg-[#A38349]"
                                  : "bg-[#221C1A] text-white hover:bg-[#B8975A]"
                              }`}
                            >
                              {isSelected ? (
                                <>
                                  <Check size={13} weight="bold" />
                                  <span>Selected</span>
                                </>
                              ) : (
                                <>
                                  <Plus size={13} weight="bold" />
                                  <span>Add Treatment</span>
                                </>
                              )}
                            </button>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </section>
              ))
            )}
          </div>

          {/* ========================================================= */}
          {/* Right Column: Desktop Sticky Concierge Sidebar (4 cols) */}
          {/* ========================================================= */}
          <div className="hidden lg:block lg:col-span-4 pl-2 sticky top-28">
            <div className="bg-[#1C1816] text-white rounded-3xl p-6 sm:p-7 border border-[#3A332F] shadow-2xl relative overflow-hidden">
              {/* Subtle luxury ambient glow */}
              <div className="absolute -top-16 -right-16 w-44 h-44 rounded-full bg-[#B8975A]/15 blur-3xl pointer-events-none" />

              {/* Header */}
              <div className="flex items-center justify-between pb-4 border-b border-white/10 mb-5 relative z-10">
                <div>
                  <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-0.5">
                    Your Ritual Bag
                  </span>
                  <h3 className="font-serif text-xl text-white font-normal">
                    Selected Treatments
                  </h3>
                </div>
                {selectedServices.length > 0 && (
                  <button
                    type="button"
                    onClick={clearAll}
                    className="text-[11px] text-white/50 hover:text-white transition-colors underline"
                  >
                    Clear All
                  </button>
                )}
              </div>

              {/* Selected List or Empty State */}
              {selectedServices.length === 0 ? (
                <div className="py-10 text-center text-white/50 relative z-10">
                  <div className="w-12 h-12 rounded-full bg-white/5 border border-white/10 text-white/30 flex items-center justify-center mx-auto mb-3">
                    <Basket size={22} weight="light" />
                  </div>
                  <p className="text-xs font-light max-w-xs mx-auto leading-relaxed">
                    No treatments selected yet. Pick one or more rituals from the menu to curate your appointment.
                  </p>
                </div>
              ) : (
                <div className="space-y-3 mb-6 max-h-[42vh] overflow-y-auto pr-1 relative z-10 custom-scrollbar">
                  <AnimatePresence>
                    {selectedServices.map((service) => (
                      <motion.div
                        key={service.id}
                        initial={{ opacity: 0, height: 0 }}
                        animate={{ opacity: 1, height: "auto" }}
                        exit={{ opacity: 0, height: 0 }}
                        className="bg-white/5 hover:bg-white/10 rounded-2xl p-3 border border-white/10 flex items-center justify-between gap-3 transition-colors"
                      >
                        <div className="relative w-11 h-11 rounded-xl overflow-hidden shrink-0 bg-white/10">
                          <ServiceImage
                            src={service.image}
                            alt={service.name}
                            sizes="44px"
                            className="object-cover"
                          />
                        </div>
                        <div className="flex-1 min-w-0">
                          <h4 className="text-xs font-medium text-white truncate">
                            {service.name}
                          </h4>
                          <div className="flex items-center gap-2 text-[10px] text-white/60">
                            <span>{service.duration_minutes || 60}m</span>
                            <span>&bull;</span>
                            <span className="text-[#E8C99B] font-medium">
                              {formatRwf(service.price)} RWF
                            </span>
                          </div>
                        </div>
                        <button
                          type="button"
                          onClick={(e) => removeService(service.id, e)}
                          className="w-7 h-7 rounded-full text-white/40 hover:text-rose-400 hover:bg-rose-400/10 flex items-center justify-center transition-colors shrink-0"
                          title="Remove treatment"
                        >
                          <Trash size={14} />
                        </button>
                      </motion.div>
                    ))}
                  </AnimatePresence>
                </div>
              )}

              {/* Total Summary Breakdown */}
              <div className="border-t border-white/10 pt-4 mb-6 relative z-10 space-y-2">
                <div className="flex items-center justify-between text-xs text-white/70">
                  <span>Estimated Total Time</span>
                  <span className="font-medium text-white flex items-center gap-1">
                    <Clock size={13} />
                    <span>{formattedDuration}</span>
                  </span>
                </div>
                <div className="flex items-baseline justify-between pt-1">
                  <span className="text-xs font-light text-white/70">Estimated Total</span>
                  <span className="font-serif text-2xl text-white font-normal">
                    {formatRwf(totalPrice)}{" "}
                    <span className="text-xs font-sans text-[#E8C99B]">RWF</span>
                  </span>
                </div>
              </div>

              {/* Primary Next Step CTA */}
              <button
                type="button"
                disabled={selectedServices.length === 0}
                onClick={handleProceedToStep2}
                className="w-full py-4 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white hover:bg-[#B8975A] hover:text-white transition-all duration-300 shadow-xl disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2 group cursor-pointer relative z-10"
              >
                <CalendarCheck size={16} weight="bold" />
                <span>
                  {selectedServices.length === 0
                    ? "Select at Least 1 Treatment"
                    : `Continue to Select Stylist (${selectedServices.length})`}
                </span>
                <ArrowRight
                  size={15}
                  weight="bold"
                  className="group-hover:translate-x-1 transition-transform"
                />
              </button>

              {/* VIP Concierge WhatsApp Card */}
              <div className="mt-5 p-3.5 rounded-2xl bg-white/5 border border-white/10 flex items-center gap-3 relative z-10">
                <div className="w-8 h-8 rounded-xl bg-[#25D366]/20 text-[#25D366] flex items-center justify-center shrink-0">
                  <WhatsappLogo size={18} weight="fill" />
                </div>
                <div className="text-[11px] leading-tight">
                  <span className="text-white font-medium block mb-0.5">Custom or Bridal Inquiries?</span>
                  <a
                    href="https://wa.me/250790395169?text=Hello%20Isaiah%20Nail%20Bar,%20I%20would%20like%20assistance%20choosing%20treatments%20for%20my%20booking."
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-[#E8C99B] hover:underline"
                  >
                    Chat with Concierge &rarr;
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* ========================================================= */}
      {/* Mobile Sticky Floating Summary Bar (Appears when items > 0) */}
      {/* ========================================================= */}
      <AnimatePresence>
        {selectedServices.length > 0 && (
          <motion.div
            initial={{ y: 100 }}
            animate={{ y: 0 }}
            exit={{ y: 100 }}
            className="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-[#1C1816] text-white border-t border-white/10 shadow-2xl p-4 safe-area-bottom"
          >
            {/* Expandable Drawer for Reviewing Selected Items on Mobile */}
            <AnimatePresence>
              {isMobileDrawerOpen && (
                <motion.div
                  initial={{ height: 0, opacity: 0 }}
                  animate={{ height: "auto", opacity: 1 }}
                  exit={{ height: 0, opacity: 0 }}
                  className="pb-4 mb-3 border-b border-white/10 max-h-56 overflow-y-auto space-y-2 pr-1"
                >
                  <div className="flex items-center justify-between text-xs text-white/70 mb-2">
                    <span>Your Selected Items ({selectedServices.length})</span>
                    <button
                      type="button"
                      onClick={clearAll}
                      className="text-rose-400 text-[11px] underline"
                    >
                      Clear
                    </button>
                  </div>
                  {selectedServices.map((s) => (
                    <div
                      key={s.id}
                      className="flex items-center justify-between gap-2 p-2 bg-white/5 rounded-xl text-xs"
                    >
                      <span className="truncate flex-1 text-white">{s.name}</span>
                      <span className="text-[#E8C99B] whitespace-nowrap">
                        {formatRwf(s.price)} RWF
                      </span>
                      <button
                        type="button"
                        onClick={(e) => removeService(s.id, e)}
                        className="text-white/40 hover:text-rose-400 p-1"
                      >
                        <Trash size={13} />
                      </button>
                    </div>
                  ))}
                </motion.div>
              )}
            </AnimatePresence>

            <div className="flex items-center justify-between gap-3">
              <button
                type="button"
                onClick={() => setIsMobileDrawerOpen(!isMobileDrawerOpen)}
                className="flex items-center gap-2 text-left"
              >
                <div>
                  <div className="flex items-center gap-1.5 text-xs text-[#E8C99B]">
                    <span>{selectedServices.length} Selected</span>
                    <span>&bull;</span>
                    <span>{formattedDuration}</span>
                    {isMobileDrawerOpen ? <CaretDown size={12} /> : <CaretUp size={12} />}
                  </div>
                  <div className="font-serif text-lg font-bold text-white">
                    {formatRwf(totalPrice)} <span className="text-xs font-sans">RWF</span>
                  </div>
                </div>
              </button>

              <button
                type="button"
                onClick={handleProceedToStep2}
                className="px-6 py-3 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white hover:bg-[#B8975A] hover:text-white transition-all shadow-md flex items-center gap-1.5"
              >
                <span>Continue</span>
                <ArrowRight size={14} weight="bold" />
              </button>
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* ========================================================= */}
      {/* LUXURY QUICK VIEW MODAL */}
      {/* ========================================================= */}
      <AnimatePresence>
        {activeModalService && (
          <div
            className="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 lg:p-8 bg-black/70 backdrop-blur-md overflow-y-auto"
            onClick={() => setActiveModalService(null)}
          >
            <motion.div
              initial={{ opacity: 0, scale: 0.95, y: 20 }}
              animate={{ opacity: 1, scale: 1, y: 0 }}
              exit={{ opacity: 0, scale: 0.95, y: 20 }}
              transition={{ duration: 0.25 }}
              onClick={(e) => e.stopPropagation()}
              className="relative w-full max-w-2xl bg-white rounded-3xl overflow-hidden shadow-2xl border border-[#E5DFD5] my-auto"
            >
              {/* Close Button */}
              <button
                type="button"
                onClick={() => setActiveModalService(null)}
                className="absolute top-4 right-4 z-20 w-9 h-9 rounded-full bg-white/90 backdrop-blur-md text-[#221C1A] hover:bg-[#221C1A] hover:text-white flex items-center justify-center shadow-md transition-all cursor-pointer"
                aria-label="Close modal"
              >
                <X size={16} weight="bold" />
              </button>

              <div className="grid grid-cols-1 md:grid-cols-2">
                {/* Modal Left Image */}
                <div className="relative h-56 md:h-full min-h-[220px] bg-[#E5DFD5]">
                  <ServiceImage
                    src={activeModalService.image}
                    alt={activeModalService.name}
                    sizes="(max-width: 768px) 100vw, 350px"
                    className="object-cover object-center"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent md:hidden" />
                </div>

                {/* Modal Right Content */}
                <div className="p-6 sm:p-8 flex flex-col justify-between">
                  <div>
                    <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-1">
                      {activeModalService.category_name}
                    </span>
                    <h3 className="font-serif text-2xl text-[#221C1A] font-normal mb-3">
                      {activeModalService.name}
                    </h3>

                    <div className="flex items-center gap-4 pb-4 mb-4 border-b border-[#E5DFD5]">
                      <div>
                        <span className="text-[10px] text-[#8E857F] uppercase tracking-wider block">
                          Investment
                        </span>
                        <span className="font-serif text-xl font-semibold text-[#221C1A]">
                          {formatRwf(activeModalService.price)} RWF
                        </span>
                      </div>
                      <div className="h-8 w-px bg-[#E5DFD5]" />
                      <div>
                        <span className="text-[10px] text-[#8E857F] uppercase tracking-wider block">
                          Duration
                        </span>
                        <span className="text-sm font-medium text-[#221C1A] flex items-center gap-1 mt-0.5">
                          <Clock size={14} />
                          <span>{activeModalService.duration_minutes || 60} mins</span>
                        </span>
                      </div>
                    </div>

                    <p className="text-xs sm:text-sm text-[#5C5550] font-light leading-relaxed mb-6">
                      {activeModalService.description}
                    </p>
                  </div>

                  <div className="space-y-3 pt-4 border-t border-[#E5DFD5]">
                    <button
                      type="button"
                      onClick={() => {
                        toggleService(activeModalService.id);
                        setActiveModalService(null);
                      }}
                      className={`w-full py-3.5 rounded-full text-xs font-semibold uppercase tracking-wider transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer shadow-md ${
                        selectedIds.includes(activeModalService.id)
                          ? "bg-[#B8975A] text-white hover:bg-[#A38349]"
                          : "bg-[#221C1A] text-white hover:bg-[#B8975A]"
                      }`}
                    >
                      {selectedIds.includes(activeModalService.id) ? (
                        <>
                          <Check size={16} weight="bold" />
                          <span>Selected in Ritual</span>
                        </>
                      ) : (
                        <>
                          <Plus size={16} weight="bold" />
                          <span>Add to Curated Ritual</span>
                        </>
                      )}
                    </button>
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
