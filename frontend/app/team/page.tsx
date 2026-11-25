import { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import ProvidersGrid from "@/components/team/ProvidersGrid";
import SupportStaffSection from "@/components/team/SupportStaffSection";
import ServiceBookingCta from "@/components/services/ServiceBookingCta";
import { getTeamData } from "@/lib/team-data";
import { CalendarCheck } from "@phosphor-icons/react/dist/ssr";

export const dynamic = "force-dynamic";
export const revalidate = 0;

export const metadata: Metadata = {
  title: "Meet Our Team | Expert Nail Artists - Isaiah Nail Bar Kigali",
  description:
    "Meet the master nail technicians and specialists at Isaiah Nail Bar in Kigali, Rwanda. European-certified Russian manicure, BIAB, extension, and botanical pedicure artists.",
  keywords: [
    "nail artists Kigali",
    "best nail technicians Rwanda",
    "Russian manicure artist Kigali",
    "Isaiah Nail Bar team",
    "nail specialists Gisementi",
  ],
  openGraph: {
    title: "Meet Our Team | Isaiah Nail Bar Kigali",
    description:
      "Dedicated professionals committed to delivering perfection in every stroke at Kigali's boutique nail sanctuary.",
    images: [{ url: "/banner.jpg", width: 1200, height: 630, alt: "Isaiah Nail Bar Team" }],
  },
};

export default async function TeamPage() {
  const teamData = await getTeamData();
  const providers = teamData?.providers || [];
  const support = teamData?.support || [];

  // ItemList Schema for Team Providers
  const teamSchema = {
    "@context": "https://schema.org",
    "@type": "ItemList",
    name: "Isaiah Nail Bar Master Artists",
    description: "Certified senior nail technicians and specialists at Isaiah Nail Bar Kigali",
    numberOfItems: providers.length,
    itemListElement: providers.map((provider, index) => {
      const rawPhoto = provider.photo || "/images/logo.png";
      const photoUrl = rawPhoto.startsWith("http")
        ? rawPhoto
        : `https://isaiahnailbar.com${rawPhoto.startsWith("/") ? "" : "/"}${rawPhoto}`;

      return {
        "@type": "ListItem",
        position: index + 1,
        item: {
          "@type": "Person",
          name: provider.name,
          jobTitle: provider.role,
          description: provider.bio || "",
          image: photoUrl,
          worksFor: {
            "@type": "NailSalon",
            name: "Isaiah Nail Bar",
            address: {
              "@type": "PostalAddress",
              streetAddress: "KG 4 Roundabout, Gisementi",
              addressLocality: "Kigali",
              addressCountry: "RW",
            },
          },
        },
      };
    }),
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
        name: "Meet Our Team",
        item: "https://isaiahnailbar.com/team",
      },
    ],
  };

  return (
    <div className="min-h-screen flex flex-col bg-[#FAF7F2] text-[#221C1A]">
      {/* Structured Data Scripts */}
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(teamSchema) }}
      />
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(breadcrumbSchema) }}
      />

      <Header />

      <main className="flex-1">
        {/* 1. Team Hero Banner (Full-Bleed with Black Opacity Overlay) */}
        <section className="relative min-h-[50vh] sm:min-h-[56vh] flex items-center justify-center overflow-hidden bg-[#181514]">
          {/* Background Image */}
          <div className="absolute inset-0 z-0">
            <Image
              src="/banner.jpg"
              alt="Team Banner"
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
              <span className="text-white font-medium">The Artists</span>
            </div>

            <span className="text-xs font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-3">
              The Artists &bull; Kigali Atelier
            </span>

            <h1 className="font-serif text-4xl sm:text-6xl lg:text-7xl font-normal text-white leading-[1.08] tracking-tight mb-6 drop-shadow-sm">
              Meet the Team
            </h1>

            <p className="text-base sm:text-lg lg:text-xl text-white/85 font-light leading-relaxed max-w-2xl mx-auto mb-8">
              Dedicated professionals committed to delivering perfection in every stroke. Certified in medical hygiene and precision Russian nail craft.
            </p>

            <div>
              <Link
                href="/booking"
                className="inline-flex items-center gap-2.5 px-7 py-3.5 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white hover:bg-[#FAF7F2] hover:text-[#B8975A] transition-all duration-300 shadow-xl hover:-translate-y-0.5"
              >
                <CalendarCheck size={16} weight="bold" />
                <span>Reserve Appointment Online</span>
              </Link>
            </div>
          </div>
        </section>

        {/* 2. Senior Artists Grid (Crisp Pure White) */}
        <ProvidersGrid providers={providers} />

        {/* 3. Support Staff Section (Warm Cashmere Sand) */}
        <SupportStaffSection members={support} />

        {/* 4. Booking & Concierge Invitation (Warm Sandstone Linen) */}
        <ServiceBookingCta />
      </main>

      <Footer />
    </div>
  );
}
