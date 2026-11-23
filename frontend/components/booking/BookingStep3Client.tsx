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
  SunHorizon,
  Sun,
  MoonStars,
  User,
} from "@phosphor-icons/react";
import { TeamProvider } from "@/lib/team-data";
import { ServiceItem } from "@/lib/services-data";

interface PickupZone {
  id: number;
  name: string;
  fee: number;
}

interface BookingStep3ClientProps {
  providers: TeamProvider[];
  allServices: ServiceItem[];
}

export default function BookingStep3Client({
  providers,
  allServices,
}: BookingStep3ClientProps) {
  const router = useRouter();
  const searchParams = useSearchParams();

  // State from previous steps
  const [selectedServices, setSelectedServices] = useState<ServiceItem[]>([]);
  const [selectedProviderId, setSelectedProviderId] = useState<number | null>(null);

  // Step 3 State: Date, Time & Location
  const [selectedDate, setSelectedDate] = useState<string>("");
  const [selectedTime, setSelectedTime] = useState<string>("");
  const [locationType, setLocationType] = useState<"salon" | "home">("salon");
  const [homeAddress, setHomeAddress] = useState<string>("");
  const [wantsPickup, setWantsPickup] = useState<boolean>(false);
  const [selectedPickupZoneId, setSelectedPickupZoneId] = useState<number | "">("");
  const [pickupAddress, setPickupAddress] = useState<string>("");
  const [pickupZones, setPickupZones] = useState<PickupZone[]>([]);
  const [availableSlots, setAvailableSlots] = useState<{ time: string; available: boolean }[]>([]);
  const [availabilityLoading, setAvailabilityLoading] = useState(false);
  const [availabilityError, setAvailabilityError] = useState("");

  // Generate next 14 calendar dates
  const next14Days = useMemo(() => {
    const days = [];
    const today = new Date();

    for (let i = 0; i < 14; i++) {
      const d = new Date(today);
      d.setDate(today.getDate() + i);

      const year = d.getFullYear();
      const month = String(d.getMonth() + 1).padStart(2, "0");
      const dayNum = String(d.getDate()).padStart(2, "0");
      const dateString = `${year}-${month}-${dayNum}`;

      const weekdayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
      const monthNames = [
        "Jan", "Feb", "Mar", "Apr", "May", "Jun",
        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
      ];

      days.push({
        dateString,
        dayOfWeek: weekdayNames[d.getDay()],
        dayNumber: d.getDate(),
        monthName: monthNames[d.getMonth()],
        isToday: i === 0,
        isSunday: d.getDay() === 0,
      });
    }
    return days;
  }, []);

  // Initialize selections from URL or localStorage
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

    // 3. Date
    const dateParam = searchParams.get("date");
    if (dateParam) {
      setSelectedDate(dateParam);
    } else if (next14Days.length > 0) {
      // Default to today or tomorrow if Sunday
      const initialDay = next14Days[0].isSunday ? next14Days[1].dateString : next14Days[0].dateString;
      setSelectedDate(initialDay);
    }

    // 4. Time
    const timeParam = searchParams.get("time");
    if (timeParam) setSelectedTime(timeParam);
  }, [searchParams, allServices, next14Days]);

  useEffect(() => {
    const controller = new AbortController();
    fetch("/api/v1/booking/options", { cache: "no-store", signal: controller.signal })
      .then(async (response) => {
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || "Pickup options are unavailable.");
        setPickupZones(result.data?.pickup_locations || []);
      })
      .catch((error: unknown) => {
        if (error instanceof Error && error.name !== "AbortError") {
          setPickupZones([]);
        }
      });
    return () => controller.abort();
  }, []);

  useEffect(() => {
    if (!selectedDate || selectedServices.length === 0) {
      setAvailableSlots([]);
      return;
    }

    const controller = new AbortController();
    const params = new URLSearchParams({
      date: selectedDate,
      provider_id: String(selectedProviderId ?? 0),
    });
    selectedServices.forEach((service) => params.append("service_ids[]", String(service.id)));
    setAvailabilityLoading(true);
    setAvailabilityError("");

    fetch(`/api/v1/booking/availability?${params.toString()}`, {
      cache: "no-store",
      signal: controller.signal,
    })
      .then(async (response) => {
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || "Could not load live appointment times.");
        const slots = result.data?.slots || [];
        setAvailableSlots(slots);
        setSelectedTime((current) => slots.some((slot: { time: string; available: boolean }) => slot.time === current && slot.available) ? current : "");
      })
      .catch((error: unknown) => {
        if (error instanceof Error && error.name !== "AbortError") {
          setAvailableSlots([]);
          setAvailabilityError(error.message || "Could not load live appointment times.");
        }
      })
      .finally(() => {
        if (!controller.signal.aborted) setAvailabilityLoading(false);
      });

    return () => controller.abort();
  }, [selectedDate, selectedProviderId, selectedServices]);

  useEffect(() => {
    if (pickupZones.length > 0 && selectedPickupZoneId !== "" && !pickupZones.some((zone) => zone.id === selectedPickupZoneId)) {
      setSelectedPickupZoneId("");
    }
  }, [pickupZones, selectedPickupZoneId]);

  // Selected Provider Details
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

  // Calculations
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
  const selectedPickupZone = pickupZones.find((z) => z.id === selectedPickupZoneId);
  const pickupFee = wantsPickup && locationType === "salon" && selectedPickupZone ? selectedPickupZone.fee : 0;
  const finalTotalPrice = baseServicePrice * locationMultiplier + pickupFee;

  const formatRwf = (amount: number) => {
    return new Intl.NumberFormat("en-RW", {
      style: "decimal",
      maximumFractionDigits: 0,
    }).format(amount);
  };

  // Group live server availability into the page's three time-of-day sections.
  const timeSlotsByPeriod = useMemo(() => {
    const groups = { morning: [] as { time: string; isTaken: boolean }[], afternoon: [] as { time: string; isTaken: boolean }[], evening: [] as { time: string; isTaken: boolean }[] };
    availableSlots.forEach(({ time, available }) => {
      const hour = Number(time.slice(0, 2));
      const minuteOfDay = hour * 60 + Number(time.slice(3, 5));
      const group = minuteOfDay < 12 * 60 ? groups.morning : minuteOfDay < 16 * 60 + 30 ? groups.afternoon : groups.evening;
      group.push({ time, isTaken: !available });
    });
    return groups;
  }, [availableSlots]);

  // Handle proceed to Step 4
  const handleProceedToStep4 = () => {
    if (!selectedDate || !selectedTime) return;

    if (locationType === "home" && !homeAddress.trim()) {
      alert("Please enter your residential address or hotel for home service.");
      return;
    }

    if (wantsPickup && locationType === "salon") {
      if (!selectedPickupZoneId || !pickupAddress.trim()) {
        alert("Please specify your pickup location zone and exact pickup address.");
        return;
      }
    }

    // Persist details
    const bookingDetails = {
      services: selectedServices.map((s) => s.id),
      providerId: selectedProviderId,
      date: selectedDate,
      time: selectedTime,
      locationType,
      homeAddress: locationType === "home" ? homeAddress : null,
      wantsPickup: wantsPickup && locationType === "salon",
      pickupLocationId: selectedPickupZoneId,
      pickupFee,
      pickupAddress: wantsPickup ? pickupAddress : null,
      totalPrice: finalTotalPrice,
    };

    try {
      localStorage.setItem("isaiah_booking_step3_data", JSON.stringify(bookingDetails));
    } catch {
      // Ignore
    }

    const servicesQuery = selectedServices.map((s) => s.id).join(",");
    const providerQuery = selectedProviderId !== null ? `&provider=${selectedProviderId}` : "";
    const dateQuery = `&date=${encodeURIComponent(selectedDate)}`;
    const timeQuery = `&time=${encodeURIComponent(selectedTime)}`;
    const locQuery = `&location=${locationType}`;

    router.push(
      `/booking/step4?services=${servicesQuery}${providerQuery}${dateQuery}${timeQuery}${locQuery}`
    );
  };

  // Formatted date string for humans
  const formattedSelectedDate = useMemo(() => {
    if (!selectedDate) return "";
    const [y, m, d] = selectedDate.split("-").map(Number);
    const dateObj = new Date(y, m - 1, d);
    return dateObj.toLocaleDateString("en-US", {
      weekday: "short",
      month: "short",
      day: "numeric",
      year: "numeric",
    });
  }, [selectedDate]);

  return (
    <div className="bg-[#FAF7F2] min-h-screen py-10 lg:py-16">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
          {/* ========================================================= */}
          {/* Left Column: Date, Time & Location Stage (8 cols) */}
          {/* ========================================================= */}
          <div className="lg:col-span-8 space-y-10">
            {/* 1. Date Selection Section */}
            <div>
              <div className="flex items-center justify-between pb-3 border-b border-[#E5DFD5] mb-5">
                <div>
                  <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-1">
                    01 &bull; Appointment Date
                  </span>
                  <h2 className="font-serif text-2xl sm:text-3xl text-[#221C1A] font-normal">
                    Select Your Preferred Day
                  </h2>
                </div>
                {selectedDate && (
                  <span className="text-xs text-[#B8975A] font-medium hidden sm:inline-block">
                    {formattedSelectedDate}
                  </span>
                )}
              </div>

              {/* Horizontal 14-Day Date Ribbon */}
              <div className="flex items-center gap-2.5 overflow-x-auto pb-3 pt-1 scrollbar-none snap-x">
                {next14Days.map((day) => {
                  const isSelected = selectedDate === day.dateString;
                  return (
                    <button
                      key={day.dateString}
                      type="button"
                      onClick={() => {
                        setSelectedDate(day.dateString);
                        // Reset time if user changes day
                        setSelectedTime("");
                      }}
                      className={`snap-start shrink-0 w-20 sm:w-22 py-3 px-2 rounded-2xl border text-center transition-all duration-200 cursor-pointer flex flex-col items-center justify-between ${
                        isSelected
                          ? "bg-[#221C1A] text-white border-[#221C1A] shadow-md ring-2 ring-[#B8975A]/40 scale-102"
                          : day.isSunday
                          ? "bg-white/50 text-[#8E857F] border-[#E5DFD5] opacity-75 hover:opacity-100"
                          : "bg-white text-[#221C1A] border-[#E5DFD5] hover:border-[#221C1A]/40 hover:shadow-xs"
                      }`}
                    >
                      <span
                        className={`text-[10px] font-semibold tracking-wider uppercase block ${
                          isSelected ? "text-[#E8C99B]" : "text-[#8E857F]"
                        }`}
                      >
                        {day.isToday ? "Today" : day.dayOfWeek}
                      </span>
                      <span className="font-serif text-xl sm:text-2xl font-normal my-1">
                        {day.dayNumber}
                      </span>
                      <span
                        className={`text-[10px] font-light block ${
                          isSelected ? "text-white/80" : "text-[#5C5550]"
                        }`}
                      >
                        {day.monthName}
                      </span>
                    </button>
                  );
                })}
              </div>

              {selectedDate && next14Days.find((d) => d.dateString === selectedDate)?.isSunday && (
                <div className="mt-3 p-3 rounded-xl bg-white border border-[#E5DFD5] flex items-center gap-2 text-xs text-[#8E857F]">
                  <Info size={16} className="text-[#B8975A] shrink-0" />
                  <span>
                    Sundays are reserved for private VIP sessions and bridal group bookings. Contact concierge via WhatsApp for private opening requests.
                  </span>
                </div>
              )}
            </div>

            {/* 2. Time Slot Selection Section */}
            <div>
              <div className="flex items-center justify-between pb-3 border-b border-[#E5DFD5] mb-5">
                <div>
                  <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-1">
                    02 &bull; Time of Service
                  </span>
                  <h2 className="font-serif text-2xl sm:text-3xl text-[#221C1A] font-normal">
                    Choose Your Sanctuary Slot
                  </h2>
                </div>
                <div className="flex items-center gap-3 text-[11px] text-[#8E857F]">
                  <span className="flex items-center gap-1">
                    <span className="w-2.5 h-2.5 rounded-full bg-white border border-[#E5DFD5]" />
                    <span>Open</span>
                  </span>
                  <span className="flex items-center gap-1">
                    <span className="w-2.5 h-2.5 rounded-full bg-[#221C1A]" />
                    <span>Selected</span>
                  </span>
                </div>
              </div>

              {/* Time Slots Grouped by Period */}
              <div className="space-y-6">
                {availabilityLoading && (
                  <p role="status" className="rounded-2xl border border-[#E5DFD5] bg-white px-4 py-3 text-xs text-[#8E857F]">
                    Checking live availability…
                  </p>
                )}
                {availabilityError && (
                  <p role="alert" className="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-800">
                    {availabilityError} Please refresh the page before choosing a time.
                  </p>
                )}
                {!availabilityLoading && !availabilityError && availableSlots.length === 0 && (
                  <p className="rounded-2xl border border-[#E5DFD1] bg-white px-4 py-3 text-xs text-[#8E857F]">
                    No appointment times are available for this date and service selection.
                  </p>
                )}
                {/* Morning */}
                {timeSlotsByPeriod.morning.length > 0 && (
                <div className="bg-white rounded-3xl p-5 sm:p-6 border border-[#E5DFD5]">
                  <div className="flex items-center gap-2 mb-4 text-[#221C1A]">
                    <SunHorizon size={18} weight="fill" className="text-[#B8975A]" />
                    <h3 className="font-serif text-base font-medium">Morning Sanctuary</h3>
                  </div>

                  <div className="grid grid-cols-3 sm:grid-cols-6 gap-2.5">
                    {timeSlotsByPeriod.morning.map(({ time, isTaken }) => {
                      const isSelected = selectedTime === time;
                      return (
                        <button
                          key={time}
                          type="button"
                          disabled={isTaken}
                          onClick={() => setSelectedTime(time)}
                          className={`py-3 px-2 rounded-2xl border text-xs font-medium transition-all duration-200 cursor-pointer flex flex-col items-center justify-center gap-0.5 ${
                            isSelected
                              ? "bg-[#221C1A] text-white border-[#221C1A] shadow-md ring-2 ring-[#B8975A]/40"
                              : isTaken
                              ? "bg-[#F5F2EC] text-[#8E857F]/50 border-transparent cursor-not-allowed line-through"
                              : "bg-white text-[#221C1A] border-[#E5DFD5] hover:border-[#221C1A]/40 hover:shadow-xs"
                          }`}
                        >
                          <span className="font-semibold">{time}</span>
                          <span className="text-[10px] opacity-75 font-light">
                            {isSelected ? "Selected" : isTaken ? "Taken" : "Available"}
                          </span>
                        </button>
                      );
                    })}
                  </div>
                </div>
                )}

                {/* Afternoon */}
                {timeSlotsByPeriod.afternoon.length > 0 && (
                <div className="bg-white rounded-3xl p-5 sm:p-6 border border-[#E5DFD5]">
                  <div className="flex items-center gap-2 mb-4 text-[#221C1A]">
                    <Sun size={18} weight="fill" className="text-[#B8975A]" />
                    <h3 className="font-serif text-base font-medium">Afternoon Serenity</h3>
                  </div>

                  <div className="grid grid-cols-3 sm:grid-cols-6 gap-2.5">
                    {timeSlotsByPeriod.afternoon.map(({ time, isTaken }) => {
                      const isSelected = selectedTime === time;
                      return (
                        <button
                          key={time}
                          type="button"
                          disabled={isTaken}
                          onClick={() => setSelectedTime(time)}
                          className={`py-3 px-2 rounded-2xl border text-xs font-medium transition-all duration-200 cursor-pointer flex flex-col items-center justify-center gap-0.5 ${
                            isSelected
                              ? "bg-[#221C1A] text-white border-[#221C1A] shadow-md ring-2 ring-[#B8975A]/40"
                              : isTaken
                              ? "bg-[#F5F2EC] text-[#8E857F]/50 border-transparent cursor-not-allowed line-through"
                              : "bg-white text-[#221C1A] border-[#E5DFD5] hover:border-[#221C1A]/40 hover:shadow-xs"
                          }`}
                        >
                          <span className="font-semibold">{time}</span>
                          <span className="text-[10px] opacity-75 font-light">
                            {isSelected ? "Selected" : isTaken ? "Taken" : "Available"}
                          </span>
                        </button>
                      );
                    })}
                  </div>
                </div>
                )}

                {/* Evening */}
                {timeSlotsByPeriod.evening.length > 0 && (
                <div className="bg-white rounded-3xl p-5 sm:p-6 border border-[#E5DFD5]">
                  <div className="flex items-center gap-2 mb-4 text-[#221C1A]">
                    <MoonStars size={18} weight="fill" className="text-[#B8975A]" />
                    <h3 className="font-serif text-base font-medium">Evening Golden Hour</h3>
                  </div>

                  <div className="grid grid-cols-3 sm:grid-cols-4 gap-2.5">
                    {timeSlotsByPeriod.evening.map(({ time, isTaken }) => {
                      const isSelected = selectedTime === time;
                      return (
                        <button
                          key={time}
                          type="button"
                          disabled={isTaken}
                          onClick={() => setSelectedTime(time)}
                          className={`py-3 px-2 rounded-2xl border text-xs font-medium transition-all duration-200 cursor-pointer flex flex-col items-center justify-center gap-0.5 ${
                            isSelected
                              ? "bg-[#221C1A] text-white border-[#221C1A] shadow-md ring-2 ring-[#B8975A]/40"
                              : isTaken
                              ? "bg-[#F5F2EC] text-[#8E857F]/50 border-transparent cursor-not-allowed line-through"
                              : "bg-white text-[#221C1A] border-[#E5DFD5] hover:border-[#221C1A]/40 hover:shadow-xs"
                          }`}
                        >
                          <span className="font-semibold">{time}</span>
                          <span className="text-[10px] opacity-75 font-light">
                            {isSelected ? "Selected" : isTaken ? "Taken" : "Available"}
                          </span>
                        </button>
                      );
                    })}
                  </div>
                </div>
                )}
              </div>
            </div>

            {/* 3. Location & Chauffeur Pickup Section */}
            <div>
              <div className="flex items-center justify-between pb-3 border-b border-[#E5DFD5] mb-5">
                <div>
                  <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-1">
                    03 &bull; Experience Location
                  </span>
                  <h2 className="font-serif text-2xl sm:text-3xl text-[#221C1A] font-normal">
                    In-Salon or Private Mobile Atelier
                  </h2>
                </div>
              </div>

              {/* 2 Location Choice Cards */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                {/* Salon Option */}
                <div
                  onClick={() => {
                    setLocationType("salon");
                  }}
                  className={`p-5 rounded-3xl border transition-all duration-200 cursor-pointer flex flex-col justify-between ${
                    locationType === "salon"
                      ? "bg-white border-[#B8975A] ring-2 ring-[#B8975A]/25 shadow-md bg-gradient-to-b from-[#FAF7F2] to-white"
                      : "bg-white border-[#E5DFD5] hover:border-[#221C1A]/40"
                  }`}
                >
                  <div>
                    <div className="flex items-center justify-between mb-3">
                      <div className="w-10 h-10 rounded-2xl bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center">
                        <Storefront size={22} weight="light" />
                      </div>
                      <div
                        className={`w-6 h-6 rounded-full flex items-center justify-center transition-all ${
                          locationType === "salon"
                            ? "bg-[#B8975A] text-white shadow-xs"
                            : "border border-black/15 bg-white text-transparent"
                        }`}
                      >
                        <Check size={14} weight="bold" />
                      </div>
                    </div>
                    <h3 className="font-serif text-base text-[#221C1A] font-semibold mb-1">
                      In-Salon Sanctuary Experience
                    </h3>
                    <p className="text-xs text-[#5C5550] font-light leading-relaxed mb-3">
                      KG 4 Roundabout, Gisementi. Enjoy our calming lounge, espresso, herbal infusions, and hospital-grade autoclave hygiene.
                    </p>
                  </div>
                  <span className="text-[11px] font-semibold text-[#34A853]">
                    Standard Salon Pricing
                  </span>
                </div>

                {/* Home Service Option */}
                <div
                  onClick={() => {
                    setLocationType("home");
                    setWantsPickup(false); // Can't have chauffeur pickup if service is at home
                  }}
                  className={`p-5 rounded-3xl border transition-all duration-200 cursor-pointer flex flex-col justify-between ${
                    locationType === "home"
                      ? "bg-white border-[#B8975A] ring-2 ring-[#B8975A]/25 shadow-md bg-gradient-to-b from-[#FAF7F2] to-white"
                      : "bg-white border-[#E5DFD5] hover:border-[#221C1A]/40"
                  }`}
                >
                  <div>
                    <div className="flex items-center justify-between mb-3">
                      <div className="w-10 h-10 rounded-2xl bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center">
                        <House size={22} weight="light" />
                      </div>
                      <div
                        className={`w-6 h-6 rounded-full flex items-center justify-center transition-all ${
                          locationType === "home"
                            ? "bg-[#B8975A] text-white shadow-xs"
                            : "border border-black/15 bg-white text-transparent"
                        }`}
                      >
                        <Check size={14} weight="bold" />
                      </div>
                    </div>
                    <h3 className="font-serif text-base text-[#221C1A] font-semibold mb-1">
                      Mobile Concierge (Home / Hotel)
                    </h3>
                    <p className="text-xs text-[#5C5550] font-light leading-relaxed mb-3">
                      Our certified senior technicians travel directly to your residence, villa, or hotel with portable sterilized equipment.
                    </p>
                  </div>
                  <span className="text-[11px] font-semibold text-[#B8975A]">
                    +100% (Double Total Investment)
                  </span>
                </div>
              </div>

              {/* Conditional Home Address Input */}
              <AnimatePresence>
                {locationType === "home" && (
                  <motion.div
                    initial={{ opacity: 0, height: 0 }}
                    animate={{ opacity: 1, height: "auto" }}
                    exit={{ opacity: 0, height: 0 }}
                    className="p-5 sm:p-6 bg-white rounded-3xl border border-[#E5DFD5] space-y-3 mb-6"
                  >
                    <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A]">
                      Destination Address in Kigali <span className="text-[#C4847A]">*</span>
                    </label>
                    <textarea
                      rows={3}
                      required
                      value={homeAddress}
                      onChange={(e) => setHomeAddress(e.target.value)}
                      placeholder="e.g. Nyarutarama KG 9 Ave, Villa 14 (near Golf Club), or Kigali Serena Hotel Room 302..."
                      className="w-full px-4 py-3 bg-[#FAF7F2] border border-[#E5DFD5] focus:border-[#221C1A] rounded-2xl text-xs sm:text-sm text-[#221C1A] placeholder-[#8E857F] outline-none transition-colors resize-none"
                    />
                    <p className="text-[11px] text-[#8E857F] font-light flex items-center gap-1.5">
                      <ShieldCheck size={14} className="text-[#B8975A] shrink-0" />
                      <span>Our artists arrive with individual sealed autoclave packs and medical-grade PPE.</span>
                    </p>
                  </motion.div>
                )}
              </AnimatePresence>

              {/* Conditional Chauffeur Pickup Option (For In-Salon) */}
              {locationType === "salon" && (
                <div className="p-5 sm:p-6 bg-white rounded-3xl border border-[#E5DFD5]">
                  <label className="flex items-center gap-3 cursor-pointer">
                    <input
                      type="checkbox"
                      checked={wantsPickup}
                      onChange={(e) => setWantsPickup(e.target.checked)}
                      className="w-4 h-4 accent-[#221C1A] cursor-pointer"
                    />
                    <div className="flex-1">
                      <span className="font-serif text-sm font-semibold text-[#221C1A] flex items-center gap-2">
                        <Car size={18} className="text-[#B8975A]" />
                        <span>Need Studio Chauffeur Pickup in Kigali?</span>
                        <span className="px-2 py-0.5 rounded-full bg-[#B8975A]/15 text-[#B8975A] text-[9px] font-semibold uppercase">
                          VIP Transport
                        </span>
                      </span>
                      <p className="text-xs text-[#5C5550] font-light">
                        A dedicated driver will pick you up and bring you directly to our Gisementi sanctuary.
                      </p>
                    </div>
                  </label>

                  <AnimatePresence>
                    {wantsPickup && (
                      <motion.div
                        initial={{ opacity: 0, height: 0 }}
                        animate={{ opacity: 1, height: "auto" }}
                        exit={{ opacity: 0, height: 0 }}
                        className="mt-4 pt-4 border-t border-[#E5DFD5] space-y-4"
                      >
                        <div>
                          <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                            Pickup Zone <span className="text-[#C4847A]">*</span>
                          </label>
                          <select
                            value={selectedPickupZoneId}
                            onChange={(e) =>
                              setSelectedPickupZoneId(e.target.value ? Number(e.target.value) : "")
                            }
                            className="w-full px-4 py-3 bg-[#FAF7F2] border border-[#E5DFD5] focus:border-[#221C1A] rounded-2xl text-xs sm:text-sm text-[#221C1A] outline-none cursor-pointer"
                          >
                            <option value="">Select your Kigali neighborhood / zone</option>
                            {pickupZones.map((zone) => (
                              <option key={zone.id} value={zone.id}>
                                {zone.name} (+{formatRwf(zone.fee)} RWF)
                              </option>
                            ))}
                          </select>
                          {pickupZones.length === 0 && (
                            <p className="mt-1.5 text-[11px] text-[#8E857F]">Pickup is not currently configured. Contact the salon if you need transport.</p>
                          )}
                        </div>

                        <div>
                          <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                            Exact Pickup Address / Landmark <span className="text-[#C4847A]">*</span>
                          </label>
                          <input
                            type="text"
                            value={pickupAddress}
                            onChange={(e) => setPickupAddress(e.target.value)}
                            placeholder="e.g. Kimihurura, near Marriott or Kigali Convention Center gate..."
                            className="w-full px-4 py-3 bg-[#FAF7F2] border border-[#E5DFD5] focus:border-[#221C1A] rounded-2xl text-xs sm:text-sm text-[#221C1A] placeholder-[#8E857F] outline-none"
                          />
                        </div>
                      </motion.div>
                    )}
                  </AnimatePresence>
                </div>
              )}
            </div>

            {/* Back to Step 2 Button */}
            <div className="pt-4 flex items-center justify-between">
              <Link
                href={`/booking/step2?services=${selectedServices.map((s) => s.id).join(",")}${
                  selectedProviderId !== null ? `&provider=${selectedProviderId}` : ""
                }`}
                className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[#5C5550] hover:text-[#221C1A] transition-colors"
              >
                <ArrowLeft size={16} />
                <span>&larr; Back to Select Artist</span>
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
                    Step 3 of 5 &bull; Summary
                  </span>
                  <h3 className="font-serif text-xl text-white font-normal">
                    Your Reservation
                  </h3>
                </div>
                <span className="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-[#B8975A]/20 text-[#E8C99B]">
                  Live Preview
                </span>
              </div>

              {/* Schedule Details Card */}
              <div className="mb-4 p-3.5 rounded-2xl bg-white/5 border border-white/10 relative z-10 space-y-2">
                {/* Date & Time */}
                <div className="flex items-center justify-between text-xs">
                  <span className="text-white/60">Date &amp; Time</span>
                  <span className="font-medium text-[#E8C99B] flex items-center gap-1">
                    <CalendarCheck size={14} />
                    <span>
                      {selectedDate ? formattedSelectedDate : "Select date"} &bull;{" "}
                      {selectedTime ? selectedTime : "--:--"}
                    </span>
                  </span>
                </div>

                {/* Artist */}
                <div className="flex items-center justify-between text-xs pt-1 border-t border-white/5">
                  <span className="text-white/60">Artist</span>
                  <span className="font-medium text-white flex items-center gap-1.5">
                    <User size={13} />
                    <span>{selectedProvider ? selectedProvider.name : "First Available"}</span>
                  </span>
                </div>

                {/* Location */}
                <div className="flex items-center justify-between text-xs pt-1 border-t border-white/5">
                  <span className="text-white/60">Location</span>
                  <span className="font-medium text-white flex items-center gap-1">
                    {locationType === "salon" ? <Storefront size={13} /> : <House size={13} />}
                    <span>{locationType === "salon" ? "Sanctuary Studio" : "Mobile Concierge"}</span>
                  </span>
                </div>

                {/* Pickup note if enabled */}
                {wantsPickup && selectedPickupZone && locationType === "salon" && (
                  <div className="flex items-center justify-between text-xs pt-1 border-t border-white/5 text-[#E8C99B]">
                    <span>VIP Chauffeur</span>
                    <span>+{formatRwf(selectedPickupZone.fee)} RWF</span>
                  </div>
                )}
              </div>

              {/* Selected Services Itemized Recap */}
              <div className="space-y-2.5 mb-5 max-h-40 overflow-y-auto pr-1 relative z-10 custom-scrollbar">
                {selectedServices.map((s) => (
                  <div
                    key={s.id}
                    className="p-2.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between gap-2 text-xs"
                  >
                    <div className="truncate">
                      <h5 className="font-medium text-white truncate">{s.name}</h5>
                      <span className="text-[10px] text-white/50">{s.duration_minutes || 60}m</span>
                    </div>
                    <span className="text-[#E8C99B] font-medium shrink-0">
                      {formatRwf(s.price)} RWF
                    </span>
                  </div>
                ))}
              </div>

              {/* Price Breakdown */}
              <div className="border-t border-white/10 pt-4 mb-6 relative z-10 space-y-2">
                <div className="flex items-center justify-between text-xs text-white/70">
                  <span>Total Duration</span>
                  <span className="font-medium text-white flex items-center gap-1">
                    <Clock size={13} />
                    <span>{formattedDuration}</span>
                  </span>
                </div>

                {locationType === "home" && (
                  <div className="flex items-center justify-between text-xs text-[#E8C99B]">
                    <span>Mobile Concierge (Home / Hotel)</span>
                    <span>+100% (2x)</span>
                  </div>
                )}

                <div className="flex items-baseline justify-between pt-1">
                  <span className="text-xs font-light text-white/70">Total Investment</span>
                  <span className="font-serif text-2xl text-white font-normal">
                    {formatRwf(finalTotalPrice)}{" "}
                    <span className="text-xs font-sans text-[#E8C99B]">RWF</span>
                  </span>
                </div>
              </div>

              {/* Continue to Step 4 Button */}
              <button
                type="button"
                disabled={!selectedDate || !selectedTime}
                onClick={handleProceedToStep4}
                className="w-full py-4 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white hover:bg-[#B8975A] hover:text-white transition-all duration-300 shadow-xl disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2 group cursor-pointer relative z-10"
              >
                <CalendarCheck size={16} weight="bold" />
                <span>
                  {!selectedDate || !selectedTime
                    ? "Select Date & Time Slot"
                    : "Continue to Guest Details"}
                </span>
                <ArrowRight
                  size={15}
                  weight="bold"
                  className="group-hover:translate-x-1 transition-transform"
                />
              </button>

              {/* Need assistance */}
              <div className="mt-5 p-3.5 rounded-2xl bg-white/5 border border-white/10 flex items-center gap-3 relative z-10">
                <div className="w-8 h-8 rounded-xl bg-[#25D366]/20 text-[#25D366] flex items-center justify-center shrink-0">
                  <WhatsappLogo size={18} weight="fill" />
                </div>
                <div className="text-[11px] leading-tight">
                  <span className="text-white font-medium block mb-0.5">
                    Need a custom time or group slot?
                  </span>
                  <a
                    href="https://wa.me/250790395169?text=Hello%20Isaiah%20Nail%20Bar,%20I%20would%20like%20to%20request%20a%20specific%20appointment%20time."
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
      {/* Mobile Floating Sticky Bar (Always visible on mobile) */}
      {/* ========================================================= */}
      <div className="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-[#1C1816] text-white border-t border-white/10 shadow-2xl p-4 safe-area-bottom">
        <div className="flex items-center justify-between gap-3">
          <div>
            <div className="flex items-center gap-1.5 text-xs text-[#E8C99B]">
              <span>{selectedTime ? `${selectedTime}` : "Pick a Time"}</span>
              <span>&bull;</span>
              <span>{formattedDuration}</span>
            </div>
            <div className="font-serif text-lg font-bold text-white">
              {formatRwf(finalTotalPrice)} <span className="text-xs font-sans">RWF</span>
            </div>
          </div>

          <button
            type="button"
            disabled={!selectedDate || !selectedTime}
            onClick={handleProceedToStep4}
            className="px-6 py-3 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white hover:bg-[#B8975A] hover:text-white transition-all shadow-md flex items-center gap-1.5 disabled:opacity-40 disabled:cursor-not-allowed"
          >
            <span>Continue</span>
            <ArrowRight size={14} weight="bold" />
          </button>
        </div>
      </div>
    </div>
  );
}
