import { Metadata } from "next";
import AdminPerformance from "@/components/admin/AdminPerformance";

export const metadata: Metadata = {
  title: "Admin Sanctuary Console — Staff & Stylist Performance | Isaiah Nail Bar",
  description: "Technician productivity, completion rates, gross collections, and individual audits.",
};

export default function AdminPerformancePage() {
  return <AdminPerformance />;
}

