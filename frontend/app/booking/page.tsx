import { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { Suspense } from "react";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import BookingStepper from "@/components/booking/BookingStepper";
import BookingStep1Client from "@/components/booking/BookingStep1Client";
import BookingGuarantees from "@/components/booking/BookingGuarantees";
import { getServicesData } from "@/lib/services-data";

export const metadata: Metadata = {
  title: "Book Nail Services & Treatments Kigali | Isaiah Nail Bar",
  description:
    "Curate your bespoke nail appointment online. Russian manicures, gel sculpted extensions, botanical pedicures, and custom nail art at Kigali's luxury nail sanctuary in Gisementi.",
  keywords: [
    "book nail appointment Kigali",
    "reserve Russian manicure Rwanda",
    "nail salon booking Gisementi",
    "luxury pedicure booking Kigali",
    "Isaiah Nail Bar booking",
  ],
  openGraph: {
    title: "Curate Your Sanctuary Ritual | Isaiah Nail Bar Booking",
    description:
      "Select your treatments, choose your artist, and reserve your luxury nail experience in Kigali with instant confirmation.",
    images: [{ url: "/banner.jpg", width: 1200, height: 630, alt: "Book Isaiah Nail Bar" }],
  },
};

export default async function BookingPage() {
  const { services, categories } = await getServicesData();

  // Reservation Schema
  const reservationSchema = {
    "@context": "https://schema.org",
    "@type": "WebPage",
    name: "Book an Appointment | Isaiah Nail Bar",
    description: "Online booking system for Isaiah Nail Bar luxury services in Kigali, Rwanda.",
    url: "https://isaiahnailbar.com/booking",
  };

  return (
    <div className="min-h-screen flex flex-col bg-[#FAF7F2] text-[#221C1A]">
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(reservationSchema) }}
      />

      <Header />

      <main className="flex-1">
        {/* Hero Banner (Full-Bleed with Black Opacity Overlay) */}
        <section className="relative min-h-[38vh] sm:min-h-[44vh] flex items-center justify-center overflow-hidden bg-[#181514]">
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
              <span className="text-white font-medium">Bespoke Booking</span>
              <span>/</span>
              <span className="text-[#E8C99B] font-medium">Step 1: Curate Treatments</span>
            </div>

            <span className="text-xs font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-2">
              Step 1 of 5 &bull; Personalize Your Ritual
            </span>

            <h1 className="font-serif text-3xl sm:text-5xl lg:text-6xl font-normal text-white leading-tight tracking-tight mb-4 drop-shadow-sm">
              Curate Your Experience
            </h1>

            <p className="text-sm sm:text-base lg:text-lg text-white/85 font-light leading-relaxed max-w-2xl mx-auto">
              Select one or more bespoke treatments to begin. You can combine manicures, pedicures, and signature nail art into a single appointment.
            </p>
          </div>
        </section>

        {/* 5-Step Progress Stepper */}
        <BookingStepper currentStep={1} />

        {/* Step 1 Interactive Treatments Selection with Suspense boundary for useSearchParams */}
        <Suspense
          fallback={
            <div className="py-24 text-center text-[#8E857F] text-sm">
              Loading sanctuary treatments...
            </div>
          }
        >
          <BookingStep1Client services={services} categories={categories} />
        </Suspense>

        {/* Sanctuary Hygiene & Service Guarantee (Alternating Warm Cashmere Sand) */}
        <BookingGuarantees />
      </main>

      <Footer />
    </div>
  );
}
