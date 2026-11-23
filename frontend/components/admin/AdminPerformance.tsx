"use client";

import { useState, useEffect, useCallback, useMemo } from "react";
import Link from "next/link";
import {
  ChartBar,
  CalendarCheck,
  CurrencyDollar,
  CheckCircle,
  Percent,
  Scissors,
  User,
  Star,
  ArrowsClockwise,
  DownloadSimple,
  Printer,
  Eye,
  X,
  Phone,
  EnvelopeSimple,
  TrendUp,
  Clock,
  ArrowSquareOut,
  Calendar,
  Medal,
  ChartLineUp,
} from "@phosphor-icons/react";
import AdminHeader from "./AdminHeader";
import { AdminPerformanceData, ProviderPerformanceItem } from "@/types";

export default function AdminPerformance() {
  const [data, setData] = useState<AdminPerformanceData | null>(null);
  const [loading, setLoading] = useState(true);
  const [range, setRange] = useState<string>("30days");
  const [search, setSearch] = useState("");
  const [selectedProvider, setSelectedProvider] = useState<ProviderPerformanceItem | null>(null);
  const [drawerLoading, setDrawerLoading] = useState(false);

  // Fetch performance data
  const fetchPerformance = useCallback(async (providerId?: number) => {
    setLoading(true);
    try {
      const params = new URLSearchParams();
      params.append("range", range);
      if (providerId) params.append("provider_id", providerId.toString());

      const res = await fetch(`/api/v1/admin/performance?${params.toString()}`, {
        cache: "no-store",
      });
      const json: AdminPerformanceData = await res.json();
      if (res.ok && json.success) {
        setData(json);
        if (providerId && json.selected_provider) {
          setSelectedProvider(json.selected_provider);
        }
      }
    } catch (err) {
      console.error("Failed to load provider performance", err);
    } finally {
      setLoading(false);
    }
  }, [range]);

  useEffect(() => {
    const requestedProvider = Number(new URLSearchParams(window.location.search).get("provider_id"));
    void fetchPerformance(requestedProvider || undefined);
  }, [fetchPerformance]);

  // Deep-dive into single provider
  const handleInspectProvider = async (provider: ProviderPerformanceItem) => {
    setDrawerLoading(true);
    setSelectedProvider(provider);
    try {
      const res = await fetch(
        `/api/v1/admin/performance?range=${range}&provider_id=${provider.id}`,
        { cache: "no-store" }
      );
      const json: AdminPerformanceData = await res.json();
      if (res.ok && json.success && json.selected_provider) {
        setSelectedProvider(json.selected_provider);
      }
    } catch (err) {
      console.error("Failed to fetch detailed provider performance", err);
    } finally {
      setDrawerLoading(false);
    }
  };

  // Filtered providers by search
  const filteredProviders = useMemo(() => {
    if (!data?.providers) return [];
    if (!search.trim()) return data.providers;
    const q = search.toLowerCase();
    return data.providers.filter(
      (p) =>
        p.name.toLowerCase().includes(q) ||
        (p.email && p.email.toLowerCase().includes(q)) ||
        (p.phone && p.phone.includes(q))
    );
  }, [data, search]);

  // Max daily revenue for single provider chart
  const maxProviderDailyRevenue = useMemo(() => {
    if (!selectedProvider?.daily_trend || selectedProvider.daily_trend.length === 0) return 1;
    const max = Math.max(...selectedProvider.daily_trend.map((d) => d.total));
    return max > 0 ? max : 1;
  }, [selectedProvider]);

  // Export CSV
  const handleExportCSV = () => {
    if (!data?.providers || data.providers.length === 0) return;
    const headers = [
      "Provider ID",
      "Name",
      "Email",
      "Phone",
      "Active Status",
      "Total Appointments",
      "Completed",
      "Accepted",
      "Cancelled",
      "Completion Rate (%)",
      "Total Revenue (RWF)",
      "Avg Ticket (RWF)",
    ];
    const rows = data.providers.map((p) => [
      p.id,
      `"${p.name}"`,
      `"${p.email || ""}"`,
      `"${p.phone || ""}"`,
      p.active ? "Active" : "Inactive",
      p.total_bookings,
      p.completed_bookings,
      p.accepted_bookings,
      p.cancelled_bookings,
      p.completion_rate,
      p.total_revenue,
      p.avg_ticket,
    ]);

    const csvContent =
      "data:text/csv;charset=utf-8," +
      [headers.join(","), ...rows.map((e) => e.join(","))].join("\n");
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `stylist_performance_${range}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  return (
    <div className="flex-1 flex flex-col min-w-0 bg-gray-50 pb-16">
      {/* Top Bar Header */}
      <AdminHeader
        title="Stylist & Staff Performance"
        subtitle="Monitor salon technician productivity, booking completion benchmarks, and individual revenue output."
      />

      <div className="p-4 sm:p-6 max-w-7xl w-full mx-auto space-y-4">
        {/* Top Control Bar */}
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-gray-200 shadow-2xs">
          <div>
            <h2 className="text-base font-bold text-gray-900 tracking-tight flex items-center gap-2">
              <ChartBar size={18} className="text-gray-900" />
              Technician Productivity Benchmarks
            </h2>
            <p className="text-xs text-gray-500 mt-0.5">
              Compare completion velocity, individual revenue generation, and client retention across salon specialists.
            </p>
          </div>

          <div className="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            {/* Range Presets */}
            <div className="inline-flex items-center bg-gray-100 rounded-lg p-0.5 border border-gray-200 text-xs">
              {[
                { id: "7days", label: "7 Days" },
                { id: "30days", label: "30 Days" },
                { id: "this_month", label: "This Month" },
                { id: "all_time", label: "All Time" },
              ].map((r) => (
                <button
                  key={r.id}
                  onClick={() => setRange(r.id)}
                  className={`px-2.5 py-1 rounded-md font-semibold transition ${
                    range === r.id
                      ? "bg-white text-gray-900 shadow-2xs"
                      : "text-gray-500 hover:text-gray-900"
                  }`}
                >
                  {r.label}
                </button>
              ))}
            </div>

            <button
              onClick={handleExportCSV}
              disabled={!data?.providers || data.providers.length === 0}
              className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 transition shadow-2xs disabled:opacity-40"
            >
              <DownloadSimple size={13} />
              <span>Export CSV</span>
            </button>

            <Link
              href="/admin/providers"
              className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-semibold hover:bg-gray-800 transition shadow-xs"
            >
              <Scissors size={13} weight="bold" />
              <span>Manage Staff</span>
            </Link>

            <button
              onClick={() => fetchPerformance()}
              disabled={loading}
              title="Refresh"
              className="p-1.5 border border-gray-300 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition shadow-2xs"
            >
              <ArrowsClockwise size={14} className={loading ? "animate-spin" : ""} />
            </button>
          </div>
        </div>

        {/* 4 Team Summary KPI Cards */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          {/* Card 1: Team Revenue */}
          <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs flex flex-col justify-between">
            <div className="flex items-center justify-between text-gray-500 mb-1.5">
              <span className="text-xs font-bold uppercase tracking-wider">Team Production</span>
              <div className="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                <CurrencyDollar size={16} weight="bold" />
              </div>
            </div>
            <div>
              <div className="text-xl sm:text-2xl font-bold tracking-tight tabular-nums text-gray-900">
                {data?.summary.team_revenue.toLocaleString() || "0"} RWF
              </div>
              <p className="text-[11px] text-gray-400 mt-1">
                From {data?.summary.team_completed_bookings || 0} completed client treatments
              </p>
            </div>
          </div>

          {/* Card 2: Team Appointments */}
          <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs flex flex-col justify-between">
            <div className="flex items-center justify-between text-gray-500 mb-1.5">
              <span className="text-xs font-bold uppercase tracking-wider">Appointments Volume</span>
              <div className="w-7 h-7 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
                <CalendarCheck size={16} weight="bold" />
              </div>
            </div>
            <div>
              <div className="text-xl sm:text-2xl font-bold text-gray-900">
                {data?.summary.team_total_bookings || 0} Bookings
              </div>
              <p className="text-[11px] text-gray-400 mt-1">
                Completed:{" "}
                <span className="font-semibold text-emerald-700">
                  {data?.summary.team_completed_bookings || 0}
                </span>{" "}
                appointments
              </p>
            </div>
          </div>

          {/* Card 3: Average Completion Rate */}
          <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs flex flex-col justify-between">
            <div className="flex items-center justify-between text-gray-500 mb-1.5">
              <span className="text-xs font-bold uppercase tracking-wider">Team Completion Benchmark</span>
              <div className="w-7 h-7 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center">
                <Percent size={16} weight="bold" />
              </div>
            </div>
            <div>
              <div className="text-xl sm:text-2xl font-bold text-gray-900">
                {data?.summary.team_avg_completion_rate || 0}%
              </div>
              <div className="w-full h-1.5 bg-gray-100 rounded-full mt-2 overflow-hidden">
                <div
                  style={{ width: `${data?.summary.team_avg_completion_rate || 0}%` }}
                  className="h-full bg-purple-600 rounded-full"
                />
              </div>
            </div>
          </div>

          {/* Card 4: Top Performer Stylist */}
          <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs flex flex-col justify-between">
            <div className="flex items-center justify-between text-gray-500 mb-1.5">
              <span className="text-xs font-bold uppercase tracking-wider">Top Performer</span>
              <div className="w-7 h-7 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
                <Medal size={16} weight="bold" />
              </div>
            </div>
            <div>
              <div className="text-base font-bold text-gray-900 truncate flex items-center gap-1.5">
                <span>{data?.summary.top_performer?.name || "No records"}</span>
                {data?.summary.top_performer && (
                  <span className="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-800">
                    STAR
                  </span>
                )}
              </div>
              <p className="text-[11px] text-gray-500 font-medium tabular-nums mt-1">
                {data?.summary.top_performer?.revenue.toLocaleString() || "0"} RWF produced
              </p>
            </div>
          </div>
        </div>

        {/* Staff Table Card */}
        <div className="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
          {/* Table Header & Search */}
          <div className="p-3.5 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 bg-gray-50/50">
            <div>
              <h3 className="text-xs font-bold text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                <User size={14} className="text-gray-700" />
                Staff Performance Roster ({filteredProviders.length})
              </h3>
              <p className="text-[11px] text-gray-500 mt-0.5">
                Click any specialist to open their individual 7-day revenue trend and appointment history.
              </p>
            </div>

            <div className="w-full sm:w-64">
              <input
                type="text"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Search stylist by name..."
                className="w-full px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"
              />
            </div>
          </div>

          <div className="overflow-x-auto min-h-[220px]">
            <table className="w-full text-left border-collapse text-xs">
              <thead>
                <tr className="bg-gray-50 border-b border-gray-200 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                  <th className="px-4 py-2.5">Stylist / Specialist</th>
                  <th className="px-4 py-2.5">Status</th>
                  <th className="px-4 py-2.5">Workload (Done / Total)</th>
                  <th className="px-4 py-2.5">Completion Rate</th>
                  <th className="px-4 py-2.5">Avg Ticket</th>
                  <th className="px-4 py-2.5">Gross Revenue</th>
                  <th className="px-4 py-2.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {loading ? (
                  Array.from({ length: 3 }).map((_, i) => (
                    <tr key={i} className="animate-pulse">
                      <td colSpan={7} className="px-4 py-3">
                        <div className="h-3.5 bg-gray-200 rounded w-full mb-1"></div>
                        <div className="h-2.5 bg-gray-100 rounded w-2/3"></div>
                      </td>
                    </tr>
                  ))
                ) : filteredProviders.length === 0 ? (
                  <tr>
                    <td colSpan={7} className="px-4 py-10 text-center text-gray-400">
                      No technicians found matching query.
                    </td>
                  </tr>
                ) : (
                  filteredProviders.map((p) => {
                    const isTop = data?.summary.top_performer?.id === p.id;
                    return (
                      <tr
                        key={p.id}
                        onClick={() => handleInspectProvider(p)}
                        className="hover:bg-gray-50/80 transition cursor-pointer group"
                      >
                        {/* 1. Stylist */}
                        <td className="px-4 py-3 align-middle">
                          <div className="flex items-center gap-2.5">
                            <div className="w-8 h-8 rounded-full bg-gray-900 text-white font-bold text-xs flex items-center justify-center shrink-0">
                              {p.name.charAt(0).toUpperCase()}
                            </div>
                            <div>
                              <div className="font-semibold text-gray-900 text-xs flex items-center gap-1.5">
                                <span>{p.name}</span>
                                {isTop && (
                                  <span className="p-0.5 text-amber-500" title="Top Performer">
                                    <Star size={12} weight="fill" />
                                  </span>
                                )}
                              </div>
                              <div className="text-[11px] text-gray-400 font-normal">
                                {p.phone || p.email || `ID: #${p.id}`}
                              </div>
                            </div>
                          </div>
                        </td>

                        {/* 2. Status */}
                        <td className="px-4 py-3 align-middle">
                          {p.active ? (
                            <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 uppercase">
                              Active
                            </span>
                          ) : (
                            <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600 border border-gray-200 uppercase">
                              Inactive
                            </span>
                          )}
                        </td>

                        {/* 3. Workload */}
                        <td className="px-4 py-3 align-middle text-gray-700">
                          <span className="font-semibold text-gray-900">{p.completed_bookings}</span>
                          <span className="text-gray-400"> / {p.total_bookings} bookings</span>
                        </td>

                        {/* 4. Completion Rate */}
                        <td className="px-4 py-3 align-middle">
                          <div className="flex items-center gap-2 max-w-[120px]">
                            <span
                              className={`text-xs font-bold ${
                                p.completion_rate >= 70
                                  ? "text-emerald-700"
                                  : p.completion_rate >= 40
                                  ? "text-amber-700"
                                  : "text-gray-500"
                              }`}
                            >
                              {p.completion_rate}%
                            </span>
                            <div className="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                              <div
                                style={{ width: `${p.completion_rate}%` }}
                                className={`h-full rounded-full ${
                                  p.completion_rate >= 70
                                    ? "bg-emerald-600"
                                    : p.completion_rate >= 40
                                    ? "bg-amber-500"
                                    : "bg-gray-400"
                                }`}
                              />
                            </div>
                          </div>
                        </td>

                        {/* 5. Avg Ticket */}
                        <td className="px-4 py-3 align-middle font-medium tabular-nums text-gray-700">
                          {p.avg_ticket.toLocaleString()} RWF
                        </td>

                        {/* 6. Revenue */}
                        <td className="px-4 py-3 align-middle font-bold tabular-nums text-gray-900">
                          {p.total_revenue.toLocaleString()} RWF
                        </td>

                        {/* 7. Action */}
                        <td className="px-4 py-3 align-middle text-right whitespace-nowrap">
                          <button
                            onClick={(e) => {
                              e.stopPropagation();
                              handleInspectProvider(p);
                            }}
                            className="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition shadow-2xs"
                          >
                            <Eye size={12} />
                            <span>Audit Report</span>
                          </button>
                        </td>
                      </tr>
                    );
                  })
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {/* Single Provider Performance Slide-Over Drawer */}
      {selectedProvider && (
        <div className="fixed inset-0 z-50 overflow-hidden flex justify-end bg-black/40 backdrop-blur-2xs transition-opacity animate-in fade-in">
          <div className="relative w-full max-w-xl bg-white h-full shadow-2xl flex flex-col z-10 animate-in slide-in-from-right duration-200">
            {/* Drawer Header */}
            <div className="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-gray-50">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-full bg-gray-900 text-white font-bold text-sm flex items-center justify-center">
                  {selectedProvider.name.charAt(0).toUpperCase()}
                </div>
                <div>
                  <h3 className="text-base font-bold text-gray-900 flex items-center gap-1.5">
                    <span>{selectedProvider.name}</span>
                    {selectedProvider.active ? (
                      <span className="w-2 h-2 rounded-full bg-emerald-500" title="Active Specialist" />
                    ) : (
                      <span className="w-2 h-2 rounded-full bg-gray-400" title="Inactive" />
                    )}
                  </h3>
                  <p className="text-xs text-gray-500 mt-0.5">
                    Stylist Performance Audit · Range: {range}
                  </p>
                </div>
              </div>
              <button
                onClick={() => setSelectedProvider(null)}
                className="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-200 transition"
              >
                <X size={18} />
              </button>
            </div>

            {/* Drawer Body */}
            <div className="flex-1 overflow-y-auto p-6 space-y-5 text-xs">
              {/* 4 Mini KPI Cards */}
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                <div className="bg-gray-50 rounded-xl p-3 border border-gray-200">
                  <span className="text-[10px] uppercase font-bold text-gray-400 block mb-0.5">Revenue</span>
                  <span className="font-bold tabular-nums text-sm text-gray-900">
                    {selectedProvider.total_revenue.toLocaleString()} RWF
                  </span>
                </div>
                <div className="bg-gray-50 rounded-xl p-3 border border-gray-200">
                  <span className="text-[10px] uppercase font-bold text-gray-400 block mb-0.5">Completed</span>
                  <span className="font-bold text-sm text-emerald-700">
                    {selectedProvider.completed_bookings} / {selectedProvider.total_bookings}
                  </span>
                </div>
                <div className="bg-gray-50 rounded-xl p-3 border border-gray-200">
                  <span className="text-[10px] uppercase font-bold text-gray-400 block mb-0.5">Completion Rate</span>
                  <span className="font-bold text-sm text-gray-900">
                    {selectedProvider.completion_rate}%
                  </span>
                </div>
                <div className="bg-gray-50 rounded-xl p-3 border border-gray-200">
                  <span className="text-[10px] uppercase font-bold text-gray-400 block mb-0.5">Avg Ticket</span>
                  <span className="font-bold tabular-nums text-sm text-gray-900">
                    {selectedProvider.avg_ticket.toLocaleString()} RWF
                  </span>
                </div>
              </div>

              {/* 7-Day Revenue Progression Bar Chart */}
              <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs space-y-3">
                <div className="flex items-center justify-between border-b border-gray-100 pb-2">
                  <span className="font-bold uppercase tracking-wider text-[11px] text-gray-700 flex items-center gap-1.5">
                    <ChartLineUp size={14} className="text-gray-900" />
                    Last 7 Days Revenue Trend
                  </span>
                  <span className="text-[10px] text-gray-400">Daily collections</span>
                </div>

                <div className="h-32 w-full flex items-end gap-2 pt-4 px-1">
                  {selectedProvider.daily_trend.map((point, index) => {
                    const heightPercent = Math.max(
                      8,
                      Math.round((point.total / maxProviderDailyRevenue) * 100)
                    );
                    const hasRev = point.total > 0;

                    return (
                      <div
                        key={index}
                        className="flex-1 flex flex-col items-center gap-1 group cursor-pointer h-full justify-end"
                      >
                        <div className="w-full flex items-end justify-center h-20 relative">
                          <div className="absolute -top-6 opacity-0 group-hover:opacity-100 transition-opacity bg-gray-900 text-white text-[9px] px-1.5 py-0.5 rounded shadow pointer-events-none whitespace-nowrap z-10 font-medium tabular-nums">
                            {point.total.toLocaleString()} RWF
                          </div>
                          <div
                            style={{ height: `${hasRev ? heightPercent : 6}%` }}
                            className={`w-full rounded-t-sm transition-all duration-300 ${
                              hasRev ? "bg-gray-900 group-hover:bg-emerald-600" : "bg-gray-100"
                            }`}
                          />
                        </div>
                        <span className="text-[9px] text-gray-400 font-medium tabular-nums">
                          {point.date}
                        </span>
                      </div>
                    );
                  })}
                </div>
              </div>

              {/* Top Treatments Rendered */}
              <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs space-y-2.5">
                <span className="font-bold uppercase tracking-wider text-[11px] text-gray-700 flex items-center gap-1.5">
                  <Scissors size={14} className="text-gray-900" />
                  Most Requested Treatments
                </span>

                <div className="divide-y divide-gray-100">
                  {selectedProvider.top_services.length === 0 ? (
                    <div className="py-2 text-gray-400 text-center">No service history found.</div>
                  ) : (
                    selectedProvider.top_services.map((s, idx) => (
                      <div key={idx} className="py-2 flex items-center justify-between text-xs">
                        <span className="font-semibold text-gray-900">{s.name}</span>
                        <div className="flex items-center gap-3">
                          <span className="text-gray-400">{s.count} times</span>
                          <span className="font-bold tabular-nums text-gray-800">
                            {s.revenue.toLocaleString()} RWF
                          </span>
                        </div>
                      </div>
                    ))
                  )}
                </div>
              </div>

              {/* Recent Appointments Handled */}
              {selectedProvider.recent_appointments && (
                <div className="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
                  <div className="bg-gray-50 px-3.5 py-2 border-b border-gray-200 font-bold uppercase tracking-wider text-[11px] text-gray-700">
                    Recent Client Appointments ({selectedProvider.recent_appointments.length})
                  </div>
                  <div className="divide-y divide-gray-100">
                    {selectedProvider.recent_appointments.length === 0 ? (
                      <div className="p-3 text-center text-gray-400">No appointments recorded yet.</div>
                    ) : (
                      selectedProvider.recent_appointments.map((app) => (
                        <div key={app.id} className="p-2.5 flex items-center justify-between hover:bg-gray-50">
                          <div>
                            <div className="font-semibold text-gray-900">{app.customer_name}</div>
                            <div className="text-[11px] text-gray-400">
                              {app.date_formatted} at {app.time_formatted} · {app.services.map((s) => s.name).join(", ")}
                            </div>
                          </div>
                          <div className="text-right">
                            <span className="font-bold tabular-nums text-gray-900">
                              {app.total_price.toLocaleString()} RWF
                            </span>
                            <div className="text-[10px] uppercase font-bold text-emerald-700">
                              {app.status}
                            </div>
                          </div>
                        </div>
                      ))
                    )}
                  </div>
                </div>
              )}
            </div>

            {/* Drawer Footer Actions */}
            <div className="px-6 py-3.5 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
              <Link
                href="/admin/providers"
                className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-semibold hover:bg-gray-800 transition shadow-xs"
              >
                <Scissors size={13} />
                <span>Configure Working Hours</span>
              </Link>
              <button
                onClick={() => setSelectedProvider(null)}
                className="px-3 py-1.5 bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-300 transition"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

