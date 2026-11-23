import type { Metadata } from "next";
import AdminProviders from "@/components/admin/AdminProviders";

export const metadata: Metadata = {
  title: "Providers — Admin Sanctuary",
  description: "Manage salon providers, service assignments, and team access.",
};

export default function AdminProvidersPage() {
  return <AdminProviders />;
}
