import type { Metadata } from "next";
import AdminServices from "@/components/admin/AdminServices";

export const metadata: Metadata = {
  title: "Services — Admin Sanctuary",
  description: "Manage the service menu, pricing, and provider submissions.",
};

export default function AdminServicesPage() {
  return <AdminServices />;
}
