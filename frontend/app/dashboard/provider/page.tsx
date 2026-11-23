import type { Metadata } from "next";
import ProviderDashboard from "@/components/dashboard/ProviderDashboard";

export const metadata: Metadata = {
  title: "Provider Workspace | Isaiah Nail Bar",
  description: "Manage your assigned appointments at Isaiah Nail Bar.",
};

export default function ProviderDashboardPage() {
  return <ProviderDashboard />;
}
