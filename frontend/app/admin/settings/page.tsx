import type { Metadata } from "next";
import AdminSettings from "@/components/admin/AdminSettings";

export const metadata: Metadata = {
  title: "Site Settings — Admin Sanctuary",
  description: "Manage booking alert email recipients.",
};

export default function AdminSettingsPage() {
  return <AdminSettings />;
}
