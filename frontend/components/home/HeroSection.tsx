"use client";

import { useState, useEffect, useCallback } from "react";
import Link from "next/link";
import Image from "next/image";
import { motion, AnimatePresence } from "framer-motion";
import {
  CaretLeft,
  CaretRight,
  ArrowRight,
  ShieldCheck,
  Heart,
} from "@phosphor-icons/react";

interface HeroSlideItem {
  id?: number;
  title: string;
  subtitle?: string;
  description?: string;
  image?: string;
  button_text?: string;
  button_url?: string;
  secondary_button_text?: string;
  secondary_button_url?: string;
}

const defaultSlides: HeroSlideItem[] = [
  {
    title: "The Art of\nIntentional Beauty",
    description: "Immerse yourself in precision nail artistry, restorative rituals, and hospital-grade hygiene in our sunlit Gisementi sanctuary.",
    image: "/banner.jpg",
    secondary_button_text: "Explore Services",
    secondary_button_url: "/services",
  },
  {
    title: "Mastery in Every\nSubtle Detail",
    description: "Meticulous e-file cuticular detailing and tailored arch alignment crafted to elevate your natural nails for weeks.",
    image: "/hero-2.png",
    secondary_button_text: "Explore Services",
    secondary_button_url: "/services",
  },
  {
    title: "Tranquil Rituals for\nHands & Feet",
    description: "Soak in herbal aromas, tension-relieving pressure points, and restorative natural oils in an unhurried, serene salon.",
    image: "/hero-3.jpg",
    secondary_button_text: "Explore Services",
    secondary_button_url: "/services",
  },
];

interface HeroSectionProps {
  slides?: HeroSlideItem[];
}

