"use client";

import Link from "next/link";
import { ArrowClockwise, ArrowLeft, House, WarningCircle } from "@phosphor-icons/react";

interface ErrorLandingProps {
  code: string;
  title: string;
  message: string;
  primaryHref?: string;
  primaryLabel?: string;
  reset?: () => void;
}

export default function ErrorLanding({
  code,
  title,
  message,
  primaryHref = "/",
  primaryLabel = "Return home",
  reset,
}: ErrorLandingProps) {
  return (
    <div className="flex min-h-screen flex-col bg-[#FAF7F2] text-[#221C1A]">
      <header className="border-b border-[#E5DFD5] bg-white/90">
        <div className="mx-auto flex max-w-7xl items-center justify-between px-5 py-5 sm:px-8">
          <Link href="/" className="text-xs font-semibold tracking-[0.22em] text-[#8B7048]">
            ISAIAH NAIL BAR
          </Link>
          <Link href="/services" className="text-sm text-[#5C5550] transition hover:text-[#B8975A]">
            Explore services
          </Link>
        </div>
      </header>

      <main className="flex flex-1 items-center justify-center px-5 py-16 sm:px-8">
        <section className="w-full max-w-xl text-center">
          <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-[#B8975A]/25 bg-white text-[#B8975A] shadow-sm">
            <WarningCircle size={30} weight="light" aria-hidden="true" />
          </div>
          <p className="mt-7 text-xs font-semibold uppercase tracking-[0.28em] text-[#9A7B4C]">
            {code === "404" ? "Page not found" : `Error ${code}`}
          </p>
          <h1 className="mt-3 font-serif text-4xl font-normal leading-tight sm:text-5xl">
            {title}
          </h1>
          <p className="mx-auto mt-5 max-w-md text-sm leading-7 text-[#756E67] sm:text-base">
            {message}
          </p>

          <div className="mt-8 flex flex-wrap justify-center gap-3">
            {reset && (
              <button
                type="button"
                onClick={reset}
                className="inline-flex items-center gap-2 rounded-full border border-[#B8975A]/40 bg-white px-5 py-3 text-sm font-medium transition hover:border-[#B8975A] hover:bg-[#FBF8F3]"
              >
                <ArrowClockwise size={17} /> Try again
              </button>
            )}
            <Link
              href={primaryHref}
              className="inline-flex items-center gap-2 rounded-full bg-[#221C1A] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#4A3D32]"
            >
              {primaryHref === "/" ? <House size={17} /> : <ArrowLeft size={17} />}
              {primaryLabel}
            </Link>
          </div>

          <p className="mt-8 text-sm text-[#817A72]">
            Need help? Call <a className="font-medium text-[#6C5540] hover:underline" href="tel:+250790395169">+250 790 395 169</a>
          </p>
        </section>
      </main>

      <footer className="border-t border-[#E5DFD5] px-5 py-5 text-center text-xs text-[#8E857F]">
        Isaiah Nail Bar · Kigali
      </footer>
    </div>
  );
}
