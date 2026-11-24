"use client";

import { useState, useEffect, useRef } from "react";
import { motion } from "framer-motion";
import { TeamSupportMember } from "@/lib/team-data";

function getInitials(name?: string | null): string {
  if (!name || !name.trim()) return "IS";
  const parts = name.trim().split(/\s+/);
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

function StaffCardPhoto({ photo, name, role }: { photo?: string | null; name: string; role: string }) {
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
    <div className="relative w-full h-full min-h-[220px] bg-[#FAF7F2] overflow-hidden select-none">
      <div className="absolute inset-0 bg-gradient-to-br from-[#FAF7F2] via-[#F5EFEB] to-[#E5DFD5] flex flex-col items-center justify-center p-6 text-center select-none">
        <div className="w-20 h-20 rounded-full bg-white shadow-md border border-[#B8975A]/30 flex items-center justify-center text-[#B8975A] mb-3">
          <span className="font-serif font-bold text-2xl tracking-wider">
            {getInitials(name)}
          </span>
        </div>
        <h4 className="font-serif text-lg text-[#221C1A] font-medium">{name}</h4>
        <span className="text-[11px] font-semibold uppercase tracking-wider text-[#B8975A] mt-0.5">
          {role}
        </span>
      </div>

      {!isInvalid && (
        <img
          ref={imgRef}
          src={cleanPhoto}
          alt={name}
          className={`absolute inset-0 w-full h-full object-cover object-top transition-opacity duration-300 ${
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

interface SupportStaffSectionProps {
  members: TeamSupportMember[];
}

export default function SupportStaffSection({ members }: SupportStaffSectionProps) {
  if (!members || members.length === 0) return null;

  return (
    <section className="py-20 lg:py-24 bg-[#EFE8DD] border-y border-[#DDD2C2] relative">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center max-w-2xl mx-auto mb-16">
          <span className="text-[11px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-3">
            Atelier Hospitality
          </span>
          <h2 className="font-serif text-3xl sm:text-4xl lg:text-5xl font-normal text-[#221C1A] mb-4">
            The Faces Behind the Service
          </h2>
          <p className="text-sm sm:text-base text-[#5C5550] font-light leading-relaxed">
            Our guest care coordinators ensure your sanctuary visit is effortless, punctual, and relaxing from arrival to departure.
          </p>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-8 max-w-3xl mx-auto">
          {members.map((member, idx) => (
            <motion.div
              key={member.id}
              initial={{ opacity: 0, y: 15 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.45, delay: idx * 0.12 }}
              className="bg-white rounded-3xl p-6 sm:p-7 border border-[#DDD3C2] shadow-xs flex flex-col justify-between hover:shadow-md transition-all duration-300"
            >
              <div>
                <div className="relative aspect-[3/4] rounded-2xl overflow-hidden mb-5 bg-[#FAF7F2] border border-black/5">
                  <StaffCardPhoto photo={member.photo} name={member.name} role={member.role} />
                </div>

                <div className="text-center space-y-1.5">
                  <h3 className="font-serif text-xl sm:text-2xl text-[#221C1A] font-semibold">
                    {member.name}
                  </h3>
                  <span className="text-[11px] font-semibold uppercase tracking-wider text-[#C4847A] block">
                    {member.role}
                  </span>
                  {member.bio && (
                    <p className="text-xs text-[#5C5550] font-light leading-relaxed pt-2">
                      {member.bio}
                    </p>
                  )}
                </div>
              </div>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
}
