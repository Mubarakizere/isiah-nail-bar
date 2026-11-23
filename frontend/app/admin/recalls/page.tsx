import type { Metadata } from "next";
import AdminRecalls from "@/components/admin/AdminRecalls";

export const metadata: Metadata = {
  title: "Client Call List & Re-booking Recalls | Admin",
  description: "Identify and contact clients overdue for their 3-week nail refill or care.",
};

export default function AdminRecallsPage() {
  return <AdminRecalls />;
}
