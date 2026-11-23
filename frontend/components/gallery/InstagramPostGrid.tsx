"use client";

import { useEffect } from "react";
import Script from "next/script";
import { InstagramLogo, ArrowRight } from "@phosphor-icons/react";
import { InstagramPost } from "@/lib/gallery-data";

interface InstagramPostGridProps {
  posts: InstagramPost[];
}

export default function InstagramPostGrid({ posts }: InstagramPostGridProps) {
  useEffect(() => {
    // Trigger Instagram embed processing whenever posts change or script loads
    if (typeof window !== "undefined" && (window as any).instgrm) {
      (window as any).instgrm.Embeds?.process();
    }
  }, [posts]);

  return (
    <section className="py-16 sm:py-24 bg-white min-h-[60vh]">
      <Script
        src="//www.instagram.com/embed.js"
        strategy="lazyOnload"
        onLoad={() => {
          if (typeof window !== "undefined" && (window as any).instgrm) {
            (window as any).instgrm.Embeds?.process();
          }
        }}
      />

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {posts && posts.length > 0 ? (
          <div>
            {/* Instagram Posts Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 items-start">
              {posts.map((post) => (
                <div
                  key={post.id}
                  className="group bg-[#FAF7F2] rounded-3xl overflow-hidden border border-[#E5DFD5] hover:border-[#B8975A]/50 shadow-xs hover:shadow-xl transition-all duration-300 p-4"
                >
                  {/* Official Instagram Embed Container */}
                  <div className="flex justify-center w-full min-h-[420px] rounded-2xl overflow-hidden bg-white">
                    <blockquote
                      className="instagram-media"
                      data-instgrm-permalink={post.url}
                      data-instgrm-version="14"
                      style={{
                        background: "#FFF",
                        border: 0,
                        borderRadius: "16px",
                        boxShadow: "none",
                        margin: "0 auto",
                        maxWidth: "540px",
                        minWidth: "280px",
                        padding: 0,
                        width: "100%",
                      }}
                    >
                      {/* Fallback while embed script executes */}
                      <div className="p-8 text-center flex flex-col items-center justify-center min-h-[400px]">
                        <InstagramLogo size={42} className="text-[#E1306C] mb-4 animate-pulse" />
                        <span className="font-serif text-base text-[#221C1A] mb-2 block">
                          Instagram Post
                        </span>
                        <a
                          href={post.url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="text-xs text-[#B8975A] hover:underline font-medium"
                        >
                          View post on Instagram &rarr;
                        </a>
                      </div>
                    </blockquote>
                  </div>

                  {/* Clean Bottom Link */}
                  <div className="pt-3.5 px-2 flex items-center justify-between text-xs text-[#8E857F]">
                    <span className="flex items-center gap-1.5">
                      <InstagramLogo size={15} className="text-[#E1306C]" />
                      <span>@isaiah_nails_art_kigali_rwanda</span>
                    </span>
                    <a
                      href={post.url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="font-medium text-[#221C1A] hover:text-[#B8975A] transition-colors"
                    >
                      View on Instagram
                    </a>
                  </div>
                </div>
              ))}
            </div>

            {/* Bottom Instagram Action CTA */}
            <div className="mt-20 text-center">
              <p className="text-sm text-[#5C5550] mb-5 font-light">
                See more of our daily atelier updates, reels &amp; stories
              </p>
              <a
                href="https://www.instagram.com/isaiah_nails_art_kigali_rwanda"
                target="_blank"
                rel="noopener noreferrer"
                className="group inline-flex items-center gap-3 px-8 py-4 bg-[#221C1A] text-white text-xs font-semibold uppercase tracking-wider rounded-full hover:bg-[#B8975A] transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-0.5"
              >
                <InstagramLogo size={18} weight="bold" />
                <span>View More on Instagram</span>
                <ArrowRight size={14} className="group-hover:translate-x-1 transition-transform" />
              </a>
            </div>
          </div>
        ) : (
          /* Empty State Matching Blade View */
          <div className="text-center py-28 max-w-md mx-auto">
            <div className="inline-flex items-center justify-center w-20 h-20 rounded-full bg-[#FAF7F2] border border-[#E5DFD5] mb-6">
              <InstagramLogo size={36} className="text-[#8E857F]" />
            </div>
            <h3 className="text-2xl font-serif text-[#221C1A] mb-2 font-normal">
              No posts yet
            </h3>
            <p className="text-sm text-[#5C5550] font-light leading-relaxed mb-8">
              Our latest work will appear here when we add posts to the gallery. You can also visit our Instagram feed directly.
            </p>
            <a
              href="https://www.instagram.com/isaiah_nails_art_kigali_rwanda"
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-2 px-6 py-3 rounded-full text-xs font-semibold uppercase tracking-wider bg-[#221C1A] text-white hover:bg-[#B8975A] transition-colors shadow-md"
            >
              <InstagramLogo size={16} weight="bold" />
              <span>Visit Our Instagram</span>
            </a>
          </div>
        )}
      </div>
    </section>
  );
}
