"use client";

import React, { useState, useEffect, useRef } from "react";
import { useRouter } from "next/navigation";
import AdminSidebar from "@/components/admin/AdminSidebar";
import { useAuth } from "@/lib/auth-context";
import { AdminContext } from "@/lib/admin-context";

export default function AdminLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const router = useRouter();
  const { user, loading } = useAuth();
  const [mobileOpen, setMobileOpen] = useState(false);
  const [mounted, setMounted] = useState(false);
  const mobileMenuButton = useRef<HTMLButtonElement | null>(null);
  const wasMobileSidebarOpen = useRef(false);

  useEffect(() => {
    setMounted(true);
  }, []);

  useEffect(() => {
    if (mounted && !loading && (!user || user.role !== "admin")) {
      router.push("/login?redirect=/admin");
    }
  }, [mounted, user, loading, router]);

  useEffect(() => {
    if (wasMobileSidebarOpen.current && !mobileOpen) {
      window.requestAnimationFrame(() => mobileMenuButton.current?.focus());
    }
    wasMobileSidebarOpen.current = mobileOpen;
  }, [mobileOpen]);

  if (!mounted) return null;

  return (
    <AdminContext.Provider
      value={{
        onOpenMobileSidebar: () => setMobileOpen(true),
        mobileSidebarOpen: mobileOpen,
        setMobileMenuButton: (button) => { mobileMenuButton.current = button; },
      }}
    >
      <div className="admin-console font-sans h-screen flex overflow-hidden bg-gray-50 text-gray-900 antialiased">
        {/* Sidebar */}
        <AdminSidebar
          mobileOpen={mobileOpen}
          setMobileOpen={setMobileOpen}
        />

        {/* Main content — scrollable */}
        <main className="flex-1 flex flex-col overflow-y-auto min-w-0">
          {loading ? (
            <div className="flex-1 flex items-center justify-center">
              <div className="w-6 h-6 border-2 border-gray-300 border-t-gray-900 rounded-full animate-spin" />
            </div>
          ) : (
            children
          )}
        </main>
      </div>
    </AdminContext.Provider>
  );
}
