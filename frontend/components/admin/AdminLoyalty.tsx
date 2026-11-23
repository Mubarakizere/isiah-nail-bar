"use client";

import { useCallback, useEffect, useState } from "react";
import {
  Gift,
  Crown,
  Sparkle,
  Wine,
  Ticket,
  MagnifyingGlass,
  ArrowsClockwise,
  PlusCircle,
  MinusCircle,
  CheckCircle,
  WarningCircle,
  X,
  Funnel,
  User,
  Phone,
  EnvelopeSimple,
  Check,
} from "@phosphor-icons/react";
import AdminHeader from "@/components/admin/AdminHeader";

interface CustomerReward {
  id: number;
  customer_id: number;
  loyalty_reward_id: number;
  voucher_code: string;
  status: "unclaimed" | "redeemed";
  unlocked_at: string;
  redeemed_at: string | null;
  notes: string | null;
  reward?: {
    id: number;
    title: string;
    description: string | null;
    reward_type: string;
    discount_percentage: number | null;
    icon: string;
  };
}

interface ClientCardItem {
  id: number;
  customer_id: number;
  current_stamps: number;
  lifetime_stamps: number;
  card_cycle: number;
  completed_at: string | null;
  created_at: string;
  customer?: {
    id: number;
    name: string;
    phone: string;
    email: string;
    rewards?: CustomerReward[];
  };
}

interface LoyaltyMetrics {
  total_cards: number;
  unclaimed_vouchers: number;
  redeemed_vouchers: number;
  vip_clients: number;
  max_stamps: number;
}

