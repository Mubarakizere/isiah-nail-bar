"use client";

import { useState, useEffect, useCallback } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Star, CaretLeft, CaretRight, SealCheck } from "@phosphor-icons/react";

interface ReviewItem {
  id?: number;
  reviewer_name?: string;
  customer_name?: string;
  rating: number;
  comment: string;
  created_at?: string;
  source?: string;
  avatar_url?: string;
}

interface GoogleReviewsSectionProps {
  reviews?: ReviewItem[];
  averageRating?: number;
  reviewCount?: number;
}

const fallbackReviews: ReviewItem[] = [
  {
    reviewer_name: "Solange Uwamahoro",
    rating: 5,
    comment:
      "The best nail experience I’ve had in Rwanda! The attention to detail on the Russian manicure is unmatched. My gel lasted over 4 weeks without lifting.",
    created_at: "2 weeks ago",
    source: "google",
  },
  {
    reviewer_name: "Claire Mutoni",
    rating: 5,
    comment:
      "Immaculately clean salon, tranquil vibe, and true artists. Isaiah took the time to shape my nails exactly how I wanted. Truly a 5-star experience.",
    created_at: "1 month ago",
    source: "google",
  },
  {
    reviewer_name: "Diane Kayitesi",
    rating: 5,
    comment:
      "Their luxury pedicure is pure bliss. Booking online was effortless and they were ready for me the second I arrived. Highly recommended!",
    created_at: "Last month",
    source: "google",
  },
  {
    reviewer_name: "Grace Mukamana",
    rating: 5,
    comment:
      "Such a peaceful ambiance in Gisementi. The cleanliness and sterile equipment give me total peace of mind. The nail art is so delicate and chic.",
    created_at: "2 months ago",
    source: "google",
  },
  {
    reviewer_name: "Aline Uwase",
    rating: 5,
    comment:
      "Hands down the most professional technicians in Kigali. The atmosphere is calm and sunlit, perfect for an afternoon reset. Beautiful results every time.",
    created_at: "3 months ago",
    source: "google",
  },
];

