import { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import ServicesClient from "@/components/services/ServicesClient";
import IsaiahStandardSection from "@/components/services/IsaiahStandardSection";
import ServiceFaqSection from "@/components/services/ServiceFaqSection";
import ServiceBookingCta from "@/components/services/ServiceBookingCta";
import ShareMenuButton from "@/components/services/ShareMenuButton";
import { getServicesData } from "@/lib/services-data";

export const dynamic = "force-dynamic";
export const revalidate = 0;

export const metadata: Metadata = {
  title: "Nail Services Menu & Prices Kigali | Isaiah Nail Bar",
  description:
    "View our complete nail service menu with prices in RWF. Russian manicures from 20,000 RWF, sculpted hard gel extensions, luxury pedicures, and nail art at Kigali's premier boutique atelier.",
  keywords: [
    "nail services Kigali",
    "manicure prices Rwanda",
    "pedicure prices Kigali",
    "gel polish price Rwanda",
    "Russian manicure Kigali",
    "acrylic nails cost Kigali",
    "BIAB nails Rwanda",
    "Isaiah Nail Bar service menu",
  ],
  openGraph: {
    title: "Nail Services Menu & Prices | Isaiah Nail Bar Kigali",
    description:
      "Precision Russian manicures, sculpted extensions, and botanical pedicures in Gisementi, Kigali. Transparent prices in RWF.",
    images: [{ url: "/banner.jpg", width: 1200, height: 630, alt: "Isaiah Nail Bar Services" }],
  },
};

export default async function ServicesPage() {
  const { services, categories } = await getServicesData();

  // ItemList Schema for SEO rich results
  const itemListSchema = {
    "@context": "https://schema.org",
    "@type": "ItemList",
    name: "Isaiah Nail Bar Service Menu",
    description: "Complete nail service menu with prices at Isaiah Nail Bar, Kigali Rwanda",
    numberOfItems: services.length,
    itemListElement: services.map((service, index) => ({
      "@type": "ListItem",
      position: index + 1,
      item: {
        "@type": "Service",
        name: service.name,
        description: service.description,
        provider: {
          "@type": "NailSalon",
          name: "Isaiah Nail Bar",
          address: {
            "@type": "PostalAddress",
            streetAddress: "KG 4 Roundabout, Gisementi",
            addressLocality: "Kigali",
            addressCountry: "RW",
          },
        },
        areaServed: {
          "@type": "City",
          name: "Kigali",
        },
        offers: {
          "@type": "Offer",
          price: service.price,
          priceCurrency: "RWF",
          availability: "https://schema.org/InStock",
        },
      },
    })),
  };

  // Breadcrumb Schema
  const breadcrumbSchema = {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    itemListElement: [
      {
        "@type": "ListItem",
        position: 1,
        name: "Home",
        item: "https://isaiahnailbar.com",
      },
      {
        "@type": "ListItem",
        position: 2,
        name: "Services",
        item: "https://isaiahnailbar.com/services",
      },
    ],
  };

  return (
    <div className="min-h-screen flex flex-col bg-[#FAF7F2] text-[#221C1A]">
      {/* Structured Data Scripts */}
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(itemListSchema) }}
      />
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(breadcrumbSchema) }}
      />

      <Header />

      <main className="flex-1">
        {/* 1. Services Hero Banner (Full-Bleed with Black Opacity Overlay) */}
        <section className="relative min-h-[50vh] sm:min-h-[56vh] flex items-center justify-center overflow-hidden bg-[#181514]">
          {/* Background Image */}
          <div className="absolute inset-0 z-0">
            <Image
              src="/banner.jpg"
              alt="Isaiah Nail Bar Service Menu Banner"
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
          <div className="relative z-20 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
            {/* Breadcrumb path */}
            <div className="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/80 text-xs mb-6">
              <Link href="/" className="hover:text-white transition-colors">
                Home
              </Link>
              <span>/</span>
              <span className="text-white font-medium">Services Menu</span>
            </div>

            <h1 className="font-serif text-4xl sm:text-6xl lg:text-7xl font-normal text-white leading-[1.08] tracking-tight mb-6 drop-shadow-sm">
              The Service Menu
            </h1>

            <p className="text-base sm:text-lg lg:text-xl text-white/85 font-light leading-relaxed max-w-2xl mx-auto mb-8">
              Explore our comprehensive rituals of precision Russian manicures, bespoke extensions, and restorative botanical pedicures in Kigali.
            </p>

            <div className="flex flex-wrap items-center justify-center gap-3">
              <ShareMenuButton />
            </div>
          </div>
        </section>

        {/* 2. Interactive Catalog: Live Filtering, Search, Grid, and Modals (Crisp White Background) */}
        <ServicesClient initialServices={services} categories={categories} />

        {/* 3. Alternating Section: The Isaiah Standard (Warm Cashmere Sand) */}
        <IsaiahStandardSection />

        {/* 4. Alternating Section: Service FAQs (Crisp White) */}
        <ServiceFaqSection />

        {/* 5. Alternating Section: Booking Invitation CTA (Warm Sandstone Linen) */}
        <ServiceBookingCta />
      </main>

      <Footer />
    </div>
  );
}
