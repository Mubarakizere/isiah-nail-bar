"use client";

import { useState, useEffect, useRef } from "react";
import Link from "next/link";
import Image from "next/image";
import { usePathname } from "next/navigation";
import { List, X, CalendarCheck, PhoneCall, User, SignOut, CaretDown, SquaresFour, ShieldCheck } from "@phosphor-icons/react";
import { useAuth } from "@/lib/auth-context";
import { useSiteSettings } from "@/lib/settings-context";

const navLinks = [
  { name: "Home", href: "/" },
  { name: "Services", href: "/services" },
  { name: "Gallery", href: "/gallery" },
  { name: "About", href: "/about" },
  { name: "Team", href: "/team" },
  { name: "Contact", href: "/contact" },
];

export default function Header() {
  const [isOpen, setIsOpen] = useState(false);
  const [userDropdownOpen, setUserDropdownOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const pathname = usePathname();
  const dropdownRef = useRef<HTMLDivElement>(null);

  const { user, logout } = useAuth();
  const { settings } = useSiteSettings();

  useEffect(() => {
    const handleScroll = () => {
      setScrolled(window.scrollY > 15);
    };
    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  // Close dropdown when clicking outside
  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(e.target as Node)) {
        setUserDropdownOpen(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  return (
    <header
      className={`sticky top-0 z-50 transition-all duration-300 ${scrolled
        ? "bg-white/95 backdrop-blur-md shadow-[0_2px_15px_-3px_rgba(34,28,26,0.06)] border-b border-[#E8E2D9] py-3"
        : "bg-[#FAF7F2]/90 backdrop-blur-sm border-b border-[#E8E2D9]/80 py-4"
        }`}
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between">
          {/* Brand Logo with Real Salon Emblem */}
          <Link href="/" className="flex items-center gap-3.5 group">
            <div className="relative h-11 w-11 sm:h-12 sm:w-12 rounded-xl overflow-hidden bg-white/80 p-0.5 border border-[#B8975A]/25 shadow-xs transition-transform duration-300 group-hover:scale-105 group-hover:border-[#B8975A]/50">
              <Image
                src="/logo.png"
                alt="Isaiah Nail Bar"
                fill
                sizes="48px"
                className="object-contain"
                priority
              />
            </div>
            <div className="flex flex-col">
              <span className="font-serif font-bold text-lg sm:text-xl tracking-tight text-[#221C1A] group-hover:text-[#C4847A] transition-colors leading-tight">
                Isaiah Nail Bar
              </span>
              <span className="text-[10px] tracking-[0.24em] uppercase text-[#8E857F] font-medium">
                Kigali &bull; Rwanda
              </span>
            </div>
          </Link>

          {/* Desktop Navigation Links */}
          <nav className="hidden lg:flex items-center gap-7">
            {navLinks.map((link) => {
              const isActive = pathname === link.href;
              return (
                <Link
                  key={link.href}
                  href={link.href}
                  className={`text-sm tracking-wide transition-colors relative py-1 ${isActive
                    ? "text-[#C4847A] font-semibold"
                    : "text-[#4A423D] hover:text-[#C4847A] font-medium"
                    }`}
                >
                  {link.name}
                  {isActive && (
                    <span className="absolute bottom-0 left-1/2 -translate-x-1/2 w-4 h-[2px] bg-[#C4847A] rounded-full" />
                  )}
                </Link>
              );
            })}
          </nav>

          {/* Action Button, Auth & Telephone Concierge */}
          <div className="hidden lg:flex items-center gap-4 xl:gap-5">
            <a
              href={settings.tel_url}
              className="flex items-center gap-2 text-xs text-[#5C5550] hover:text-[#221C1A] transition-colors group"
            >
              <div className="w-7 h-7 rounded-full bg-[#B8975A]/10 flex items-center justify-center text-[#B8975A] group-hover:bg-[#B8975A] group-hover:text-white transition-all duration-200">
                <PhoneCall size={13} weight="bold" />
              </div>
              <span className="font-medium tracking-wide">{settings.phone}</span>
            </a>

            {/* Auth Buttons or User Profile */}
            {user ? (
              <div className="relative" ref={dropdownRef}>
                <button
                  type="button"
                  onClick={() => setUserDropdownOpen(!userDropdownOpen)}
                  className="flex items-center gap-2 py-1.5 px-3 rounded-full bg-white border border-[#E5DFD5] hover:border-[#B8975A] text-[#221C1A] text-xs font-medium transition-all shadow-2xs hover:shadow-sm cursor-pointer"
                  aria-expanded={userDropdownOpen}
                >
                  <div className="w-6 h-6 rounded-full bg-[#FAF7F2] border border-[#B8975A]/40 text-[#B8975A] flex items-center justify-center font-bold text-[10px]">
                    {user.name.charAt(0).toUpperCase()}
                  </div>
                  <span className="max-w-[100px] truncate">{user.name.split(" ")[0]}</span>
                  <CaretDown
                    size={12}
                    className={`text-[#8E857F] transition-transform duration-200 ${userDropdownOpen ? "rotate-180" : ""
                      }`}
                  />
                </button>

                {/* Dropdown Menu */}
                {userDropdownOpen && (
                  <div className="absolute right-0 mt-2 w-60 bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-[#E5DFD5] py-2 z-50 text-xs animate-in fade-in zoom-in-95 duration-150">
                    <div className="px-4 py-2.5 border-b border-[#FAF7F2]">
                      <span className="text-[9px] font-semibold text-[#B8975A] uppercase tracking-[0.2em] block mb-0.5">
                        {user.role === "admin" ? "Sanctuary Admin" : "Sanctuary Member"}
                      </span>
                      <p className="font-serif font-semibold text-sm text-[#221C1A] truncate">
                        {user.name}
                      </p>
                      <p className="text-[11px] text-[#8E857F] truncate">{user.email}</p>
                    </div>

                    <div className="py-1">
                      {user.role === "admin" && (
                        <Link
                          href="/admin"
                          onClick={() => setUserDropdownOpen(false)}
                          className="flex items-center gap-2.5 px-4 py-2 text-[#221C1A] font-semibold hover:bg-[#FAF7F2] transition-colors"
                        >
                          <ShieldCheck size={16} className="text-[#B8975A]" />
                          <span>Admin Console</span>
                        </Link>
                      )}

                      <Link
                        href="/dashboard"
                        onClick={() => setUserDropdownOpen(false)}
                        className="flex items-center gap-2.5 px-4 py-2 text-[#5C5550] hover:text-[#221C1A] hover:bg-[#FAF7F2] transition-colors"
                      >
                        <SquaresFour size={15} className="text-[#B8975A]" />
                        <span>My Appointments</span>
                      </Link>

                      <Link
                        href="/booking"
                        onClick={() => setUserDropdownOpen(false)}
                        className="flex items-center gap-2.5 px-4 py-2 text-[#5C5550] hover:text-[#221C1A] hover:bg-[#FAF7F2] transition-colors"
                      >
                        <CalendarCheck size={15} className="text-[#B8975A]" />
                        <span>Book a Ritual</span>
                      </Link>
                    </div>

                    <div className="pt-1 border-t border-[#FAF7F2]">
                      <button
                        type="button"
                        onClick={() => {
                          logout();
                          setUserDropdownOpen(false);
                        }}
                        className="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-[#C0392B] hover:bg-[#FDEDEC] transition-colors cursor-pointer"
                      >
                        <SignOut size={15} />
                        <span className="font-medium">Sign Out</span>
                      </button>
                    </div>
                  </div>
                )}
              </div>
            ) : (
              <div className="flex items-center gap-2">
                <Link
                  href="/login"
                  className="px-3 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wider text-[#4A423D] hover:text-[#221C1A] transition-colors"
                >
                  Sign In
                </Link>
                <Link
                  href="/register"
                  className="px-3.5 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-white border border-[#E5DFD5] hover:border-[#221C1A] transition-all shadow-2xs hover:shadow-xs"
                >
                  Sign Up
                </Link>
              </div>
            )}

            <Link
              href="/booking"
              className="inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 rounded-full text-xs font-semibold tracking-wider uppercase text-white bg-[#221C1A] hover:bg-[#C4847A] transition-all shadow-sm hover:shadow-[0_4px_16px_rgba(196,132,122,0.3)] hover:-translate-y-0.5 duration-200"
            >
              <CalendarCheck size={15} weight="bold" />
              <span>Book Appointment</span>
            </Link>
          </div>

          {/* Mobile Menu Toggle Button */}
          <button
            onClick={() => setIsOpen(!isOpen)}
            className="lg:hidden p-2 rounded-lg text-[#221C1A] hover:bg-black/5 transition-colors cursor-pointer"
            aria-label="Toggle navigation menu"
          >
            {isOpen ? <X size={24} weight="bold" /> : <List size={24} weight="bold" />}
          </button>
        </div>
      </div>

      {/* Mobile Drawer */}
      {isOpen && (
        <div className="lg:hidden bg-white border-b border-[#E8E2D9] px-6 py-6 space-y-4 shadow-lg animate-in slide-in-from-top duration-200">
          <nav className="flex flex-col space-y-2">
            {navLinks.map((link) => (
              <Link
                key={link.href}
                href={link.href}
                onClick={() => setIsOpen(false)}
                className={`py-2 text-base transition-colors ${pathname === link.href
                  ? "text-[#C4847A] font-semibold"
                  : "text-[#4A423D] hover:text-[#C4847A]"
                  }`}
              >
                {link.name}
              </Link>
            ))}
          </nav>

          {/* Mobile Auth Actions */}
          <div className="pt-3 border-t border-[#E8E2D9]">
            {user ? (
              <div className="p-3.5 rounded-2xl bg-[#FAF7F2] border border-[#E5DFD5] space-y-3">
                <div className="flex items-center justify-between">
                  <div>
                    <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block">
                      Signed in as
                    </span>
                    <p className="font-serif text-sm font-semibold text-[#221C1A]">{user.name}</p>
                    <p className="text-[11px] text-[#8E857F]">{user.email}</p>
                  </div>
                  <button
                    type="button"
                    onClick={() => {
                      logout();
                      setIsOpen(false);
                    }}
                    className="px-3 py-1.5 rounded-full text-xs font-semibold text-[#C0392B] bg-white border border-[#F5B7B1] hover:bg-[#FDEDEC] transition-colors cursor-pointer"
                  >
                    Sign Out
                  </button>
                </div>

                {user.role === "admin" && (
                  <Link
                    href="/admin"
                    onClick={() => setIsOpen(false)}
                    className="flex items-center justify-center gap-2 py-2 rounded-xl text-xs font-semibold text-white bg-[#221C1A] hover:bg-[#B8975A] transition-colors"
                  >
                    <ShieldCheck size={16} className="text-[#B8975A]" />
                    <span>Admin Sanctuary Console</span>
                  </Link>
                )}

                <Link
                  href="/dashboard"
                  onClick={() => setIsOpen(false)}
                  className="flex items-center justify-center gap-2 py-2 rounded-xl text-xs font-semibold text-[#221C1A] bg-white border border-[#E5DFD5]"
                >
                  <SquaresFour size={15} className="text-[#B8975A]" />
                  <span>My Appointments Dashboard</span>
                </Link>
              </div>
            ) : (
              <div className="grid grid-cols-2 gap-2.5">
                <Link
                  href="/login"
                  onClick={() => setIsOpen(false)}
                  className="text-center py-2.5 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-[#FAF7F2] border border-[#E5DFD5] hover:bg-white transition-colors"
                >
                  Sign In
                </Link>
                <Link
                  href="/register"
                  onClick={() => setIsOpen(false)}
                  className="text-center py-2.5 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-colors shadow-xs"
                >
                  Sign Up
                </Link>
              </div>
            )}
          </div>

          <div className="pt-3 border-t border-[#E8E2D9] flex flex-col gap-3">
            <a
              href={settings.tel_url}
              className="flex items-center gap-2 text-sm text-[#5C5550]"
            >
              <PhoneCall size={16} className="text-[#B8975A]" weight="bold" />
              <span>{settings.phone}</span>
            </a>
            <Link
              href="/booking"
              onClick={() => setIsOpen(false)}
              className="w-full text-center py-3 rounded-full text-xs font-semibold tracking-wider uppercase text-white bg-[#221C1A] hover:bg-[#C4847A] transition-colors shadow-sm"
            >
              Book Appointment
            </Link>
          </div>
        </div>
      )}
    </header>
  );
}
