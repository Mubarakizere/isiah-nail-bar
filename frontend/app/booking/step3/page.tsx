import { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { Suspense } from "react";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import BookingStepper from "@/components/booking/BookingStepper";
import BookingStep3Client from "@/components/booking/BookingStep3Client";
import BookingGuarantees from "@/components/booking/BookingGuarantees";
import { getServicesData } from "@/lib/services-data";
import { getTeamData } from "@/lib/team-data";

export const dynamic = "force-dynamic";
export const revalidate = 0;

export const metadata: Metadata = {
  title: "Select Date & Time | Isaiah Nail Bar Kigali Booking",
  description:
    "Schedule your luxury nail appointment in Kigali. Choose your preferred day, time slot, in-salon or mobile home service, and optional studio chauffeur transport.",
};

export default async function BookingStep3Page() {
  const { services } = await getServicesData();
  const { providers } = await getTeamData();

  return (
    <div className="min-h-screen flex flex-col bg-[#FAF7F2] text-[#221C1A]">
      <Header />

      <main className="flex-1">
        {/* Hero Banner (Full-Bleed with Black Opacity Overlay) */}
        <section className="relative min-h-[36vh] sm:min-h-[42vh] flex items-center justify-center overflow-hidden bg-[#181514]">
          {/* Background Image */}
          <div className="absolute inset-0 z-0">
            <Image
              src="/banner.jpg"
              alt="Isaiah Nail Bar Kigali Booking Banner"
              fill
              priority
              sizes="100vw"
              className="object-cover object-center"
            />
          </div>

          {/* Black Opacity Overlay - Eliminates White Haze */}
          <div className="absolute inset-0 bg-black/60 z-10" />
          <div className="absolute inset-0 bg-gradient-to-t from-black/85 via-black/55 to-black/35 z-10" />

          {/* Hero Content */}
          <div className="relative z-20 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
            {/* Breadcrumb path */}
            <div className="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/80 text-xs mb-5">
              <Link href="/" className="hover:text-white transition-colors">
                Home
              </Link>
              <span>/</span>
              <Link href="/booking" className="hover:text-white transition-colors">
                Booking
              </Link>
              <span>/</span>
              <span className="text-[#E8C99B] font-medium">Step 3: Schedule Date &amp; Time</span>
            </div>

            <span className="text-xs font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-2">
              Step 3 of 5 &bull; Sanctuary Scheduling
            </span>

            <h1 className="font-serif text-3xl sm:text-5xl lg:text-6xl font-normal text-white leading-tight tracking-tight mb-4 drop-shadow-sm">
              Schedule Your Visit
            </h1>

            <p className="text-sm sm:text-base lg:text-lg text-white/85 font-light leading-relaxed max-w-2xl mx-auto">
              Select your preferred day, time slot, and location preference. Enjoy in-studio pampering at Gisementi or luxury mobile concierge service.
            </p>
          </div>
        </section>

        {/* Stepper at Step 3 */}
        <BookingStepper currentStep={3} />

        {/* Step 3 Client Content */}
        <Suspense
          fallback={
            <div className="py-24 text-center text-[#8E857F] text-sm">
              Loading calendar and time slots...
            </div>
          }
        >
          <BookingStep3Client providers={providers} allServices={services} />
        </Suspense>

        {/* Guarantees Section */}
        <BookingGuarantees />
      </main>

      <Footer />
    </div>
  );
}
