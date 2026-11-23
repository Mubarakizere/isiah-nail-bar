"use client";

import { useCallback, useEffect, useRef, useState, type KeyboardEvent as ReactKeyboardEvent } from "react";
import Link from "next/link";
import Image from "next/image";
import { usePathname, useRouter } from "next/navigation";
import {
  House,
  Calendar,
  ChartLineUp,
  ChartBar,
  Folder,
  Scissors,
  UsersThree,
  Users,
  Clock,
  EnvelopeSimple,
  EnvelopeOpen,
  Slideshow,
  Car,
  InstagramLogo,
  Tag,
  Star,
  CalendarPlus,
  Hourglass,
  PaperPlaneTilt,
  WebhooksLogo,
  Sliders,
  SignOut,
  ArrowSquareOut,
  CaretDown,
  CaretLeft,
  CaretRight,
  X,
  PhoneCall,
  Gift,
} from "@phosphor-icons/react";
import { useAuth } from "@/lib/auth-context";

type BadgeKey = "pendingBookings" | "pendingServices" | "unreadMessages" | "dueRecalls";

interface NavItem {
  label: string;
  href: string;
  icon: any;
  badge?: BadgeKey;
  children?: NavItem[];
}

interface NavGroup {
  id: string;
  heading: string;
  items: NavItem[];
}

const groups: NavGroup[] = [
  {
    id: "work",
    heading: "Work",
    items: [
      { label: "Overview", href: "/admin", icon: House },
      {
        label: "Calendar",
        href: "/admin/calendar",
        icon: Calendar,
        children: [
          { label: "Time Slots", href: "/admin/slots", icon: Clock },
        ],
      },
      {
        label: "Bookings",
        href: "/admin/bookings",
        icon: Calendar,
        badge: "pendingBookings",
        children: [
          { label: "Manual Booking", href: "/admin/bookings/manual", icon: CalendarPlus },
          { label: "Record Walk-in", href: "/admin/bookings?action=walk-in", icon: CalendarPlus },
        ],
      },
    ],
  },
  {
    id: "salon",
    heading: "Salon",
    items: [
      {
        label: "Services",
        href: "/admin/services",
        icon: Scissors,
        children: [
          { label: "Pending Services", href: "/admin/services/pending", icon: Hourglass, badge: "pendingServices" },
        ],
      },
      { label: "Categories", href: "/admin/categories", icon: Folder },
      { label: "Tags", href: "/admin/tags", icon: Tag },
    ],
  },
  {
    id: "team",
    heading: "Team",
    items: [
      { label: "Providers", href: "/admin/providers", icon: UsersThree },
      { label: "Team Members", href: "/admin/team-members", icon: Users },
    ],
  },
  {
    id: "customer-care",
    heading: "Customer Care",
    items: [
      { label: "Messages", href: "/admin/messages", icon: EnvelopeSimple, badge: "unreadMessages" },
      { label: "Loyalty & Rewards", href: "/admin/loyalty", icon: Gift },
      { label: "Client Call List", href: "/admin/recalls", icon: PhoneCall, badge: "dueRecalls" },
      { label: "Webmail", href: "/admin/webmail", icon: EnvelopeOpen },
      { label: "Reviews", href: "/admin/reviews", icon: Star },
    ],
  },
  {
    id: "website",
    heading: "Website",
    items: [
      { label: "Hero Slides", href: "/admin/hero-slides", icon: Slideshow },
      { label: "Gallery", href: "/admin/gallery", icon: InstagramLogo },
      { label: "Pickup Locations", href: "/admin/pickup-locations", icon: Car },
    ],
  },
  {
    id: "insights",
    heading: "Insights",
    items: [
      { label: "Reports", href: "/admin/reports", icon: ChartLineUp },
      { label: "Performance", href: "/admin/performance", icon: ChartBar },
    ],
  },
  {
    id: "system",
    heading: "System",
    items: [
      { label: "Email Logs", href: "/admin/email-logs", icon: PaperPlaneTilt },
      { label: "Webhooks", href: "/admin/webhooks", icon: WebhooksLogo },
      { label: "Site Settings", href: "/admin/settings", icon: Sliders },
    ],
  },
];

const DEFAULT_COLLAPSED: Record<string, boolean> = {
  website: true,
  system: true,
};
const PREFERENCES_KEY = "isaiah-admin-sidebar-preferences";

interface Props {
  mobileOpen: boolean;
  setMobileOpen: (open: boolean) => void;
}

interface SidebarCounts {
  pendingBookings?: number;
  pendingServices?: number;
  unreadMessages?: number;
  dueRecalls?: number;
}

