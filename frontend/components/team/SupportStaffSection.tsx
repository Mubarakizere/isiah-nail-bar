"use client";

import Image from "next/image";
import { motion } from "framer-motion";
import { TeamSupportMember } from "@/lib/team-data";

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
                  <Image
                    src={member.photo}
                    alt={member.name}
                    fill
                    sizes="(max-width: 640px) 100vw, 400px"
                    className="object-cover object-top"
                  />
                </div>

                <div className="text-center space-y-1.5">
                  <h3 className="font-serif text-xl sm:text-2xl text-[#221C1A] font-semibold">
                    {member.name}
                  </h3>
                  <span className="text-[11px] font-semibold uppercase tracking-wider text-[#C4847A] block">
                    {member.role}
                  </span>
                  <p className="text-xs text-[#5C5550] font-light leading-relaxed pt-2">
                    {member.bio}
                  </p>
                </div>
              </div>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
}
