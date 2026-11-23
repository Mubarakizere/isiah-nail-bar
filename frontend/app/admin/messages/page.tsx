import type { Metadata } from "next";
import AdminMessages from "@/components/admin/AdminMessages";

export const metadata: Metadata = {
  title: "Messages — Admin Sanctuary",
  description: "View and manage customer inquiries.",
};

export default function AdminMessagesPage() {
  return <AdminMessages />;
}
