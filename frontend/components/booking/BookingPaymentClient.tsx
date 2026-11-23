"use client";

import { useState, useEffect, useRef, useCallback } from "react";
import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { motion, AnimatePresence } from "framer-motion";
import {
  LockSimple,
  ShieldCheck,
  ArrowClockwise,
  Warning,
} from "@phosphor-icons/react";

export default function BookingPaymentClient() {
  const router = useRouter();
  const searchParams = useSearchParams();

  // Reference and iframe parameters
  const [reference, setReference] = useState<string>("");
  const [iframeUrl, setIframeUrl] = useState<string>("");
  const [amount, setAmount] = useState<number>(0);

  // Processing overlay state (from payment-iframe.blade.php)
  const [paymentSubmitted, setPaymentSubmitted] = useState<boolean>(false);
  const [statusMessage, setStatusMessage] = useState<string>("Waiting for payment submission...");
  const [showCancelConfirm, setShowCancelConfirm] = useState<boolean>(false);

  const pollingIntervalRef = useRef<NodeJS.Timeout | null>(null);
  const iframeRef = useRef<HTMLIFrameElement>(null);

  // Never invent a booking reference or fall back to a simulated gateway.
  useEffect(() => {
    const bookingReference = searchParams.get("reference");
    if (!bookingReference) {
      router.replace("/booking/step1");
      return;
    }
    setReference(bookingReference);
  }, [searchParams, router]);

  const verifyPaymentStatus = useCallback(async () => {
    if (!reference) return;
    setStatusMessage("Checking the signed payment update from WeFlexy…");

    try {
      const accessToken = sessionStorage.getItem(`isaiah_booking_access_${reference}`);
      if (!accessToken) {
        setStatusMessage("This booking can only be accessed from the browser session used to create it. Return to checkout or contact the salon with your reference.");
        return;
      }
      const response = await fetch(`/api/v1/booking/status/${encodeURIComponent(reference)}`, {
        cache: "no-store",
        headers: { "X-Booking-Access": accessToken },
      });
      const result = await response.json();
      if (!response.ok) {
        setStatusMessage(result.message || "We could not load this booking. Please contact the salon with your reference.");
        return;
      }

      const nextAmount = Number(result.booking?.payableToday);
      if (Number.isFinite(nextAmount) && nextAmount > 0) setAmount(nextAmount);
      if (result.status === "success") {
        setPaymentSubmitted(true);
        setStatusMessage("Payment confirmed by WeFlexy. Preparing your booking pass…");
        router.replace(`/booking/success?reference=${encodeURIComponent(reference)}`);
        return;
      }

      if (result.status === "failed") {
        setPaymentSubmitted(true);
        setStatusMessage("WeFlexy confirmed that this payment attempt failed.");
        router.replace(`/booking/failed?reference=${encodeURIComponent(reference)}&reason=declined`);
        return;
      }

      if (typeof result.payment_url === "string" && result.payment_url !== iframeUrl) {
        setIframeUrl(result.payment_url);
      }
      setStatusMessage(result.payment_url
        ? "Complete the payment in the secure WeFlexy window. We will confirm it when the signed update arrives."
        : result.message || "Payment setup is still being confirmed. Checking again shortly…");
    } catch {
      setStatusMessage("Could not reach the booking server. We will keep checking; no payment is assumed successful.");
    }
  }, [reference, iframeUrl, router]);

  useEffect(() => {
    if (!reference) return;
    void verifyPaymentStatus();
    pollingIntervalRef.current = setInterval(() => void verifyPaymentStatus(), 4000);
    return () => {
      if (pollingIntervalRef.current) clearInterval(pollingIntervalRef.current);
    };
  }, [reference, verifyPaymentStatus]);

  useEffect(() => {
    if (!iframeUrl) return;
    let expectedOrigin = "";
    try {
      expectedOrigin = new URL(iframeUrl).origin;
    } catch {
      setIframeUrl("");
      return;
    }

    const handleMessage = (event: MessageEvent) => {
      if (event.source !== iframeRef.current?.contentWindow || event.origin !== expectedOrigin) return;
      if (event.data?.type !== "PAYMENT_STATUS") return;

      if (event.data.status === "success" || event.data.status === "failed") {
        setPaymentSubmitted(true);
        setStatusMessage("Payment response received. Waiting for WeFlexy’s signed confirmation…");
        void verifyPaymentStatus();
      } else if (event.data.status === "close" || event.data.status === "cancelled") {
        setShowCancelConfirm(true);
      }
    };

    window.addEventListener("message", handleMessage);
    return () => window.removeEventListener("message", handleMessage);
  }, [iframeUrl, verifyPaymentStatus]);

  const formatRwf = (val: number) => {
    return new Intl.NumberFormat("en-RW", {
      style: "decimal",
      maximumFractionDigits: 0,
    }).format(val);
  };

  return (
    <div className="min-h-screen bg-[#181514] flex flex-col text-[#221C1A]">
      {/* ========================================================= */}
      {/* Boutique Top Gateway Bar */}
      {/* ========================================================= */}
      <header className="bg-[#1C1816] border-b border-white/10 px-4 sm:px-6 py-3.5 flex items-center justify-between text-white shrink-0 z-30">
        <div className="flex items-center gap-3">
          <Link
            href="/"
            className="flex items-center gap-2 group transition-opacity hover:opacity-85"
          >
            <div className="w-8 h-8 rounded-full bg-[#B8975A]/20 border border-[#B8975A]/40 flex items-center justify-center text-[#E8C99B] font-serif font-bold text-sm">
              I
            </div>
            <span className="font-serif text-sm tracking-wider uppercase text-white hidden sm:inline">
              Isaiah Nail Bar
            </span>
          </Link>
          <span className="text-white/20 hidden sm:inline">|</span>
          <div className="inline-flex items-center gap-1.5 text-xs text-[#E8C99B]">
            <LockSimple size={14} weight="bold" />
            <span className="font-mono text-[11px] tracking-wide">
              256-Bit SSL Sanctuary Gateway
            </span>
          </div>
        </div>

        <div className="flex items-center gap-4">
          <div className="text-right hidden sm:block">
            <span className="text-[10px] uppercase tracking-wider text-white/50 block">
              Reference
            </span>
            <span className="font-mono text-xs font-bold text-[#E8C99B]">
              {reference}
            </span>
          </div>

          <div className="text-right pl-3 sm:border-l border-white/10">
            <span className="text-[10px] uppercase tracking-wider text-white/50 block">
              Due Now
            </span>
            <span className="font-serif text-sm font-bold text-white">
              {formatRwf(amount)} RWF
            </span>
          </div>

          <button
            type="button"
            onClick={() => setShowCancelConfirm(true)}
            className="text-xs text-white/60 hover:text-white px-3 py-1.5 rounded-full border border-white/10 hover:border-white/30 transition-colors cursor-pointer"
          >
            Cancel
          </button>
        </div>
      </header>

      {/* ========================================================= */}
      {/* Main Payment Stage with Responsive Iframe */}
      {/* ========================================================= */}
      <main className="flex-1 relative flex flex-col bg-[#F5F2EB] overflow-hidden">
        {iframeUrl ? (
          <iframe
            ref={iframeRef}
            src={iframeUrl}
            title="Isaiah Nail Bar Secure Payment Terminal"
            allow="payment *"
            id="weflexIframe"
            className="w-full h-full flex-1 border-0"
            style={{
              minHeight: "calc(100vh - 60px)",
              display: paymentSubmitted ? "none" : "block",
            }}
          />
        ) : (
          <div className="flex-1 flex flex-col items-center justify-center gap-3 px-6 text-center text-xs text-[#8E857F]">
            <span>{statusMessage || "Preparing the secure WeFlexy terminal…"}</span>
            <button type="button" onClick={() => void verifyPaymentStatus()} className="rounded-full border border-[#B8975A]/40 px-4 py-2 font-semibold text-[#221C1A] hover:bg-white">
              Check payment status
            </button>
          </div>
        )}

        {/* ========================================================= */}
        {/* Processing Verification Overlay (Matches payment-iframe.blade.php) */}
        {/* ========================================================= */}
        <AnimatePresence>
          {paymentSubmitted && (
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              className="absolute inset-0 bg-[#1C1816]/95 backdrop-blur-md z-50 flex flex-col items-center justify-center p-6 text-center text-white"
            >
              <div className="max-w-md w-full bg-white/5 border border-white/10 rounded-3xl p-8 sm:p-10 shadow-2xl relative overflow-hidden">
                <div className="absolute -top-16 -right-16 w-44 h-44 rounded-full bg-[#B8975A]/20 blur-3xl pointer-events-none" />

                {/* Animated Gold Spinner */}
                <div className="relative w-20 h-20 mx-auto mb-6 flex items-center justify-center">
                  <div className="absolute inset-0 rounded-full border-4 border-white/10" />
                  <div className="absolute inset-0 rounded-full border-4 border-[#B8975A] border-t-transparent animate-spin" />
                  <LockSimple size={24} className="text-[#E8C99B]" />
                </div>

                <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-2">
                  Sanctuary Authorization &bull; Reference #{reference}
                </span>

                <h2 className="font-serif text-2xl sm:text-3xl text-white font-normal mb-3">
                  Processing Your Payment...
                </h2>

                <p className="text-xs sm:text-sm text-white/70 font-light leading-relaxed mb-6">
                  Please hold while we verify your reservation with the Rwandan banking network. Do not close or refresh this tab.
                </p>

                <div className="p-4 rounded-2xl bg-white/5 border border-white/10 text-xs font-mono text-[#E8C99B] flex items-center justify-center gap-2">
                  <ArrowClockwise size={16} className="animate-spin text-[#B8975A]" />
                  <span>{statusMessage}</span>
                </div>

                <div className="mt-6 pt-4 border-t border-white/10 flex items-center justify-center gap-2 text-[11px] text-white/50">
                  <ShieldCheck size={14} className="text-[#34A853]" />
                  <span>Encrypted Bank Handshake Active</span>
                </div>
              </div>
            </motion.div>
          )}
        </AnimatePresence>

        {/* ========================================================= */}
        {/* Cancel Confirmation Modal */}
        {/* ========================================================= */}
        <AnimatePresence>
          {showCancelConfirm && (
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              className="fixed inset-0 bg-black/75 backdrop-blur-xs z-50 flex items-center justify-center p-4"
            >
              <div className="bg-white rounded-3xl p-6 sm:p-8 max-w-sm w-full text-center border border-[#E5DFD5] shadow-2xl">
                <div className="w-12 h-12 rounded-full bg-[#FDEDEC] text-[#C0392B] flex items-center justify-center mx-auto mb-4">
                  <Warning size={24} weight="bold" />
                </div>
                <h3 className="font-serif text-xl text-[#221C1A] font-normal mb-2">
                  Cancel Payment?
                </h3>
                <p className="text-xs text-[#5C5550] leading-relaxed mb-6">
                  The appointment remains reserved until WeFlexy confirms success or failure. Do not start another payment while this one is pending.
                </p>
                <div className="flex gap-3">
                  <button
                    type="button"
                    onClick={() => setShowCancelConfirm(false)}
                    className="w-1/2 py-2.5 rounded-full text-xs font-semibold uppercase tracking-wider bg-[#FAF7F2] border border-[#E5DFD5] text-[#221C1A] hover:bg-white transition-colors cursor-pointer"
                  >
                    Keep Paying
                  </button>
                  <button
                    type="button"
                    onClick={() => {
                      setShowCancelConfirm(false);
                      router.push(
                        `/booking/failed?reference=${encodeURIComponent(
                          reference
                        )}&reason=cancelled`
                      );
                    }}
                    className="w-1/2 py-2.5 rounded-full text-xs font-semibold uppercase tracking-wider bg-[#C0392B] text-white hover:bg-[#A93226] transition-colors cursor-pointer"
                  >
                    Yes, Cancel
                  </button>
                </div>
              </div>
            </motion.div>
          )}
        </AnimatePresence>
      </main>
    </div>
  );
}