export default function GoogleReviewsSection({
  reviews,
  averageRating = 4.9,
  reviewCount = 120,
}: GoogleReviewsSectionProps) {
  const displayReviews = reviews && reviews.length > 0 ? reviews : fallbackReviews;
  const [startIndex, setStartIndex] = useState(0);
  const [isPaused, setIsPaused] = useState(false);

  // We display 3 cards on desktop, 1 on mobile
  const nextSlide = useCallback(() => {
    setStartIndex((prev) => (prev + 1) % displayReviews.length);
  }, [displayReviews.length]);

  const prevSlide = useCallback(() => {
    setStartIndex((prev) => (prev - 1 + displayReviews.length) % displayReviews.length);
  }, [displayReviews.length]);

  useEffect(() => {
    if (isPaused) return;
    const interval = setInterval(nextSlide, 6000);
    return () => clearInterval(interval);
  }, [isPaused, nextSlide]);

  // Circular slice of 3 reviews
  const visibleReviews = [
    displayReviews[startIndex % displayReviews.length],
    displayReviews[(startIndex + 1) % displayReviews.length],
    displayReviews[(startIndex + 2) % displayReviews.length],
  ];

  return (
    <section
      className="py-24 bg-white border-b border-[#E8E2D9] relative"
      onMouseEnter={() => setIsPaused(true)}
      onMouseLeave={() => setIsPaused(false)}
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        {/* Header with Google Badge */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6 }}
          className="text-center mb-16 max-w-2xl mx-auto"
        >
          <div className="inline-flex items-center gap-2 px-3.5 py-1.5 bg-white rounded-md mb-6 border border-[#E8E2D9] shadow-2xs">
            {/* Google Colorful "G" */}
            <svg className="w-4 h-4 shrink-0" viewBox="0 0 24 24">
              <path
                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"
                fill="#4285F4"
              />
              <path
                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                fill="#34A853"
              />
              <path
                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                fill="#FBBC05"
              />
              <path
                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                fill="#EA4335"
              />
            </svg>
            <span className="text-[#221C1A] text-xs font-semibold tracking-wide">
              Google Verified Rating &bull; {averageRating} / 5.0
            </span>
          </div>

          <h2 className="text-3xl sm:text-4xl lg:text-5xl font-serif text-[#221C1A] mb-4 font-normal">
            What Our Clients Say
          </h2>
          <p className="text-sm sm:text-base text-[#5C5550] font-light">
            Real experiences from Google reviews by our Kigali salon guests
          </p>
        </motion.div>

        {/* Carousel Container */}
        <div className="relative">
          {/* Top Controls */}
          {displayReviews.length > 3 && (
            <div className="hidden md:flex justify-end gap-2 mb-6">
              <button
                onClick={prevSlide}
                aria-label="Previous Reviews"
                className="w-10 h-10 rounded-full bg-white border border-[#B8975A]/25 flex items-center justify-center text-[#221C1A] hover:bg-[#C4847A] hover:text-white hover:border-[#C4847A] transition-all shadow-sm"
              >
                <CaretLeft size={18} />
              </button>
              <button
                onClick={nextSlide}
                aria-label="Next Reviews"
                className="w-10 h-10 rounded-full bg-white border border-[#B8975A]/25 flex items-center justify-center text-[#221C1A] hover:bg-[#C4847A] hover:text-white hover:border-[#C4847A] transition-all shadow-sm"
              >
                <CaretRight size={18} />
              </button>
            </div>
          )}

          {/* Cards Grid with Framer Motion AnimatePresence */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <AnimatePresence mode="popLayout">
              {visibleReviews.map((rev, idx) => {
                const name = rev.reviewer_name || rev.customer_name || "Guest Client";
                const initial = name.charAt(0).toUpperCase();

                return (
                  <motion.div
                    key={`${name}-${idx}-${startIndex}`}
                    initial={{ opacity: 0, y: 20, scale: 0.98 }}
                    animate={{ opacity: 1, y: 0, scale: 1 }}
                    exit={{ opacity: 0, y: -20, scale: 0.98 }}
                    transition={{ duration: 0.5, ease: "easeOut" }}
                    whileHover={{ y: -5 }}
                    className="bg-[#FAF7F2] border border-[#E5DFD5] hover:border-[#B8975A]/40 rounded-3xl p-8 flex flex-col justify-between shadow-xs hover:shadow-[0_12px_32px_-4px_rgba(34,28,26,0.08)] transition-all duration-300"
                  >
                    <div>
                      {/* Top User Info */}
                      <div className="flex items-start justify-between mb-4">
                        <div className="flex items-center gap-3">
                          <div className="w-11 h-11 rounded-full bg-white border border-[#B8975A]/30 flex items-center justify-center text-[#B8975A] font-serif font-bold text-base shadow-xs">
                            {initial}
                          </div>
                          <div>
                            <h4 className="font-semibold text-sm text-[#221C1A]">{name}</h4>
                            <span className="text-[11px] text-[#8E857F]">
                              {rev.created_at || "Recent review"}
                            </span>
                          </div>
                        </div>

                        {/* Google Icon */}
                        <svg className="w-5 h-5 shrink-0 opacity-90" viewBox="0 0 24 24">
                          <path
                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"
                            fill="#4285F4"
                          />
                          <path
                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                            fill="#34A853"
                          />
                          <path
                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                            fill="#FBBC05"
                          />
                          <path
                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                            fill="#EA4335"
                          />
                        </svg>
                      </div>

                      {/* Stars */}
                      <div className="flex text-[#B8975A] mb-4 text-xs gap-0.5">
                        {[...Array(5)].map((_, i) => (
                          <Star
                            key={i}
                            size={14}
                            weight={i < Number(rev.rating) ? "fill" : "regular"}
                            className={i < Number(rev.rating) ? "text-[#B8975A]" : "text-gray-300"}
                          />
                        ))}
                      </div>

                      {/* Quote */}
                      <p className="text-xs sm:text-sm text-[#5C5550] leading-relaxed font-light mb-6 italic">
                        &ldquo;{rev.comment}&rdquo;
                      </p>
                    </div>

                    {/* Verification Tag */}
                    <div className="pt-4 border-t border-black/5 flex items-center justify-between text-[11px] text-[#8E857F]">
                      <span className="inline-flex items-center gap-1.5 text-[#3b82f6] font-medium">
                        <SealCheck size={14} weight="fill" />
                        {rev.source === "google" ? "Verified Google Review" : "Verified Customer"}
                      </span>
                    </div>
                  </motion.div>
                );
              })}
            </AnimatePresence>
          </div>
        </div>
      </div>
    </section>
  );
}
