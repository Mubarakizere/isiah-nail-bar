"use client";

import { useState, useEffect, useMemo } from "react";
import Image from "next/image";
import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { motion, AnimatePresence } from "framer-motion";
import {
  ArrowLeft,
  CalendarCheck,
  Clock,
  Storefront,
  House,
  Check,
  CheckCircle,
  WhatsappLogo,
  ShieldCheck,
  User,
  PhoneCall,
  EnvelopeSimple,
  CreditCard,
  DeviceMobile,
  Scroll,
  ClockCountdown,
  SpeakerSimpleSlash,
  LockSimple,
  Users,
} from "@phosphor-icons/react";
import { TeamProvider } from "@/lib/team-data";
import { ServiceItem } from "@/lib/services-data";

interface BookingStep5ClientProps {
  providers: TeamProvider[];
  allServices: ServiceItem[];
}

export default function BookingStep5Client({
  providers,
  allServices,
}: BookingStep5ClientProps) {
  const router = useRouter();
  const searchParams = useSearchParams();

  // State
  const [selectedServices, setSelectedServices] = useState<ServiceItem[]>([]);
  const [selectedProviderId, setSelectedProviderId] = useState<number | null>(null);
  const [selectedDate, setSelectedDate] = useState<string>("");
  const [selectedTime, setSelectedTime] = useState<string>("");
  const [locationType, setLocationType] = useState<"salon" | "home">("salon");
  const [homeAddress, setHomeAddress] = useState<string>("");
  const [wantsPickup, setWantsPickup] = useState<boolean>(false);
  const [pickupFee, setPickupFee] = useState<number>(0);
  const [pickupLocationId, setPickupLocationId] = useState<number | null>(null);
  const [pickupAddress, setPickupAddress] = useState<string>("");

  // Guest & Payment info from Step 4
  const [guestName, setGuestName] = useState("");
  const [guestPhone, setGuestPhone] = useState("");
  const [guestEmail, setGuestEmail] = useState("");
  const [specialNotes, setSpecialNotes] = useState("");
  const [paymentOption, setPaymentOption] = useState<"full" | "deposit">("full");
  const [paymentMethod, setPaymentMethod] = useState<"momo" | "card">("momo");

  // Step 5 Policies Checkbox State
  const [silentAppointment, setSilentAppointment] = useState(false);
  const [termsAccepted, setTermsAccepted] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [submittingStep, setSubmittingStep] = useState(0);
  const [submitError, setSubmitError] = useState("");
  const [bookingSuccess, setBookingSuccess] = useState(false);
  const [bookingRef, setBookingRef] = useState("");
  const [copiedRef, setCopiedRef] = useState(false);

  // Load prior booking data
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

    // 3. Date, Time, Location
    const dateParam = searchParams.get("date");
    if (dateParam) setSelectedDate(dateParam);
    const timeParam = searchParams.get("time");
    if (timeParam) setSelectedTime(timeParam);
    const locParam = searchParams.get("location");
    if (locParam === "home") setLocationType("home");

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
          const savedPickupId = Number(parsed.pickupLocationId ?? parsed.pickupZoneId);
          if (Number.isInteger(savedPickupId) && savedPickupId > 0) setPickupLocationId(savedPickupId);
          if (parsed.pickupAddress) setPickupAddress(parsed.pickupAddress);
        }
      }
    } catch {
      // Ignore
    }

    // 4. Guest info from Step 4
    try {
      const savedGuest = localStorage.getItem("isaiah_booking_guest_info");
      if (savedGuest) {
        const parsed = JSON.parse(savedGuest);
        if (parsed.name) setGuestName(parsed.name);
        if (parsed.phone) setGuestPhone(parsed.phone);
        if (parsed.email) setGuestEmail(parsed.email);
        if (parsed.notes) setSpecialNotes(parsed.notes);
        if (parsed.paymentOption) setPaymentOption(parsed.paymentOption);
        if (parsed.paymentMethod) setPaymentMethod(parsed.paymentMethod);
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
        bio: "Our lead team allocates your ritual to the best-suited master artist on duty.",
        photo: "/banner.jpg",
      };
    }
    return providers.find((p) => p.id === selectedProviderId) || null;
  }, [selectedProviderId, providers]);

  // Pricing
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

  const formattedSelectedDate = useMemo(() => {
    if (!selectedDate) return "Date to be confirmed";
    const [y, m, d] = selectedDate.split("-").map(Number);
    const dateObj = new Date(y, m - 1, d);
    return dateObj.toLocaleDateString("en-US", {
      weekday: "long",
      month: "long",
      day: "numeric",
      year: "numeric",
    });
  }, [selectedDate]);

  // Query parameter strings for back links
  const queryParams = useMemo(() => {
    const s = selectedServices.map((x) => x.id).join(",");
    const p = selectedProviderId !== null ? `&provider=${selectedProviderId}` : "";
    const d = selectedDate ? `&date=${encodeURIComponent(selectedDate)}` : "";
    const t = selectedTime ? `&time=${encodeURIComponent(selectedTime)}` : "";
    const l = `&location=${locationType}`;
    return `services=${s}${p}${d}${t}${l}`;
  }, [selectedServices, selectedProviderId, selectedDate, selectedTime, locationType]);

  // Copy reference to clipboard
  const handleCopyReference = () => {
    if (!bookingRef) return;
    navigator.clipboard.writeText(bookingRef);
    setCopiedRef(true);
    setTimeout(() => setCopiedRef(false), 2500);
  };

  // Generate & Download iCalendar file
  const handleDownloadCalendar = () => {
    if (!selectedDate || !selectedTime) return;
    const [y, m, d] = selectedDate.split("-").map(Number);
    const [h, min] = selectedTime.split(":").map(Number);
    const startDate = new Date(y, m - 1, d, h || 10, min || 0);
    const endDate = new Date(startDate.getTime() + totalDurationMinutes * 60000);

    const pad = (n: number) => (n < 10 ? `0${n}` : `${n}`);
    const formatIcsDate = (dt: Date) =>
      `${dt.getUTCFullYear()}${pad(dt.getUTCMonth() + 1)}${pad(dt.getUTCDate())}T${pad(
        dt.getUTCHours()
      )}${pad(dt.getUTCMinutes())}00Z`;

    const summary = `Isaiah Nail Bar — ${selectedServices.map((s) => s.name).join(", ")}`;
    const location =
      locationType === "salon"
        ? "KG 4 Roundabout, Gisementi, Kigali, Rwanda"
        : homeAddress || "Client Residence, Kigali";
    const description = `Bespoke nail ritual appointment with ${
      selectedProvider ? selectedProvider.name : "First Available Master"
    }. Booking Reference: ${bookingRef}. Total: ${formatRwf(totalInvestment)} RWF.`;

    const icsContent = [
      "BEGIN:VCALENDAR",
      "VERSION:2.0",
      "PRODID:-//Isaiah Nail Bar Kigali//Boutique Reservation//EN",
      "CALSCALE:GREGORIAN",
      "METHOD:PUBLISH",
      "BEGIN:VEVENT",
      `UID:INB-${Date.now()}@isaiahnailbar.rw`,
      `DTSTAMP:${formatIcsDate(new Date())}`,
      `DTSTART:${formatIcsDate(startDate)}`,
      `DTEND:${formatIcsDate(endDate)}`,
      `SUMMARY:${summary}`,
      `DESCRIPTION:${description}`,
      `LOCATION:${location}`,
      "STATUS:CONFIRMED",
      "BEGIN:VALARM",
      "TRIGGER:-PT2H",
      "ACTION:DISPLAY",
      "DESCRIPTION:Reminder: Upcoming luxury nail ritual at Isaiah Nail Bar",
      "END:VALARM",
      "END:VEVENT",
      "END:VCALENDAR",
    ].join("\r\n");

    const blob = new Blob([icsContent], { type: "text/calendar;charset=utf-8" });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `Isaiah_Nail_Bar_${bookingRef || "Reservation"}.ics`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  };

  // Handle final submission with luxury progress animation
  const handleFinalSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!termsAccepted) {
      alert("Please review and accept our salon sanctuary policies to confirm.");
      return;
    }

    setSubmitting(true);
    setSubmittingStep(1);
    setSubmitError("");

    const storageKey = "isaiah_booking_checkout_key";
    const idempotencyKey = sessionStorage.getItem(storageKey) || crypto.randomUUID();
    sessionStorage.setItem(storageKey, idempotencyKey);

    try {
      const response = await fetch("/api/v1/booking/checkout", {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify({
          idempotency_key: idempotencyKey,
          name: guestName.trim(),
          phone: guestPhone.trim(),
          email: guestEmail.trim() || null,
          service_ids: selectedServices.map((service) => service.id),
          provider_id: selectedProviderId ?? 0,
          date: selectedDate,
          time: selectedTime,
          location_type: locationType,
          home_address: locationType === "home" ? homeAddress.trim() : null,
          wants_pickup: wantsPickup && locationType === "salon",
          pickup_location_id: wantsPickup && locationType === "salon" ? pickupLocationId : null,
          pickup_address: wantsPickup && locationType === "salon" ? pickupAddress.trim() : null,
          payment_option: paymentOption,
          payment_method: paymentMethod,
          special_notes: specialNotes.trim(),
          silent_appointment: silentAppointment,
          terms_accepted: termsAccepted,
        }),
      });
      const responseText = await response.text();
      let result: any;
      try {
        result = responseText ? JSON.parse(responseText) : {};
      } catch {
        throw new Error(response.status >= 500
          ? "The booking or payment request may still be processing. Submit this same form again to recover the existing checkout; do not start a second booking."
          : "The booking server returned an unreadable response. Please try again from this page.");
      }

      if (result.reference && result.access_token) {
        sessionStorage.setItem(`isaiah_booking_access_${result.reference}`, result.access_token);
      }

      if (result.reference && result.status === "failed") {
        sessionStorage.removeItem(storageKey);
        setBookingRef(result.reference);
        router.push(`/booking/failed?reference=${encodeURIComponent(result.reference)}&reason=declined`);
        return;
      }

      if (!response.ok || !result.success || !result.reference) {
        if (response.status < 500) sessionStorage.removeItem(storageKey);
        throw new Error(result.message || result.errors?.time?.[0] || result.errors?.service_ids?.[0] || "Could not create your booking. Please review the details and try again.");
      }

      setSubmittingStep(3);
      setBookingRef(result.reference);
      const finalRecord = {
        reference: result.reference,
        name: guestName.trim(),
        phone: guestPhone.trim(),
        email: guestEmail.trim(),
        notes: specialNotes,
        date: selectedDate,
        time: selectedTime,
        locationType,
        homeAddress,
        wantsPickup,
        pickupLocationId,
        pickupFee,
        pickupAddress,
        selectedProviderId,
        providerName: selectedProvider?.name || "First Available Master",
        serviceIds: selectedServices.map((service) => service.id),
        paymentOption,
        paymentMethod,
        payableToday: Number(result.amount ?? payableToday),
        remainingAtSalon,
        totalInvestment,
        silentAppointment,
        isPaid: false,
      };
      localStorage.setItem("isaiah_booking_final_record", JSON.stringify(finalRecord));
      sessionStorage.removeItem(storageKey);
      router.push(`/booking/payment?reference=${encodeURIComponent(result.reference)}`);
    } catch (error) {
      setSubmitting(false);
      setSubmittingStep(0);
      setSubmitError(error instanceof Error ? error.message : "Could not create your booking. Please try again.");
    }
  };

  return (
    <div className="bg-[#FAF7F2] min-h-screen py-10 lg:py-16">
      <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        {bookingSuccess ? (
          /* ========================================================= */
          /* Deluxe Boarding Pass / Confirmation Ticket */
          /* ========================================================= */
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
            className="max-w-2xl mx-auto"
          >
            {/* Top Pass Card */}
            <div className="bg-white rounded-3xl border border-[#E5DFD5] shadow-xl overflow-hidden relative">
              {/* Header Ribbon */}
              <div className="bg-[#1C1816] text-white p-6 sm:p-8 text-center relative overflow-hidden">
                <div className="absolute -top-12 -right-12 w-40 h-40 rounded-full bg-[#B8975A]/20 blur-2xl pointer-events-none" />
                <div className="w-14 h-14 rounded-full bg-[#E6F4EA] text-[#34A853] border border-[#34A853]/25 flex items-center justify-center mx-auto mb-4">
                  <CheckCircle size={32} weight="fill" />
                </div>
                <span className="text-[10px] font-semibold tracking-[0.28em] uppercase text-[#B8975A] block mb-1">
                  Sanctuary Reservation Confirmed
                </span>
                <h2 className="font-serif text-2xl sm:text-3xl text-white font-normal mb-1">
                  We Await Your Presence
                </h2>
                <p className="text-xs text-white/75 font-light">
                  A bespoke ritual slot has been locked for{" "}
                  <strong className="text-white font-medium">{guestName || "Guest"}</strong>.
                </p>
              </div>

              {/* Booking Reference Pill */}
              <div className="bg-[#FAF7F2] border-y border-[#E5DFD5] py-3.5 px-6 flex items-center justify-between">
                <div>
                  <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block">
                    Official Reference Code
                  </span>
                  <span className="font-mono text-base font-bold text-[#221C1A] tracking-wider">
                    {bookingRef}
                  </span>
                </div>
                <button
                  type="button"
                  onClick={handleCopyReference}
                  className="px-3 py-1.5 rounded-full text-[11px] font-semibold uppercase tracking-wider bg-white border border-[#E5DFD5] hover:border-[#221C1A] text-[#221C1A] transition-colors cursor-pointer"
                >
                  {copiedRef ? "Copied!" : "Copy Code"}
                </button>
              </div>

              {/* Pass Body Content */}
              <div className="p-6 sm:p-8 space-y-5">
                {/* 2-column key info */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-5 border-b border-[#E5DFD5]">
                  <div>
                    <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block mb-0.5">
                      Schedule &amp; Time
                    </span>
                    <p className="text-sm font-semibold text-[#221C1A]">
                      {formattedSelectedDate}
                    </p>
                    <p className="text-xs text-[#5C5550]">
                      at {selectedTime || "10:00"} &bull; {formattedDuration}
                    </p>
                  </div>

                  <div>
                    <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block mb-0.5">
                      Master Artisan
                    </span>
                    <p className="text-sm font-semibold text-[#221C1A]">
                      {selectedProvider ? selectedProvider.name : "First Available Master"}
                    </p>
                    <p className="text-xs text-[#5C5550]">
                      {selectedProvider ? selectedProvider.role : "European Certified"}
                    </p>
                  </div>

                  <div>
                    <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block mb-0.5">
                      Sanctuary Location
                    </span>
                    <p className="text-sm font-semibold text-[#221C1A]">
                      {locationType === "salon" ? "In-Studio Sanctuary" : "Mobile Concierge"}
                    </p>
                    <p className="text-xs text-[#5C5550] line-clamp-1">
                      {locationType === "salon"
                        ? "KG 4 Roundabout, Gisementi"
                        : homeAddress || "Client Residence"}
                    </p>
                  </div>

                  <div>
                    <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block mb-0.5">
                      Guest &amp; Phone
                    </span>
                    <p className="text-sm font-semibold text-[#221C1A] truncate">
                      {guestName || "Guest"}
                    </p>
                    <p className="text-xs text-[#5C5550]">
                      +250 {guestPhone || "788 000 000"}
                    </p>
                  </div>
                </div>

                {/* Services list */}
                <div>
                  <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block mb-2 font-semibold">
                    Reserved Rituals ({selectedServices.length})
                  </span>
                  <div className="space-y-1.5">
                    {selectedServices.map((s) => (
                      <div
                        key={s.id}
                        className="flex items-center justify-between text-xs text-[#5C5550]"
                      >
                        <span className="flex items-center gap-2">
                          <span className="w-1.5 h-1.5 rounded-full bg-[#B8975A]" />
                          <strong className="text-[#221C1A] font-medium">{s.name}</strong>
                          <span className="text-[10px] text-[#8E857F]">
                            ({s.duration_minutes || 60}m)
                          </span>
                        </span>
                        <span className="font-medium text-[#221C1A]">
                          {formatRwf(s.price)} RWF
                        </span>
                      </div>
                    ))}
                  </div>
                </div>

                {/* Financial overview */}
                <div className="p-4 rounded-2xl bg-[#FAF7F2] border border-[#E5DFD5] space-y-1.5 text-xs">
                  <div className="flex justify-between">
                    <span className="text-[#8E857F]">Total Investment</span>
                    <span className="font-semibold text-[#221C1A]">
                      {formatRwf(totalInvestment)} RWF
                    </span>
                  </div>
                  <div className="flex justify-between text-[#B8975A] font-semibold">
                    <span>
                      {paymentOption === "full" ? "Amount Paid in Full" : "40% Deposit Paid"} (
                      {paymentMethod === "momo" ? "MTN/Airtel MoMo" : "Card"})
                    </span>
                    <span>{formatRwf(payableToday)} RWF</span>
                  </div>
                  {paymentOption === "deposit" && (
                    <div className="flex justify-between text-[11px] text-[#8E857F] pt-1 border-t border-[#E5DFD5]">
                      <span>Remaining Balance at Studio</span>
                      <span>{formatRwf(remainingAtSalon)} RWF</span>
                    </div>
                  )}
                  {silentAppointment && (
                    <div className="flex items-center gap-1.5 text-[11px] text-[#5C5550] pt-1">
                      <SpeakerSimpleSlash size={13} className="text-[#B8975A]" />
                      <span>Silent Ritual Requested</span>
                    </div>
                  )}
                </div>

                {/* What happens next */}
                <div className="pt-1">
                  <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block mb-1.5 font-semibold">
                    Sanctuary Arrival Guidance
                  </span>
                  <ul className="text-xs text-[#5C5550] space-y-1 font-light leading-relaxed">
                    <li>&bull; Arrive 5&ndash;10 minutes early to enjoy our herbal tea lounge.</li>
                    <li>&bull; Hospital-grade autoclave sterilization pouches will be opened in your presence.</li>
                    <li>&bull; Need to reschedule? Please inform our concierge at least 48 hours in advance.</li>
                  </ul>
                </div>
              </div>

              {/* Action Buttons */}
              <div className="p-6 bg-[#FAF7F2] border-t border-[#E5DFD5] flex flex-col sm:flex-row items-center gap-3">
                <button
                  type="button"
                  onClick={handleDownloadCalendar}
                  className="w-full sm:w-1/2 py-3 px-4 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-colors shadow-xs flex items-center justify-center gap-2 cursor-pointer"
                >
                  <CalendarCheck size={16} />
                  <span>Add to Calendar (.ics)</span>
                </button>

                <a
                  href={`https://wa.me/250790395169?text=Hello%20Isaiah%20Nail%20Bar,%20I%20have%20confirmed%20my%20reservation%20(Ref:%20${bookingRef})%20for%20${encodeURIComponent(
                    formattedSelectedDate
                  )}%20at%20${encodeURIComponent(selectedTime || "10:00")}.`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="w-full sm:w-1/2 py-3 px-4 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white border border-[#E5DFD5] hover:border-[#221C1A] transition-colors flex items-center justify-center gap-2 shadow-2xs"
                >
                  <WhatsappLogo size={16} weight="fill" className="text-[#25D366]" />
                  <span>WhatsApp Concierge</span>
                </a>
              </div>

              {/* Bottom links */}
              <div className="p-4 bg-white border-t border-[#E5DFD5] flex items-center justify-between text-xs">
                <button
                  type="button"
                  onClick={() => window.print()}
                  className="text-[#8E857F] hover:text-[#221C1A] font-medium transition-colors cursor-pointer"
                >
                  Print / Save Receipt
                </button>
                <Link
                  href="/"
                  className="text-[#B8975A] hover:underline font-semibold"
                >
                  Return to Homepage &rarr;
                </Link>
              </div>
            </div>
          </motion.div>
        ) : (
          /* ========================================================= */
          /* Step 5 Review & Final Confirmation Form */
          /* ========================================================= */
          <form onSubmit={handleFinalSubmit}>
            <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
              {/* ========================================================= */}
              {/* Left Column: Comprehensive Review Cards (8 cols) */}
              {/* ========================================================= */}
              <div className="lg:col-span-8 space-y-6">
                <div className="pb-3 border-b border-[#E5DFD5]">
                  <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-1">
                    Step 5 of 5 &bull; Sanctuary Verification
                  </span>
                  <h2 className="font-serif text-2xl sm:text-3xl text-[#221C1A] font-normal">
                    Review Your Appointment Details
                  </h2>
                </div>
              {submitError && (
                <p role="alert" className="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                  {submitError}
                </p>
              )}

                {/* 1. When & With Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  {/* When Card */}
                  <div className="bg-white rounded-3xl p-5 border border-[#E5DFD5] flex flex-col justify-between shadow-2xs">
                    <div className="flex items-start gap-3.5 mb-3">
                      <div className="w-10 h-10 rounded-2xl bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center shrink-0">
                        <CalendarCheck size={20} weight="light" />
                      </div>
                      <div>
                        <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block">
                          Schedule
                        </span>
                        <h4 className="font-serif text-base font-semibold text-[#221C1A]">
                          {formattedSelectedDate}
                        </h4>
                        <p className="text-xs text-[#5C5550] font-light mt-0.5">
                          at {selectedTime || "10:00"} &bull; {formattedDuration}
                        </p>
                      </div>
                    </div>
                    <div className="pt-3 border-t border-[#E5DFD5] flex justify-end">
                      <Link
                        href={`/booking/step3?${queryParams}`}
                        className="text-[11px] font-semibold uppercase tracking-wider text-[#B8975A] hover:underline"
                      >
                        Edit Time &rarr;
                      </Link>
                    </div>
                  </div>

                  {/* With Card */}
                  <div className="bg-white rounded-3xl p-5 border border-[#E5DFD5] flex flex-col justify-between shadow-2xs">
                    <div className="flex items-start gap-3.5 mb-3">
                      {selectedProvider && selectedProvider.photo ? (
                        <div className="relative w-10 h-10 rounded-2xl overflow-hidden shrink-0 border border-[#E5DFD5]">
                          <Image
                            src={selectedProvider.photo}
                            alt={selectedProvider.name}
                            fill
                            sizes="40px"
                            className="object-cover"
                          />
                        </div>
                      ) : (
                        <div className="w-10 h-10 rounded-2xl bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center shrink-0">
                          <User size={20} weight="light" />
                        </div>
                      )}
                      <div>
                        <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block">
                          Dedicated Artisan
                        </span>
                        <h4 className="font-serif text-base font-semibold text-[#221C1A]">
                          {selectedProvider ? selectedProvider.name : "First Available Master"}
                        </h4>
                        <p className="text-xs text-[#5C5550] font-light mt-0.5 line-clamp-1">
                          {selectedProvider ? selectedProvider.role : "European Certified"}
                        </p>
                      </div>
                    </div>
                    <div className="pt-3 border-t border-[#E5DFD5] flex justify-end">
                      <Link
                        href={`/booking/step2?${queryParams}`}
                        className="text-[11px] font-semibold uppercase tracking-wider text-[#B8975A] hover:underline"
                      >
                        Change Artist &rarr;
                      </Link>
                    </div>
                  </div>
                </div>

                {/* 2. Where & Guest Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  {/* Where Card */}
                  <div className="bg-white rounded-3xl p-5 border border-[#E5DFD5] flex flex-col justify-between shadow-2xs">
                    <div className="flex items-start gap-3.5 mb-3">
                      <div className="w-10 h-10 rounded-2xl bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center shrink-0">
                        {locationType === "salon" ? (
                          <Storefront size={20} weight="light" />
                        ) : (
                          <House size={20} weight="light" />
                        )}
                      </div>
                      <div>
                        <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block">
                          Sanctuary Location
                        </span>
                        <h4 className="font-serif text-base font-semibold text-[#221C1A]">
                          {locationType === "salon" ? "Sanctuary Studio" : "Mobile Concierge"}
                        </h4>
                        <p className="text-xs text-[#5C5550] font-light mt-0.5 line-clamp-1">
                          {locationType === "salon"
                            ? "KG 4 Roundabout, Gisementi"
                            : homeAddress || "Client Residence"}
                        </p>
                        {wantsPickup && currentPickupFee > 0 && (
                          <p className="text-[11px] text-[#B8975A] mt-1 font-medium">
                            + Chauffeur Pickup ({formatRwf(currentPickupFee)} RWF)
                          </p>
                        )}
                      </div>
                    </div>
                    <div className="pt-3 border-t border-[#E5DFD5] flex justify-end">
                      <Link
                        href={`/booking/step3?${queryParams}`}
                        className="text-[11px] font-semibold uppercase tracking-wider text-[#B8975A] hover:underline"
                      >
                        Edit Location &rarr;
                      </Link>
                    </div>
                  </div>

                  {/* Guest Contact Card */}
                  <div className="bg-white rounded-3xl p-5 border border-[#E5DFD5] flex flex-col justify-between shadow-2xs">
                    <div className="flex items-start gap-3.5 mb-3">
                      <div className="w-10 h-10 rounded-2xl bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center shrink-0">
                        <PhoneCall size={20} weight="light" />
                      </div>
                      <div>
                        <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block">
                          Guest &amp; Contact
                        </span>
                        <h4 className="font-serif text-base font-semibold text-[#221C1A] truncate">
                          {guestName || "Guest"}
                        </h4>
                        <p className="text-xs text-[#5C5550] font-light mt-0.5">
                          +250 {guestPhone || "788 000 000"}
                        </p>
                        {guestEmail && (
                          <p className="text-[11px] text-[#8E857F] truncate">{guestEmail}</p>
                        )}
                      </div>
                    </div>
                    <div className="pt-3 border-t border-[#E5DFD5] flex justify-end">
                      <Link
                        href={`/booking/step4?${queryParams}`}
                        className="text-[11px] font-semibold uppercase tracking-wider text-[#B8975A] hover:underline"
                      >
                        Edit Contact &rarr;
                      </Link>
                    </div>
                  </div>
                </div>

                {/* 3. Itemized Services Folio */}
                <div className="bg-white rounded-3xl p-6 border border-[#E5DFD5] shadow-2xs">
                  <div className="flex items-center justify-between pb-3 border-b border-[#E5DFD5] mb-4">
                    <div>
                      <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block">
                        Selected Rituals
                      </span>
                      <h3 className="font-serif text-base font-semibold text-[#221C1A]">
                        Treatment Folio ({selectedServices.length})
                      </h3>
                    </div>
                    <Link
                      href={`/booking?${queryParams}`}
                      className="text-[11px] text-[#B8975A] hover:underline font-semibold uppercase tracking-wider"
                    >
                      Modify Services &rarr;
                    </Link>
                  </div>

                  <div className="space-y-3">
                    {selectedServices.map((s) => (
                      <div
                        key={s.id}
                        className="flex items-center justify-between text-xs py-1.5 border-b border-[#FAF7F2] last:border-0"
                      >
                        <div className="flex items-center gap-2.5">
                          <span className="w-1.5 h-1.5 rounded-full bg-[#B8975A]" />
                          <span className="font-medium text-[#221C1A]">{s.name}</span>
                          <span className="text-[10px] text-[#8E857F]">
                            ({s.duration_minutes || 60}m)
                          </span>
                        </div>
                        <span className="font-semibold text-[#221C1A]">
                          {formatRwf(s.price)} RWF
                        </span>
                      </div>
                    ))}
                  </div>

                  {specialNotes && (
                    <div className="mt-4 pt-3 border-t border-[#E5DFD5] text-xs text-[#5C5550]">
                      <span className="text-[10px] uppercase font-semibold tracking-wider text-[#8E857F] block mb-0.5">
                        Special Instructions / Nail Notes:
                      </span>
                      <p className="italic bg-[#FAF7F2] p-3 rounded-xl border border-[#E5DFD5]/60">
                        &ldquo;{specialNotes}&rdquo;
                      </p>
                    </div>
                  )}
                </div>

                {/* 4. Boutique Salon Policies (Elevated from Blade) */}
                <div className="bg-[#FAF7F2] rounded-3xl p-6 sm:p-7 border border-[#E5DFD5] space-y-5">
                  <div className="flex items-center gap-2.5 pb-3 border-b border-[#E5DFD5]">
                    <Scroll size={20} weight="light" className="text-[#B8975A]" />
                    <div>
                      <h3 className="font-serif text-base font-semibold text-[#221C1A]">
                        Salon Sanctuary Policies
                      </h3>
                      <p className="text-[11px] text-[#8E857F]">
                        Our protocols ensure a tranquil, pristine, and punctual haven for every guest.
                      </p>
                    </div>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-[#5C5550]">
                    {/* Deposit */}
                    <div className="flex items-start gap-3 bg-white p-4 rounded-2xl border border-[#E5DFD5]">
                      <div className="w-8 h-8 rounded-full bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center shrink-0 mt-0.5">
                        <CreditCard size={16} />
                      </div>
                      <div>
                        <strong className="block font-semibold text-[#221C1A] mb-0.5">
                          Deposit Commitment (40%)
                        </strong>
                        <span className="leading-relaxed">
                          A 40% pre-payment secures your master specialist&apos;s uninterrupted calendar slot.
                        </span>
                      </div>
                    </div>

                    {/* Rescheduling */}
                    <div className="flex items-start gap-3 bg-white p-4 rounded-2xl border border-[#E5DFD5]">
                      <div className="w-8 h-8 rounded-full bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center shrink-0 mt-0.5">
                        <ClockCountdown size={16} />
                      </div>
                      <div>
                        <strong className="block font-semibold text-[#221C1A] mb-0.5">
                          48-Hour Notice Window
                        </strong>
                        <span className="leading-relaxed">
                          You may reschedule your ritual with at least 48 hours notice without deposit forfeiture.
                        </span>
                      </div>
                    </div>

                    {/* Punctuality */}
                    <div className="flex items-start gap-3 bg-white p-4 rounded-2xl border border-[#E5DFD5]">
                      <div className="w-8 h-8 rounded-full bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center shrink-0 mt-0.5">
                        <Clock size={16} />
                      </div>
                      <div>
                        <strong className="block font-semibold text-[#221C1A] mb-0.5">
                          15-Minute Grace Window
                        </strong>
                        <span className="leading-relaxed">
                          Please arrive 5&ndash;10 minutes early. Late arrivals beyond 15 minutes may require adjustment.
                        </span>
                      </div>
                    </div>

                    {/* Sanctuary Atmosphere */}
                    <div className="flex items-start gap-3 bg-white p-4 rounded-2xl border border-[#E5DFD5]">
                      <div className="w-8 h-8 rounded-full bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center shrink-0 mt-0.5">
                        <Users size={16} />
                      </div>
                      <div>
                        <strong className="block font-semibold text-[#221C1A] mb-0.5">
                          Intimate Atmosphere
                        </strong>
                        <span className="leading-relaxed">
                          To maintain hygiene and tranquility, please do not bring extra unbooked guests or children.
                        </span>
                      </div>
                    </div>
                  </div>

                  {/* Silent Appointment Option (Luxury Feature from Blade) */}
                  <div className="pt-2">
                    <label className="flex items-center gap-3 p-4 bg-white rounded-2xl border border-[#E5DFD5] hover:border-[#B8975A]/40 transition-colors cursor-pointer">
                      <input
                        type="checkbox"
                        checked={silentAppointment}
                        onChange={(e) => setSilentAppointment(e.target.checked)}
                        className="w-4 h-4 accent-[#221C1A] cursor-pointer"
                      />
                      <div>
                        <span className="text-xs font-semibold text-[#221C1A] flex items-center gap-1.5">
                          <SpeakerSimpleSlash size={15} className="text-[#B8975A]" />
                          <span>Request a Silent Ritual (Mindful Tranquility)</span>
                        </span>
                        <p className="text-[11px] text-[#8E857F] font-light mt-0.5">
                          No small talk beyond initial consultation—just restful music, complimentary tea, and peaceful pampering.
                        </p>
                      </div>
                    </label>
                  </div>
                </div>

                {/* 5. Terms Acceptance */}
                <div className="bg-white rounded-2xl p-4 sm:p-5 border border-[#E5DFD5] shadow-2xs">
                  <label className="flex items-start gap-3.5 cursor-pointer">
                    <input
                      type="checkbox"
                      required
                      checked={termsAccepted}
                      onChange={(e) => setTermsAccepted(e.target.checked)}
                      className="w-4 h-4 mt-0.5 accent-[#221C1A] cursor-pointer"
                    />
                    <span className="text-xs text-[#5C5550] leading-relaxed">
                      I confirm that all reservation details above are accurate and I agree to Isaiah Nail Bar&apos;s sanctuary cancellation, punctuality, and hygiene policies.
                    </span>
                  </label>
                </div>

                {/* Navigation Back */}
                <div className="pt-2">
                  <Link
                    href={`/booking/step4?${queryParams}`}
                    className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[#5C5550] hover:text-[#221C1A] transition-colors"
                  >
                    <ArrowLeft size={16} />
                    <span>&larr; Back to Guest Info &amp; Payment Channel</span>
                  </Link>
                </div>
              </div>

              {/* ========================================================= */}
              {/* Right Column: Final Summary Sidebar (4 cols) */}
              {/* ========================================================= */}
              <div className="lg:col-span-4 pl-2 sticky top-28">
                <div className="bg-[#1C1816] text-white rounded-3xl p-6 sm:p-7 border border-[#3A332F] shadow-2xl relative overflow-hidden">
                  <div className="absolute -top-16 -right-16 w-44 h-44 rounded-full bg-[#B8975A]/15 blur-3xl pointer-events-none" />

                  <div className="flex items-center justify-between pb-4 border-b border-white/10 mb-5 relative z-10">
                    <div>
                      <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-0.5">
                        Step 5 of 5 &bull; Authorization
                      </span>
                      <h3 className="font-serif text-xl text-white font-normal">
                        Total Investment
                      </h3>
                    </div>
                    <span className="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-[#B8975A]/20 text-[#E8C99B]">
                      Sanctuary Pass
                    </span>
                  </div>

                  {/* Summary Financials */}
                  <div className="space-y-3 mb-6 relative z-10 text-xs">
                    <div className="flex justify-between text-white/70">
                      <span>Services Subtotal ({selectedServices.length})</span>
                      <span>{formatRwf(baseServicePrice)} RWF</span>
                    </div>

                    <div className="flex justify-between text-white/70">
                      <span>Total Duration</span>
                      <span>{formattedDuration}</span>
                    </div>

                    {locationType === "home" && (
                      <div className="flex justify-between text-[#E8C99B]">
                        <span>Mobile Concierge (+100%)</span>
                        <span>+{formatRwf(baseServicePrice)} RWF</span>
                      </div>
                    )}

                    {wantsPickup && currentPickupFee > 0 && (
                      <div className="flex justify-between text-[#E8C99B]">
                        <span>Studio Chauffeur</span>
                        <span>+{formatRwf(currentPickupFee)} RWF</span>
                      </div>
                    )}

                    <div className="pt-2.5 border-t border-white/10 flex justify-between font-medium text-white">
                      <span className="text-sm">Grand Total</span>
                      <span className="font-serif text-lg font-bold text-white">
                        {formatRwf(totalInvestment)} RWF
                      </span>
                    </div>

                    {/* Pre-payment Box */}
                    <div className="p-3.5 rounded-2xl bg-white/5 border border-white/10 space-y-1.5 mt-2">
                      <div className="flex justify-between text-[#E8C99B] font-semibold text-xs">
                        <span>
                          {paymentOption === "full" ? "Amount Payable Today" : "40% Deposit Due Now"}
                        </span>
                        <span className="font-bold">{formatRwf(payableToday)} RWF</span>
                      </div>

                      {paymentOption === "deposit" && (
                        <div className="flex justify-between text-[11px] text-white/50 pt-1 border-t border-white/5">
                          <span>Balance at Studio</span>
                          <span>{formatRwf(remainingAtSalon)} RWF</span>
                        </div>
                      )}

                      <div className="flex justify-between text-[11px] text-white/60 pt-1">
                        <span>Payment Channel</span>
                        <span className="font-medium text-white">
                          {paymentMethod === "momo" ? "MTN / Airtel MoMo" : "Visa / Mastercard"}
                        </span>
                      </div>
                    </div>
                  </div>

                  {/* Submit Button */}
                  <button
                    type="submit"
                    disabled={submitting || !termsAccepted}
                    className="w-full py-4 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white hover:bg-[#B8975A] hover:text-white transition-all duration-300 shadow-xl disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2 group cursor-pointer relative z-10"
                  >
                    <LockSimple size={16} weight="bold" />
                    <span>
                      {submitting
                        ? submittingStep === 1
                          ? "Securing Artist Calendar..."
                          : submittingStep === 2
                          ? "Generating Sanctuary Pass..."
                          : "Finalizing..."
                        : "Confirm & Secure Ritual"}
                    </span>
                  </button>

                  <p className="text-center text-[10px] text-white/50 mt-4 relative z-10">
                    Secured by 256-Bit SSL Sanctuary Encryption &bull; Instant SMS &amp; WhatsApp concierge confirmation.
                  </p>
                </div>
              </div>
            </div>

            {/* Mobile Fixed Sticky Bar */}
            <div className="lg:hidden fixed bottom-0 left-0 right-0 bg-[#1C1816]/95 backdrop-blur-md border-t border-white/10 p-4 z-40">
              <div className="max-w-md mx-auto flex items-center justify-between gap-4">
                <div>
                  <span className="text-[10px] text-white/60 uppercase tracking-wider block">
                    {paymentOption === "full" ? "Due in Full" : "40% Deposit"}
                  </span>
                  <span className="font-serif text-base font-bold text-white">
                    {formatRwf(payableToday)} RWF
                  </span>
                </div>
                <button
                  type="submit"
                  disabled={submitting || !termsAccepted}
                  className="py-3 px-6 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white hover:bg-[#B8975A] hover:text-white transition-colors shadow-md disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2 cursor-pointer"
                >
                  <LockSimple size={15} weight="bold" />
                  <span>{submitting ? "Securing..." : "Confirm Booking"}</span>
                </button>
              </div>
            </div>
          </form>
        )}
      </div>
    </div>
  );
}
