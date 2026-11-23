import { Metadata } from "next";
import ClientDashboard from "@/components/dashboard/ClientDashboard";

export const metadata: Metadata = {
  title: "Sanctuary Portal — My Appointments | Isaiah Nail Bar",
  description:
    "Manage your appointments, view treatment receipts, and schedule your bespoke manicure and pedicure rituals at Isaiah Nail Bar Kigali.",
};

export default function DashboardPage() {
  return <ClientDashboard />;
}

