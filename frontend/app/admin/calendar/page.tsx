import type { Metadata } from "next";
import AdminCalendar from "@/components/admin/AdminCalendar";

export const metadata: Metadata = {
  title: "Unified Calendar — Admin Sanctuary",
  description: "Coordinate appointments, providers, blocked slots, and walk-in visits.",
};

export default function AdminCalendarPage() {
  return <AdminCalendar />;
}
