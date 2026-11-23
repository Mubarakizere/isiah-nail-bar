import type { Metadata } from "next";
import AdminLoyalty from "@/components/admin/AdminLoyalty";

export const metadata: Metadata = {
  title: "Loyalty & Stamp Cards | Admin",
  description: "Manage client digital stamp cards, award stamps, and redeem reward vouchers.",
};

export default function AdminLoyaltyPage() {
  return <AdminLoyalty />;
}
