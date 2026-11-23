"use client";

import { useEffect, useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import {
  Wine,
  Sparkle,
  Ticket,
  Gift,
  Copy,
  Check,
  CheckCircle,
  Crown,
  Clock,
  ArrowClockwise,
  WarningCircle,
  Tag,
  ShieldCheck,
} from "@phosphor-icons/react";

interface LoyaltyCardData {
  id: number;
  customer_id: number;
  customer_name: string;
  current_stamps: number;
  max_stamps: number;
  lifetime_stamps: number;
  card_cycle: number;
  completed_at: string | null;
}

interface LoyaltyRewardTier {
  id: number;
  title: string;
  description: string | null;
  stamps_required: number;
  reward_type: string;
  discount_percentage: number | null;
  icon: string;
  is_active: boolean;
}

interface CustomerRewardItem {
  id: number;
  voucher_code: string;
  status: "unclaimed" | "redeemed";
  unlocked_at: string;
  redeemed_at: string | null;
  notes?: string | null;
  reward?: {
    title: string;
    description: string | null;
    reward_type: string;
    discount_percentage: number | null;
    icon: string;
  };
}

interface LoyaltyLogItem {
  id: number;
  action: string;
  change: number;
  reason: string;
  created_at: string;
}

export default function ClientLoyaltyCard() {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [card, setCard] = useState<LoyaltyCardData | null>(null);
  const [tiers, setTiers] = useState<LoyaltyRewardTier[]>([]);
  const [vouchers, setVouchers] = useState<CustomerRewardItem[]>([]);
  const [logs, setLogs] = useState<LoyaltyLogItem[]>([]);
  const [copiedCode, setCopiedCode] = useState<string | null>(null);

  const fetchLoyaltyData = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await fetch("/api/v1/customer/loyalty", {
        cache: "no-store",
        credentials: "same-origin",
      });
      const data = await res.json();

      if (res.ok && data.success) {
        setCard(data.data.card);
        setTiers(data.data.tiers || []);
        setVouchers(data.data.vouchers || []);
        setLogs(data.data.logs || []);
      } else {
        setError(data.message || "Failed to load loyalty card details.");
      }
    } catch {
      setError("Unable to connect to salon rewards server. Please try again.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchLoyaltyData();
  }, []);

  const handleCopyCode = async (code: string) => {
    try {
      await navigator.clipboard.writeText(code);
      setCopiedCode(code);
      setTimeout(() => setCopiedCode(null), 2500);
    } catch {
      // Fallback
      setCopiedCode(code);
      setTimeout(() => setCopiedCode(null), 2500);
    }
  };

  if (loading) {
    return (
      <div className="p-12 rounded-3xl bg-white border border-[#EAE4D9] flex flex-col items-center justify-center gap-3">
        <ArrowClockwise size={28} className="animate-spin text-[#B8975A]" />
        <p className="font-serif text-sm text-[#6E6660]">Loading your VIP Loyalty Passport...</p>
      </div>
    );
  }

  if (error || !card) {
    return (
      <div className="p-8 rounded-3xl bg-[#FDEDEC] border border-[#F5B7B1] text-center space-y-4">
        <div className="w-12 h-12 rounded-full bg-white text-[#C0392B] mx-auto flex items-center justify-center">
          <WarningCircle size={24} weight="fill" />
        </div>
        <div>
          <h3 className="font-serif text-lg text-[#221C1A]">Loyalty Passport Unavailable</h3>
          <p className="text-xs text-[#6E6660] mt-1">{error || "Please check back shortly."}</p>
        </div>
        <button
          type="button"
          onClick={fetchLoyaltyData}
          className="px-5 py-2.5 rounded-full text-xs font-semibold bg-[#221C1A] text-white hover:bg-[#B8975A] transition-colors"
        >
          Try Again
        </button>
      </div>
    );
  }

  const currentStamps = card.current_stamps;
  const maxStamps = card.max_stamps || 8;
  const unclaimedVouchers = vouchers.filter((v) => v.status === "unclaimed");
  const redeemedVouchers = vouchers.filter((v) => v.status === "redeemed");

  // Determine next milestone
  const nextMilestone =
    currentStamps < 3
      ? { stamps: 3, perk: "Complimentary Wine & Scrub" }
      : currentStamps < 6
      ? { stamps: 6, perk: "20% Off Treatment" }
      : currentStamps < 8
      ? { stamps: 8, perk: "Free Deluxe Gel Set / Pedicure" }
      : null;

  return (
    <div className="space-y-8">
      {/* ========================================================= */}
      {/* 1. LUXURY DIGITAL STAMP PASSPORT CARD                     */}
      {/* ========================================================= */}
      <div className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#1E1715] via-[#2A211D] to-[#161210] border-2 border-[#C5A059]/40 shadow-2xl p-6 sm:p-10 text-white">
        {/* Subtle decorative gold foil glow */}
        <div className="pointer-events-none absolute -top-24 -right-24 w-80 h-80 rounded-full bg-[#B8975A]/15 blur-3xl" />
        <div className="pointer-events-none absolute -bottom-24 -left-24 w-80 h-80 rounded-full bg-[#E8C99B]/10 blur-3xl" />

        <div className="relative z-10 space-y-8">
          {/* Passport Top Banner */}
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#C5A059]/20">
            <div className="space-y-1">
              <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#C5A059]/15 border border-[#C5A059]/40 text-[#E8C99B] text-[11px] font-semibold tracking-widest uppercase">
                <Crown size={13} weight="fill" />
                <span>Isaiah VIP Sanctuary Passport</span>
              </div>
              <h2 className="font-serif text-2xl sm:text-3xl text-[#FAF7F2] font-normal tracking-wide">
                {card.customer_name}
              </h2>
              <p className="text-xs text-[#A99D95]">
                Card Cycle #{card.card_cycle} &bull; {card.lifetime_stamps} Lifetime Salon Visits
              </p>
            </div>

            <div className="flex items-center gap-3 self-start sm:self-auto">
              <div className="px-4 py-2.5 rounded-2xl bg-black/40 border border-[#C5A059]/30 text-right">
                <span className="text-[10px] uppercase font-semibold text-[#C5A059] block">
                  Current Balance
                </span>
                <span className="font-serif text-2xl font-bold text-[#FAF7F2]">
                  {currentStamps} <span className="text-sm font-normal text-[#A99D95]">/ {maxStamps}</span>
                </span>
              </div>
            </div>
          </div>

          {/* 8-Stamp Visual Grid */}
          <div>
            <div className="flex items-center justify-between mb-4">
              <span className="text-xs uppercase font-semibold tracking-widest text-[#C5A059]">
                8-Visit Reward Grid
              </span>
              <span className="text-xs text-[#A99D95]">
                {nextMilestone
                  ? `${nextMilestone.stamps - currentStamps} visit${nextMilestone.stamps - currentStamps > 1 ? "s" : ""} to ${nextMilestone.perk}`
                  : "🎉 Card Cycle Completed! Ready for full reward."}
              </span>
            </div>

            <div className="grid grid-cols-4 sm:grid-cols-8 gap-3 sm:gap-4">
              {Array.from({ length: maxStamps }, (_, idx) => {
                const stampNum = idx + 1;
                const isEarned = stampNum <= currentStamps;
                const isMilestone = stampNum === 3 || stampNum === 6 || stampNum === 8;

                return (
                  <motion.div
                    key={stampNum}
                    initial={{ scale: 0.9, opacity: 0 }}
                    animate={{ scale: 1, opacity: 1 }}
                    transition={{ delay: idx * 0.05 }}
                    className={`relative aspect-square rounded-2xl flex flex-col items-center justify-center p-2 transition-all ${
                      isEarned
                        ? "bg-gradient-to-br from-[#E8C99B] via-[#C5A059] to-[#8C6D37] text-[#1E1715] shadow-lg shadow-[#C5A059]/30 border border-[#FAF0E4]/60"
                        : "bg-white/5 border-2 border-dashed border-[#C5A059]/30 text-[#8E857F] hover:border-[#C5A059]/60"
                    }`}
                  >
                    {/* Milestone pill badge */}
                    {isMilestone && (
                      <span
                        className={`absolute -top-2 px-1.5 py-0.5 rounded-full text-[9px] font-bold tracking-tight uppercase ${
                          isEarned
                            ? "bg-[#1E1715] text-[#E8C99B] border border-[#E8C99B]/40"
                            : "bg-[#C5A059]/20 text-[#E8C99B] border border-[#C5A059]/30"
                        }`}
                      >
                        {stampNum === 3 ? "🍷 Wine" : stampNum === 6 ? "✨ 20%" : "👑 Free"}
                      </span>
                    )}

                    {isEarned ? (
                      <div className="flex flex-col items-center justify-center text-center">
                        <Sparkle size={24} weight="fill" className="text-[#1E1715] drop-shadow-sm" />
                        <span className="text-[10px] font-bold uppercase tracking-wider mt-1 text-[#1E1715]">
                          Stamp #{stampNum}
                        </span>
                      </div>
                    ) : (
                      <div className="flex flex-col items-center justify-center text-center">
                        <span className="font-serif text-lg font-light text-[#A99D95]">
                          {stampNum}
                        </span>
                        <span className="text-[9px] font-medium uppercase tracking-tight text-[#7A716B]">
                          Locked
                        </span>
                      </div>
                    )}
                  </motion.div>
                );
              })}
            </div>
          </div>

          {/* Progress Bar & Etiquette */}
          <div className="space-y-3 pt-2">
            <div className="w-full h-2.5 rounded-full bg-black/40 overflow-hidden border border-[#C5A059]/30">
              <motion.div
                initial={{ width: 0 }}
                animate={{ width: `${(currentStamps / maxStamps) * 100}%` }}
                transition={{ duration: 0.8, ease: "easeOut" }}
                className="h-full bg-gradient-to-r from-[#C5A059] to-[#FAF0E4] rounded-full shadow-sm"
              />
            </div>

            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs text-[#A99D95] gap-2">
              <span className="flex items-center gap-1.5">
                <CheckCircle size={15} className="text-[#C5A059]" weight="fill" />
                Automatic stamping upon every completed salon or home service visit.
              </span>
              <span className="text-[#C5A059]">
                {currentStamps} of {maxStamps} stamps completed
              </span>
            </div>
          </div>
        </div>
      </div>

      {/* ========================================================= */}
      {/* 2. ACTIVE UNCLAIMED REWARD VOUCHERS (VOUCHER TICKETS)    */}
      {/* ========================================================= */}
      <div className="space-y-4">
        <div className="flex items-center justify-between">
          <div className="space-y-0.5">
            <h3 className="font-serif text-xl text-[#221C1A] flex items-center gap-2">
              <Ticket size={22} className="text-[#B8975A]" />
              <span>Your Reward Vouchers</span>
            </h3>
            <p className="text-xs text-[#6E6660]">
              Present the voucher code to your technician or front desk concierge upon arrival.
            </p>
          </div>
          {unclaimedVouchers.length > 0 && (
            <span className="px-3 py-1 rounded-full bg-[#FAF0E4] border border-[#E8C99B] text-[#8C6D37] text-xs font-semibold">
              {unclaimedVouchers.length} Unclaimed Reward{unclaimedVouchers.length > 1 ? "s" : ""}
            </span>
          )}
        </div>

        {unclaimedVouchers.length === 0 ? (
          <div className="p-8 rounded-2xl bg-white border border-[#EAE4D9] text-center space-y-3">
            <div className="w-12 h-12 rounded-full bg-[#FAF7F2] text-[#B8975A] mx-auto flex items-center justify-center">
              <Gift size={24} />
            </div>
            <p className="font-serif text-base text-[#221C1A]">No Unclaimed Vouchers Yet</p>
            <p className="text-xs text-[#6E6660] max-w-md mx-auto">
              Your earned vouchers will appear here automatically as soon as you reach 3, 6, or 8 stamps.
              Keep booking your rituals to unlock complimentary wine, discounts, and free sets!
            </p>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {unclaimedVouchers.map((v) => (
              <motion.div
                key={v.id}
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                className="relative rounded-2xl bg-gradient-to-br from-white to-[#FAF7F2] border-2 border-[#E8C99B] shadow-md p-5 flex flex-col justify-between space-y-4"
              >
                {/* Perforated ticket badge header */}
                <div className="flex items-start justify-between gap-3">
                  <div className="space-y-1">
                    <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#FAF0E4] text-[#8C6D37] text-[10px] font-bold uppercase tracking-wider">
                      <Gift size={12} weight="fill" />
                      Active Voucher
                    </span>
                    <h4 className="font-serif text-lg text-[#221C1A] font-medium">
                      {v.reward?.title || "Exclusive Loyalty Perk"}
                    </h4>
                    <p className="text-xs text-[#6E6660] leading-relaxed">
                      {v.reward?.description || "Complimentary sanctuary treatment reward."}
                    </p>
                  </div>
                  <div className="w-10 h-10 rounded-xl bg-[#FAF0E4] text-[#B8975A] flex items-center justify-center shrink-0">
                    {v.reward?.icon === "wine" ? (
                      <Wine size={22} weight="fill" />
                    ) : v.reward?.icon === "crown" ? (
                      <Crown size={22} weight="fill" />
                    ) : (
                      <Sparkle size={22} weight="fill" />
                    )}
                  </div>
                </div>

                {/* Perforated Divider */}
                <div className="relative border-t-2 border-dashed border-[#EAE4D9] -mx-5 px-5 pt-3">
                  <div className="flex items-center justify-between gap-3">
                    <div>
                      <span className="text-[10px] uppercase font-semibold text-[#8E857F] block">
                        Voucher Code
                      </span>
                      <span className="font-mono text-base font-bold text-[#221C1A] tracking-wider">
                        {v.voucher_code}
                      </span>
                    </div>

                    <button
                      type="button"
                      onClick={() => handleCopyCode(v.voucher_code)}
                      className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-[#221C1A] text-white hover:bg-[#B8975A] transition-colors cursor-pointer"
                    >
                      {copiedCode === v.voucher_code ? (
                        <>
                          <Check size={14} weight="bold" />
                          <span>Copied!</span>
                        </>
                      ) : (
                        <>
                          <Copy size={14} />
                          <span>Copy Code</span>
                        </>
                      )}
                    </button>
                  </div>
                  <p className="text-[10px] text-[#8E857F] mt-2">
                    Unlocked on {new Date(v.unlocked_at).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" })}
                  </p>
                </div>
              </motion.div>
            ))}
          </div>
        )}
      </div>

      {/* ========================================================= */}
      {/* 3. REWARD TIERS ROADMAP                                   */}
      {/* ========================================================= */}
      <div className="space-y-4 pt-2">
        <div className="space-y-0.5">
          <h3 className="font-serif text-xl text-[#221C1A]">Sanctuary Milestone Roadmap</h3>
          <p className="text-xs text-[#6E6660]">
            Every treatment brings you closer to exclusive salon privileges.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          {tiers.map((tier) => {
            const isUnlocked = currentStamps >= tier.stamps_required;
            return (
              <div
                key={tier.id}
                className={`p-6 rounded-2xl border transition-all space-y-3 ${
                  isUnlocked
                    ? "bg-white border-[#C5A059] shadow-sm"
                    : "bg-white/60 border-[#EAE4D9] opacity-80"
                }`}
              >
                <div className="flex items-center justify-between">
                  <div
                    className={`w-10 h-10 rounded-xl flex items-center justify-center ${
                      isUnlocked
                        ? "bg-[#FAF0E4] text-[#B8975A]"
                        : "bg-[#FAF7F2] text-[#8E857F]"
                    }`}
                  >
                    {tier.icon === "wine" ? (
                      <Wine size={22} weight={isUnlocked ? "fill" : "regular"} />
                    ) : tier.icon === "crown" ? (
                      <Crown size={22} weight={isUnlocked ? "fill" : "regular"} />
                    ) : (
                      <Sparkle size={22} weight={isUnlocked ? "fill" : "regular"} />
                    )}
                  </div>
                  <span
                    className={`px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${
                      isUnlocked
                        ? "bg-[#E6F4EA] text-[#2D8E47] border border-[#34A853]/30"
                        : "bg-[#FAF7F2] text-[#8E857F] border border-[#EAE4D9]"
                    }`}
                  >
                    {isUnlocked ? "Unlocked ✓" : `${tier.stamps_required} Stamps Required`}
                  </span>
                </div>

                <div>
                  <h4 className="font-serif text-base text-[#221C1A]">{tier.title}</h4>
                  <p className="text-xs text-[#6E6660] mt-1 leading-relaxed">
                    {tier.description}
                  </p>
                </div>
              </div>
            );
          })}
        </div>
      </div>

      {/* ========================================================= */}
      {/* 4. RECENT STAMP ACTIVITY LOGS                             */}
      {/* ========================================================= */}
      {logs.length > 0 && (
        <div className="space-y-4 pt-2">
          <div className="space-y-0.5">
            <h3 className="font-serif text-xl text-[#221C1A]">Stamp Passport History</h3>
            <p className="text-xs text-[#6E6660]">Recent stamps awarded on your account.</p>
          </div>

          <div className="bg-white rounded-2xl border border-[#EAE4D9] overflow-hidden">
            <div className="divide-y divide-[#FAF7F2]">
              {logs.map((log) => (
                <div
                  key={log.id}
                  className="p-4 flex items-center justify-between text-xs hover:bg-[#FAF7F2]/50 transition-colors"
                >
                  <div className="flex items-center gap-3">
                    <div
                      className={`w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs ${
                        log.change > 0
                          ? "bg-[#FAF0E4] text-[#B8975A]"
                          : "bg-[#FDEDEC] text-[#C0392B]"
                      }`}
                    >
                      {log.change > 0 ? `+${log.change}` : log.change}
                    </div>
                    <div>
                      <p className="font-semibold text-[#221C1A]">{log.reason}</p>
                      <p className="text-[11px] text-[#8E857F]">
                        {new Date(log.created_at).toLocaleDateString("en-US", {
                          month: "short",
                          day: "numeric",
                          year: "numeric",
                          hour: "2-digit",
                          minute: "2-digit",
                        })}
                      </p>
                    </div>
                  </div>
                  <span className="capitalize text-[11px] font-medium text-[#6E6660] px-2.5 py-1 rounded-full bg-[#FAF7F2] border border-[#EAE4D9]">
                    {log.action.replace("_", " ")}
                  </span>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      {/* ========================================================= */}
      {/* 5. REDEEMED VOUCHERS ARCHIVE (IF ANY)                     */}
      {/* ========================================================= */}
      {redeemedVouchers.length > 0 && (
        <div className="space-y-4 pt-2">
          <div className="space-y-0.5">
            <h3 className="font-serif text-lg text-[#221C1A]">Redeemed Perks Archive</h3>
            <p className="text-xs text-[#6E6660]">Complimentary privileges you have enjoyed.</p>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            {redeemedVouchers.map((v) => (
              <div
                key={v.id}
                className="p-4 rounded-xl bg-white/70 border border-[#EAE4D9] flex items-center justify-between text-xs text-[#6E6660]"
              >
                <div>
                  <p className="font-semibold text-[#221C1A]">{v.reward?.title}</p>
                  <p className="font-mono text-[11px] text-[#8E857F]">{v.voucher_code}</p>
                </div>
                <div className="text-right">
                  <span className="inline-flex items-center gap-1 text-[10px] font-bold text-[#34A853]">
                    <CheckCircle size={13} weight="fill" />
                    Redeemed
                  </span>
                  {v.redeemed_at && (
                    <p className="text-[10px] text-[#8E857F]">
                      {new Date(v.redeemed_at).toLocaleDateString("en-US", { month: "short", day: "numeric" })}
                    </p>
                  )}
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