export default function AdminSidebar({ mobileOpen, setMobileOpen }: Props) {
  const pathname = usePathname();
  const router = useRouter();
  const { user, logout } = useAuth();
  const scrollRef = useRef<HTMLDivElement>(null);
  const mobileDrawerRef = useRef<HTMLElement | null>(null);
  const [collapsed, setCollapsed] = useState<Record<string, boolean>>(DEFAULT_COLLAPSED);
  const [compact, setCompact] = useState(false);
  const [preferencesLoaded, setPreferencesLoaded] = useState(false);
  const [counts, setCounts] = useState<SidebarCounts>({});

  useEffect(() => {
    try {
      const saved = localStorage.getItem(PREFERENCES_KEY);
      if (saved) {
        const preferences = JSON.parse(saved) as { collapsed?: Record<string, boolean>; compact?: boolean };
        if (preferences.collapsed && typeof preferences.collapsed === "object") {
          setCollapsed({ ...DEFAULT_COLLAPSED, ...preferences.collapsed });
        }
        if (typeof preferences.compact === "boolean") setCompact(preferences.compact);
      }
    } catch {
      // Ignore stale or unavailable browser preferences.
    } finally {
      setPreferencesLoaded(true);
    }
  }, []);

  useEffect(() => {
    if (!preferencesLoaded) return;
    try {
      localStorage.setItem(PREFERENCES_KEY, JSON.stringify({ collapsed, compact }));
    } catch {
      // Navigation remains usable if storage is disabled.
    }
  }, [collapsed, compact, preferencesLoaded]);

  const isPathActive = useCallback((href: string) => {
    const path = href.split("?")[0];
    if (path === "/admin") return pathname === "/admin";
    return pathname === path || pathname.startsWith(`${path}/`);
  }, [pathname]);

  const isItemActive = (item: NavItem) => {
    const childIsActive = item.children?.some((child) => isPathActive(child.href)) ?? false;
    return !childIsActive && isPathActive(item.href);
  };

  const groupIsActive = (group: NavGroup) => group.items.some((item) =>
    isPathActive(item.href) || item.children?.some((child) => isPathActive(child.href)),
  );

  useEffect(() => {
    const activeGroup = groups.find(groupIsActive);
    if (activeGroup) {
      setCollapsed((current) => current[activeGroup.id] ? { ...current, [activeGroup.id]: false } : current);
    }
    // Route changes should reopen the section containing the current page.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pathname]);

  const refreshCounts = useCallback(async () => {
    try {
      const [bookingsResponse, pendingServicesResponse, messagesResponse, recallsResponse] = await Promise.all([
        fetch("/api/v1/admin/bookings?per_page=1", { cache: "no-store" }),
        fetch("/api/v1/admin/pending-services?page=1", { cache: "no-store" }),
        fetch("/api/v1/admin/messages?unread=1&page=1", { cache: "no-store" }),
        fetch("/api/v1/admin/recalls?bracket=all&status=pending", { cache: "no-store" }),
      ]);
      const [bookings, pendingServices, messages, recalls] = await Promise.all([
        bookingsResponse.ok ? bookingsResponse.json() : Promise.resolve(null),
        pendingServicesResponse.ok ? pendingServicesResponse.json() : Promise.resolve(null),
        messagesResponse.ok ? messagesResponse.json() : Promise.resolve(null),
        recallsResponse.ok ? recallsResponse.json() : Promise.resolve(null),
      ]);

      setCounts((current) => ({
        ...current,
        ...(Number.isFinite(Number(bookings?.status_counts?.pending))
          ? { pendingBookings: Number(bookings.status_counts.pending) }
          : {}),
        ...(Number.isFinite(Number(pendingServices?.meta?.total))
          ? { pendingServices: Number(pendingServices.meta.total) }
          : {}),
        ...(Number.isFinite(Number(messages?.meta?.unread_count))
          ? { unreadMessages: Number(messages.meta.unread_count) }
          : {}),
        ...(Number.isFinite(Number(recalls?.metrics?.due_3w_count))
          ? { dueRecalls: Number(recalls.metrics.due_3w_count) }
          : {}),
      }));
    } catch {
      // Counts are helpful, but a failed request must not affect navigation.
    }
  }, []);

  useEffect(() => {
    let mounted = true;
    const loadCounts = async () => {
      if (mounted) await refreshCounts();
    };
    void loadCounts();
    const interval = window.setInterval(() => void loadCounts(), 60_000);
    window.addEventListener("focus", loadCounts);
    window.addEventListener("admin-sidebar-refresh", loadCounts);
    return () => {
      mounted = false;
      window.clearInterval(interval);
      window.removeEventListener("focus", loadCounts);
      window.removeEventListener("admin-sidebar-refresh", loadCounts);
    };
  }, [refreshCounts]);

  useEffect(() => {
    if (!mobileOpen) return;
    const previousOverflow = document.body.style.overflow;
    const closeOnEscape = (event: KeyboardEvent) => {
      if (event.key === "Escape") setMobileOpen(false);
    };
    document.body.style.overflow = "hidden";
    window.addEventListener("keydown", closeOnEscape);
    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener("keydown", closeOnEscape);
    };
  }, [mobileOpen, setMobileOpen]);

  useEffect(() => {
    if (!mobileOpen) return;
    const frame = window.requestAnimationFrame(() => {
      mobileDrawerRef.current?.querySelector<HTMLButtonElement>('[aria-label="Close navigation"]')?.focus();
    });
    return () => window.cancelAnimationFrame(frame);
  }, [mobileOpen]);

  const toggleGroup = (id: string) => setCollapsed((current) => ({ ...current, [id]: !current[id] }));

  const handleLogout = () => {
    logout();
    router.push("/login");
  };

  const getBadgeCount = (key?: BadgeKey) => key ? counts[key] ?? 0 : 0;

  const trapMobileFocus = (event: ReactKeyboardEvent<HTMLDivElement>) => {
    if (event.key !== "Tab") return;
    const focusable = mobileDrawerRef.current?.querySelectorAll<HTMLElement>('a[href], button:not([disabled])');
    if (!focusable?.length) return;
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  };

  const renderItem = (item: NavItem, isCompact: boolean, nested = false) => {
    const active = isItemActive(item);
    const Icon = item.icon;
    const badgeCount = getBadgeCount(item.badge);
    const badgeDescription = item.badge === "unreadMessages" ? "unread" : "pending";
    const title = badgeCount > 0 ? `${item.label} · ${badgeCount} ${badgeDescription}` : item.label;

    return (
      <Link
        key={item.href}
        href={item.href}
        onClick={() => setMobileOpen(false)}
        title={isCompact ? title : undefined}
        aria-label={isCompact ? title : undefined}
        aria-current={active ? "page" : undefined}
        className={`group relative flex items-center rounded-lg transition-colors ${
          isCompact
            ? "mx-auto h-10 w-10 justify-center px-0"
            : nested
              ? "gap-2.5 py-1.5 pl-8 pr-2.5 text-xs"
              : "gap-2.5 px-2.5 py-2 text-[13px]"
        } ${
          active
            ? "bg-white/[0.09] font-semibold text-white"
            : "text-gray-400 hover:bg-white/[0.05] hover:text-gray-100"
        }`}
      >
        <Icon
          size={nested ? 14 : 17}
          weight={active ? "fill" : "regular"}
          className={`shrink-0 ${active ? "text-rose-400" : "text-gray-500 group-hover:text-gray-300"}`}
        />
        {!isCompact && <span className="min-w-0 flex-1 truncate">{item.label}</span>}
        {!isCompact && badgeCount > 0 && (
          <span className="min-w-5 rounded-full bg-rose-500/15 px-1.5 py-0.5 text-center text-[10px] font-bold tabular-nums text-rose-300">
            {badgeCount > 99 ? "99+" : badgeCount}
          </span>
        )}
        {isCompact && badgeCount > 0 && <span className="absolute right-1 top-1 h-2 w-2 rounded-full bg-rose-400" />}
      </Link>
    );
  };

  const renderInner = (isCompact: boolean) => (
    <div className="flex h-full flex-col bg-[#0f1117] text-gray-300 select-none">
      <div className={`flex h-16 shrink-0 items-center border-b border-white/[0.06] ${isCompact ? "justify-center px-2" : "justify-between px-4"}`}>
        <Link href="/admin" title={isCompact ? "Isaiah Nail Bar admin" : undefined} className={`flex min-w-0 items-center ${isCompact ? "justify-center" : "gap-3"}`}>
          <Image src="/logo.png" alt="Isaiah Nail Bar" width={32} height={32} className="shrink-0 rounded-lg" />
          {!isCompact && (
            <div className="min-w-0 truncate leading-tight">
              <span className="block truncate text-[13px] font-semibold text-white">Isaiah Nail Bar</span>
              <span className="block text-[10px] font-medium text-gray-500">Admin Console</span>
            </div>
          )}
        </Link>

        <div className="flex items-center gap-1">
          {!mobileOpen && (
            <button
              type="button"
              onClick={() => setCompact((value) => !value)}
              title={isCompact ? "Expand sidebar" : "Collapse sidebar"}
              aria-label={isCompact ? "Expand sidebar" : "Collapse sidebar"}
              className="hidden rounded-md p-1.5 text-gray-500 transition hover:bg-white/[0.06] hover:text-white lg:inline-flex"
            >
              {isCompact ? <CaretRight size={15} /> : <CaretLeft size={15} />}
            </button>
          )}
          {mobileOpen && (
            <button
              type="button"
              onClick={() => setMobileOpen(false)}
              aria-label="Close navigation"
              className="rounded-md p-1.5 text-gray-500 transition hover:bg-white/[0.06] hover:text-white"
            >
              <X size={18} />
            </button>
          )}
        </div>
      </div>

      <nav ref={scrollRef} aria-label="Admin navigation" className={`admin-sidebar-scroll flex-1 space-y-4 overflow-y-auto overscroll-contain py-4 ${isCompact ? "px-2" : "px-3"}`}>
        {groups.map((group, groupIndex) => {
          const isCollapsed = !!collapsed[group.id];
          const visibleItems = isCompact
            ? group.items.flatMap((item) => [item, ...(item.children ?? [])])
            : group.items;
          return (
            <div key={group.id} className={isCompact && groupIndex > 0 ? "border-t border-white/[0.07] pt-3" : ""}>
              {!isCompact && (
                <button
                  type="button"
                  onClick={() => toggleGroup(group.id)}
                  aria-expanded={!isCollapsed}
                  aria-controls={`admin-nav-${group.id}`}
                  className="mb-1 flex w-full items-center justify-between rounded px-2 py-1 text-left text-[10px] font-bold uppercase tracking-[0.15em] text-gray-500 transition hover:text-gray-300"
                >
                  <span>{group.heading}</span>
                  <CaretDown size={12} weight="bold" className={`transition-transform duration-150 ${isCollapsed ? "-rotate-90" : ""}`} />
                </button>
              )}

              <ul id={`admin-nav-${group.id}`} hidden={!isCompact && isCollapsed} className={isCompact ? "space-y-1" : "space-y-0.5"}>
                {visibleItems.map((item) => (
                  <li key={item.href}>
                    {renderItem(item, isCompact, isCompact && group.items.some((parent) => parent.children?.some((child) => child.href === item.href)))}
                    {!isCompact && item.children && (
                      <ul className="mt-0.5 space-y-0.5">
                        {item.children.map((child) => <li key={child.href}>{renderItem(child, false, true)}</li>)}
                      </ul>
                    )}
                  </li>
                ))}
              </ul>
            </div>
          );
        })}
      </nav>

      <footer className="shrink-0 border-t border-white/[0.06]">
        <Link href="/" target="_blank" title={isCompact ? "View live site" : undefined} className={`flex items-center rounded-lg py-2.5 text-[12px] text-gray-500 transition-colors hover:bg-white/[0.04] hover:text-gray-300 ${isCompact ? "mx-auto h-10 w-10 justify-center px-0" : "gap-2 px-4"}`}>
          <ArrowSquareOut size={15} />
          {!isCompact && <span>View live site</span>}
        </Link>
        <div className={`flex items-center border-t border-white/[0.06] py-3 ${isCompact ? "flex-col justify-center gap-2 px-2" : "gap-3 px-4"}`}>
          <div className="relative shrink-0">
            <div className="flex h-8 w-8 items-center justify-center rounded-full bg-gray-800 text-xs font-semibold text-white">{user?.name?.charAt(0).toUpperCase() || "A"}</div>
            <span className="absolute -bottom-px -right-px h-2.5 w-2.5 rounded-full bg-emerald-400 ring-2 ring-[#0f1117]" />
          </div>
          {!isCompact && (
            <div className="min-w-0 flex-1">
              <p className="truncate text-[13px] font-medium text-white">{user?.name || "Admin"}</p>
              <p className="truncate text-[11px] text-gray-500">{user?.email || "admin@isaiahnailbar.com"}</p>
            </div>
          )}
          <button onClick={handleLogout} title="Sign out" aria-label="Sign out" className="rounded-md p-1.5 text-gray-500 transition-colors hover:bg-white/[0.05] hover:text-rose-400">
            <SignOut size={16} />
          </button>
        </div>
      </footer>
    </div>
  );

  return (
    <>
      <aside className={`hidden h-screen shrink-0 transition-[width] duration-200 lg:block ${compact ? "w-[72px]" : "w-[260px]"}`}>
        {renderInner(compact)}
      </aside>

      {mobileOpen && (
        <div className="fixed inset-0 z-50 lg:hidden" onKeyDown={trapMobileFocus}>
          <button type="button" aria-label="Close navigation" className="absolute inset-0 bg-black/60" onClick={() => setMobileOpen(false)} />
          <aside ref={mobileDrawerRef} className="relative h-full w-[290px] max-w-[88vw] shadow-2xl">
            {renderInner(false)}
          </aside>
        </div>
      )}
    </>
  );
}
