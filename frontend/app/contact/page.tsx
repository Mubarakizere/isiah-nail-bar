import { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import ContactFormSection from "@/components/contact/ContactFormSection";
import ContactFaqSection from "@/components/contact/ContactFaqSection";
import ServiceBookingCta from "@/components/services/ServiceBookingCta";
import { PhoneCall, ChatCircleDots } from "@phosphor-icons/react/dist/ssr";

export const dynamic = "force-dynamic";
export const revalidate = 0;

export const metadata: Metadata = {
  title: "Contact Isaiah Nail Bar | Book Appointment Kigali Rwanda",
  description:
    "Contact Isaiah Nail Bar in Kigali, Rwanda. Call +250 790 395 169, WhatsApp us, or visit our salon at KG 4 Roundabout, Gisementi. Open Mon-Sat 8AM-8PM. Book your nail appointment today!",
  keywords: [
    "contact Isaiah Nail Bar",
    "nail salon phone Kigali",
    "nail salon location Gisementi",
    "nail salon WhatsApp Rwanda",
    "book nail appointment Kigali",
    "nail salon address Rwanda",
    "nail salon opening hours Kigali",
  ],
  openGraph: {
    title: "Contact Isaiah Nail Bar | Kigali Rwanda",
    description:
      "Reach out to Kigali's premier luxury nail sanctuary. Dedicated concierge, phone, WhatsApp, and private consultations at Gisementi.",
    images: [{ url: "/banner.jpg", width: 1200, height: 630, alt: "Contact Isaiah Nail Bar" }],
  },
};

export default function ContactPage() {
  // ContactPage Schema matching original Blade
  const contactPageSchema = {
    "@context": "https://schema.org",
    "@type": "ContactPage",
    name: "Contact Isaiah Nail Bar",
    description:
      "Contact Isaiah Nail Bar in Kigali, Rwanda for appointments, inquiries, and bookings.",
    url: "https://isaiahnailbar.com/contact",
    mainEntity: {
      "@type": "NailSalon",
      "@id": "https://isaiahnailbar.com/#business",
      contactPoint: [
        {
          "@type": "ContactPoint",
          telephone: "+250790395169",
          contactType: "customer service",
          availableLanguage: ["English", "French", "Kinyarwanda"],
          areaServed: "RW",
        },
      ],
    },
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
        name: "Contact & Directions",
        item: "https://isaiahnailbar.com/contact",
      },
    ],
  };

  return (
    <div className="min-h-screen flex flex-col bg-[#FAF7F2] text-[#221C1A]">
      {/* Structured Data Scripts */}
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(contactPageSchema) }}
      />
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(breadcrumbSchema) }}
      />

      <Header />

      <main className="flex-1">
        {/* Contact Hero Banner (Full-Bleed with Black Opacity Overlay) */}
        <section className="relative min-h-[46vh] sm:min-h-[52vh] flex items-center justify-center overflow-hidden bg-[#181514]">
          {/* Background Image */}
          <div className="absolute inset-0 z-0">
            <Image
              src="/banner.jpg"
              alt="Isaiah Nail Bar Kigali Contact Banner"
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
              <span className="text-white font-medium">Contact &amp; Directions</span>
            </div>

            <span className="text-xs font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-3">
              Get in Touch
            </span>

            <h1 className="font-serif text-4xl sm:text-6xl lg:text-7xl font-normal text-white leading-[1.08] tracking-tight mb-6 drop-shadow-sm">
              Let&apos;s Connect
            </h1>

            <p className="text-base sm:text-lg lg:text-xl text-white/85 font-light leading-relaxed max-w-2xl mx-auto mb-8">
              Have a question about our bespoke nail rituals or planning a private group booking? We are here to guide you.
            </p>

            <div className="flex flex-wrap items-center justify-center gap-3">
              <a
                href="tel:+250790395169"
                className="inline-flex items-center gap-2 px-6 py-3 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A]/90 hover:bg-[#B8975A] border border-white/20 transition-all duration-300 shadow-md hover:-translate-y-0.5"
              >
                <PhoneCall size={16} weight="fill" />
                <span>Call +250 790 395 169</span>
              </a>
              <a
                href="https://wa.me/250790395169?text=Hello%20Isaiah%20Nail%20Bar,%20I%20would%20like%20to%20inquire%20about%20booking%20an%20appointment."
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2 px-6 py-3 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#25D366]/80 hover:bg-[#25D366] transition-all duration-300 shadow-md hover:-translate-y-0.5"
              >
                <ChatCircleDots size={16} weight="fill" />
                <span>Instant WhatsApp</span>
              </a>
            </div>
          </div>
        </section>

        {/* 1. Interactive Contact Form, 4 Quick Cards & Interactive Map (Crisp Pure White) */}
        <ContactFormSection />

        {/* 2. Boutique Arrival Guidance & Etiquette (Warm Cashmere Sand) */}
        <ContactFaqSection />

        {/* 3. Booking Call to Action (Warm Sandstone Linen) */}
        <ServiceBookingCta />
      </main>

      <Footer />
    </div>
  );
}
