import type { Metadata } from "next";
import AdminWebhooks from "@/components/admin/AdminWebhooks";

export const metadata: Metadata = {
  title: "Webhook Logs — Admin Sanctuary",
  description: "Monitor payment gateway webhook activity and payloads.",
};

export default function AdminWebhooksPage() {
  return <AdminWebhooks />;
}
