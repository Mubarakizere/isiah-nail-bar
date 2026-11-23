import type { Metadata } from "next";
import AdminReviews from "@/components/admin/AdminReviews";

export const metadata: Metadata = {
  title: "Customer Reviews — Admin Sanctuary",
  description: "Manage customer and Google reviews displayed on the website.",
};

export default function AdminReviewsPage() {
  return <AdminReviews />;
}