export default function HeroSection({ slides }: HeroSectionProps) {
  const activeSlides = slides && slides.length > 0 ? slides : defaultSlides;
  const [current, setCurrent] = useState(0);
  const [isPaused, setIsPaused] = useState(false);

  const nextSlide = useCallback(() => {
    setCurrent((prev) => (prev + 1) % activeSlides.length);
  }, [activeSlides.length]);

  const prevSlide = useCallback(() => {
    setCurrent((prev) => (prev - 1 + activeSlides.length) % activeSlides.length);
  }, [activeSlides.length]);

  useEffect(() => {
    if (isPaused || activeSlides.length <= 1) return;
    const interval = setInterval(nextSlide, 7000);
    return () => clearInterval(interval);
  }, [isPaused, nextSlide, activeSlides.length]);

  useEffect(() => {
    setCurrent((index) => index % activeSlides.length);
  }, [activeSlides.length]);

  const slide = activeSlides[current];

  return (
    <section
      className="relative min-h-[85vh] lg:min-h-[88vh] flex items-center overflow-hidden bg-[#181514]"
      onMouseEnter={() => setIsPaused(true)}
      onMouseLeave={() => setIsPaused(false)}
    >
      {/* 1. Full-Bleed Background Image Slides */}
      <div className="absolute inset-0 z-0">
        <AnimatePresence mode="wait">
          <motion.div
            key={current}
            initial={{ opacity: 0, scale: 1.05 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0 }}
            transition={{ duration: 1.0, ease: "easeOut" }}
            className="absolute inset-0 w-full h-full"
          >
            <Image
              src={slide.image || "/banner.jpg"}
              alt={slide.title.replace("\n", " ")}
              fill
              unoptimized
              priority={current === 0}
              sizes="100vw"
              className="object-cover object-center"
            />
          </motion.div>
        </AnimatePresence>
      </div>

      {/* 2. Black Overlay with Opacity - makes image clearly visible without white haze */}
      <div className="absolute inset-0 bg-black/55 z-10" />
      <div className="absolute inset-0 bg-gradient-to-r from-black/85 via-black/60 to-black/35 z-10" />
      <div className="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-black/60 to-transparent z-10" />

      {/* 3. Hero Content Container */}
      <div className="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28 w-full">
        <div className="max-w-2xl space-y-7">
          {/* Main Headline (Top eyebrow removed per user request) */}
          <AnimatePresence mode="wait">
            <motion.h1
              key={`title-${current}`}
              initial={{ opacity: 0, y: 22 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -14 }}
              transition={{ duration: 0.6, ease: "easeOut" }}
              className="font-serif text-4xl sm:text-6xl lg:text-7xl font-normal text-white leading-[1.08] tracking-tight whitespace-pre-line drop-shadow-sm"
            >
              {slide.title}
            </motion.h1>
          </AnimatePresence>

          {/* Description */}
          <AnimatePresence mode="wait">
            <motion.p
              key={`desc-${current}`}
              initial={{ opacity: 0, y: 16 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -10 }}
              transition={{ duration: 0.5, delay: 0.1 }}
              className="text-base sm:text-lg lg:text-xl text-white/85 font-light leading-relaxed max-w-xl"
            >
              {slide.description}
            </motion.p>
          </AnimatePresence>

          {/* Single Primary CTA Button (Reserve Appointment removed per user request) */}
          <div className="pt-2">
            <Link
              href={slide.secondary_button_url || "/services"}
              className="group inline-flex items-center gap-3 px-8 py-4 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white hover:bg-[#FAF7F2] hover:text-[#B8975A] transition-all duration-300 shadow-xl hover:-translate-y-0.5"
            >
              <span>Explore Services</span>
              <ArrowRight size={16} className="group-hover:translate-x-1 transition-transform" />
            </Link>
          </div>

          {/* Salon Credibility Glass Pill */}
          <div className="pt-4">
            <div className="inline-flex flex-wrap items-center gap-4 sm:gap-6 px-5 py-3 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/90 shadow-lg">
              <div className="flex items-center gap-2 text-xs font-medium">
                <span className="w-2 h-2 rounded-full bg-[#34A853] animate-pulse" />
                <span>Top Rated in Kigali</span>
              </div>

              <div className="h-3 w-px bg-white/25 hidden sm:block" />

              <div className="flex items-center gap-1.5 text-xs text-white/80">
                <Heart size={14} weight="fill" className="text-[#E88C8C]" />
                <span><strong className="text-white font-semibold">500+</strong> Regular Guests</span>
              </div>

              <div className="h-3 w-px bg-white/25 hidden sm:block" />

              <div className="flex items-center gap-1.5 text-xs text-white/80">
                <ShieldCheck size={15} weight="bold" className="text-[#86EFAC]" />
                <span>Hospital-Grade Sterilization</span>
              </div>
            </div>
          </div>
        </div>

        {/* Carousel Navigation Controls (Arrows + Indicators) */}
        {activeSlides.length > 1 && (
          <div className="pt-12 sm:pt-16 flex flex-wrap items-center justify-between gap-4 border-t border-white/15 mt-10">
            {/* Slide Indicator Dots */}
            <div className="flex items-center gap-2">
              {activeSlides.map((_, i) => (
                <button
                  key={i}
                  onClick={() => setCurrent(i)}
                  aria-label={`Go to slide ${i + 1}`}
                  className={`h-1.5 rounded-full transition-all duration-300 ${
                    i === current
                      ? "w-8 bg-white"
                      : "w-2 bg-white/40 hover:bg-white/70"
                  }`}
                />
              ))}
            </div>

            {/* Caret Arrows */}
            <div className="flex items-center gap-2">
              <button
                onClick={prevSlide}
                aria-label="Previous Slide"
                className="w-10 h-10 rounded-full bg-white/10 hover:bg-white/25 backdrop-blur-md border border-white/20 text-white flex items-center justify-center transition-all duration-200"
              >
                <CaretLeft size={18} weight="bold" />
              </button>
              <button
                onClick={nextSlide}
                aria-label="Next Slide"
                className="w-10 h-10 rounded-full bg-white/10 hover:bg-white/25 backdrop-blur-md border border-white/20 text-white flex items-center justify-center transition-all duration-200"
              >
                <CaretRight size={18} weight="bold" />
              </button>
            </div>
          </div>
        )}
      </div>
    </section>
  );
}
