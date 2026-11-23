"use client";

import { useState, useEffect, useMemo } from "react";
import Image from "next/image";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { motion } from "framer-motion";
import {
  CheckCircle,
  CalendarCheck,
  User,
  Storefront,
  House,
  WhatsappLogo,
  Printer,
  Copy,
  Check,
  ShieldCheck,
  ClockCountdown,
  ArrowRight,
  ArrowLeft,
  SpeakerSimpleSlash,
} from "@phosphor-icons/react";
import { TeamProvider } from "@/lib/team-data";
import { ServiceItem } from "@/lib/services-data";

interface BookingSuccessClientProps {
  providers?: TeamProvider[];
  allServices?: ServiceItem[];
}

interface StoredBookingRecord {
  reference: string;
  name: string;
  phone: string;
  email?: string;
  notes?: string;
  date: string;
  time: string;
  locationType: "salon" | "home";
  homeAddress?: string;
  wantsPickup?: boolean;
  pickupFee?: number;
  pickupAddress?: string;
  selectedProviderId: number | null;
  serviceIds: number[];
  paymentOption: "full" | "deposit";
  paymentMethod: "momo" | "card";
  payableToday: number;
  remainingAtSalon: number;
  totalInvestment: number;
  silentAppointment?: boolean;
  isPaid?: boolean;
}

