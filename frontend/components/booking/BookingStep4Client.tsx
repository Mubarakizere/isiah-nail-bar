"use client";

import { useState, useEffect, useMemo } from "react";
import Image from "next/image";
import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { motion, AnimatePresence } from "framer-motion";
import {
  ArrowLeft,
  ArrowRight,
  CalendarCheck,
  Clock,
  MapPin,
  House,
  Storefront,
  Car,
  Check,
  Info,
  WhatsappLogo,
  ShieldCheck,
  User,
  PhoneCall,
  EnvelopeSimple,
  CreditCard,
  DeviceMobile,
  CurrencyCircleDollar,
  Receipt,
  CheckCircle,
} from "@phosphor-icons/react";
import { TeamProvider } from "@/lib/team-data";
import { ServiceItem } from "@/lib/services-data";

interface BookingStep4ClientProps {
  providers: TeamProvider[];
  allServices: ServiceItem[];
}

export default function BookingStep4Client({
  providers,
  allServices,
}: BookingStep4ClientProps) {
  const router = useRouter();
  const searchParams = useSearchParams();

  // State inherited from Steps 1, 2, 3
  const [selectedServices, setSelectedServices] = useState<ServiceItem[]>([]);
  const [selectedProviderId, setSelectedProviderId] = useState<number | null>(null);
  const [selectedDate, setSelectedDate] = useState<string>("");
  const [selectedTime, setSelectedTime] = useState<string>("");
  const [locationType, setLocationType] = useState<"salon" | "home">("salon");
  const [homeAddress, setHomeAddress] = useState<string>("");
  const [wantsPickup, setWantsPickup] = useState<boolean>(false);
  const [pickupFee, setPickupFee] = useState<number>(0);
  const [pickupAddress, setPickupAddress] = useState<string>("");

  // Step 4 Form State
  const [guestName, setGuestName] = useState("");
  const [guestPhone, setGuestPhone] = useState("");
  const [guestEmail, setGuestEmail] = useState("");
  const [specialNotes, setSpecialNotes] = useState("");
  const [paymentOption, setPaymentOption] = useState<"full" | "deposit">("full");
  const [paymentMethod, setPaymentMethod] = useState<"momo" | "card">("momo");

  // Load prior data
  useEffect(() => {
    // 1. Services
    const servicesParam = searchParams.get("services");
    let serviceIds: number[] = [];
    if (servicesParam) {
      serviceIds = servicesParam
        .split(",")
        .map((s) => parseInt(s.trim(), 10))
        .filter((n) => !isNaN(n));
    } else {
      try {
        const saved = localStorage.getItem("isaiah_booking_selection");
        if (saved) serviceIds = JSON.parse(saved);
      } catch {
        // Ignore
      }
    }
    if (serviceIds.length > 0) {
      const matched = allServices.filter((s) => serviceIds.includes(s.id));
      setSelectedServices(matched);
    }

    // 2. Provider
    const providerParam = searchParams.get("provider");
    if (providerParam !== null) {
      const pid = parseInt(providerParam, 10);
      if (!isNaN(pid)) setSelectedProviderId(pid);
    } else {
      try {
        const savedProvider = localStorage.getItem("isaiah_booking_provider");
        if (savedProvider !== null) {
          const pid = parseInt(savedProvider, 10);
          if (!isNaN(pid)) setSelectedProviderId(pid);
        }
      } catch {
        // Ignore
      }
    }

    // 3. Date & Time from URL or LocalStorage
    const dateParam = searchParams.get("date");
    if (dateParam) setSelectedDate(dateParam);
    const timeParam = searchParams.get("time");
    if (timeParam) setSelectedTime(timeParam);
    const locParam = searchParams.get("location");
    if (locParam === "home") setLocationType("home");

    // Check step 3 saved data
    try {
      const savedStep3 = localStorage.getItem("isaiah_booking_step3_data");
      if (savedStep3) {
        const parsed = JSON.parse(savedStep3);
        if (parsed.date && !dateParam) setSelectedDate(parsed.date);
        if (parsed.time && !timeParam) setSelectedTime(parsed.time);
        if (parsed.locationType) setLocationType(parsed.locationType);
        if (parsed.homeAddress) setHomeAddress(parsed.homeAddress);
        if (parsed.wantsPickup) {
          setWantsPickup(true);
          setPickupFee(parsed.pickupFee || 5000);
          if (parsed.pickupAddress) setPickupAddress(parsed.pickupAddress);
        }
      }
    } catch {
      // Ignore
    }

    // Check guest info in localstorage if previously filled
    try {
      const savedGuest = localStorage.getItem("isaiah_booking_guest_info");
      if (savedGuest) {
        const parsed = JSON.parse(savedGuest);
        if (parsed.name) setGuestName(parsed.name);
        if (parsed.phone) setGuestPhone(parsed.phone);
        if (parsed.email) setGuestEmail(parsed.email);
        if (parsed.notes) setSpecialNotes(parsed.notes);
      }
    } catch {
      // Ignore
    }
  }, [searchParams, allServices]);

  // Selected Provider Object
  const selectedProvider = useMemo(() => {
    if (selectedProviderId === null) return null;
    if (selectedProviderId === 0) {
      return {
        id: 0,
        name: "First Available Artist",
        role: "Optimal Availability",
        photo: "/banner.jpg",
      };
    }
    return providers.find((p) => p.id === selectedProviderId) || null;
  }, [selectedProviderId, providers]);

  // Pricing Calculations
  const baseServicePrice = useMemo(() => {
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

  const locationMultiplier = locationType === "home" ? 2 : 1;
  const currentPickupFee = wantsPickup && locationType === "salon" ? pickupFee : 0;
  const totalInvestment = baseServicePrice * locationMultiplier + currentPickupFee;

  // Deposit is 40% of total
  const depositAmount = useMemo(() => {
    return Math.round(totalInvestment * 0.4);
  }, [totalInvestment]);

  const payableToday = paymentOption === "full" ? totalInvestment : depositAmount;
  const remainingAtSalon = paymentOption === "full" ? 0 : totalInvestment - depositAmount;

  const formatRwf = (amount: number) => {
    return new Intl.NumberFormat("en-RW", {
      style: "decimal",
      maximumFractionDigits: 0,
    }).format(amount);
  };

  // Formatted date string
  const formattedSelectedDate = useMemo(() => {
    if (!selectedDate) return "Date to be scheduled";
    const [y, m, d] = selectedDate.split("-").map(Number);
    const dateObj = new Date(y, m - 1, d);
    return dateObj.toLocaleDateString("en-US", {
      weekday: "short",
      month: "short",
      day: "numeric",
      year: "numeric",
    });
  }, [selectedDate]);

  // Handle Proceed to Step 5
  const handleProceedToStep5 = (e: React.FormEvent) => {
    e.preventDefault();

    if (!guestName.trim() || !guestPhone.trim()) {
      alert("Please provide your name and contact phone number.");
      return;
    }

    const guestData = {
      name: guestName.trim(),
      phone: guestPhone.trim(),
      email: guestEmail.trim(),
      notes: specialNotes.trim(),
      paymentOption,
      paymentMethod,
      payableToday,
      remainingAtSalon,
      totalInvestment,
    };

    try {
      localStorage.setItem("isaiah_booking_guest_info", JSON.stringify(guestData));
    } catch {
      // Ignore
    }

    const servicesQuery = selectedServices.map((s) => s.id).join(",");
    const providerQuery = selectedProviderId !== null ? `&provider=${selectedProviderId}` : "";
    const dateQuery = `&date=${encodeURIComponent(selectedDate)}`;
    const timeQuery = `&time=${encodeURIComponent(selectedTime)}`;
    const locQuery = `&location=${locationType}`;

    router.push(
      `/booking/step5?services=${servicesQuery}${providerQuery}${dateQuery}${timeQuery}${locQuery}`
    );
  };

  return (
    <div className="bg-[#FAF7F2] min-h-screen py-10 lg:py-16">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form onSubmit={handleProceedToStep5}>
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            {/* ========================================================= */}
            {/* Left Column: Guest Info & Payment Preference (8 cols) */}
            {/* ========================================================= */}
            <div className="lg:col-span-8 space-y-10">
              {/* 1. Guest Identification Details */}
              <div className="bg-white rounded-3xl p-6 sm:p-8 border border-[#E5DFD5]">
                <div className="flex items-center justify-between pb-4 border-b border-[#E5DFD5] mb-6">
                  <div>
                    <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-1">
                      01 &bull; Guest Identification
                    </span>
                    <h2 className="font-serif text-2xl sm:text-3xl text-[#221C1A] font-normal">
                      Your Contact Information
                    </h2>
                  </div>
                  <span className="text-xs text-[#8E857F] font-light hidden sm:inline-block">
                    Instant confirmation via SMS &amp; WhatsApp
                  </span>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                  {/* Full Name */}
                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-2">
                      Full Name <span className="text-[#C4847A]">*</span>
                    </label>
                    <div className="relative">
                      <User size={16} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8E857F]" />
                      <input
                        type="text"
                        required
                        value={guestName}
                        onChange={(e) => setGuestName(e.target.value)}
                        placeholder="e.g. Marie Claire"
                        className="w-full pl-10 pr-4 py-3 bg-[#FAF7F2] border border-[#E5DFD5] focus:border-[#221C1A] rounded-2xl text-xs sm:text-sm text-[#221C1A] placeholder-[#8E857F] outline-none transition-colors"
                      />
                    </div>
                  </div>

                  {/* Phone Number */}
                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-2">
                      Phone Number (MoMo / WhatsApp) <span className="text-[#C4847A]">*</span>
                    </label>
                    <div className="relative">
                      <span className="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-medium text-[#8E857F] border-r border-[#E5DFD5] pr-2 flex items-center gap-1">
                        <PhoneCall size={14} />
                        <span>+250</span>
                      </span>
                      <input
                        type="tel"
                        required
                        value={guestPhone}
                        onChange={(e) => setGuestPhone(e.target.value)}
                        placeholder="788 123 456"
                        className="w-full pl-22 pr-4 py-3 bg-[#FAF7F2] border border-[#E5DFD5] focus:border-[#221C1A] rounded-2xl text-xs sm:text-sm text-[#221C1A] placeholder-[#8E857F] outline-none transition-colors font-medium"
                      />
                    </div>
                  </div>
                </div>

                {/* Email Address */}
                <div className="mb-5">
                  <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-2">
                    Email Address <span className="text-[#8E857F] font-light">(For Calendar Invite &amp; Digital Receipt)</span>
                  </label>
                  <div className="relative">
                    <EnvelopeSimple size={16} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8E857F]" />
                    <input
                      type="email"
                      value={guestEmail}
                      onChange={(e) => setGuestEmail(e.target.value)}
                      placeholder="marieclaire@example.com"
                      className="w-full pl-10 pr-4 py-3 bg-[#FAF7F2] border border-[#E5DFD5] focus:border-[#221C1A] rounded-2xl text-xs sm:text-sm text-[#221C1A] placeholder-[#8E857F] outline-none transition-colors"
                    />
                  </div>
                </div>

                {/* Special Requests or Allergy Notes */}
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-2">
                    Special Notes, Inquiries or Nail Sensitivities <span className="text-[#8E857F] font-light">(Optional)</span>
                  </label>
                  <textarea
                    rows={3}
                    value={specialNotes}
                    onChange={(e) => setSpecialNotes(e.target.value)}
                    placeholder="Mention previous gel extensions needing removal, sensitive cuticles, color preferences, or bridal inspiration..."
                    className="w-full px-4 py-3 bg-[#FAF7F2] border border-[#E5DFD5] focus:border-[#221C1A] rounded-2xl text-xs sm:text-sm text-[#221C1A] placeholder-[#8E857F] outline-none transition-colors resize-none"
                  />
                </div>
              </div>

              {/* 2. Payment Amount Selection */}
              <div>
                <div className="flex items-center justify-between pb-3 border-b border-[#E5DFD5] mb-5">
                  <div>
                    <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-1">
                      02 &bull; Payment Amount
                    </span>
                    <h2 className="font-serif text-2xl sm:text-3xl text-[#221C1A] font-normal">
                      Select Payment Option
                    </h2>
                  </div>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  {/* Full Payment */}
                  <div
                    onClick={() => setPaymentOption("full")}
                    className={`p-6 rounded-3xl border transition-all duration-200 cursor-pointer flex flex-col justify-between ${
                      paymentOption === "full"
                        ? "bg-white border-[#B8975A] ring-2 ring-[#B8975A]/25 shadow-md bg-gradient-to-b from-[#FAF7F2] to-white"
                        : "bg-white border-[#E5DFD5] hover:border-[#221C1A]/40"
                    }`}
                  >
                    <div>
                      <div className="flex items-center justify-between mb-4">
                        <div className="w-10 h-10 rounded-2xl bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center">
                          <CheckCircle size={22} weight="light" />
                        </div>
                        <div
                          className={`w-6 h-6 rounded-full flex items-center justify-center transition-all ${
                            paymentOption === "full"
                              ? "bg-[#B8975A] text-white shadow-xs"
                              : "border border-black/15 bg-white text-transparent"
                          }`}
                        >
                          <Check size={14} weight="bold" />
                        </div>
                      </div>

                      <h3 className="font-serif text-lg text-[#221C1A] font-semibold mb-1">
                        Pay in Full (100%)
                      </h3>
                      <p className="text-xs text-[#5C5550] font-light leading-relaxed mb-4">
                        Settle your complete appointment in advance for a tranquil, seamless departure upon ritual conclusion.
                      </p>
                    </div>

                    <div className="pt-3 border-t border-[#E5DFD5] flex items-baseline justify-between">
                      <span className="text-xs text-[#8E857F]">Due Now</span>
                      <span className="font-serif text-xl font-bold text-[#221C1A]">
                        {formatRwf(totalInvestment)} RWF
                      </span>
                    </div>
                  </div>

                  {/* 40% Deposit */}
                  <div
                    onClick={() => setPaymentOption("deposit")}
                    className={`p-6 rounded-3xl border transition-all duration-200 cursor-pointer flex flex-col justify-between ${
                      paymentOption === "deposit"
                        ? "bg-white border-[#B8975A] ring-2 ring-[#B8975A]/25 shadow-md bg-gradient-to-b from-[#FAF7F2] to-white"
                        : "bg-white border-[#E5DFD5] hover:border-[#221C1A]/40"
                    }`}
                  >
                    <div>
                      <div className="flex items-center justify-between mb-4">
                        <div className="w-10 h-10 rounded-2xl bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center">
                          <CurrencyCircleDollar size={22} weight="light" />
                        </div>
                        <div
                          className={`w-6 h-6 rounded-full flex items-center justify-center transition-all ${
                            paymentOption === "deposit"
                              ? "bg-[#B8975A] text-white shadow-xs"
                              : "border border-black/15 bg-white text-transparent"
                          }`}
                        >
                          <Check size={14} weight="bold" />
                        </div>
                      </div>

                      <h3 className="font-serif text-lg text-[#221C1A] font-semibold mb-1">
                        Sanctuary Deposit (40%)
                      </h3>
                      <p className="text-xs text-[#5C5550] font-light leading-relaxed mb-4">
                        Secure your master artist&apos;s calendar slot now. The remaining 60% balance is payable after treatment at the studio.
                      </p>
                    </div>

                    <div className="pt-3 border-t border-[#E5DFD5] flex items-baseline justify-between">
                      <span className="text-xs text-[#8E857F]">Deposit Due Now</span>
                      <span className="font-serif text-xl font-bold text-[#B8975A]">
                        {formatRwf(depositAmount)} RWF
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              {/* 3. Payment Method */}
              <div>
                <div className="flex items-center justify-between pb-3 border-b border-[#E5DFD5] mb-5">
                  <div>
                    <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-1">
                      03 &bull; Payment Channel
                    </span>
                    <h2 className="font-serif text-2xl sm:text-3xl text-[#221C1A] font-normal">
                      Preferred Method
                    </h2>
                  </div>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  {/* Mobile Money */}
                  <div
                    onClick={() => setPaymentMethod("momo")}
                    className={`p-5 rounded-3xl border transition-all duration-200 cursor-pointer flex flex-col justify-between ${
                      paymentMethod === "momo"
                        ? "bg-white border-[#B8975A] ring-2 ring-[#B8975A]/25 shadow-md bg-gradient-to-b from-[#FAF7F2] to-white"
                        : "bg-white border-[#E5DFD5] hover:border-[#221C1A]/40"
                    }`}
                  >
                    <div>
                      <div className="w-10 h-10 rounded-2xl bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center mb-3">
                        <DeviceMobile size={22} weight="light" />
                      </div>
                      <h4 className="font-serif text-base font-semibold text-[#221C1A] mb-1">
                        MTN / Airtel MoMo
                      </h4>
                      <p className="text-xs text-[#5C5550] font-light">
                        Instant prompt sent to your Rwandan mobile number.
                      </p>
                    </div>
                    <span className="text-[10px] font-semibold text-[#34A853] mt-3 block">
                      Local Instant Pay
                    </span>
                  </div>

                  {/* Credit / Debit Card */}
                  <div
                    onClick={() => setPaymentMethod("card")}
                    className={`p-5 rounded-3xl border transition-all duration-200 cursor-pointer flex flex-col justify-between ${
                      paymentMethod === "card"
                        ? "bg-white border-[#B8975A] ring-2 ring-[#B8975A]/25 shadow-md bg-gradient-to-b from-[#FAF7F2] to-white"
                        : "bg-white border-[#E5DFD5] hover:border-[#221C1A]/40"
                    }`}
                  >
                    <div>
                      <div className="w-10 h-10 rounded-2xl bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center mb-3">
                        <CreditCard size={22} weight="light" />
                      </div>
                      <h4 className="font-serif text-base font-semibold text-[#221C1A] mb-1">
                        Visa / Mastercard
                      </h4>
                      <p className="text-xs text-[#5C5550] font-light">
                        International &amp; regional credit/debit cards.
                      </p>
                    </div>
                    <span className="text-[10px] font-semibold text-[#B8975A] mt-3 block">
                      Secure Gateway
                    </span>
                  </div>
                </div>
              </div>

              {/* Navigation Back */}
              <div className="pt-4 flex items-center justify-between">
                <Link
                  href={`/booking/step3?services=${selectedServices.map((s) => s.id).join(",")}${
                    selectedProviderId !== null ? `&provider=${selectedProviderId}` : ""
                  }${selectedDate ? `&date=${encodeURIComponent(selectedDate)}` : ""}${
                    selectedTime ? `&time=${encodeURIComponent(selectedTime)}` : ""
                  }&location=${locationType}`}
                  className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[#5C5550] hover:text-[#221C1A] transition-colors"
                >
                  <ArrowLeft size={16} />
                  <span>&larr; Back to Schedule Date &amp; Time</span>
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
                      Step 4 of 5 &bull; Financial Summary
                    </span>
                    <h3 className="font-serif text-xl text-white font-normal">
                      Reservation Breakdown
                    </h3>
                  </div>
                  <span className="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-[#B8975A]/20 text-[#E8C99B]">
                    Guaranteed
                  </span>
                </div>

                {/* Reservation Key Specs */}
                <div className="mb-4 p-3.5 rounded-2xl bg-white/5 border border-white/10 relative z-10 space-y-2 text-xs">
                  <div className="flex items-center justify-between">
                    <span className="text-white/60">Schedule</span>
                    <span className="font-medium text-[#E8C99B]">
                      {formattedSelectedDate} &bull; {selectedTime || "TBD"}
                    </span>
                  </div>
                  <div className="flex items-center justify-between pt-1 border-t border-white/5">
                    <span className="text-white/60">Specialist</span>
                    <span className="font-medium text-white">
                      {selectedProvider ? selectedProvider.name : "First Available"}
                    </span>
                  </div>
                  <div className="flex items-center justify-between pt-1 border-t border-white/5">
                    <span className="text-white/60">Experience Mode</span>
                    <span className="font-medium text-white">
                      {locationType === "salon" ? "Sanctuary Studio" : "Mobile Concierge (Home)"}
                    </span>
                  </div>
                </div>

                {/* Treatments List */}
                <div className="space-y-2 mb-5 max-h-36 overflow-y-auto pr-1 relative z-10 custom-scrollbar">
                  {selectedServices.map((s) => (
                    <div
                      key={s.id}
                      className="p-2 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between text-xs"
                    >
                      <span className="truncate text-white flex-1">{s.name}</span>
                      <span className="text-[#E8C99B] whitespace-nowrap ml-2">
                        {formatRwf(s.price)} RWF
                      </span>
                    </div>
                  ))}
                </div>

                {/* Final Breakdown */}
                <div className="border-t border-white/10 pt-4 mb-6 relative z-10 space-y-2 text-xs">
                  <div className="flex justify-between text-white/70">
                    <span>Base Rituals Subtotal</span>
                    <span>{formatRwf(baseServicePrice)} RWF</span>
                  </div>

                  {locationType === "home" && (
                    <div className="flex justify-between text-[#E8C99B]">
                      <span>Mobile Concierge Premium (+100%)</span>
                      <span>+{formatRwf(baseServicePrice)} RWF</span>
                    </div>
                  )}

                  {wantsPickup && currentPickupFee > 0 && (
                    <div className="flex justify-between text-[#E8C99B]">
                      <span>Studio Chauffeur Transport</span>
                      <span>+{formatRwf(currentPickupFee)} RWF</span>
                    </div>
                  )}

                  <div className="flex justify-between text-white/70 pt-1 border-t border-white/5">
                    <span>Total Investment</span>
                    <span className="font-medium text-white">{formatRwf(totalInvestment)} RWF</span>
                  </div>

                  <div className="flex items-baseline justify-between pt-2 border-t border-white/10">
                    <span className="text-xs uppercase tracking-wider text-[#B8975A] font-semibold">
                      {paymentOption === "full" ? "Payable in Full" : "Deposit Payable Now"}
                    </span>
                    <span className="font-serif text-2xl text-white font-semibold">
                      {formatRwf(payableToday)}{" "}
                      <span className="text-xs font-sans font-normal text-[#E8C99B]">RWF</span>
                    </span>
                  </div>

                  {paymentOption === "deposit" && (
                    <div className="flex justify-between text-[11px] text-white/50 pt-1">
                      <span>Remaining Balance at Studio</span>
                      <span>{formatRwf(remainingAtSalon)} RWF</span>
                    </div>
                  )}
                </div>

                {/* CTA Button */}
                <button
                  type="submit"
                  className="w-full py-4 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white hover:bg-[#B8975A] hover:text-white transition-all duration-300 shadow-xl flex items-center justify-center gap-2 group cursor-pointer relative z-10"
                >
                  <Receipt size={16} weight="bold" />
                  <span>Review &amp; Confirm Booking</span>
                  <ArrowRight
                    size={15}
                    weight="bold"
                    className="group-hover:translate-x-1 transition-transform"
                  />
                </button>

                {/* Direct Concierge Prompt */}
                <div className="mt-5 p-3 rounded-2xl bg-white/5 border border-white/10 flex items-center gap-2.5 relative z-10 text-[11px]">
                  <ShieldCheck size={16} className="text-[#34A853] shrink-0" />
                  <span className="text-white/75 font-light">
                    Complimentary rescheduling up to 2 hours prior to ritual.
                  </span>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>

      {/* ========================================================= */}
      {/* Mobile Floating Sticky Bar */}
      {/* ========================================================= */}
      <div className="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-[#1C1816] text-white border-t border-white/10 shadow-2xl p-4 safe-area-bottom">
        <div className="flex items-center justify-between gap-3">
          <div>
            <div className="text-[11px] text-[#E8C99B]">
              <span>{paymentOption === "full" ? "Full Payment" : "40% Deposit"}</span>
            </div>
            <div className="font-serif text-lg font-bold text-white">
              {formatRwf(payableToday)} <span className="text-xs font-sans">RWF</span>
            </div>
          </div>

          <button
            type="button"
            onClick={handleProceedToStep5}
            className="px-6 py-3 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white hover:bg-[#B8975A] hover:text-white transition-all shadow-md flex items-center gap-1.5"
          >
            <span>Review</span>
            <ArrowRight size={14} weight="bold" />
          </button>
        </div>
      </div>
    </div>
  );
}
