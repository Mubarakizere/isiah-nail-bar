import type { Metadata } from "next";
import AdminEmailLogs from "@/components/admin/AdminEmailLogs";

export const metadata: Metadata = {
  title: "Email Logs — Admin Sanctuary",
  description: "Review email delivery history for the salon.",
};

export default function AdminEmailLogsPage() {
  return <AdminEmailLogs />;
}
