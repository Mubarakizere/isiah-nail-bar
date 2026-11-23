import type { Metadata } from "next";
import AdminSlots from "@/components/admin/AdminSlots";

export const metadata: Metadata = {
  title: "Time Slots — Admin Sanctuary",
  description: "Manage provider availability and appointment time slots.",
};

export default function AdminSlotsPage() {
  return <AdminSlots />;
}
