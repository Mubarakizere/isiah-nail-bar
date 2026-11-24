import { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { Suspense } from "react";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import BookingStepper from "@/components/booking/BookingStepper";
import BookingStep2Client from "@/components/booking/BookingStep2Client";
import BookingGuarantees from "@/components/booking/BookingGuarantees";
import { getServicesData } from "@/lib/services-data";
import { getTeamData } from "@/lib/team-data";

export const metadata: Metadata = {
  title: "Select Artist | Isaiah Nail Bar Kigali Booking",
  description:
    "Choose your specialist nail technician for your curated treatments at Isaiah Nail Bar in Gisementi, Kigali.",
};

export default async function BookingStep2Page() {
  const serviceData = await getServicesData();
  const teamData = await getTeamData();
  const services = serviceData?.services || [];
  const providers = teamData?.providers || [];

  return (
    <div className="min-h-screen flex flex-col bg-[#FAF7F2] text-[#221C1A]">
      <Header />

      <main className="flex-1">
        {/* Hero Banner */}
        <section className="relative min-h-[36vh] sm:min-h-[42vh] flex items-center justify-center overflow-hidden bg-[#181514]">
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

          <div className="absolute inset-0 bg-black/60 z-10" />
          <div className="absolute inset-0 bg-gradient-to-t from-black/85 via-black/55 to-black/35 z-10" />

          <div className="relative z-20 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
            <div className="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/80 text-xs mb-5">
              <Link href="/" className="hover:text-white transition-colors">
                Home
              </Link>
              <span>/</span>
              <Link href="/booking" className="hover:text-white transition-colors">
                Booking
              </Link>
              <span>/</span>
              <span className="text-[#E8C99B] font-medium">Step 2: Select Artist</span>
            </div>

            <span className="text-xs font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-2">
              Step 2 of 5 &bull; Your Dedicated Artisan
            </span>

            <h1 className="font-serif text-3xl sm:text-5xl lg:text-6xl font-normal text-white leading-tight tracking-tight mb-4 drop-shadow-sm">
              Choose Your Specialist
            </h1>

            <p className="text-sm sm:text-base lg:text-lg text-white/85 font-light leading-relaxed max-w-2xl mx-auto">
              Select one of our European-certified senior technicians or choose the first available specialist for optimal scheduling.
            </p>
          </div>
        </section>

        {/* Stepper at Step 2 */}
        <BookingStepper currentStep={2} />

        {/* Step 2 Client */}
        <Suspense
          fallback={
            <div className="py-24 text-center text-[#8E857F] text-sm">
              Loading specialists...
            </div>
          }
        >
          <BookingStep2Client providers={providers} allServices={services} />
        </Suspense>

        <BookingGuarantees />
      </main>

      <Footer />
    </div>
  );
}
