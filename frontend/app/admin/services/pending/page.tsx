import type { Metadata } from "next";
import AdminPendingServices from "@/components/admin/AdminPendingServices";

export const metadata: Metadata = {
  title: "Pending Services — Admin Sanctuary",
  description: "Review and approve services submitted by providers.",
};

export default function AdminPendingServicesPage() {
  return <AdminPendingServices />;
}
