import { Metadata } from "next";
import { Suspense } from "react";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import BookingSuccessClient from "@/components/booking/BookingSuccessClient";
import BookingGuarantees from "@/components/booking/BookingGuarantees";
import { getServicesData } from "@/lib/services-data";
import { getTeamData } from "@/lib/team-data";

export const dynamic = "force-dynamic";
export const revalidate = 0;

export const metadata: Metadata = {
  title: "Sanctuary Reservation Confirmed | Isaiah Nail Bar Kigali",
  description: "Your luxury nail appointment has been confirmed and guaranteed at Isaiah Nail Bar Kigali.",
};

export default async function BookingSuccessPage() {
  const { services } = await getServicesData();
  const { providers } = await getTeamData();

  return (
    <div className="min-h-screen flex flex-col bg-[#FAF7F2] text-[#221C1A]">
      <Header />

      <main className="flex-1">
        <Suspense
          fallback={
            <div className="py-24 text-center text-xs text-[#8E857F]">
              Loading your reservation pass...
            </div>
          }
        >
          <BookingSuccessClient providers={providers} allServices={services} />
        </Suspense>

        <BookingGuarantees />
      </main>

      <Footer />
    </div>
  );
}

