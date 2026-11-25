"use client";

import { useEffect, useRef, useState } from "react";
import { createPortal } from "react-dom";
import {
  Check,
  Copy,
  Link as LinkIcon,
  ShareNetwork,
  WhatsappLogo,
  X,
} from "@phosphor-icons/react";

const CANONICAL_MENU_URL = "https://isaiahnailbar.com/services";

export default function ShareMenuButton() {
  const [isOpen, setIsOpen] = useState(false);
  const [copied, setCopied] = useState(false);
  const copyTimerRef = useRef<number | null>(null);

  const shareUrl =
    typeof window !== "undefined"
      ? `${window.location.origin}/services`
      : CANONICAL_MENU_URL;
  const canNativeShare =
    typeof navigator !== "undefined" && typeof navigator.share === "function";

  useEffect(() => {
    return () => {
      if (copyTimerRef.current !== null) {
        window.clearTimeout(copyTimerRef.current);
      }
    };
  }, []);

  useEffect(() => {
    if (!isOpen) return;

    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";

    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        setIsOpen(false);
      }
    };

    window.addEventListener("keydown", handleKeyDown);
    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener("keydown", handleKeyDown);
    };
  }, [isOpen]);

  const handleCopyLink = async () => {
    try {
      if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(shareUrl);
      } else {
        throw new Error("Clipboard API unavailable");
      }
    } catch {
      const textarea = document.createElement("textarea");
      textarea.value = shareUrl;
      textarea.setAttribute("readonly", "");
      textarea.style.position = "fixed";
      textarea.style.opacity = "0";
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand("copy");
      document.body.removeChild(textarea);
    }

    setCopied(true);
    if (copyTimerRef.current !== null) {
      window.clearTimeout(copyTimerRef.current);
    }
    copyTimerRef.current = window.setTimeout(() => {
      setCopied(false);
      copyTimerRef.current = null;
    }, 2500);
  };

  const handleNativeShare = async () => {
    if (!canNativeShare) return;
    try {
      await navigator.share({
        title: "Nail Services Menu & Prices | Isaiah Nail Bar Kigali",
        text: "Explore Isaiah Nail Bar's complete nail services menu and prices in Kigali.",
        url: shareUrl,
      });
    } catch {
      // User cancelled native share sheet; keep Share Menu dialog open for copy/WhatsApp.
    }
  };

  const whatsappHref = `https://wa.me/?text=${encodeURIComponent(
    `Explore Isaiah Nail Bar's Nail Services Menu & Prices in Kigali: ${shareUrl}`
  )}`;

  return (
    <>
      <button
        type="button"
        onClick={() => {
          setCopied(false);
          setIsOpen(true);
        }}
        className="inline-flex items-center gap-2.5 px-7 py-3.5 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white hover:bg-[#FAF7F2] hover:text-[#B8975A] transition-all duration-300 shadow-xl hover:-translate-y-0.5 cursor-pointer"
      >
        <ShareNetwork size={16} weight="bold" />
        <span>Share Menu</span>
      </button>

      {isOpen &&
        typeof document !== "undefined" &&
        createPortal(
          <div
            className="fixed inset-0 z-[90] flex items-center justify-center bg-black/60 backdrop-blur-xs p-4"
            onMouseDown={(event) => {
              if (event.target === event.currentTarget) {
                setIsOpen(false);
              }
            }}
          >
            <section
              role="dialog"
              aria-modal="true"
              aria-labelledby="share-menu-title"
              className="relative w-full max-w-md rounded-3xl border border-[#E5DFD5] bg-white p-6 sm:p-7 text-left shadow-2xl"
            >
              <button
                type="button"
                onClick={() => setIsOpen(false)}
                aria-label="Close share menu"
                className="absolute top-4 right-4 w-9 h-9 rounded-full bg-[#FAF7F2] hover:bg-[#F0EAE1] text-[#221C1A] flex items-center justify-center transition-colors cursor-pointer"
              >
                <X size={18} />
              </button>

              <span className="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#B8975A] block mb-1.5">
                Isaiah Nail Bar Kigali
              </span>
              <h2
                id="share-menu-title"
                className="font-serif text-2xl sm:text-3xl font-normal text-[#221C1A] mb-2 pr-8"
              >
                Share Service Menu
              </h2>
              <p className="text-xs sm:text-sm text-[#5C5550] font-light leading-relaxed mb-5">
                Copy this link to share our live nail services menu &amp; prices with friends, brides, or clients.
              </p>

              {/* Copyable Link Bar */}
              <div className="rounded-2xl border border-[#E5DFD5] bg-[#FAF7F2] p-2 pl-3.5 flex items-center gap-2 mb-4">
                <LinkIcon size={16} className="text-[#B8975A] shrink-0" />
                <input
                  type="text"
                  readOnly
                  value={shareUrl}
                  aria-label="Services menu link"
                  onClick={(event) => event.currentTarget.select()}
                  className="min-w-0 flex-1 bg-transparent text-xs sm:text-sm text-[#221C1A] font-medium focus:outline-none"
                />
                <button
                  type="button"
                  onClick={() => void handleCopyLink()}
                  className={`inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-semibold uppercase tracking-wider transition-all shrink-0 cursor-pointer ${
                    copied
                      ? "bg-emerald-600 text-white"
                      : "bg-[#221C1A] text-white hover:bg-[#B8975A]"
                  }`}
                >
                  {copied ? (
                    <>
                      <Check size={14} weight="bold" />
                      <span>Copied</span>
                    </>
                  ) : (
                    <>
                      <Copy size={14} weight="bold" />
                      <span>Copy Link</span>
                    </>
                  )}
                </button>
              </div>

              {copied && (
                <p className="text-xs font-medium text-emerald-700 mb-4">
                  Menu link copied to clipboard.
                </p>
              )}

              {/* Direct Share Actions */}
              <div className="flex flex-col sm:flex-row gap-2.5 pt-2">
                <a
                  href={whatsappHref}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex-1 inline-flex items-center justify-center gap-2 rounded-full border border-[#E5DFD5] bg-white hover:border-[#25D366] hover:bg-[#FAF7F2] px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[#221C1A] transition-colors"
                >
                  <WhatsappLogo size={17} weight="fill" className="text-[#25D366]" />
                  <span>Share via WhatsApp</span>
                </a>

                {canNativeShare && (
                  <button
                    type="button"
                    onClick={() => void handleNativeShare()}
                    className="flex-1 inline-flex items-center justify-center gap-2 rounded-full border border-[#E5DFD5] bg-white hover:border-[#221C1A] hover:bg-[#FAF7F2] px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[#221C1A] transition-colors cursor-pointer"
                  >
                    <ShareNetwork size={16} weight="bold" />
                    <span>More Options</span>
                  </button>
                )}
              </div>
            </section>
          </div>,
          document.body
        )}
    </>
  );
}
