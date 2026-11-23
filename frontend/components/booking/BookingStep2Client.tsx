"use client";

import { useState, useEffect, useMemo } from "react";
import Image from "next/image";
import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { motion, AnimatePresence } from "framer-motion";
import {
  ArrowLeft,
  ArrowRight,
  Check,
  Clock,
  SealCheck,
  ShieldCheck,
  UsersThree,
  Info,
  X,
  CalendarCheck,
  WhatsappLogo,
  User,
} from "@phosphor-icons/react";
import { TeamProvider } from "@/lib/team-data";
import { ServiceItem } from "@/lib/services-data";

interface BookingStep2ClientProps {
  providers: TeamProvider[];
  allServices: ServiceItem[];
}

export default function BookingStep2Client({
  providers,
  allServices,
}: BookingStep2ClientProps) {
  const router = useRouter();
  const searchParams = useSearchParams();

  const [selectedServices, setSelectedServices] = useState<ServiceItem[]>([]);
  const [selectedProviderId, setSelectedProviderId] = useState<number | null>(null);
  const [activeBioProvider, setActiveBioProvider] = useState<TeamProvider | null>(null);

  // Load selected services from URL or localStorage
  useEffect(() => {
    const servicesParam = searchParams.get("services");
    let ids: number[] = [];

    if (servicesParam) {
      ids = servicesParam
        .split(",")
        .map((s) => parseInt(s.trim(), 10))
        .filter((n) => !isNaN(n));
    } else {
      try {
        const saved = localStorage.getItem("isaiah_booking_selection");
        if (saved) {
          ids = JSON.parse(saved);
        }
      } catch {
        // Ignore
      }
    }

    if (ids.length > 0) {
      const matched = allServices.filter((s) => ids.includes(s.id));
      setSelectedServices(matched);
    }

    // Load saved provider from URL or localStorage
    const providerParam = searchParams.get("provider");
    if (providerParam !== null) {
      const pid = parseInt(providerParam, 10);
      if (!isNaN(pid)) {
        setSelectedProviderId(pid);
        return;
      }
    }

    try {
      const savedProvider = localStorage.getItem("isaiah_booking_provider");
      if (savedProvider !== null) {
        const pid = parseInt(savedProvider, 10);
        if (!isNaN(pid)) setSelectedProviderId(pid);
      }
    } catch {
      // Ignore
    }
  }, [searchParams, allServices]);

  // Persist provider selection
  const handleSelectProvider = (id: number) => {
    setSelectedProviderId(id);
    try {
      localStorage.setItem("isaiah_booking_provider", String(id));
    } catch {
      // Ignore
    }
  };

  // Calculations
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

  const formatRwf = (amount: number) => {
    return new Intl.NumberFormat("en-RW", {
      style: "decimal",
      maximumFractionDigits: 0,
    }).format(amount);
  };

  // Get selected provider object
  const selectedProvider = useMemo(() => {
    if (selectedProviderId === null) return null;
    if (selectedProviderId === 0) {
      return {
        id: 0,
        name: "First Available Artist",
        role: "Optimal Availability",
        photo: "/banner.jpg",
        bio: "Our concierge team pairs you with the best available European-certified artist suited for your scheduled rituals.",
      };
    }
    return providers.find((p) => p.id === selectedProviderId) || null;
  }, [selectedProviderId, providers]);

  // Check if provider specialties match any selected service
  const isSpecialtyMatch = (provider: TeamProvider) => {
    if (selectedServices.length === 0) return false;
    const serviceNames = selectedServices.map((s) => s.name.toLowerCase()).join(" ");
    return provider.specialties.some((spec) => {
      const specLower = spec.toLowerCase();
      return serviceNames.includes(specLower) ||
        (specLower.includes("russian") && serviceNames.includes("russian")) ||
        (specLower.includes("pedicure") && serviceNames.includes("pedicure")) ||
        (specLower.includes("gel") && serviceNames.includes("gel")) ||
        (specLower.includes("art") && serviceNames.includes("art"));
    });
  };

  const handleProceedToStep3 = () => {
    if (selectedProviderId === null) return;
    const servicesQuery = selectedServices.map((s) => s.id).join(",");
    router.push(`/booking/step3?services=${servicesQuery}&provider=${selectedProviderId}`);
  };

  return (
    <div className="bg-[#FAF7F2] min-h-screen py-10 lg:py-16">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* If no services selected, show helpful alert */}
        {selectedServices.length === 0 && (
          <div className="mb-8 p-4 sm:p-5 rounded-2xl bg-white border border-[#E5DFD5] shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div className="flex items-center gap-3">
              <div className="w-9 h-9 rounded-xl bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center shrink-0">
                <Info size={18} weight="fill" />
              </div>
              <div>
                <p className="text-xs sm:text-sm font-medium text-[#221C1A]">
                  No treatments selected yet
                </p>
                <p className="text-xs text-[#5C5550] font-light">
                  Pick your preferred artist now, or return to step 1 to curate your nail treatments first.
                </p>
              </div>
            </div>
            <Link
              href="/booking"
              className="px-4 py-2 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-colors shrink-0"
            >
              Curate Treatments &rarr;
            </Link>
          </div>
        )}

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
          {/* ========================================================= */}
          {/* Left Column: Artists Selection (8 cols) */}
          {/* ========================================================= */}
          <div className="lg:col-span-8 space-y-8">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-[#E5DFD5]">
              <div>
                <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-1">
                  Master Artisans
                </span>
                <h2 className="font-serif text-2xl sm:text-3xl text-[#221C1A] font-normal">
                  Choose Your Dedicated Specialist
                </h2>
              </div>
              <span className="text-xs text-[#8E857F] font-light">
                {providers.length + 1} artist choices available
              </span>
            </div>

            {/* Providers Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
              {/* Option 0: First Available Specialist (Auto-Assign) */}
              <div
                onClick={() => handleSelectProvider(0)}
                className={`p-6 rounded-3xl border transition-all duration-300 cursor-pointer flex flex-col justify-between ${
                  selectedProviderId === 0
                    ? "bg-white border-[#B8975A] ring-2 ring-[#B8975A]/25 shadow-lg bg-gradient-to-b from-[#FAF7F2] to-white"
                    : "bg-white border-[#E5DFD5] hover:border-[#221C1A]/40 hover:shadow-md"
                }`}
              >
                <div>
                  <div className="flex items-center justify-between mb-4">
                    <div className="w-14 h-14 rounded-2xl bg-[#FAF7F2] border border-[#B8975A]/25 text-[#B8975A] flex items-center justify-center">
                      <UsersThree size={26} weight="light" />
                    </div>
                    <div
                      className={`w-6 h-6 rounded-full flex items-center justify-center transition-all ${
                        selectedProviderId === 0
                          ? "bg-[#B8975A] text-white scale-100 shadow-xs"
                          : "border border-black/15 bg-white text-transparent scale-90"
                      }`}
                    >
                      <Check size={14} weight="bold" />
                    </div>
                  </div>

                  <span className="text-[10px] font-semibold uppercase tracking-widest text-[#B8975A] block mb-1">
                    Flexible Preference
                  </span>
                  <h3 className="font-serif text-lg text-[#221C1A] font-semibold mb-2">
                    First Available Specialist
                  </h3>
                  <p className="text-xs text-[#5C5550] font-light leading-relaxed mb-4">
                    Maximize your scheduling flexibility. Our atelier concierge will automatically pair you with the best available European-certified artist suited for your treatments.
                  </p>
                </div>

                <div className="pt-3 border-t border-[#E5DFD5] flex items-center justify-between text-[11px] text-[#8E857F]">
                  <span className="flex items-center gap-1.5 text-[#221C1A] font-medium">
                    <ShieldCheck size={14} weight="fill" className="text-[#B8975A]" />
                    <span>European Certified</span>
                  </span>
                  <span className="text-[#34A853] font-medium">Fastest Booking</span>
                </div>
              </div>

              {/* Individual Artists */}
              {providers.map((artist) => {
                const isSelected = selectedProviderId === artist.id;
                const isRecommended = isSpecialtyMatch(artist);

                return (
                  <div
                    key={artist.id}
                    onClick={() => handleSelectProvider(artist.id)}
                    className={`p-6 rounded-3xl border transition-all duration-300 cursor-pointer flex flex-col justify-between ${
                      isSelected
                        ? "bg-white border-[#B8975A] ring-2 ring-[#B8975A]/25 shadow-lg bg-gradient-to-b from-[#FAF7F2] to-white"
                        : "bg-white border-[#E5DFD5] hover:border-[#221C1A]/40 hover:shadow-md"
                    }`}
                  >
                    <div>
                      {/* Top Header: Avatar + Active Indicator + Radio Check */}
                      <div className="flex items-center justify-between mb-4">
                        <div className="relative">
                          <div className="relative w-16 h-16 rounded-2xl overflow-hidden border-2 border-white shadow-xs">
                            <Image
                              src={artist.photo}
                              alt={artist.name}
                              fill
                              sizes="64px"
                              className="object-cover object-center"
                            />
                          </div>
                          {/* Live Active Status Indicator (Pulsing Green Dot) */}
                          <span className="absolute -bottom-1 -right-1 flex h-3.5 w-3.5">
                            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75" />
                            <span className="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500 border-2 border-white" />
                          </span>
                        </div>

                        <div className="flex items-center gap-2">
                          {isRecommended && (
                            <span className="px-2 py-0.5 rounded-full bg-[#B8975A]/15 text-[#B8975A] text-[9px] font-semibold uppercase tracking-wider border border-[#B8975A]/30">
                              Top Match
                            </span>
                          )}
                          <div
                            className={`w-6 h-6 rounded-full flex items-center justify-center transition-all ${
                              isSelected
                                ? "bg-[#B8975A] text-white scale-100 shadow-xs"
                                : "border border-black/15 bg-white text-transparent scale-90"
                            }`}
                          >
                            <Check size={14} weight="bold" />
                          </div>
                        </div>
                      </div>

                      <h3 className="font-serif text-lg text-[#221C1A] font-semibold mb-0.5">
                        {artist.name}
                      </h3>
                      <span className="text-[11px] font-medium uppercase tracking-wider text-[#B8975A] block mb-2">
                        {artist.role}
                      </span>

                      <p className="text-xs text-[#5C5550] font-light leading-relaxed line-clamp-2 mb-3">
                        {artist.bio}
                      </p>

                      {/* Specialties Pill Badges */}
                      <div className="flex flex-wrap gap-1.5 mb-4">
                        {artist.specialties.map((spec) => (
                          <span
                            key={spec}
                            className="px-2 py-0.5 rounded-md bg-[#FAF7F2] text-[10px] text-[#5C5550] border border-[#E5DFD5]"
                          >
                            {spec}
                          </span>
                        ))}
                      </div>
                    </div>

                    {/* Card Footer: Experience & Bio Button */}
                    <div className="pt-3 border-t border-[#E5DFD5] flex items-center justify-between text-[11px] text-[#8E857F]">
                      <span className="flex items-center gap-1.5 text-[#221C1A] font-medium">
                        <SealCheck size={14} weight="fill" className="text-[#B8975A]" />
                        <span>{artist.experience_years || 4}+ Years Precision Exp</span>
                      </span>

                      <button
                        type="button"
                        onClick={(e) => {
                          e.stopPropagation();
                          setActiveBioProvider(artist);
                        }}
                        className="text-[11px] text-[#B8975A] hover:underline font-medium inline-flex items-center gap-0.5"
                      >
                        <Info size={13} />
                        <span>Bio</span>
                      </button>
                    </div>
                  </div>
                );
              })}
            </div>

            {/* Back to Step 1 Button */}
            <div className="pt-4 flex items-center justify-between">
              <Link
                href="/booking"
                className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[#5C5550] hover:text-[#221C1A] transition-colors"
              >
                <ArrowLeft size={16} />
                <span>&larr; Back to Curate Treatments</span>
              </Link>
            </div>
          </div>

          {/* ========================================================= */}
          {/* Right Column: Desktop Sticky Concierge Sidebar (4 cols) */}
          {/* ========================================================= */}
          <div className="hidden lg:block lg:col-span-4 pl-2 sticky top-28">
            <div className="bg-[#1C1816] text-white rounded-3xl p-6 sm:p-7 border border-[#3A332F] shadow-2xl relative overflow-hidden">
              <div className="absolute -top-16 -right-16 w-44 h-44 rounded-full bg-[#B8975A]/15 blur-3xl pointer-events-none" />

              {/* Header */}
              <div className="flex items-center justify-between pb-4 border-b border-white/10 mb-5 relative z-10">
                <div>
                  <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-0.5">
                    Step 2 of 5 &bull; Summary
                  </span>
                  <h3 className="font-serif text-xl text-white font-normal">
                    Your Reservation
                  </h3>
                </div>
                <Link
                  href="/booking"
                  className="text-[11px] text-white/50 hover:text-white transition-colors underline"
                >
                  Edit Treatments
                </Link>
              </div>

              {/* Selected Artist Highlight Card in Sidebar */}
              <div className="mb-5 p-3.5 rounded-2xl bg-white/5 border border-white/10 relative z-10">
                <span className="text-[10px] uppercase tracking-wider text-[#B8975A] block mb-1.5">
                  Selected Artisan
                </span>
                {selectedProvider ? (
                  <div className="flex items-center gap-3">
                    <div className="relative w-10 h-10 rounded-xl overflow-hidden bg-white/10 shrink-0">
                      <Image
                        src={selectedProvider.photo}
                        alt={selectedProvider.name}
                        fill
                        sizes="40px"
                        className="object-cover"
                      />
                    </div>
                    <div className="min-w-0 flex-1">
                      <h4 className="text-xs font-semibold text-white truncate">
                        {selectedProvider.name}
                      </h4>
                      <p className="text-[10px] text-white/60 truncate">
                        {selectedProvider.role}
                      </p>
                    </div>
                    <span className="text-[10px] text-[#34A853] font-medium shrink-0">
                      &bull; Assigned
                    </span>
                  </div>
                ) : (
                  <div className="flex items-center gap-2 text-xs text-white/50 py-1">
                    <User size={16} />
                    <span>Choose an artisan on the left to proceed</span>
                  </div>
                )}
              </div>

              {/* Selected Services Itemized Recap */}
              <div className="space-y-2.5 mb-5 max-h-48 overflow-y-auto pr-1 relative z-10 custom-scrollbar">
                {selectedServices.length === 0 ? (
                  <p className="text-xs text-white/50 font-light italic">
                    No services selected yet.
                  </p>
                ) : (
                  selectedServices.map((s) => (
                    <div
                      key={s.id}
                      className="p-2.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between gap-2 text-xs"
                    >
                      <div className="truncate">
                        <h5 className="font-medium text-white truncate">{s.name}</h5>
                        <span className="text-[10px] text-white/50">
                          {s.duration_minutes || 60} mins
                        </span>
                      </div>
                      <span className="text-[#E8C99B] font-medium shrink-0">
                        {formatRwf(s.price)} RWF
                      </span>
                    </div>
                  ))
                )}
              </div>

              {/* Summary Breakdown */}
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

              {/* Continue to Step 3 Button */}
              <button
                type="button"
                disabled={selectedProviderId === null}
                onClick={handleProceedToStep3}
                className="w-full py-4 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white hover:bg-[#B8975A] hover:text-white transition-all duration-300 shadow-xl disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2 group cursor-pointer relative z-10"
              >
                <CalendarCheck size={16} weight="bold" />
                <span>
                  {selectedProviderId === null
                    ? "Select an Artist Above"
                    : "Continue to Date & Time"}
                </span>
                <ArrowRight
                  size={15}
                  weight="bold"
                  className="group-hover:translate-x-1 transition-transform"
                />
              </button>

              {/* WhatsApp Support Prompt */}
              <div className="mt-5 p-3.5 rounded-2xl bg-white/5 border border-white/10 flex items-center gap-3 relative z-10">
                <div className="w-8 h-8 rounded-xl bg-[#25D366]/20 text-[#25D366] flex items-center justify-center shrink-0">
                  <WhatsappLogo size={18} weight="fill" />
                </div>
                <div className="text-[11px] leading-tight">
                  <span className="text-white font-medium block mb-0.5">
                    Prefer a specific artist not listed?
                  </span>
                  <a
                    href="https://wa.me/250790395169?text=Hello%20Isaiah%20Nail%20Bar,%20I%20would%20like%20to%20request%20a%20specific%20nail%20artist%20for%20my%20booking."
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-[#E8C99B] hover:underline"
                  >
                    Chat on WhatsApp Concierge &rarr;
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* ========================================================= */}
      {/* Mobile Sticky Floating Summary Bar (Always Visible on Mobile) */}
      {/* ========================================================= */}
      <div className="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-[#1C1816] text-white border-t border-white/10 shadow-2xl p-4 safe-area-bottom">
        <div className="flex items-center justify-between gap-3">
          <div>
            <div className="flex items-center gap-1.5 text-xs text-[#E8C99B]">
              <span>{selectedProvider ? selectedProvider.name : "Select an Artist"}</span>
              <span>&bull;</span>
              <span>{formattedDuration}</span>
            </div>
            <div className="font-serif text-lg font-bold text-white">
              {formatRwf(totalPrice)} <span className="text-xs font-sans">RWF</span>
            </div>
          </div>

          <button
            type="button"
            disabled={selectedProviderId === null}
            onClick={handleProceedToStep3}
            className="px-6 py-3 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white hover:bg-[#B8975A] hover:text-white transition-all shadow-md flex items-center gap-1.5 disabled:opacity-40 disabled:cursor-not-allowed"
          >
            <span>Continue</span>
            <ArrowRight size={14} weight="bold" />
          </button>
        </div>
      </div>

      {/* ========================================================= */}
      {/* ARTIST BIO & PROFILE MODAL */}
      {/* ========================================================= */}
      <AnimatePresence>
        {activeBioProvider && (
          <div
            className="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 lg:p-8 bg-black/70 backdrop-blur-md overflow-y-auto"
            onClick={() => setActiveBioProvider(null)}
          >
            <motion.div
              initial={{ opacity: 0, scale: 0.95, y: 20 }}
              animate={{ opacity: 1, scale: 1, y: 0 }}
              exit={{ opacity: 0, scale: 0.95, y: 20 }}
              transition={{ duration: 0.25 }}
              onClick={(e) => e.stopPropagation()}
              className="relative w-full max-w-xl bg-white rounded-3xl overflow-hidden shadow-2xl border border-[#E5DFD5] my-auto"
            >
              <button
                type="button"
                onClick={() => setActiveBioProvider(null)}
                className="absolute top-4 right-4 z-20 w-9 h-9 rounded-full bg-white/90 backdrop-blur-md text-[#221C1A] hover:bg-[#221C1A] hover:text-white flex items-center justify-center shadow-md transition-all cursor-pointer"
                aria-label="Close modal"
              >
                <X size={16} weight="bold" />
              </button>

              <div className="grid grid-cols-1 sm:grid-cols-5">
                <div className="sm:col-span-2 relative h-64 sm:h-auto min-h-[200px] bg-[#E5DFD5]">
                  <Image
                    src={activeBioProvider.photo}
                    alt={activeBioProvider.name}
                    fill
                    sizes="(max-width: 640px) 100vw, 240px"
                    className="object-cover object-center"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent sm:hidden" />
                </div>

                <div className="sm:col-span-3 p-6 sm:p-8 flex flex-col justify-between">
                  <div>
                    <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-1">
                      {activeBioProvider.role}
                    </span>
                    <h3 className="font-serif text-2xl text-[#221C1A] font-normal mb-3">
                      {activeBioProvider.name}
                    </h3>

                    <div className="flex items-center gap-2 mb-4 pb-3 border-b border-[#E5DFD5] text-xs text-[#5C5550]">
                      <SealCheck size={16} weight="fill" className="text-[#B8975A]" />
                      <span>{activeBioProvider.experience_years || 4}+ Years European Precision Exp</span>
                    </div>

                    <p className="text-xs sm:text-sm text-[#5C5550] font-light leading-relaxed mb-5">
                      {activeBioProvider.bio}
                    </p>

                    <div className="mb-6">
                      <span className="text-[10px] font-semibold uppercase tracking-wider text-[#8E857F] block mb-2">
                        Specialized Crafts
                      </span>
                      <div className="flex flex-wrap gap-1.5">
                        {activeBioProvider.specialties.map((spec) => (
                          <span
                            key={spec}
                            className="px-2.5 py-1 rounded-md bg-[#FAF7F2] text-[11px] text-[#221C1A] border border-[#E5DFD5]"
                          >
                            {spec}
                          </span>
                        ))}
                      </div>
                    </div>
                  </div>

                  <button
                    type="button"
                    onClick={() => {
                      handleSelectProvider(activeBioProvider.id);
                      setActiveBioProvider(null);
                    }}
                    className="w-full py-3 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-colors shadow-md flex items-center justify-center gap-2"
                  >
                    <Check size={15} weight="bold" />
                    <span>Select {activeBioProvider.name} for Ritual</span>
                  </button>
                </div>
              </div>
            </motion.div>
          </div>
        )}
      </AnimatePresence>
    </div>
  );
}