export default function AdminLoyalty() {
  const [cards, setCards] = useState<ClientCardItem[]>([]);
  const [metrics, setMetrics] = useState<LoyaltyMetrics>({
    total_cards: 0,
    unclaimed_vouchers: 0,
    redeemed_vouchers: 0,
    vip_clients: 0,
    max_stamps: 8,
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Search & Filter
  const [search, setSearch] = useState("");
  const [filter, setFilter] = useState<"all" | "ready_for_reward" | "completed" | "vip">("all");
  const [page, setPage] = useState(1);
  const [meta, setMeta] = useState({ current_page: 1, last_page: 1, total: 0 });

  // Quick Voucher Redeem State
  const [quickCode, setQuickCode] = useState("");
  const [quickRedeeming, setQuickRedeeming] = useState(false);

  // Stamp Adjustment Modal
  const [adjustTargetCard, setAdjustTargetCard] = useState<ClientCardItem | null>(null);
  const [adjustChange, setAdjustChange] = useState<number>(1);
  const [adjustReason, setAdjustReason] = useState<string>("Bespoke concierge loyalty credit");
  const [adjusting, setAdjusting] = useState(false);

  // Client Vouchers Modal
  const [voucherModalCard, setVoucherModalCard] = useState<ClientCardItem | null>(null);
  const [redeemingVoucherId, setRedeemingVoucherId] = useState<number | null>(null);
  const [redeemNotes, setRedeemNotes] = useState("");

  // Toast feedback
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(null);

  const showToast = (message: string, type: "success" | "error" = "success") => {
    setToast({ message, type });
    setTimeout(() => setToast(null), 4000);
  };

  const fetchCards = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const params = new URLSearchParams();
      if (search.trim()) params.set("search", search.trim());
      if (filter !== "all") params.set("filter", filter);
      params.set("page", String(page));

      const res = await fetch(`/api/v1/admin/loyalty/cards?${params.toString()}`, {
        cache: "no-store",
      });
      const data = await res.json();

      if (res.ok && data.success) {
        setCards(data.data || []);
        if (data.metrics) setMetrics(data.metrics);
        if (data.meta) setMeta(data.meta);
      } else {
        setError(data.message || "Failed to load loyalty cards.");
      }
    } catch {
      setError("Unable to connect to salon database.");
    } finally {
      setLoading(false);
    }
  }, [search, filter, page]);

  useEffect(() => {
    fetchCards();
  }, [fetchCards]);

  // Quick redeem voucher by typing or scanning code
  const handleQuickRedeem = async (e: React.FormEvent) => {
    e.preventDefault();
    const code = quickCode.trim().toUpperCase();
    if (!code) return;

    setQuickRedeeming(true);
    try {
      const res = await fetch(`/api/v1/admin/loyalty/vouchers/${encodeURIComponent(code)}/redeem`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ notes: "Redeemed via concierge quick desk" }),
      });
      const data = await res.json();

      if (res.ok && data.success) {
        showToast(data.message || `Voucher ${code} successfully redeemed!`);
        setQuickCode("");
        fetchCards();
      } else {
        showToast(data.message || "Failed to redeem voucher.", "error");
      }
    } catch {
      showToast("Network error while redeeming voucher.", "error");
    } finally {
      setQuickRedeeming(false);
    }
  };

  // Adjust stamps modal submit
  const handleAdjustSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!adjustTargetCard || !adjustTargetCard.customer_id) return;

    setAdjusting(true);
    try {
      const res = await fetch(`/api/v1/admin/loyalty/cards/${adjustTargetCard.customer_id}/adjust`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          change: adjustChange,
          reason: adjustReason.trim() || "Manual adjustment by staff",
        }),
      });
      const data = await res.json();

      if (res.ok && data.success) {
        showToast(data.message || "Stamps updated successfully.");
        setAdjustTargetCard(null);
        fetchCards();
      } else {
        showToast(data.message || "Failed to adjust stamps.", "error");
      }
    } catch {
      showToast("Network error while adjusting stamps.", "error");
    } finally {
      setAdjusting(false);
    }
  };

  // Redeem voucher from card modal
  const handleRedeemVoucher = async (voucher: CustomerReward) => {
    setRedeemingVoucherId(voucher.id);
    try {
      const res = await fetch(`/api/v1/admin/loyalty/vouchers/${encodeURIComponent(voucher.voucher_code)}/redeem`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ notes: redeemNotes.trim() || null }),
      });
      const data = await res.json();

      if (res.ok && data.success) {
        showToast(data.message || "Reward voucher redeemed successfully.");
        setRedeemNotes("");
        setVoucherModalCard(null);
        fetchCards();
      } else {
        showToast(data.message || "Could not redeem voucher.", "error");
      }
    } catch {
      showToast("Network error while redeeming voucher.", "error");
    } finally {
      setRedeemingVoucherId(null);
    }
  };

  return (
    <div className="min-h-screen bg-[#FAF7F2] text-[#221C1A]">
      <AdminHeader
        title="Loyalty & Stamp Cards"
        subtitle="Digital 8-visit stamp passports, wine perks, and client reward vouchers"
      />

      {/* Global Toast */}
      {toast && (
        <div
          className={`fixed bottom-6 right-6 z-50 px-5 py-3 rounded-2xl shadow-xl flex items-center gap-3 text-xs font-semibold ${
            toast.type === "success"
              ? "bg-[#E6F4EA] border border-[#34A853]/40 text-[#2D8E47]"
              : "bg-[#FDEDEC] border border-[#F5B7B1] text-[#C0392B]"
          }`}
        >
          {toast.type === "success" ? <CheckCircle size={18} weight="fill" /> : <WarningCircle size={18} weight="fill" />}
          <span>{toast.message}</span>
        </div>
      )}

      <div className="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">
        {/* ========================================================= */}
        {/* 1. TOP METRICS & QUICK REDEEM TOOL                        */}
        {/* ========================================================= */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div className="p-5 rounded-2xl bg-white border border-[#EAE4D9] shadow-2xs space-y-2">
            <div className="flex items-center justify-between text-[#8E857F]">
              <span className="text-[10px] font-bold uppercase tracking-wider">Active Passports</span>
              <Gift size={18} className="text-[#B8975A]" />
            </div>
            <p className="font-serif text-3xl font-normal text-[#221C1A]">{metrics.total_cards}</p>
            <p className="text-[11px] text-[#6E6660]">Registered client stamp cards</p>
          </div>

          <div className="p-5 rounded-2xl bg-white border border-[#EAE4D9] shadow-2xs space-y-2">
            <div className="flex items-center justify-between text-[#8E857F]">
              <span className="text-[10px] font-bold uppercase tracking-wider">Unclaimed Perks</span>
              <Ticket size={18} className="text-[#8C6D37]" />
            </div>
            <p className="font-serif text-3xl font-normal text-[#8C6D37]">{metrics.unclaimed_vouchers}</p>
            <p className="text-[11px] text-[#6E6660]">Ready for salon redemption</p>
          </div>

          <div className="p-5 rounded-2xl bg-white border border-[#EAE4D9] shadow-2xs space-y-2">
            <div className="flex items-center justify-between text-[#8E857F]">
              <span className="text-[10px] font-bold uppercase tracking-wider">Redeemed Privileges</span>
              <Wine size={18} className="text-[#34A853]" />
            </div>
            <p className="font-serif text-3xl font-normal text-[#221C1A]">{metrics.redeemed_vouchers}</p>
            <p className="text-[11px] text-[#6E6660]">Wine, discounts & free sets served</p>
          </div>

          <div className="p-5 rounded-2xl bg-white border border-[#EAE4D9] shadow-2xs space-y-2">
            <div className="flex items-center justify-between text-[#8E857F]">
              <span className="text-[10px] font-bold uppercase tracking-wider">VIP Repeat Clients</span>
              <Crown size={18} className="text-[#B8975A]" />
            </div>
            <p className="font-serif text-3xl font-normal text-[#B8975A]">{metrics.vip_clients}</p>
            <p className="text-[11px] text-[#6E6660]">Completed 8+ visits</p>
          </div>
        </div>

        {/* Quick Redeem Bar */}
        <div className="p-5 rounded-2xl bg-[#221C1A] text-white flex flex-col md:flex-row md:items-center md:justify-between gap-4 shadow-md">
          <div className="space-y-0.5">
            <h3 className="font-serif text-lg text-[#FAF7F2] flex items-center gap-2">
              <Ticket size={20} className="text-[#E8C99B]" />
              <span>Front Desk Voucher Redemption Desk</span>
            </h3>
            <p className="text-xs text-[#A99D95]">
              Paste or type client voucher code (e.g. <span className="font-mono text-[#E8C99B]">REW-WINE-XXXXX</span>) to redeem perks instantly.
            </p>
          </div>

          <form onSubmit={handleQuickRedeem} className="flex items-center gap-2 w-full md:w-auto">
            <input
              type="text"
              placeholder="e.g. REW-WINE-7A9B2"
              value={quickCode}
              onChange={(e) => setQuickCode(e.target.value)}
              className="px-4 py-2 bg-black/40 border border-[#C5A059]/40 rounded-xl text-xs text-white placeholder-[#7A716B] font-mono uppercase tracking-wider outline-none focus:border-[#E8C99B] w-full md:w-56"
            />
            <button
              type="submit"
              disabled={quickRedeeming || !quickCode.trim()}
              className="px-4 py-2 rounded-xl text-xs font-semibold bg-[#B8975A] hover:bg-[#C5A059] text-[#1E1715] transition-colors cursor-pointer shrink-0 disabled:opacity-50"
            >
              {quickRedeeming ? "Verifying..." : "Redeem Perk"}
            </button>
          </form>
        </div>

        {/* ========================================================= */}
        {/* 2. SEARCH & FILTER CONTROLS                               */}
        {/* ========================================================= */}
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-4 rounded-2xl border border-[#EAE4D9]">
          <div className="relative flex-1 max-w-md">
            <MagnifyingGlass size={16} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8E857F]" />
            <input
              type="text"
              placeholder="Search by client name, Rwandan phone, or email..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="w-full pl-9 pr-4 py-2 text-xs bg-[#FAF7F2] border border-[#EAE4D9] rounded-xl outline-none focus:border-[#221C1A] text-[#221C1A]"
            />
          </div>

          <div className="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
            <button
              type="button"
              onClick={() => { setFilter("all"); setPage(1); }}
              className={`px-3 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-colors ${
                filter === "all" ? "bg-[#221C1A] text-white" : "bg-[#FAF7F2] text-[#6E6660] hover:text-[#221C1A]"
              }`}
            >
              All Clients
            </button>
            <button
              type="button"
              onClick={() => { setFilter("ready_for_reward"); setPage(1); }}
              className={`px-3 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-colors ${
                filter === "ready_for_reward" ? "bg-[#221C1A] text-white" : "bg-[#FAF7F2] text-[#6E6660] hover:text-[#221C1A]"
              }`}
            >
              🍷 Ready for Reward (≥3)
            </button>
            <button
              type="button"
              onClick={() => { setFilter("completed"); setPage(1); }}
              className={`px-3 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-colors ${
                filter === "completed" ? "bg-[#221C1A] text-white" : "bg-[#FAF7F2] text-[#6E6660] hover:text-[#221C1A]"
              }`}
            >
              👑 Completed Card (8)
            </button>
            <button
              type="button"
              onClick={() => { setFilter("vip"); setPage(1); }}
              className={`px-3 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-colors ${
                filter === "vip" ? "bg-[#221C1A] text-white" : "bg-[#FAF7F2] text-[#6E6660] hover:text-[#221C1A]"
              }`}
            >
              ✨ VIP Retainers
            </button>
            <button
              type="button"
              onClick={fetchCards}
              className="p-2 rounded-xl text-[#8E857F] hover:text-[#221C1A] hover:bg-[#FAF7F2] transition-colors"
              title="Refresh"
            >
              <ArrowsClockwise size={16} className={loading ? "animate-spin" : ""} />
            </button>
          </div>
        </div>

        {/* ========================================================= */}
        {/* 3. CLIENT LOYALTY PASSPORTS TABLE                         */}
        {/* ========================================================= */}
        <div className="bg-white rounded-2xl border border-[#EAE4D9] overflow-hidden shadow-2xs">
          {loading ? (
            <div className="p-16 flex flex-col items-center justify-center gap-3">
              <ArrowsClockwise size={26} className="animate-spin text-[#B8975A]" />
              <p className="font-serif text-sm text-[#6E6660]">Loading client loyalty passports...</p>
            </div>
          ) : error ? (
            <div className="p-12 text-center space-y-3">
              <WarningCircle size={28} className="text-[#C0392B] mx-auto" />
              <p className="text-xs text-[#C0392B]">{error}</p>
              <button
                type="button"
                onClick={fetchCards}
                className="px-4 py-2 rounded-full text-xs font-semibold bg-[#221C1A] text-white hover:bg-[#B8975A]"
              >
                Retry
              </button>
            </div>
          ) : cards.length === 0 ? (
            <div className="p-16 text-center space-y-2">
              <Gift size={28} className="text-[#8E857F] mx-auto" />
              <p className="font-serif text-base text-[#221C1A]">No Loyalty Passports Found</p>
              <p className="text-xs text-[#6E6660]">Try adjusting your search query or filter.</p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead className="bg-[#FAF7F2] border-b border-[#EAE4D9] text-[#8E857F] uppercase tracking-wider text-[10px] font-bold">
                  <tr>
                    <th className="py-3 px-4">Client</th>
                    <th className="py-3 px-4">Current Stamp Card</th>
                    <th className="py-3 px-4">Lifetime Visits</th>
                    <th className="py-3 px-4">Active Vouchers</th>
                    <th className="py-3 px-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-[#FAF7F2]">
                  {cards.map((card) => {
                    const client = card.customer;
                    const unclaimed = client?.rewards?.filter((r) => r.status === "unclaimed") || [];

                    return (
                      <tr key={card.id} className="hover:bg-[#FAF7F2]/60 transition-colors">
                        {/* Client details */}
                        <td className="py-3.5 px-4">
                          <div className="space-y-0.5">
                            <p className="font-semibold text-sm text-[#221C1A]">
                              {client?.name || `Customer #${card.customer_id}`}
                            </p>
                            <div className="flex items-center gap-3 text-[11px] text-[#6E6660]">
                              {client?.phone && (
                                <span className="flex items-center gap-1 font-mono">
                                  <Phone size={12} className="text-[#B8975A]" />
                                  {client.phone}
                                </span>
                              )}
                              {client?.email && (
                                <span className="flex items-center gap-1">
                                  <EnvelopeSimple size={12} />
                                  {client.email}
                                </span>
                              )}
                            </div>
                          </div>
                        </td>

                        {/* Stamp progress */}
                        <td className="py-3.5 px-4">
                          <div className="space-y-1.5 w-44">
                            <div className="flex items-center justify-between text-[11px]">
                              <span className="font-bold text-[#221C1A]">
                                {card.current_stamps} / {metrics.max_stamps} Stamps
                              </span>
                              <span className="text-[10px] text-[#8E857F]">
                                Cycle #{card.card_cycle}
                              </span>
                            </div>
                            <div className="w-full h-2 rounded-full bg-[#EAE4D9] overflow-hidden">
                              <div
                                className="h-full bg-gradient-to-r from-[#B8975A] to-[#8C6D37] rounded-full transition-all"
                                style={{ width: `${(card.current_stamps / metrics.max_stamps) * 100}%` }}
                              />
                            </div>
                          </div>
                        </td>

                        {/* Lifetime visits */}
                        <td className="py-3.5 px-4">
                          <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#FAF7F2] border border-[#EAE4D9] text-xs font-semibold text-[#221C1A]">
                            <Crown size={13} className="text-[#B8975A]" weight="fill" />
                            {card.lifetime_stamps} visits
                          </span>
                        </td>

                        {/* Active Vouchers */}
                        <td className="py-3.5 px-4">
                          {unclaimed.length > 0 ? (
                            <button
                              type="button"
                              onClick={() => setVoucherModalCard(card)}
                              className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#FAF0E4] border border-[#E8C99B] text-[#8C6D37] text-xs font-bold hover:bg-[#E8C99B]/30 transition-colors cursor-pointer"
                            >
                              <Gift size={13} weight="fill" />
                              <span>{unclaimed.length} Unclaimed Reward{unclaimed.length > 1 ? "s" : ""}</span>
                            </button>
                          ) : (
                            <span className="text-[11px] text-[#8E857F]">No active vouchers</span>
                          )}
                        </td>

                        {/* Actions */}
                        <td className="py-3.5 px-4 text-right">
                          <div className="flex items-center justify-end gap-2">
                            <button
                              type="button"
                              onClick={() => {
                                setAdjustTargetCard(card);
                                setAdjustChange(1);
                                setAdjustReason("Concierge loyalty stamp award");
                              }}
                              className="px-3 py-1.5 rounded-full text-xs font-semibold bg-[#221C1A] text-white hover:bg-[#B8975A] transition-colors cursor-pointer"
                            >
                              Adjust Stamps
                            </button>

                            {unclaimed.length > 0 && (
                              <button
                                type="button"
                                onClick={() => setVoucherModalCard(card)}
                                className="px-3 py-1.5 rounded-full text-xs font-semibold bg-[#FAF0E4] text-[#8C6D37] border border-[#E8C99B] hover:bg-[#E8C99B]/30 transition-colors cursor-pointer"
                              >
                                Redeem Perk
                              </button>
                            )}
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          )}

          {/* Pagination */}
          {meta.last_page > 1 && (
            <div className="p-4 bg-[#FAF7F2] border-t border-[#EAE4D9] flex items-center justify-between text-xs text-[#6E6660]">
              <span>
                Showing page {meta.current_page} of {meta.last_page} ({meta.total} clients)
              </span>
              <div className="flex items-center gap-2">
                <button
                  type="button"
                  disabled={page <= 1}
                  onClick={() => setPage((p) => Math.max(1, p - 1))}
                  className="px-3 py-1.5 rounded-lg bg-white border border-[#EAE4D9] disabled:opacity-40 hover:bg-[#FAF7F2]"
                >
                  Previous
                </button>
                <button
                  type="button"
                  disabled={page >= meta.last_page}
                  onClick={() => setPage((p) => Math.min(meta.last_page, p + 1))}
                  className="px-3 py-1.5 rounded-lg bg-white border border-[#EAE4D9] disabled:opacity-40 hover:bg-[#FAF7F2]"
                >
                  Next
                </button>
              </div>
            </div>
          )}
        </div>
      </div>

      {/* ========================================================= */}
      {/* MODAL 1: ADJUST CLIENT STAMPS                             */}
      {/* ========================================================= */}
      {adjustTargetCard && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
          <div className="w-full max-w-md bg-white rounded-3xl p-6 shadow-2xl space-y-5">
            <div className="flex items-center justify-between pb-3 border-b border-[#FAF7F2]">
              <div className="space-y-0.5">
                <h3 className="font-serif text-lg text-[#221C1A]">Adjust Client Stamps</h3>
                <p className="text-xs text-[#6E6660]">
                  {adjustTargetCard.customer?.name} &bull; Current: {adjustTargetCard.current_stamps}/8 stamps
                </p>
              </div>
              <button
                type="button"
                onClick={() => setAdjustTargetCard(null)}
                className="p-1 rounded-full text-[#8E857F] hover:text-[#221C1A] cursor-pointer"
              >
                <X size={18} />
              </button>
            </div>

            <form onSubmit={handleAdjustSubmit} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                  Stamp Action
                </label>
                <div className="grid grid-cols-2 gap-2">
                  <button
                    type="button"
                    onClick={() => setAdjustChange(1)}
                    className={`py-2 px-3 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition-all ${
                      adjustChange > 0
                        ? "bg-[#221C1A] text-white shadow-xs"
                        : "bg-[#FAF7F2] text-[#6E6660] border border-[#EAE4D9]"
                    }`}
                  >
                    <PlusCircle size={15} />
                    <span>Award +1 Stamp</span>
                  </button>
                  <button
                    type="button"
                    onClick={() => setAdjustChange(-1)}
                    className={`py-2 px-3 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition-all ${
                      adjustChange < 0
                        ? "bg-[#C0392B] text-white shadow-xs"
                        : "bg-[#FAF7F2] text-[#6E6660] border border-[#EAE4D9]"
                    }`}
                  >
                    <MinusCircle size={15} />
                    <span>Deduct -1 Stamp</span>
                  </button>
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                  Custom Quantity
                </label>
                <input
                  type="number"
                  min="-8"
                  max="8"
                  value={adjustChange}
                  onChange={(e) => setAdjustChange(parseInt(e.target.value) || 1)}
                  className="w-full px-3 py-2 bg-[#FAF7F2] border border-[#EAE4D9] rounded-xl text-xs font-bold text-[#221C1A] outline-none"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                  Reason for Adjustment
                </label>
                <input
                  type="text"
                  required
                  value={adjustReason}
                  onChange={(e) => setAdjustReason(e.target.value)}
                  placeholder="e.g. Birthday reward, walk-in adjustment"
                  className="w-full px-3 py-2 bg-[#FAF7F2] border border-[#EAE4D9] rounded-xl text-xs text-[#221C1A] outline-none"
                />
              </div>

              <div className="grid grid-cols-2 gap-3 pt-2">
                <button
                  type="button"
                  onClick={() => setAdjustTargetCard(null)}
                  className="py-2.5 rounded-full text-xs font-semibold bg-[#FAF7F2] text-[#6E6660] hover:bg-[#EAE4D9]"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={adjusting}
                  className="py-2.5 rounded-full text-xs font-semibold bg-[#221C1A] text-white hover:bg-[#B8975A] disabled:opacity-50"
                >
                  {adjusting ? "Updating..." : "Confirm Update"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* ========================================================= */}
      {/* MODAL 2: CLIENT VOUCHERS LIST & REDEEM                    */}
      {/* ========================================================= */}
      {voucherModalCard && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
          <div className="w-full max-w-lg bg-white rounded-3xl p-6 shadow-2xl space-y-5">
            <div className="flex items-center justify-between pb-3 border-b border-[#FAF7F2]">
              <div className="space-y-0.5">
                <h3 className="font-serif text-lg text-[#221C1A]">Customer Reward Vouchers</h3>
                <p className="text-xs text-[#6E6660]">
                  {voucherModalCard.customer?.name} &bull; {voucherModalCard.customer?.phone}
                </p>
              </div>
              <button
                type="button"
                onClick={() => setVoucherModalCard(null)}
                className="p-1 rounded-full text-[#8E857F] hover:text-[#221C1A] cursor-pointer"
              >
                <X size={18} />
              </button>
            </div>

            <div className="space-y-3 max-h-96 overflow-y-auto">
              {voucherModalCard.customer?.rewards && voucherModalCard.customer.rewards.length > 0 ? (
                voucherModalCard.customer.rewards.map((voucher) => (
                  <div
                    key={voucher.id}
                    className={`p-4 rounded-2xl border transition-all space-y-3 ${
                      voucher.status === "unclaimed"
                        ? "bg-[#FAF7F2] border-[#E8C99B]"
                        : "bg-white border-[#EAE4D9] opacity-70"
                    }`}
                  >
                    <div className="flex items-start justify-between gap-3">
                      <div>
                        <div className="flex items-center gap-2">
                          <span className="font-mono text-xs font-bold text-[#221C1A]">
                            {voucher.voucher_code}
                          </span>
                          <span
                            className={`px-2 py-0.5 rounded-full text-[9px] font-bold uppercase ${
                              voucher.status === "unclaimed"
                                ? "bg-[#FAF0E4] text-[#8C6D37]"
                                : "bg-[#E6F4EA] text-[#2D8E47]"
                            }`}
                          >
                            {voucher.status}
                          </span>
                        </div>
                        <h4 className="font-serif text-sm text-[#221C1A] font-medium mt-1">
                          {voucher.reward?.title || "Loyalty Perk"}
                        </h4>
                        <p className="text-[11px] text-[#6E6660] mt-0.5">
                          {voucher.reward?.description}
                        </p>
                      </div>

                      {voucher.status === "unclaimed" ? (
                        <button
                          type="button"
                          disabled={redeemingVoucherId === voucher.id}
                          onClick={() => handleRedeemVoucher(voucher)}
                          className="px-3 py-1.5 rounded-full text-xs font-semibold bg-[#221C1A] text-white hover:bg-[#B8975A] transition-colors shrink-0 disabled:opacity-50 cursor-pointer"
                        >
                          {redeemingVoucherId === voucher.id ? "Redeeming..." : "Mark Redeemed"}
                        </button>
                      ) : (
                        <div className="text-right text-[10px] text-[#8E857F]">
                          <span>Redeemed on {new Date(voucher.redeemed_at || "").toLocaleDateString()}</span>
                        </div>
                      )}
                    </div>
                  </div>
                ))
              ) : (
                <div className="p-8 text-center text-xs text-[#8E857F]">
                  No reward vouchers recorded for this client.
                </div>
              )}
            </div>

            <div className="pt-2">
              <button
                type="button"
                onClick={() => setVoucherModalCard(null)}
                className="w-full py-2.5 rounded-full text-xs font-semibold bg-[#FAF7F2] text-[#221C1A] hover:bg-[#EAE4D9]"
              >
                Close Desk
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
