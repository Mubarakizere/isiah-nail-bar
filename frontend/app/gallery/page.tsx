import { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import InstagramPostGrid from "@/components/gallery/InstagramPostGrid";
import ServiceBookingCta from "@/components/services/ServiceBookingCta";
import { getInstagramPosts } from "@/lib/gallery-data";
import { InstagramLogo } from "@phosphor-icons/react/dist/ssr";

export const dynamic = "force-dynamic";
export const revalidate = 0;

export const metadata: Metadata = {
  title: "Instagram Gallery & Visual Stories | Isaiah Nail Bar Kigali",
  description:
    "Browse our official Instagram feed showcasing stunning manicure, pedicure, gel polish & acrylic nail designs at Isaiah Nail Bar in Kigali, Rwanda.",
  keywords: [
    "nail art gallery Kigali",
    "nail designs Rwanda",
    "manicure photos Kigali",
    "pedicure gallery Rwanda",
    "gel nails photos",
    "acrylic nails gallery Rwanda",
    "Isaiah Nail Bar gallery",
    "Isaiah Nail Bar Instagram",
  ],
  openGraph: {
    title: "Visual Stories — Instagram Feed | Isaiah Nail Bar Kigali",
    description:
      "A curation of our finest work, straight from our studio to your screen from @isaiah_nails_art_kigali_rwanda.",
    images: [{ url: "/banner.jpg", width: 1200, height: 630, alt: "Isaiah Nail Bar Instagram Gallery" }],
  },
};

export default async function GalleryPage() {
  const posts = await getInstagramPosts();

  // ImageGallery Schema matching original Blade
  const imageGallerySchema = {
    "@context": "https://schema.org",
    "@type": "ImageGallery",
    name: "Isaiah Nail Bar Gallery",
    description:
      "Stunning nail art, manicure and pedicure photos from Isaiah Nail Bar in Kigali, Rwanda",
    url: "https://isaiahnailbar.com/gallery",
    creator: {
      "@type": "NailSalon",
      name: "Isaiah Nail Bar",
      telephone: "+250790395169",
      address: {
        "@type": "PostalAddress",
        streetAddress: "KG 4 Roundabout, Gisementi",
        addressLocality: "Kigali",
        addressCountry: "RW",
      },
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
        name: "Gallery",
        item: "https://isaiahnailbar.com/gallery",
      },
    ],
  };

  return (
    <div className="min-h-screen flex flex-col bg-[#FAF7F2] text-[#221C1A]">
      {/* Structured Data Scripts */}
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(imageGallerySchema) }}
      />
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(breadcrumbSchema) }}
      />

      <Header />

      <main className="flex-1">
        {/* 1. Gallery Hero Banner (Full-Bleed with Black Opacity Overlay) */}
        <section className="relative min-h-[50vh] sm:min-h-[56vh] flex items-center justify-center overflow-hidden bg-[#181514]">
          {/* Background Image */}
          <div className="absolute inset-0 z-0">
            <Image
              src="/banner.jpg"
              alt="Gallery Banner"
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
              <span className="text-white font-medium">Gallery</span>
            </div>

            <span className="text-xs font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-3">
              Instagram Feed
            </span>

            <h1 className="font-serif text-4xl sm:text-6xl lg:text-7xl font-normal text-white leading-[1.08] tracking-tight mb-6 drop-shadow-sm">
              Visual Stories
            </h1>

            <p className="text-base sm:text-lg lg:text-xl text-white/85 font-light leading-relaxed max-w-2xl mx-auto mb-8">
              A curation of our finest work, straight from our studio to your screen.
            </p>

            <div>
              <a
                href="https://www.instagram.com/isaiah_nails_art_kigali_rwanda"
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2.5 text-white border border-white/30 px-7 py-3.5 rounded-full hover:bg-white hover:text-[#221C1A] transition-all duration-300 backdrop-blur-sm text-xs font-semibold uppercase tracking-wider shadow-lg"
              >
                <InstagramLogo size={18} weight="bold" />
                <span>@isaiah_nails_art_kigali_rwanda</span>
              </a>
            </div>
          </div>
        </section>

        {/* 2. Instagram Posts Grid (Crisp White Background) */}
        <InstagramPostGrid posts={posts} />

        {/* 3. Booking CTA Invitation (Warm Sandstone Linen) */}
        <ServiceBookingCta />
      </main>

      <Footer />
    </div>
  );
}
