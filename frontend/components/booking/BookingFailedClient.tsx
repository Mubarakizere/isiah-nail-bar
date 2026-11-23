"use client";

import { useState, useEffect, useMemo } from "react";
import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { motion } from "framer-motion";
import {
  XCircle,
  Warning,
  ArrowClockwise,
  CreditCard,
  DeviceMobile,
  WhatsappLogo,
  ArrowLeft,
  CalendarCheck,
  User,
  ShieldCheck,
} from "@phosphor-icons/react";

export default function BookingFailedClient() {
  const router = useRouter();
  const searchParams = useSearchParams();

  const reference = searchParams.get("reference") || "";
  const reason = searchParams.get("reason") || "declined";
  const [paymentStatus, setPaymentStatus] = useState<"loading" | "pending" | "failed" | "success" | "unknown">("loading");
  const [retrying, setRetrying] = useState(false);
  const [retryError, setRetryError] = useState("");

  const [bookingData, setBookingData] = useState<{
    payableToday: number;
    paymentMethod: string;
    date: string;
    time: string;
    providerName: string;
    servicesCount: number;
  }>({
    payableToday: 0,
    paymentMethod: "momo",
    date: "—",
    time: "—",
    providerName: "Salon team",
    servicesCount: 0,
  });

  useEffect(() => {
    if (!reference) {
      setPaymentStatus("unknown");
      setRetryError("A booking reference is required.");
      return;
    }

    const controller = new AbortController();
    const accessToken = sessionStorage.getItem(`isaiah_booking_access_${reference}`);
    if (!accessToken) {
      setPaymentStatus("unknown");
      setRetryError("This booking can only be accessed from the browser session used to create it. Return to checkout or contact the salon with your reference.");
      return;
    }
    fetch(`/api/v1/booking/status/${encodeURIComponent(reference)}`, {
      cache: "no-store",
      signal: controller.signal,
      headers: { "X-Booking-Access": accessToken },
    })
      .then(async (response) => {
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || "Booking not found.");
        if (result.status === "success") {
          setPaymentStatus("success");
          router.replace(`/booking/success?reference=${encodeURIComponent(reference)}`);
          return;
        }
        setPaymentStatus(result.status === "failed" ? "failed" : "pending");
        if (result.booking) {
          setBookingData({
            payableToday: Number(result.booking.payableToday || 0),
            paymentMethod: result.booking.paymentMethod || "momo",
            date: result.booking.date || "Selected Date",
            time: result.booking.time || "10:00",
            providerName: result.booking.providerName || "Salon team",
            servicesCount: result.booking.serviceIds?.length || 0,
          });
        }
      })
      .catch((error: unknown) => {
        if (error instanceof Error && error.name !== "AbortError") {
          setPaymentStatus("unknown");
          setRetryError(error.message || "Could not confirm the payment status.");
        }
      });
    return () => controller.abort();
  }, [reference, router]);

  const formatRwf = (val: number) => {
    return new Intl.NumberFormat("en-RW", {
      style: "decimal",
      maximumFractionDigits: 0,
    }).format(val);
  };

  const reasonDetails = useMemo(() => {
    switch (reason) {
      case "cancelled":
        return {
          title: "Payment Process Cancelled",
          badge: "Cancelled by User",
          description:
            "You cancelled the payment transaction. Your reservation details remain intact so you can resume whenever you are ready.",
        };
      case "timeout":
        return {
          title: "Payment Verification Timed Out",
          badge: "Network Timeout",
          description:
            "The network took longer than expected to confirm the transaction. If funds were deducted from your account, please message our concierge with your reference code.",
        };
      default:
        return {
          title: "Authorization Unsuccessful",
          badge: "Payment Declined",
          description:
            "The mobile money prompt was declined or expired before PIN entry. This often occurs due to insufficient funds, an incorrect phone number, or a momentary carrier timeout.",
        };
    }
  }, [reason]);

  const handleRetryPayment = async () => {
    if (!reference || paymentStatus !== "failed" || retrying) return;
    setRetrying(true);
    setRetryError("");
    const storageKey = `isaiah_booking_retry_${reference}`;
    const idempotencyKey = sessionStorage.getItem(storageKey) || crypto.randomUUID();
    sessionStorage.setItem(storageKey, idempotencyKey);
    const accessToken = sessionStorage.getItem(`isaiah_booking_access_${reference}`);
    try {
      if (!accessToken) throw new Error("Booking access could not be verified. Return to the browser session used for checkout.");
      const response = await fetch(`/api/v1/booking/retry/${encodeURIComponent(reference)}`, {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json", "X-Booking-Access": accessToken },
        body: JSON.stringify({ idempotency_key: idempotencyKey, payment_method: bookingData.paymentMethod }),
      });
      const result = await response.json();
      if (!response.ok || !result.success) {
        if (response.status < 500) sessionStorage.removeItem(storageKey);
        throw new Error(result.message || "Could not retry this payment.");
      }
      if (result.access_token) sessionStorage.setItem(`isaiah_booking_access_${result.reference || reference}`, result.access_token);
      sessionStorage.removeItem(storageKey);
      router.push(`/booking/payment?reference=${encodeURIComponent(result.reference || reference)}`);
    } catch (error) {
      setRetryError(error instanceof Error ? error.message : "Could not retry this payment.");
      setRetrying(false);
    }
  };

  return (
    <div className="py-12 sm:py-16 bg-[#FAF7F2]">
      <div className="max-w-xl mx-auto px-4 sm:px-6">
        <motion.div
          initial={{ opacity: 0, y: 15 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.4 }}
          className="bg-white rounded-3xl border border-[#E5DFD5] shadow-xl overflow-hidden text-center"
        >
          {/* Header */}
          <div className="p-8 sm:p-10 border-b border-[#E5DFD5]">
            <div className="w-16 h-16 rounded-full bg-[#FDEDEC] border border-[#F5B7B1] text-[#C0392B] flex items-center justify-center mx-auto mb-5 shadow-xs">
              <XCircle size={38} weight="fill" />
            </div>

            <div className="inline-block px-3 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-[#FDEDEC] text-[#C0392B] border border-[#F5B7B1] mb-3">
              {paymentStatus === "pending" || paymentStatus === "loading" ? "Payment status pending" : reasonDetails.badge} &bull; Ref: {reference || "—"}
            </div>

            <h1 className="font-serif text-2xl sm:text-3xl text-[#221C1A] font-normal mb-3">
              {paymentStatus === "pending" || paymentStatus === "loading" ? "We’re Still Confirming Your Payment" : reasonDetails.title}
            </h1>

            <p className="text-xs sm:text-sm text-[#5C5550] font-light leading-relaxed max-w-md mx-auto">
              {paymentStatus === "pending" || paymentStatus === "loading"
                ? "The browser cannot confirm or cancel a payment. We are waiting for WeFlexy’s signed update. Please do not pay again until this status changes."
                : reasonDetails.description}
            </p>
          </div>

          {/* Reserved Slot Preserved Alert */}
          <div className="p-4 bg-[#FAF7F2] border-b border-[#E5DFD5] text-left flex items-start gap-3">
            <ShieldCheck size={20} className="text-[#B8975A] shrink-0 mt-0.5" />
            <div className="text-xs text-[#5C5550] leading-relaxed">
              <strong className="text-[#221C1A] block font-semibold">
                {paymentStatus === "pending" ? "Your Payment Attempt Is Still Open" : "Your Booking Details Are Preserved"}
              </strong>
              <span>
                {paymentStatus === "pending"
                  ? "WeFlexy may still complete an authorized transaction. We will not start another attempt until the provider confirms this one failed."
                  : "Your saved booking details remain available. The time is checked again before a retry is started."}
              </span>
            </div>
          </div>

          {/* Booking Summary Mini Ticket */}
          <div className="p-6 bg-white text-left space-y-2.5 text-xs text-[#5C5550]">
            <div className="flex justify-between items-center">
              <span className="text-[#8E857F]">Amount Due</span>
              <span className="font-serif text-base font-bold text-[#221C1A]">
                {formatRwf(bookingData.payableToday)} RWF
              </span>
            </div>

            <div className="flex justify-between items-center pt-2 border-t border-[#FAF7F2]">
              <span className="text-[#8E857F]">Scheduled Date</span>
              <span className="font-medium text-[#221C1A]">
                {bookingData.date} at {bookingData.time}
              </span>
            </div>

            <div className="flex justify-between items-center pt-2 border-t border-[#FAF7F2]">
              <span className="text-[#8E857F]">Payment Method</span>
              <span className="font-medium text-[#221C1A]">
                {bookingData.paymentMethod === "momo" ? "MTN / Airtel MoMo" : "Card"}
              </span>
            </div>
          </div>

          {retryError && <p role="alert" className="mx-6 mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-left text-xs text-rose-800">{retryError}</p>}

          {/* Primary Action Buttons */}
          <div className="p-6 bg-[#FAF7F2] border-t border-[#E5DFD5] space-y-3">
            <button
              type="button"
              onClick={handleRetryPayment}
              disabled={retrying || paymentStatus !== "failed"}
              className="w-full py-3.5 px-6 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-all duration-300 shadow-md flex items-center justify-center gap-2 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50"
            >
              <ArrowClockwise size={16} weight="bold" />
              <span>{retrying ? "Starting secure retry…" : paymentStatus === "pending" || paymentStatus === "loading" ? "Waiting for provider confirmation" : "Retry Payment with WeFlexy"}</span>
            </button>

            <div className="flex flex-col sm:flex-row gap-3">
              <Link
                href="/booking/step4"
                className="w-full sm:w-1/2 py-3 px-4 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white border border-[#E5DFD5] hover:border-[#221C1A] transition-colors flex items-center justify-center gap-2"
              >
                <CreditCard size={15} />
                <span>Change Channel</span>
              </Link>

              <a
                href={`https://wa.me/250790395169?text=Hello%20Isaiah%20Nail%20Bar,%20my%20payment%20authorization%20was%20unsuccessful%20for%20reservation%20(Ref:%20${reference}).%20Please%20assist%20me.`}
                target="_blank"
                rel="noopener noreferrer"
                className="w-full sm:w-1/2 py-3 px-4 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white border border-[#E5DFD5] hover:border-[#221C1A] transition-colors flex items-center justify-center gap-2 shadow-2xs"
              >
                <WhatsappLogo size={16} weight="fill" className="text-[#25D366]" />
                <span>WhatsApp Help</span>
              </a>
            </div>
          </div>

          {/* Back links */}
          <div className="p-4 bg-white border-t border-[#E5DFD5] text-xs">
            <Link
              href="/booking"
              className="text-[#8E857F] hover:text-[#221C1A] font-medium transition-colors"
            >
              &larr; Start a New Reservation
            </Link>
          </div>
        </motion.div>
      </div>
    </div>
  );
}

