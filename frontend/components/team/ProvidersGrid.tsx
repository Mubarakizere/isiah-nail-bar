"use client";

import { useState, useEffect, useRef } from "react";
import Link from "next/link";
import { motion } from "framer-motion";
import { ArrowRight, CalendarCheck } from "@phosphor-icons/react";
import { TeamProvider } from "@/lib/team-data";

function getInitials(name?: string | null): string {
  if (!name || !name.trim()) return "IS";
  const parts = name.trim().split(/\s+/);
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

function ArtistCardPhoto({ photo, name, role }: { photo?: string | null; name: string; role: string }) {
  const [hasError, setHasError] = useState(false);
  const [isLoaded, setIsLoaded] = useState(false);
  const imgRef = useRef<HTMLImageElement | null>(null);

  const cleanPhoto = photo?.trim();
  const isInvalid = !cleanPhoto || cleanPhoto === "" || cleanPhoto.includes("logo.png") || hasError;

  useEffect(() => {
    if (imgRef.current && imgRef.current.complete) {
      if (imgRef.current.naturalWidth > 0) {
        setIsLoaded(true);
      } else {
        setHasError(true);
      }
    }
  }, [cleanPhoto]);

  return (
    <div className="relative w-full h-full bg-[#FAF7F2] overflow-hidden select-none">
      {/* Luxury Initials Monogram Fallback - rendered behind the image */}
      <div className="absolute inset-0 bg-gradient-to-br from-[#FAF7F2] via-[#F5EFEB] to-[#E5DFD5] flex flex-col items-center justify-center p-6 text-center select-none">
        <div className="w-24 h-24 rounded-full bg-white shadow-lg border border-[#B8975A]/30 flex items-center justify-center text-[#B8975A] mb-4">
          <span className="font-serif font-bold text-3xl tracking-wider">
            {getInitials(name)}
          </span>
        </div>
        <h4 className="font-serif text-xl text-[#221C1A] font-medium">{name}</h4>
        <span className="text-[11px] font-semibold uppercase tracking-widest text-[#B8975A] mt-1">
          {role}
        </span>
      </div>

      {/* Real Image with smooth fade-in */}
      {!isInvalid && (
        <img
          ref={imgRef}
          src={cleanPhoto}
          alt={name}
          className={`absolute inset-0 w-full h-full object-cover object-top group-hover:scale-105 transition-all duration-500 ${
            isLoaded ? "opacity-100" : "opacity-0"
          }`}
          onLoad={(e) => {
            if (e.currentTarget.naturalWidth > 0) {
              setIsLoaded(true);
            } else {
              setHasError(true);
            }
          }}
          onError={() => setHasError(true)}
        />
      )}
    </div>
  );
}

interface ProvidersGridProps {
  providers: TeamProvider[];
}

export default function ProvidersGrid({ providers }: ProvidersGridProps) {
  if (!providers || providers.length === 0) return null;

  return (
    <section className="py-20 lg:py-28 bg-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center max-w-2xl mx-auto mb-16">
          <span className="text-[11px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-3">
            Bespoke Artisans
          </span>
          <h2 className="font-serif text-3xl sm:text-4xl lg:text-5xl font-normal text-[#221C1A] mb-4">
            Senior Nail Artists
          </h2>
          <p className="text-sm sm:text-base text-[#5C5550] font-light leading-relaxed">
            Every technician at Isaiah Nail Bar holds specialized certifications in Russian dry e-file safety, cuticle biology, and premium sculpting.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 sm:gap-10">
          {providers.map((artist, idx) => (
            <motion.div
              key={artist.id}
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.45, delay: idx * 0.1 }}
              className="group bg-[#FAF7F2] rounded-3xl p-5 border border-[#B8975A]/20 hover:border-[#B8975A]/60 hover:shadow-[0_16px_36px_-6px_rgba(184,151,90,0.18)] transition-all duration-300 flex flex-col justify-between"
            >
              <div>
                {/* Photo Frame */}
                <div className="relative aspect-[3/4] rounded-2xl overflow-hidden mb-5 bg-white border border-black/5">
                  <ArtistCardPhoto photo={artist.photo} name={artist.name} role={artist.role} />

                  {/* Availability Badge */}
                  <div className="absolute top-3 left-3 z-10 flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/95 backdrop-blur-md shadow-xs border border-black/5 text-[11px] font-medium text-[#221C1A]">
                    <span className="w-2 h-2 rounded-full bg-[#107C41] animate-pulse" />
                    <span>Available for Booking</span>
                  </div>

                  {/* Experience Badge */}
                  {artist.experience_years && (
                    <div className="absolute top-3 right-3 z-10 px-2.5 py-1 rounded-full bg-black/60 text-white backdrop-blur-md text-[10px] font-semibold uppercase tracking-wider">
                      {artist.experience_years}+ Yrs Craft
                    </div>
                  )}
                </div>

                {/* Content */}
                <div className="space-y-2 mb-6">
                  <div>
                    <h3 className="font-serif text-2xl text-[#221C1A] font-normal group-hover:text-[#B8975A] transition-colors">
                      {artist.name}
                    </h3>
                    <span className="text-[11px] font-semibold uppercase tracking-wider text-[#C4847A] block mt-0.5">
                      {artist.role}
                    </span>
                  </div>

                  {artist.bio ? (
                    <p className="text-xs sm:text-sm text-[#5C5550] font-light leading-relaxed pt-1">
                      {artist.bio}
                    </p>
                  ) : null}
                </div>
              </div>

              {/* Direct Booking Action Bar */}
              <div className="pt-4 border-t border-[#E5DFD5]">
                <Link
                  href={`/booking?provider=${artist.id}`}
                  className="group/btn w-full inline-flex items-center justify-center gap-2 py-3.5 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-colors shadow-xs"
                >
                  <CalendarCheck size={16} weight="bold" />
                  <span>Book with {artist.name}</span>
                  <ArrowRight size={13} className="group-hover/btn:translate-x-0.5 transition-transform" />
                </Link>
              </div>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
}
