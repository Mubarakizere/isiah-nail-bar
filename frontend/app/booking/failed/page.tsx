import { Metadata } from "next";
import { Suspense } from "react";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import BookingFailedClient from "@/components/booking/BookingFailedClient";
import BookingGuarantees from "@/components/booking/BookingGuarantees";

export const dynamic = "force-dynamic";
export const revalidate = 0;

export const metadata: Metadata = {
  title: "Payment Authorization Unsuccessful | Isaiah Nail Bar Kigali",
  description: "Retry your luxury nail appointment payment or contact our Kigali concierge for assistance.",
};

export default function BookingFailedPage() {
  return (
    <div className="min-h-screen flex flex-col bg-[#FAF7F2] text-[#221C1A]">
      <Header />

      <main className="flex-1">
        <Suspense
          fallback={
            <div className="py-24 text-center text-xs text-[#8E857F]">
              Loading payment status...
            </div>
          }
        >
          <BookingFailedClient />
        </Suspense>

        <BookingGuarantees />
      </main>

      <Footer />
    </div>
  );
}

