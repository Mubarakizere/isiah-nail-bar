import { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import AboutStorySection from "@/components/about/AboutStorySection";
import AboutPhilosophySection from "@/components/about/AboutPhilosophySection";
import AboutAtelierSection from "@/components/about/AboutAtelierSection";
import ServiceBookingCta from "@/components/services/ServiceBookingCta";
import { CalendarCheck, PhoneCall } from "@phosphor-icons/react/dist/ssr";

export const dynamic = "force-dynamic";
export const revalidate = 0;

export const metadata: Metadata = {
  title: "About Isaiah Nail Bar | Kigali's Premier Luxury Nail Salon",
  description:
    "Learn about Isaiah Nail Bar, Kigali's premier luxury nail sanctuary. 3+ years of excellence, 500+ regular guests, and 10,000+ bespoke rituals. European precision, non-toxic products, and hospital-grade hygiene in Gisementi, Rwanda.",
  keywords: [
    "about Isaiah Nail Bar",
    "nail salon Kigali story",
    "luxury nail salon Rwanda",
    "Gisementi nail salon",
    "best nail technician Kigali",
    "Russian manicure experts Rwanda",
    "nail salon hygiene standards Kigali",
  ],
  openGraph: {
    title: "About Isaiah Nail Bar | Kigali's Premier Luxury Nail Salon",
    description:
      "A sanctuary of precision craftsmanship, European e-file artistry, and hospital-grade hygiene in Gisementi, Kigali.",
    images: [{ url: "/banner.jpg", width: 1200, height: 630, alt: "About Isaiah Nail Bar" }],
  },
};

export default function AboutPage() {
  // Organization Schema matching original Blade
  const organizationSchema = {
    "@context": "https://schema.org",
    "@type": "Organization",
    "@id": "https://isaiahnailbar.com/#organization",
    name: "Isaiah Nail Bar",
    url: "https://isaiahnailbar.com",
    logo: "https://isaiahnailbar.com/logo.png",
    description:
      "Kigali's premier luxury nail salon offering world-class manicure, pedicure, and nail art services since 2022.",
    foundingDate: "2022",
    foundingLocation: {
      "@type": "Place",
      name: "Kigali, Rwanda",
    },
    address: {
      "@type": "PostalAddress",
      streetAddress: "KG 4 Roundabout, Gisementi",
      addressLocality: "Kigali",
      addressCountry: "RW",
    },
    telephone: "+250790395169",
    sameAs: [
      "https://www.instagram.com/isaiah_nails_art_kigali_rwanda",
      "https://www.facebook.com/isaiahnailbar",
    ],
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
        name: "About Us",
        item: "https://isaiahnailbar.com/about",
      },
    ],
  };

  return (
    <div className="min-h-screen flex flex-col bg-[#FAF7F2] text-[#221C1A]">
      {/* Structured Data Scripts */}
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(organizationSchema) }}
      />
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(breadcrumbSchema) }}
      />

      <Header />

      <main className="flex-1">
        {/* 1. About Hero Banner (Full-Bleed with Black Opacity Overlay) */}
        <section className="relative min-h-[50vh] sm:min-h-[58vh] flex items-center justify-center overflow-hidden bg-[#181514]">
          {/* Background Image */}
          <div className="absolute inset-0 z-0">
            <Image
              src="/banner.jpg"
              alt="About Isaiah Nail Bar Banner"
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
              <span className="text-white font-medium">About Our Atelier</span>
            </div>

            <span className="text-xs font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-3">
              Kigali&apos;s Premier Destination
            </span>

            <h1 className="font-serif text-4xl sm:text-6xl lg:text-7xl font-normal text-white leading-[1.08] tracking-tight mb-6 drop-shadow-sm">
              The Art of <br />
              <span className="text-[#E8C99B]">Nail Perfection</span>
            </h1>

            <p className="text-base sm:text-lg lg:text-xl text-white/85 font-light leading-relaxed max-w-2xl mx-auto mb-8">
              Founded on the belief that nail care should be an unhurried ritual of wellness, master craftsmanship, and uncompromising hygiene.
            </p>

            <div className="flex flex-wrap items-center justify-center gap-3">
              <Link
                href="/booking"
                className="inline-flex items-center gap-2.5 px-7 py-3.5 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white hover:bg-[#FAF7F2] hover:text-[#B8975A] transition-all duration-300 shadow-xl hover:-translate-y-0.5"
              >
                <CalendarCheck size={16} weight="bold" />
                <span>Reserve Appointment</span>
              </Link>

              <Link
                href="/services"
                className="inline-flex items-center gap-2 px-6 py-3.5 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 transition-all duration-300"
              >
                <span>View Service Menu</span>
              </Link>
            </div>
          </div>
        </section>

        {/* 2. Story Section with Real Metrics & Photos (Crisp Pure White) */}
        <AboutStorySection />

        {/* 3. Core Philosophy: Health First, Precision, Comfort (Warm Cashmere Sand) */}
        <AboutPhilosophySection />

        {/* 4. Atelier Standards & Commitments (Crisp Pure White) */}
        <AboutAtelierSection />

        {/* 5. Booking & Concierge Invitation (Warm Sandstone Linen) */}
        <ServiceBookingCta />
      </main>

      <Footer />
    </div>
  );
}