export default function BookingSuccessClient({
  providers = [],
  allServices = [],
}: BookingSuccessClientProps) {
  const searchParams = useSearchParams();
  const urlRef = searchParams.get("reference");

  const [booking, setBooking] = useState<StoredBookingRecord | null>(null);
  const [copied, setCopied] = useState(false);
  const [loading, setLoading] = useState(true);
  const [paymentConfirmed, setPaymentConfirmed] = useState(false);
  const [loadError, setLoadError] = useState("");

  // A success page is rendered only from the persisted Laravel booking status.
  useEffect(() => {
    if (!urlRef) {
      setLoadError("A booking reference is required to view a confirmation.");
      setLoading(false);
      return;
    }

    const controller = new AbortController();
    const accessToken = sessionStorage.getItem(`isaiah_booking_access_${urlRef}`);
    if (!accessToken) {
      setLoadError("This booking can only be accessed from the browser session used to create it. Return to checkout or contact the salon with your reference.");
      setLoading(false);
      return;
    }
    fetch(`/api/v1/booking/status/${encodeURIComponent(urlRef)}`, {
      cache: "no-store",
      signal: controller.signal,
      headers: { "X-Booking-Access": accessToken },
    })
      .then(async (response) => {
        const result = await response.json();
        if (!response.ok || !result.booking) throw new Error(result.message || "We could not find this booking.");
        setBooking(result.booking);
        setPaymentConfirmed(result.status === "success");
      })
      .catch((error: unknown) => {
        if (error instanceof Error && error.name !== "AbortError") setLoadError(error.message || "Could not load the booking.");
      })
      .finally(() => {
        if (!controller.signal.aborted) setLoading(false);
      });

    return () => controller.abort();
  }, [urlRef]);

  // Copy booking reference
  const handleCopy = () => {
    if (!booking) return;
    navigator.clipboard.writeText(booking.reference);
    setCopied(true);
    setTimeout(() => setCopied(false), 2200);
  };

  // Provider lookup
  const provider = useMemo(() => {
    if (!booking) return null;
    if (booking.selectedProviderId === 0 || !booking.selectedProviderId) {
      return {
        id: 0,
        name: "First Available Master",
        role: "Optimal European Standard",
        photo: "/banner.jpg",
      };
    }
    return (
      providers.find((p) => p.id === booking.selectedProviderId) || {
        id: booking.selectedProviderId,
        name: "Dedicated Specialist",
        role: "Certified Artisan",
        photo: "/banner.jpg",
      }
    );
  }, [booking, providers]);

  // Selected services lookup
  const services = useMemo(() => {
    if (!booking || !booking.serviceIds || booking.serviceIds.length === 0) return [];
    return allServices.filter((s) => booking.serviceIds.includes(s.id));
  }, [booking, allServices]);

  const formatRwf = (val: number) => {
    return new Intl.NumberFormat("en-RW", {
      style: "decimal",
      maximumFractionDigits: 0,
    }).format(val);
  };

  const formattedDate = useMemo(() => {
    if (!booking?.date) return "Confirmed Schedule";
    const [y, m, d] = booking.date.split("-").map(Number);
    const dt = new Date(y, m - 1, d);
    return dt.toLocaleDateString("en-US", {
      weekday: "long",
      month: "long",
      day: "numeric",
      year: "numeric",
    });
  }, [booking]);

  // Calendar .ics download
  const handleDownloadCalendar = () => {
    if (!booking) return;
    const [y, m, d] = booking.date.split("-").map(Number);
    const [h, min] = booking.time.split(":").map(Number);
    const startDate = new Date(y, m - 1, d, h || 10, min || 0);
    const endDate = new Date(startDate.getTime() + 90 * 60000);

    const pad = (n: number) => (n < 10 ? `0${n}` : `${n}`);
    const formatIcsDate = (dt: Date) =>
      `${dt.getUTCFullYear()}${pad(dt.getUTCMonth() + 1)}${pad(dt.getUTCDate())}T${pad(
        dt.getUTCHours()
      )}${pad(dt.getUTCMinutes())}00Z`;

    const summary = `Isaiah Nail Bar — Luxury Nail Ritual (${booking.reference})`;
    const location =
      booking.locationType === "salon"
        ? "KG 4 Roundabout, Gisementi, Kigali, Rwanda"
        : booking.homeAddress || "Client Residence, Kigali";
    const description = `Bespoke nail ritual appointment at Isaiah Nail Bar Kigali. Specialist: ${
      provider?.name || "Master Artist"
    }. Reference: ${booking.reference}. Total: ${formatRwf(booking.totalInvestment)} RWF.`;

    const icsContent = [
      "BEGIN:VCALENDAR",
      "VERSION:2.0",
      "PRODID:-//Isaiah Nail Bar Kigali//Reservation//EN",
      "CALSCALE:GREGORIAN",
      "METHOD:PUBLISH",
      "BEGIN:VEVENT",
      `UID:${booking.reference}@isaiahnailbar.rw`,
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
      "DESCRIPTION:Reminder: Upcoming luxury nail ritual at Isaiah Nail Bar Kigali",
      "END:VALARM",
      "END:VEVENT",
      "END:VCALENDAR",
    ].join("\r\n");

    const blob = new Blob([icsContent], { type: "text/calendar;charset=utf-8" });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `Isaiah_Nail_Bar_${booking.reference}.ics`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  };

  if (loading) {
    return (
      <div className="py-24 text-center text-sm text-[#8E857F]">
        Loading your reservation pass...
      </div>
    );
  }

  if (loadError || !booking) {
    return <div className="mx-auto max-w-xl px-4 py-24 text-center text-sm text-rose-800" role="alert">{loadError || "Booking not found."}</div>;
  }

  if (!paymentConfirmed) {
    return (
      <div className="mx-auto max-w-xl px-4 py-24 text-center">
        <div className="rounded-3xl border border-[#E5DFD5] bg-white p-8 shadow-xl">
          <h1 className="font-serif text-2xl text-[#221C1A]">Payment is not confirmed yet</h1>
          <p className="mt-3 text-sm leading-relaxed text-[#5C5550]">We only issue the reservation pass after WeFlexy confirms the payment. Keep reference <strong>{booking.reference}</strong> and check again shortly.</p>
          <Link href={`/booking/payment?reference=${encodeURIComponent(booking.reference)}`} className="mt-6 inline-flex rounded-full bg-[#221C1A] px-5 py-3 text-xs font-semibold uppercase tracking-wide text-white">Return to payment status</Link>
        </div>
      </div>
    );
  }

  return (
    <div className="py-12 sm:py-16 bg-[#FAF7F2]">
      <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* ========================================================= */}
        {/* Celebration Header */}
        {/* ========================================================= */}
        <motion.div
          initial={{ opacity: 0, y: 15 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5 }}
          className="text-center mb-10"
        >
          {/* Animated Halo & Green Checkmark */}
          <div className="relative inline-block mb-6">
            <div className="absolute inset-0 rounded-full bg-[#34A853]/20 animate-ping opacity-75" />
            <div className="relative w-20 h-20 rounded-full bg-[#E6F4EA] border border-[#34A853]/30 text-[#34A853] flex items-center justify-center shadow-md mx-auto">
              <CheckCircle size={44} weight="fill" />
            </div>
          </div>

          <span className="text-[10px] font-semibold tracking-[0.28em] uppercase text-[#B8975A] block mb-2">
            Sanctuary Reservation Guaranteed
          </span>

          <h1 className="font-serif text-3xl sm:text-4xl text-[#221C1A] font-normal mb-3">
            Your Appointment is Confirmed
          </h1>

          <p className="text-xs sm:text-sm text-[#5C5550] font-light leading-relaxed max-w-lg mx-auto">
            Thank you, <strong className="text-[#221C1A] font-semibold">{booking.name}</strong>. Your master artist has reserved their dedicated schedule for you. A digital receipt and calendar invitation have been transmitted to your phone.
          </p>
        </motion.div>

        {/* ========================================================= */}
        {/* Digital Sanctuary Pass / Receipt Card (From Blade success.blade.php) */}
        {/* ========================================================= */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5, delay: 0.15 }}
          className="bg-white rounded-3xl border border-[#E5DFD5] shadow-xl overflow-hidden mb-8"
        >
          {/* Top Gold Foil Bar */}
          <div className="bg-[#1C1816] text-white p-6 sm:p-7 relative overflow-hidden">
            <div className="absolute -top-12 -right-12 w-40 h-40 rounded-full bg-[#B8975A]/20 blur-2xl pointer-events-none" />

            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 relative z-10">
              <div>
                <span className="text-[10px] font-mono tracking-widest uppercase text-[#B8975A] block mb-1">
                  Official Booking Reference
                </span>
                <span className="font-mono text-2xl font-bold text-white tracking-wider">
                  #{booking.reference}
                </span>
              </div>

              <div className="flex items-center gap-2">
                <button
                  type="button"
                  onClick={handleCopy}
                  className="px-3.5 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wider bg-white/10 hover:bg-white/20 text-[#E8C99B] border border-white/15 transition-colors flex items-center gap-1.5 cursor-pointer"
                >
                  {copied ? (
                    <>
                      <Check size={14} weight="bold" />
                      <span>Copied!</span>
                    </>
                  ) : (
                    <>
                      <Copy size={14} />
                      <span>Copy Code</span>
                    </>
                  )}
                </button>
                <span className="px-3 py-1 rounded-full text-[11px] font-semibold uppercase tracking-wide bg-[#34A853]/20 text-[#85E0A3] border border-[#34A853]/30">
                  Payment Confirmed
                </span>
              </div>
            </div>
          </div>

          {/* Pass Body */}
          <div className="p-6 sm:p-8 space-y-6">
            {/* Grid 1: When & With */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-5 pb-6 border-b border-[#E5DFD5]">
              <div className="flex items-start gap-3.5">
                <div className="w-10 h-10 rounded-2xl bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center shrink-0">
                  <CalendarCheck size={20} weight="light" />
                </div>
                <div>
                  <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block mb-0.5">
                    Schedule &amp; Time
                  </span>
                  <p className="font-serif text-base font-semibold text-[#221C1A]">
                    {formattedDate}
                  </p>
                  <p className="text-xs text-[#5C5550]">at {booking.time}</p>
                </div>
              </div>

              <div className="flex items-start gap-3.5">
                {provider?.photo ? (
                  <div className="relative w-10 h-10 rounded-2xl overflow-hidden shrink-0 border border-[#E5DFD5]">
                    <Image
                      src={provider.photo}
                      alt={provider.name}
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
                  <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block mb-0.5">
                    Master Artisan
                  </span>
                  <p className="font-serif text-base font-semibold text-[#221C1A]">
                    {provider?.name}
                  </p>
                  <p className="text-xs text-[#5C5550]">{provider?.role}</p>
                </div>
              </div>
            </div>

            {/* Grid 2: Where & Contact */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-5 pb-6 border-b border-[#E5DFD5]">
              <div className="flex items-start gap-3.5">
                <div className="w-10 h-10 rounded-2xl bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center shrink-0">
                  {booking.locationType === "salon" ? (
                    <Storefront size={20} weight="light" />
                  ) : (
                    <House size={20} weight="light" />
                  )}
                </div>
                <div>
                  <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block mb-0.5">
                    Sanctuary Location
                  </span>
                  <p className="font-serif text-base font-semibold text-[#221C1A]">
                    {booking.locationType === "salon" ? "Sanctuary Studio" : "Mobile Atelier"}
                  </p>
                  <p className="text-xs text-[#5C5550] line-clamp-1">
                    {booking.locationType === "salon"
                      ? "KG 4 Roundabout, Gisementi, Kigali"
                      : booking.homeAddress || "Client Residence"}
                  </p>
                </div>
              </div>

              <div className="flex items-start gap-3.5">
                <div className="w-10 h-10 rounded-2xl bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center shrink-0">
                  <ShieldCheck size={20} weight="light" />
                </div>
                <div>
                  <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block mb-0.5">
                    Contact &amp; Guest
                  </span>
                  <p className="font-serif text-base font-semibold text-[#221C1A] truncate">
                    {booking.name}
                  </p>
                  <p className="text-xs text-[#5C5550]">{booking.phone}</p>
                </div>
              </div>
            </div>

            {/* Selected Treatments */}
            <div>
              <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block mb-3 font-semibold">
                Reserved Rituals ({services.length})
              </span>
              <div className="space-y-2">
                {services.map((s) => (
                  <div
                    key={s.id}
                    className="flex items-center justify-between text-xs py-1.5 border-b border-[#FAF7F2] last:border-0"
                  >
                    <span className="flex items-center gap-2">
                      <span className="w-1.5 h-1.5 rounded-full bg-[#B8975A]" />
                      <strong className="text-[#221C1A] font-medium">{s.name}</strong>
                      <span className="text-[10px] text-[#8E857F]">
                        ({s.duration_minutes || 60}m)
                      </span>
                    </span>
                    <span className="font-medium text-[#221C1A]">{formatRwf(s.price)} RWF</span>
                  </div>
                ))}
              </div>
            </div>

            {/* Financial Status Summary */}
            <div className="p-4 rounded-2xl bg-[#FAF7F2] border border-[#E5DFD5] space-y-2 text-xs">
              <div className="flex justify-between text-[#8E857F]">
                <span>Total Investment</span>
                <span className="font-semibold text-[#221C1A]">
                  {formatRwf(booking.totalInvestment)} RWF
                </span>
              </div>
              <div className="flex justify-between text-[#34A853] font-semibold">
                <span>
                  {booking.paymentOption === "full" ? "Amount Paid in Full" : "40% Deposit Paid"} (
                  {booking.paymentMethod === "momo" ? "MTN / Airtel MoMo" : "Card"})
                </span>
                <span>{formatRwf(booking.payableToday)} RWF</span>
              </div>
              {booking.paymentOption === "deposit" && (
                <div className="flex justify-between text-[11px] text-[#8E857F] pt-1.5 border-t border-[#E5DFD5]">
                  <span>Remaining Balance Due at Studio</span>
                  <span>{formatRwf(booking.remainingAtSalon)} RWF</span>
                </div>
              )}
            </div>

            {/* Etiquette notice */}
            <div className="p-4 rounded-2xl bg-white border border-[#E5DFD5] text-xs text-[#5C5550] space-y-1">
              <span className="text-[10px] font-semibold uppercase tracking-wider text-[#B8975A] block mb-1">
                Sanctuary Guidance
              </span>
              <p>&bull; Please arrive 5&ndash;10 minutes prior to enjoy our herbal tea lounge.</p>
              <p>&bull; Autoclave-sealed sterilization pouches will be unsealed in your presence.</p>
              <p>&bull; 48-hour advance notice is required for any appointment rescheduling.</p>
            </div>
          </div>

          {/* Action Row */}
          <div className="p-6 bg-[#FAF7F2] border-t border-[#E5DFD5] flex flex-col sm:flex-row items-center gap-3">
            <button
              type="button"
              onClick={handleDownloadCalendar}
              className="w-full sm:w-1/2 py-3 px-4 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-colors shadow-sm flex items-center justify-center gap-2 cursor-pointer"
            >
              <CalendarCheck size={16} />
              <span>Add to Calendar (.ics)</span>
            </button>

            <a
              href={`https://wa.me/250790395169?text=Hello%20Isaiah%20Nail%20Bar,%20I%20have%20confirmed%20my%20reservation%20(Ref:%20${booking.reference})%20for%20${encodeURIComponent(
                formattedDate
              )}%20at%20${encodeURIComponent(booking.time)}.`}
              target="_blank"
              rel="noopener noreferrer"
              className="w-full sm:w-1/2 py-3 px-4 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white border border-[#E5DFD5] hover:border-[#221C1A] transition-colors flex items-center justify-center gap-2 shadow-2xs"
            >
              <WhatsappLogo size={16} weight="fill" className="text-[#25D366]" />
              <span>WhatsApp Concierge</span>
            </a>
          </div>

          {/* Print receipt footer */}
          <div className="px-6 py-3.5 bg-white border-t border-[#E5DFD5] flex items-center justify-between text-xs">
            <button
              type="button"
              onClick={() => window.print()}
              className="text-[#8E857F] hover:text-[#221C1A] flex items-center gap-1.5 transition-colors cursor-pointer"
            >
              <Printer size={15} />
              <span>Print Official Receipt</span>
            </button>

            <span className="text-[11px] text-[#8E857F]">
              Hospital-Grade Standards Certified
            </span>
          </div>
        </motion.div>

        {/* ========================================================= */}
        {/* Google Review Banner (From Blade success.blade.php) */}
        {/* ========================================================= */}
        <motion.div
          initial={{ opacity: 0, y: 15 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5, delay: 0.25 }}
          className="bg-white rounded-3xl p-6 sm:p-8 border border-[#E5DFD5] text-center shadow-xs mb-8"
        >
          <div className="inline-flex items-center justify-center w-12 h-12 rounded-full bg-[#FAF7F2] border border-[#E5DFD5] mb-3">
            <svg className="w-6 h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"
                fill="#4285F4"
              />
              <path
                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                fill="#34A853"
              />
              <path
                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                fill="#FBBC05"
              />
              <path
                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                fill="#EA4335"
              />
            </svg>
          </div>

          <h3 className="font-serif text-xl font-normal text-[#221C1A] mb-1.5">
            Share Your Experience
          </h3>
          <p className="text-xs text-[#5C5550] max-w-md mx-auto mb-5 leading-relaxed font-light">
            Help other guests discover Isaiah Nail Bar Kigali by leaving a Google review. It takes 30 seconds and means the world to our artisans.
          </p>

          <a
            href="https://g.page/r/CS4QpNuz_MJkEAE/review"
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center gap-2 px-6 py-3 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white border border-[#221C1A] hover:bg-[#221C1A] hover:text-white transition-all shadow-xs"
          >
            Review Us on Google &rarr;
          </a>
        </motion.div>

        {/* Back navigation */}
        <div className="text-center">
          <Link
            href="/"
            className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[#5C5550] hover:text-[#221C1A] transition-colors"
          >
            <ArrowLeft size={16} />
            <span>Return to Homepage</span>
          </Link>
        </div>
      </div>
    </div>
  );
}

