import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import HeroSection from "@/components/home/HeroSection";
import FeaturedServices from "@/components/home/FeaturedServices";
import SanctuaryExperience from "@/components/home/SanctuaryExperience";
import GoogleReviewsSection from "@/components/home/GoogleReviewsSection";
import VisitUsSection from "@/components/home/VisitUsSection";

async function getHomeData() {
  const apiUrl = process.env.LARAVEL_API_URL || "http://127.0.0.1:8000";
  try {
    const res = await fetch(`${apiUrl}/api/v1/home`, {
      cache: "no-store",
      // Fallback quickly if backend is offline
      signal: AbortSignal.timeout(3000),
    });
    if (!res.ok) return null;
    const json = await res.json();
    return json?.data || null;
  } catch {
    return null;
  }
}

export default async function HomePage() {
  const homeData = await getHomeData();

  const heroSlides = homeData?.hero_slides;
  const featuredServices = homeData?.featured_services;
  const reviews = homeData?.reviews;
  const reviewCount = homeData?.review_count ?? 120;
  const averageRating = homeData?.average_rating ?? 4.9;

  return (
    <div className="min-h-screen flex flex-col bg-[#FAF7F2] text-[#221C1A]">
      <Header />

      <main className="flex-1">
        {/* 1. Hero Carousel */}
        <HeroSection slides={heroSlides} />

        {/* 2. Curated Treatments */}
        <FeaturedServices services={featuredServices} />

        {/* 3. Sanctuary Experience ("Why Choose Us") */}
        <SanctuaryExperience />

        {/* 4. Verified Google Reviews */}
        <GoogleReviewsSection
          reviews={reviews}
          averageRating={averageRating}
          reviewCount={reviewCount}
        />

        {/* 5. Visit Us & Interactive Location Map */}
        <VisitUsSection />
      </main>

      <Footer />
    </div>
  );
}
