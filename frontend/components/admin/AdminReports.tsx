"use client";

import { useState, useEffect, useCallback, useMemo } from "react";
import Link from "next/link";
import {
  CurrencyDollar,
  CalendarCheck,
  ChartLineUp,
  Receipt,
  DownloadSimple,
  Printer,
  Calendar,
  ArrowsClockwise,
  CreditCard,
  DeviceMobile,
  Money,
  Scissors,
  User,
  ArrowSquareOut,
  Funnel,
  CheckCircle,
  WarningCircle,
  CaretRight,
  TrendUp,
  Eye,
  FilePdf,
  Percent,
  House,
  Car,
  MapPin,
  Target,
  Sparkle,
  HandCoins,
} from "@phosphor-icons/react";
import AdminHeader from "./AdminHeader";
import { AdminReportsData, DailyReportPoint, ServiceReportStat, StylistReportStat } from "@/types";

export default function AdminReports() {
  const [data, setData] = useState<AdminReportsData | null>(null);
  const [loading, setLoading] = useState(true);

  // Active Section Tab
  const [activeTab, setActiveTab] = useState<"overview" | "commission" | "locations">("overview");

  // Preset & Custom range filters
  const [preset, setPreset] = useState<string>("this_month");
  const [from, setFrom] = useState<string>(() => {
    const d = new Date();
    d.setDate(1);
    return d.toISOString().slice(0, 10);
  });
  const [to, setTo] = useState<string>(() => {
    const d = new Date();
    d.setMonth(d.getMonth() + 1);
    d.setDate(0);
    return d.toISOString().slice(0, 10);
  });
  const [providerId, setProviderId] = useState<string>("all");
  const [paymentMethod, setPaymentMethod] = useState<string>("all");
  const [selectedDayPoint, setSelectedDayPoint] = useState<DailyReportPoint | null>(null);

  // Interactive Commission Rate (e.g. 40% for Stylist, 60% for Salon)
  const [commissionRate, setCommissionRate] = useState<number>(40);

  // Handle Preset Changes
  const handlePresetChange = (newPreset: string) => {
    setPreset(newPreset);
    const now = new Date();
    let newFrom = "";
    let newTo = "";

    if (newPreset === "today") {
      newFrom = now.toISOString().slice(0, 10);
      newTo = newFrom;
    } else if (newPreset === "yesterday") {
      const y = new Date(now);
      y.setDate(y.getDate() - 1);
      newFrom = y.toISOString().slice(0, 10);
      newTo = newFrom;
    } else if (newPreset === "7days") {
      const past = new Date(now);
      past.setDate(past.getDate() - 6);
      newFrom = past.toISOString().slice(0, 10);
      newTo = now.toISOString().slice(0, 10);
    } else if (newPreset === "this_month") {
      const start = new Date(now.getFullYear(), now.getMonth(), 1);
      const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
      newFrom = start.toISOString().slice(0, 10);
      newTo = end.toISOString().slice(0, 10);
    } else if (newPreset === "last_month") {
      const start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
      const end = new Date(now.getFullYear(), now.getMonth(), 0);
      newFrom = start.toISOString().slice(0, 10);
      newTo = end.toISOString().slice(0, 10);
    } else if (newPreset === "this_year") {
      const start = new Date(now.getFullYear(), 0, 1);
      const end = new Date(now.getFullYear(), 11, 31);
      newFrom = start.toISOString().slice(0, 10);
      newTo = end.toISOString().slice(0, 10);
    }

    if (newPreset !== "custom") {
      setFrom(newFrom);
      setTo(newTo);
    }
  };

  // Fetch Reports
  const fetchReports = useCallback(async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams();
      if (from) params.append("from", from);
      if (to) params.append("to", to);
      if (providerId !== "all") params.append("provider_id", providerId);
      if (paymentMethod !== "all") params.append("payment_method", paymentMethod);

      const res = await fetch(`/api/v1/admin/reports?${params.toString()}`, {
        cache: "no-store",
      });
      const json: AdminReportsData = await res.json();
      if (res.ok && json.success) {
        setData(json);
      }
    } catch (err) {
      console.error("Failed to load reports", err);
    } finally {
      setLoading(false);
    }
  }, [from, to, providerId, paymentMethod]);

  useEffect(() => {
    fetchReports();
  }, [fetchReports]);

  // Max daily revenue for chart scaling
  const maxDailyRevenue = useMemo(() => {
    if (!data?.daily_chart || data.daily_chart.length === 0) return 1;
    const max = Math.max(...data.daily_chart.map((d) => d.revenue));
    return max > 0 ? max : 1;
  }, [data]);

  // Export to CSV
  const handleExportCSV = () => {
    if (!data || !data.bookings || data.bookings.length === 0) return;
    const headers = [
      "ID",
      "Reference",
      "Customer",
      "Date",
      "Time",
      "Services",
      "Stylist",
      "Valuation (RWF)",
      "Amount Collected (RWF)",
      "Payment Method",
      "Status",
    ];
    const rows = data.bookings.map((b) => [
      b.id,
      `"${b.reference}"`,
      `"${b.customer_name}"`,
      b.date,
      b.time_formatted,
      `"${b.services.map((s) => s.name).join(", ")}"`,
      `"${b.provider_name}"`,
      b.total_price,
      b.total_paid,
      b.payment_method || "Unpaid",
      b.status,
    ]);

    const csvContent =
      "data:text/csv;charset=utf-8," +
      [headers.join(","), ...rows.map((e) => e.join(","))].join("\n");
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `revenue_report_${from}_to_${to}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  // Export Stylist Commission Payroll CSV
  const handleExportPayrollCSV = () => {
    if (!data?.stylist_performance || data.stylist_performance.length === 0) return;
    const headers = [
      "Stylist Name",
      "Appointments Done",
      "Gross Revenue (RWF)",
      `Commission Rate (%)`,
      `Stylist Payout (RWF)`,
      `Salon Net Retained (RWF)`,
    ];
    const rows = data.stylist_performance.map((p) => {
      const payout = Math.round((p.revenue * commissionRate) / 100);
      const salonCut = p.revenue - payout;
      return [
        `"${p.name}"`,
        p.count,
        p.revenue,
        commissionRate,
        payout,
        salonCut,
      ];
    });

    const csvContent =
      "data:text/csv;charset=utf-8," +
      [headers.join(","), ...rows.map((e) => e.join(","))].join("\n");
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `payroll_commissions_${from}_to_${to}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  // Helper for growth badges
  const renderGrowthBadge = (val: number | null | undefined, suffix = "%") => {
    if (val === null || val === undefined) return null;
    const isPos = val >= 0;
    return (
      <span
        className={`inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded text-[10px] font-bold ${
          isPos
            ? "bg-emerald-50 text-emerald-700 border border-emerald-200"
            : "bg-rose-50 text-rose-700 border border-rose-200"
        }`}
      >
        {isPos ? "↗ +" : "↘ "}
        {val}
        {suffix}
      </span>
    );
  };

  const pdfDownloadUrl = `/api/v1/admin/reports/pdf?from=${from}&to=${to}${
    providerId !== "all" ? `&provider_id=${providerId}` : ""
  }`;

  return (
    <div className="flex-1 flex flex-col min-w-0 bg-gray-50 pb-16">
      {/* Header */}
      <AdminHeader
        title="Revenue & Performance Reports"
        subtitle="Turnover audits, stylist commission calculator, location splits, and PDF statements."
      />

      <div className="p-4 sm:p-6 max-w-7xl w-full mx-auto space-y-4">
        {/* Top Control Bar */}
        <div className="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-gray-200 shadow-2xs">
          <div>
            <h2 className="text-base font-bold text-gray-900 tracking-tight flex items-center gap-2">
              <ChartLineUp size={18} className="text-gray-900" />
              Financial Statement & Revenue Analysis
            </h2>
            <p className="text-xs text-gray-500 mt-0.5">
              Period: <span className="font-semibold text-gray-800">{data?.from_formatted || from}</span> to{" "}
              <span className="font-semibold text-gray-800">{data?.to_formatted || to}</span>
            </p>
          </div>

          <div className="flex flex-wrap items-center gap-2 w-full lg:w-auto">
            <a
              href={pdfDownloadUrl}
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-600 text-white rounded-lg text-xs font-semibold hover:bg-rose-700 transition shadow-xs"
            >
              <FilePdf size={13} weight="bold" />
              <span>Download PDF</span>
            </a>

            <button
              onClick={handleExportCSV}
              disabled={!data || data.bookings.length === 0}
              className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 transition shadow-2xs disabled:opacity-40"
            >
              <DownloadSimple size={13} />
              <span>Export CSV</span>
            </button>

            <button
              onClick={() => window.print()}
              className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 transition shadow-2xs"
            >
              <Printer size={13} />
              <span>Print</span>
            </button>

            <button
              onClick={fetchReports}
              disabled={loading}
              title="Refresh"
              className="p-1.5 border border-gray-300 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition shadow-2xs"
            >
              <ArrowsClockwise size={14} className={loading ? "animate-spin" : ""} />
            </button>
          </div>
        </div>

        {/* Navigation Tabs (Overview vs Commissions vs Channel Splits) */}
        <div className="flex items-center gap-1 border-b border-gray-200">
          <button
            onClick={() => setActiveTab("overview")}
            className={`flex items-center gap-2 px-4 py-2 text-xs font-bold transition border-b-2 -mb-px ${
              activeTab === "overview"
                ? "border-gray-900 text-gray-900"
                : "border-transparent text-gray-500 hover:text-gray-900"
            }`}
          >
            <ChartLineUp size={15} />
            <span>Overview & Ledger</span>
          </button>

          <button
            onClick={() => setActiveTab("commission")}
            className={`flex items-center gap-2 px-4 py-2 text-xs font-bold transition border-b-2 -mb-px ${
              activeTab === "commission"
                ? "border-gray-900 text-gray-900"
                : "border-transparent text-gray-500 hover:text-gray-900"
            }`}
          >
            <HandCoins size={15} />
            <span>Stylist Commission Payouts</span>
            <span className="px-1.5 py-0.2 bg-emerald-100 text-emerald-800 text-[10px] rounded-full">
              {commissionRate}%
            </span>
          </button>

          <button
            onClick={() => setActiveTab("locations")}
            className={`flex items-center gap-2 px-4 py-2 text-xs font-bold transition border-b-2 -mb-px ${
              activeTab === "locations"
                ? "border-gray-900 text-gray-900"
                : "border-transparent text-gray-500 hover:text-gray-900"
            }`}
          >
            <House size={15} />
            <span>Home VIP vs In-Salon Split</span>
          </button>
        </div>

        {/* Date Presets and Filters */}
        <div className="bg-white border border-gray-200 rounded-xl p-3 shadow-2xs space-y-2.5">
          {/* Preset Buttons */}
          <div className="flex items-center gap-1 overflow-x-auto pb-0.5 scrollbar-none">
            {[
              { id: "today", label: "Today" },
              { id: "yesterday", label: "Yesterday" },
              { id: "7days", label: "Last 7 Days" },
              { id: "this_month", label: "This Month" },
              { id: "last_month", label: "Last Month" },
              { id: "this_year", label: "This Year" },
              { id: "custom", label: "Custom Range" },
            ].map((p) => (
              <button
                key={p.id}
                onClick={() => handlePresetChange(p.id)}
                className={`px-3 py-1 rounded-md text-xs font-semibold transition whitespace-nowrap ${
                  preset === p.id
                    ? "bg-gray-900 text-white shadow-xs"
                    : "bg-gray-100 text-gray-600 hover:bg-gray-200"
                }`}
              >
                {p.label}
              </button>
            ))}
          </div>

          {/* Range Inputs & Dropdowns */}
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5 pt-1 border-t border-gray-100">
            <div>
              <label className="block text-[11px] font-medium text-gray-500 mb-1">From Date</label>
              <input
                type="date"
                value={from}
                onChange={(e) => {
                  setFrom(e.target.value);
                  setPreset("custom");
                }}
                className="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs text-gray-900 focus:bg-white focus:outline-none"
              />
            </div>

            <div>
              <label className="block text-[11px] font-medium text-gray-500 mb-1">To Date</label>
              <input
                type="date"
                value={to}
                onChange={(e) => {
                  setTo(e.target.value);
                  setPreset("custom");
                }}
                className="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs text-gray-900 focus:bg-white focus:outline-none"
              />
            </div>

            <div>
              <label className="block text-[11px] font-medium text-gray-500 mb-1">Stylist</label>
              <select
                value={providerId}
                onChange={(e) => setProviderId(e.target.value)}
                className="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs text-gray-900 focus:bg-white focus:outline-none"
              >
                <option value="all">All Specialists</option>
                {data?.providers.map((p) => (
                  <option key={p.id} value={p.id}>
                    {p.name}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-[11px] font-medium text-gray-500 mb-1">Payment Method</label>
              <select
                value={paymentMethod}
                onChange={(e) => setPaymentMethod(e.target.value)}
                className="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs text-gray-900 focus:bg-white focus:outline-none"
              >
                <option value="all">All Channels</option>
                <option value="momo">MTN / Airtel MoMo</option>
                <option value="card">Card / POS</option>
                <option value="cash">Desk Cash</option>
              </select>
            </div>
          </div>
        </div>

        {/* 4 Executive Financial KPI Cards (With vs Previous Period Deltas) */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          {/* Card 1: Total Revenue */}
          <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs flex flex-col justify-between">
            <div className="flex items-center justify-between text-gray-500 mb-1.5">
              <span className="text-xs font-bold uppercase tracking-wider">Collected Revenue</span>
              <div className="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                <CurrencyDollar size={16} weight="bold" />
              </div>
            </div>
            <div>
              <div className="flex items-baseline gap-2">
                <span className="text-xl sm:text-2xl font-bold tracking-tight tabular-nums text-gray-900">
                  {data?.kpis.total_revenue.toLocaleString() || "0"} RWF
                </span>
                {renderGrowthBadge(data?.comparison?.revenue_growth)}
              </div>
              <p className="text-[11px] text-gray-400 mt-1">
                Net (excl. 5% tax):{" "}
                <span className="text-gray-700 font-medium tabular-nums">
                  {data?.kpis.net_revenue.toLocaleString() || "0"} RWF
                </span>
              </p>
            </div>
          </div>

          {/* Card 2: Bookings Volume & Ticket */}
          <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs flex flex-col justify-between">
            <div className="flex items-center justify-between text-gray-500 mb-1.5">
              <span className="text-xs font-bold uppercase tracking-wider">Completed Volume</span>
              <div className="w-7 h-7 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
                <CalendarCheck size={16} weight="bold" />
              </div>
            </div>
            <div>
              <div className="flex items-baseline gap-2">
                <span className="text-xl sm:text-2xl font-bold text-gray-900">
                  {data?.kpis.total_bookings || 0} Bookings
                </span>
                {renderGrowthBadge(data?.comparison?.bookings_growth)}
              </div>
              <p className="text-[11px] text-gray-400 mt-1">
                Avg. ticket:{" "}
                <span className="text-gray-700 font-medium tabular-nums">
                  {data?.kpis.avg_booking_value.toLocaleString() || "0"} RWF
                </span>
              </p>
            </div>
          </div>

          {/* Card 3: Monthly Target Progress */}
          <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs flex flex-col justify-between">
            <div className="flex items-center justify-between text-gray-500 mb-1.5">
              <span className="text-xs font-bold uppercase tracking-wider">Monthly Target (5M)</span>
              <div className="w-7 h-7 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
                <Target size={16} weight="bold" />
              </div>
            </div>
            <div>
              <div className="flex items-baseline justify-between mb-1">
                <span className="text-xl sm:text-2xl font-bold tracking-tight tabular-nums text-gray-900">
                  {data?.kpis.target_progress || 0}%
                </span>
                <span className="text-[11px] text-gray-500 font-medium tabular-nums">
                  {data?.kpis.total_revenue.toLocaleString()} / 5M RWF
                </span>
              </div>
              <div className="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                <div
                  style={{ width: `${Math.min(100, data?.kpis.target_progress || 0)}%` }}
                  className="h-full bg-amber-500 rounded-full transition-all duration-500"
                />
              </div>
            </div>
          </div>

          {/* Card 4: Payment Channel Distribution */}
          <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs flex flex-col justify-between">
            <div className="flex items-center justify-between text-gray-500 mb-1.5">
              <span className="text-xs font-bold uppercase tracking-wider">Payment Channels</span>
              <div className="w-7 h-7 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center">
                <DeviceMobile size={16} weight="bold" />
              </div>
            </div>
            <div className="space-y-1 text-xs">
              <div className="flex justify-between items-center text-gray-600">
                <span className="flex items-center gap-1 text-[11px]">
                  <DeviceMobile size={11} className="text-amber-600" /> MoMo:
                </span>
                <span className="font-bold tabular-nums text-gray-900">
                  {data?.kpis.momo_total.toLocaleString() || "0"} RWF
                </span>
              </div>
              <div className="flex justify-between items-center text-gray-600">
                <span className="flex items-center gap-1 text-[11px]">
                  <CreditCard size={11} className="text-blue-600" /> Card / POS:
                </span>
                <span className="font-bold tabular-nums text-gray-900">
                  {data?.kpis.card_total.toLocaleString() || "0"} RWF
                </span>
              </div>
            </div>
          </div>
        </div>

        {/* Tab 1: OVERVIEW & LEDGER */}
        {activeTab === "overview" && (
          <div className="space-y-4">
            {/* Daily Collection Progress Bar Chart */}
            {data?.daily_chart && data.daily_chart.length > 0 && (
              <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs space-y-3">
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-2">
                  <div>
                    <h3 className="text-xs font-bold text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                      <ChartLineUp size={15} className="text-gray-900" />
                      Daily Revenue Progression
                    </h3>
                    <p className="text-[11px] text-gray-500">
                      Hover bars to see daily turnover and client reservation count.
                    </p>
                  </div>

                  {selectedDayPoint && (
                    <div className="text-xs bg-gray-50 px-2.5 py-1 rounded-lg border border-gray-200 text-gray-700">
                      <span className="font-bold">{selectedDayPoint.date} ({selectedDayPoint.day_name}):</span>{" "}
                      <span className="font-bold tabular-nums text-emerald-700">
                        {selectedDayPoint.revenue.toLocaleString()} RWF
                      </span>{" "}
                      · <span>{selectedDayPoint.count} bookings</span>
                    </div>
                  )}
                </div>

                <div className="h-40 w-full flex items-end gap-1 sm:gap-2 pt-4 px-1 overflow-x-auto scrollbar-none">
                  {data.daily_chart.map((point, index) => {
                    const heightPercent = Math.max(
                      6,
                      Math.round((point.revenue / maxDailyRevenue) * 100)
                    );
                    const hasRevenue = point.revenue > 0;

                    return (
                      <div
                        key={index}
                        onMouseEnter={() => setSelectedDayPoint(point)}
                        className="flex-1 min-w-[18px] max-w-[36px] flex flex-col items-center gap-1 group cursor-pointer h-full justify-end"
                      >
                        <div className="w-full flex items-end justify-center h-28 relative">
                          <div className="absolute -top-6 opacity-0 group-hover:opacity-100 transition-opacity bg-gray-900 text-white text-[9px] px-1.5 py-0.5 rounded shadow pointer-events-none whitespace-nowrap z-10 font-medium tabular-nums">
                            {point.revenue.toLocaleString()} RWF
                          </div>

                          <div
                            style={{ height: `${hasRevenue ? heightPercent : 4}%` }}
                            className={`w-full rounded-t-sm transition-all duration-300 ${
                              hasRevenue
                                ? "bg-gray-900 group-hover:bg-emerald-600"
                                : "bg-gray-100 group-hover:bg-gray-200"
                            }`}
                          />
                        </div>
                        <span className="text-[9px] text-gray-400 group-hover:text-gray-900 transition font-medium tabular-nums truncate w-full text-center">
                          {point.date}
                        </span>
                      </div>
                    );
                  })}
                </div>
              </div>
            )}

            {/* Performance Split Grid (Top Services & Category Distribution) */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {/* Top Services */}
              <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs space-y-3">
                <div className="border-b border-gray-100 pb-2 flex items-center justify-between">
                  <h4 className="text-xs font-bold text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                    <Scissors size={14} className="text-gray-700" />
                    Top Performing Services
                  </h4>
                  <span className="text-[11px] text-gray-400">
                    {data?.top_services.length || 0} treatments
                  </span>
                </div>

                <div className="divide-y divide-gray-100">
                  {!data?.top_services || data.top_services.length === 0 ? (
                    <div className="py-4 text-center text-xs text-gray-400">
                      No treatment records in this period.
                    </div>
                  ) : (
                    data.top_services.map((s, idx) => (
                      <div key={s.id} className="py-2 flex items-center justify-between text-xs">
                        <div className="flex items-center gap-2">
                          <span className="w-4 h-4 rounded-full bg-gray-100 text-gray-600 text-[10px] font-bold flex items-center justify-center">
                            {idx + 1}
                          </span>
                          <div>
                            <div className="font-semibold text-gray-900">{s.name}</div>
                            <div className="text-[10px] text-gray-400">{s.count} appointments</div>
                          </div>
                        </div>
                        <div className="font-bold tabular-nums text-gray-800">
                          {s.revenue.toLocaleString()} RWF
                        </div>
                      </div>
                    ))
                  )}
                </div>
              </div>

              {/* Treatment Category Distribution */}
              <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs space-y-3">
                <div className="border-b border-gray-100 pb-2 flex items-center justify-between">
                  <h4 className="text-xs font-bold text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                    <Sparkle size={14} className="text-gray-700" />
                    Category Revenue Distribution
                  </h4>
                  <span className="text-[11px] text-gray-400">Share of Total</span>
                </div>

                <div className="space-y-3 pt-1">
                  {!data?.category_stats || data.category_stats.length === 0 ? (
                    <div className="py-4 text-center text-xs text-gray-400">
                      No category metrics found.
                    </div>
                  ) : (
                    data.category_stats.map((cat) => (
                      <div key={cat.id} className="space-y-1">
                        <div className="flex justify-between items-center text-xs">
                          <span className="font-semibold text-gray-900">{cat.name}</span>
                          <span className="font-bold tabular-nums text-gray-800">
                            {cat.revenue.toLocaleString()} RWF ({cat.percentage}%)
                          </span>
                        </div>
                        <div className="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                          <div
                            style={{ width: `${cat.percentage}%` }}
                            className="h-full bg-gray-900 rounded-full"
                          />
                        </div>
                      </div>
                    ))
                  )}
                </div>
              </div>
            </div>

            {/* Audited Bookings Ledger Table */}
            <div className="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
              <div className="bg-gray-50 px-4 py-2.5 border-b border-gray-200 flex items-center justify-between">
                <h3 className="text-xs font-bold text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                  <Receipt size={14} className="text-gray-700" />
                  Audited Reservations Ledger ({data?.bookings.length || 0})
                </h3>
                <span className="text-[11px] text-gray-500">
                  Completed & Accepted Treatments
                </span>
              </div>

              <div className="overflow-x-auto min-h-[160px]">
                <table className="w-full text-left border-collapse text-xs">
                  <thead>
                    <tr className="bg-gray-50/60 border-b border-gray-200 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                      <th className="px-4 py-2">Ref / ID</th>
                      <th className="px-4 py-2">Customer</th>
                      <th className="px-4 py-2">Schedule</th>
                      <th className="px-4 py-2">Treatments</th>
                      <th className="px-4 py-2">Stylist</th>
                      <th className="px-4 py-2">Channel</th>
                      <th className="px-4 py-2 text-right">Amount Paid</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-100">
                    {loading ? (
                      Array.from({ length: 4 }).map((_, i) => (
                        <tr key={i} className="animate-pulse">
                          <td colSpan={7} className="px-4 py-2.5">
                            <div className="h-3 bg-gray-200 rounded w-full"></div>
                          </td>
                        </tr>
                      ))
                    ) : !data?.bookings || data.bookings.length === 0 ? (
                      <tr>
                        <td colSpan={7} className="px-4 py-8 text-center text-gray-400 text-xs">
                          No matching records found in this date range.
                        </td>
                      </tr>
                    ) : (
                      data.bookings.map((b) => (
                        <tr key={b.id} className="hover:bg-gray-50/70 transition">
                          <td className="px-4 py-2 font-medium tabular-nums text-gray-900">
                            {b.reference}
                          </td>
                          <td className="px-4 py-2 font-medium text-gray-900">
                            {b.customer_name}
                          </td>
                          <td className="px-4 py-2 text-gray-600">
                            {b.date_formatted} · {b.time_formatted}
                          </td>
                          <td className="px-4 py-2 text-gray-600 max-w-[180px] truncate">
                            {b.services.map((s) => s.name).join(", ")}
                          </td>
                          <td className="px-4 py-2 text-gray-700 font-medium">
                            {b.provider_name}
                          </td>
                          <td className="px-4 py-2">
                            <span className="text-[10px] uppercase font-bold text-gray-500 bg-gray-100 px-1 py-0.2 rounded">
                              {b.payment_method || "Unpaid"}
                            </span>
                          </td>
                          <td className="px-4 py-2 text-right font-bold tabular-nums text-emerald-700">
                            {b.total_paid.toLocaleString()} RWF
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        )}

        {/* Tab 2: STYLIST COMMISSION & PAYOUT CALCULATOR */}
        {activeTab === "commission" && (
          <div className="space-y-4">
            {/* Commission Config Card */}
            <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs space-y-3">
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
                <div>
                  <h3 className="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <HandCoins size={16} className="text-emerald-600" />
                    Stylist Commission & Staff Payroll Calculator
                  </h3>
                  <p className="text-xs text-gray-500 mt-0.5">
                    Adjust the commission percentage slider below to instantly recalculate staff payouts vs. salon retained profit.
                  </p>
                </div>

                <button
                  onClick={handleExportPayrollCSV}
                  disabled={!data?.stylist_performance || data.stylist_performance.length === 0}
                  className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-700 text-white rounded-lg text-xs font-semibold hover:bg-emerald-800 transition shadow-xs disabled:opacity-40"
                >
                  <DownloadSimple size={13} />
                  <span>Download Payroll Slips (CSV)</span>
                </button>
              </div>

              {/* Slider Controls */}
              <div className="bg-gray-50 p-3 rounded-lg border border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div className="flex items-center gap-3 w-full sm:w-auto">
                  <span className="text-xs font-bold text-gray-700">Stylist Share:</span>
                  <input
                    type="range"
                    min={10}
                    max={90}
                    step={5}
                    value={commissionRate}
                    onChange={(e) => setCommissionRate(Number(e.target.value))}
                    className="w-40 sm:w-56 accent-gray-900 cursor-pointer"
                  />
                  <span className="font-bold tabular-nums text-base text-gray-900 bg-white px-2 py-0.5 rounded border border-gray-300">
                    {commissionRate}%
                  </span>
                </div>

                <div className="flex items-center gap-2">
                  <span className="text-xs text-gray-500">Presets:</span>
                  {[30, 40, 50, 60].map((rate) => (
                    <button
                      key={rate}
                      onClick={() => setCommissionRate(rate)}
                      className={`px-2 py-0.5 rounded text-xs font-semibold transition ${
                        commissionRate === rate
                          ? "bg-gray-900 text-white"
                          : "bg-white border border-gray-300 text-gray-700 hover:bg-gray-100"
                      }`}
                    >
                      {rate}%
                    </button>
                  ))}
                </div>
              </div>
            </div>

            {/* Payout Table */}
            <div className="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
              <table className="w-full text-left border-collapse text-xs">
                <thead>
                  <tr className="bg-gray-50 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-600">
                    <th className="px-4 py-3">Stylist / Specialist</th>
                    <th className="px-4 py-3">Completed Services</th>
                    <th className="px-4 py-3">Gross Revenue Generated</th>
                    <th className="px-4 py-3">Stylist Payout ({commissionRate}%)</th>
                    <th className="px-4 py-3 text-right">Salon Retained Cut ({100 - commissionRate}%)</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {!data?.stylist_performance || data.stylist_performance.length === 0 ? (
                    <tr>
                      <td colSpan={5} className="px-4 py-8 text-center text-gray-400">
                        No specialist appointments in this period.
                      </td>
                    </tr>
                  ) : (
                    data.stylist_performance.map((stylist) => {
                      const stylistPayout = Math.round((stylist.revenue * commissionRate) / 100);
                      const salonRetained = stylist.revenue - stylistPayout;

                      return (
                        <tr key={stylist.id} className="hover:bg-gray-50 transition">
                          <td className="px-4 py-3 font-semibold text-gray-900">
                            {stylist.name}
                          </td>
                          <td className="px-4 py-3 text-gray-600">
                            {stylist.count} treatments
                          </td>
                          <td className="px-4 py-3 font-bold tabular-nums text-gray-900">
                            {stylist.revenue.toLocaleString()} RWF
                          </td>
                          <td className="px-4 py-3 font-bold tabular-nums text-emerald-700">
                            {stylistPayout.toLocaleString()} RWF
                          </td>
                          <td className="px-4 py-3 text-right font-bold tabular-nums text-gray-700">
                            {salonRetained.toLocaleString()} RWF
                          </td>
                        </tr>
                      );
                    })
                  )}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* Tab 3: LOCATION & CHANNEL SPLIT */}
        {activeTab === "locations" && (
          <div className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              {/* In-Salon Sanctuary */}
              <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs space-y-2">
                <div className="flex items-center justify-between text-gray-500">
                  <span className="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                    <MapPin size={14} className="text-gray-900" />
                    In-Salon Sanctuary
                  </span>
                  <span className="text-xs font-bold tabular-nums text-gray-900">
                    {data?.location_split.in_salon.percentage}%
                  </span>
                </div>
                <div className="text-2xl font-bold tracking-tight tabular-nums text-gray-900">
                  {data?.location_split.in_salon.revenue.toLocaleString()} RWF
                </div>
                <p className="text-xs text-gray-500">
                  {data?.location_split.in_salon.count} appointments conducted at the salon
                </p>
              </div>

              {/* Home Service VIP */}
              <div className="bg-rose-50/60 border border-rose-200 rounded-xl p-4 shadow-2xs space-y-2">
                <div className="flex items-center justify-between text-rose-800">
                  <span className="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                    <House size={14} className="text-rose-700" />
                    Home Service VIP (+100%)
                  </span>
                  <span className="text-xs font-bold tabular-nums text-rose-900">
                    {data?.location_split.home_service.percentage}%
                  </span>
                </div>
                <div className="text-2xl font-bold tracking-tight tabular-nums text-rose-950">
                  {data?.location_split.home_service.revenue.toLocaleString()} RWF
                </div>
                <p className="text-xs text-rose-700">
                  {data?.location_split.home_service.count} appointments rendered at client homes
                </p>
              </div>

              {/* Pickup Route Shuttle */}
              <div className="bg-blue-50/60 border border-blue-200 rounded-xl p-4 shadow-2xs space-y-2">
                <div className="flex items-center justify-between text-blue-800">
                  <span className="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                    <Car size={14} className="text-blue-700" />
                    Pickup Shuttle Routes
                  </span>
                  <span className="text-xs font-bold tabular-nums text-blue-900">
                    {data?.location_split.pickup.percentage}%
                  </span>
                </div>
                <div className="text-2xl font-bold tracking-tight tabular-nums text-blue-950">
                  {data?.location_split.pickup.revenue.toLocaleString()} RWF
                </div>
                <p className="text-xs text-blue-700">
                  {data?.location_split.pickup.count} clients transported via route shuttles
                </p>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
