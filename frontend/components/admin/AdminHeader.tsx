"use client";

import { useEffect, useRef, useState } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { ArrowSquareOut, CalendarPlus, CaretDown, List, Plus, User } from "@phosphor-icons/react";
import { useAuth } from "@/lib/auth-context";
import { useAdmin } from "@/lib/admin-context";

interface AdminHeaderProps {
  title: string;
  subtitle?: string;
}

export default function AdminHeader({ title, subtitle }: AdminHeaderProps) {
  const { user } = useAuth();
  const { onOpenMobileSidebar, mobileSidebarOpen, setMobileMenuButton } = useAdmin();
  const pathname = usePathname();
  const [createOpen, setCreateOpen] = useState(false);
  const createMenuRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const closeOnOutsideClick = (event: MouseEvent) => {
      if (event.target instanceof Node && !createMenuRef.current?.contains(event.target)) {
        setCreateOpen(false);
      }
    };
    const closeOnEscape = (event: KeyboardEvent) => {
      if (event.key === "Escape") setCreateOpen(false);
    };
    document.addEventListener("mousedown", closeOnOutsideClick);
    window.addEventListener("keydown", closeOnEscape);
    return () => {
      document.removeEventListener("mousedown", closeOnOutsideClick);
      window.removeEventListener("keydown", closeOnEscape);
    };
  }, []);

  useEffect(() => {
    setCreateOpen(false);
  }, [pathname]);

  return (
    <header className="sticky top-0 z-20 flex items-center justify-between h-14 px-3 sm:px-6 bg-white border-b border-gray-200 min-w-0">
      <div className="flex items-center gap-2.5 sm:gap-3 min-w-0">
        {/* mobile hamburger */}
        <button
          ref={setMobileMenuButton}
          onClick={onOpenMobileSidebar}
          aria-label="Open navigation"
          aria-expanded={mobileSidebarOpen}
          className="lg:hidden p-1.5 -ml-1 rounded-md text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors shrink-0"
          title="Toggle Navigation"
        >
          <List size={20} />
        </button>

        <div className="min-w-0">
          <h1 className="text-sm sm:text-base md:text-lg font-bold text-gray-900 leading-tight tracking-tight font-sans truncate">
            {title}
          </h1>
          {subtitle && (
            <p className="text-xs text-gray-500 hidden sm:block truncate">{subtitle}</p>
          )}
        </div>
      </div>

      <div className="flex items-center gap-2 sm:gap-3 shrink-0">
        <Link
          href="/"
          target="_blank"
          className="hidden sm:inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-700 transition-colors"
        >
          <ArrowSquareOut size={14} />
          Live site
        </Link>

        <div ref={createMenuRef} className="relative">
          <button
            type="button"
            onClick={() => setCreateOpen((open) => !open)}
            aria-haspopup="menu"
            aria-expanded={createOpen}
            className="inline-flex items-center gap-1.5 rounded-md bg-gray-900 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-gray-800"
          >
            <Plus size={13} weight="bold" />
            <span>Create</span>
            <CaretDown size={11} weight="bold" className={`transition-transform ${createOpen ? "rotate-180" : ""}`} />
          </button>
          {createOpen && (
            <div role="menu" aria-label="Create" className="absolute right-0 top-full z-50 mt-2 w-56 overflow-hidden rounded-xl border border-gray-200 bg-white p-1.5 shadow-xl">
              <Link role="menuitem" href="/admin/bookings/manual" onClick={() => setCreateOpen(false)} className="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50 hover:text-gray-950">
                <CalendarPlus size={16} className="text-gray-500" />
                <span>Manual booking</span>
              </Link>
              <Link role="menuitem" href="/admin/bookings?action=walk-in" onClick={() => setCreateOpen(false)} className="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50 hover:text-gray-950">
                <User size={16} className="text-emerald-600" />
                <span>Record walk-in</span>
              </Link>
              <div className="my-1 border-t border-gray-100" />
              <Link role="menuitem" href="/booking" target="_blank" onClick={() => setCreateOpen(false)} className="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-xs font-medium text-gray-500 transition hover:bg-gray-50 hover:text-gray-800">
                <ArrowSquareOut size={15} />
                <span>Open public booking page</span>
              </Link>
            </div>
          )}
        </div>

        {/* avatar — desktop */}
        <div className="hidden md:flex items-center gap-2 ml-1 pl-3 border-l border-gray-200">
          <div className="w-7 h-7 rounded-full bg-gray-900 text-white flex items-center justify-center text-xs font-medium">
            {user?.name?.charAt(0).toUpperCase() || "A"}
          </div>
          <span className="text-xs font-medium text-gray-700">
            {user?.name || "Admin"}
          </span>
        </div>
      </div>
    </header>
  );
}
