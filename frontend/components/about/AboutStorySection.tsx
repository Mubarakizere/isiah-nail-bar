"use client";

import Image from "next/image";
import { motion } from "framer-motion";
import { Quotes, Heart } from "@phosphor-icons/react";

export default function AboutStorySection() {
  return (
    <section className="py-20 lg:py-28 bg-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
          {/* Left Column: Narrative & Metrics (7 cols) */}
          <div className="lg:col-span-7 space-y-8">
            <div>
              <span className="text-[11px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-3">
                Since 2022 &bull; Gisementi, Kigali
              </span>
              <h2 className="font-serif text-3xl sm:text-4xl lg:text-5xl font-normal text-[#221C1A] leading-tight mb-4">
                Redefining Luxury Nail Care in Rwanda
              </h2>
              <div className="w-16 h-0.5 bg-[#B8975A]" />
            </div>

            <div className="space-y-5 text-sm sm:text-base text-[#5C5550] font-light leading-relaxed">
              <p>
                Isaiah Nail Bar began with a clear, ambitious vision: to introduce world-class nail artistry, European e-file precision, and hospital-grade sterilization standards to Kigali.
              </p>
              <p>
                We believe that caring for your hands and feet shouldn&apos;t just be a routine maintenance chore&mdash;it should be an unhurried ritual of wellness, restoration, and aesthetic perfection.
              </p>
              <p>
                Our sanctuary in Gisementi was designed as a tranquil escape from the bustling city rhythm. From the moment you enter, every sensory detail&mdash;sunlit minimalist aesthetics, calming acoustics, ergonomic seating, and individually sealed sterile tool pouches&mdash;is dedicated to your comfort and health.
              </p>
            </div>

            {/* Key Atelier Metrics */}
            <div className="grid grid-cols-3 gap-6 pt-8 border-t border-[#E5DFD5]">
              <div>
                <span className="block font-serif text-3xl sm:text-4xl font-bold text-[#221C1A]">
                  3+
                </span>
                <span className="text-[11px] uppercase tracking-wider text-[#8E857F] block mt-1">
                  Years of Craft
                </span>
              </div>

              <div>
                <span className="block font-serif text-3xl sm:text-4xl font-bold text-[#221C1A]">
                  500+
                </span>
                <span className="text-[11px] uppercase tracking-wider text-[#8E857F] block mt-1">
                  Regular Guests
                </span>
              </div>

              <div>
                <span className="block font-serif text-3xl sm:text-4xl font-bold text-[#221C1A]">
                  10k+
                </span>
                <span className="text-[11px] uppercase tracking-wider text-[#8E857F] block mt-1">
                  Bespoke Rituals
                </span>
              </div>
            </div>
          </div>

          {/* Right Column: Visual Composition (5 cols) */}
          <div className="lg:col-span-5 relative">
            <div className="grid grid-cols-2 gap-4 items-center">
              {/* Photo 1: Manicurist Precision */}
              <div className="space-y-4 translate-y-6">
                <div className="aspect-[3/4] rounded-2xl overflow-hidden bg-gray-100 shadow-lg relative border border-black/5">
                  <Image
                    src="/banner.jpg"
                    alt="Manicurist at work in Isaiah Nail Bar"
                    fill
                    sizes="(max-width: 1024px) 50vw, 25vw"
                    className="object-cover"
                  />
                </div>
                {/* Quote Box */}
                <div className="p-5 bg-[#221C1A] text-white rounded-2xl shadow-md">
                  <Quotes size={22} weight="fill" className="text-[#B8975A] mb-2" />
                  <p className="font-serif italic text-xs leading-relaxed text-white/90">
                    &ldquo;Where hospital hygiene meets fine bespoke fashion.&rdquo;
                  </p>
                </div>
              </div>

              {/* Photo 2: Clean Detailing */}
              <div className="space-y-4">
                <div className="p-5 bg-[#FAF7F2] rounded-2xl border border-[#E5DFD5] shadow-2xs">
                  <div className="w-8 h-8 rounded-full bg-white border border-[#B8975A]/30 text-[#C4847A] flex items-center justify-center mb-2">
                    <Heart size={16} weight="fill" />
                  </div>
                  <h4 className="font-serif text-xs font-semibold text-[#221C1A]">
                    Uncompromising Care
                  </h4>
                  <p className="text-[11px] text-[#5C5550] mt-1 leading-normal font-light">
                    100% single-use buffers and medical autoclave sterile pouches.
                  </p>
                </div>

                <div className="aspect-[3/4] rounded-2xl overflow-hidden bg-gray-100 shadow-lg relative border border-black/5">
                  <Image
                    src="/hero-3.jpg"
                    alt="Clean polished manicure detail"
                    fill
                    sizes="(max-width: 1024px) 50vw, 25vw"
                    className="object-cover"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
