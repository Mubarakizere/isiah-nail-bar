import { Metadata } from "next";
import AdminOverview from "@/components/admin/AdminOverview";

export const dynamic = "force-dynamic";
export const revalidate = 0;

export const metadata: Metadata = {
  title: "Admin Sanctuary Console — Overview | Isaiah Nail Bar",
  description: "Real-time salon operations, performance metrics, and reservation management.",
};

export default function AdminPage() {
  return <AdminOverview />;
}
