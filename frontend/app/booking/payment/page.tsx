import { Metadata } from "next";
import { Suspense } from "react";
import BookingPaymentClient from "@/components/booking/BookingPaymentClient";

export const dynamic = "force-dynamic";
export const revalidate = 0;

export const metadata: Metadata = {
  title: "Secure Payment Terminal | Isaiah Nail Bar Kigali",
  description: "Complete your luxury nail appointment payment with MTN/Airtel MoMo or Card.",
};

export default function BookingPaymentPage() {
  return (
    <Suspense
      fallback={
        <div className="min-h-screen bg-[#181514] flex items-center justify-center text-xs text-white/60">
          Loading payment terminal...
        </div>
      }
    >
      <BookingPaymentClient />
    </Suspense>
  );
}

