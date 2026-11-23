import { Metadata } from "next";
import AdminCategories from "@/components/admin/AdminCategories";

export const metadata: Metadata = {
  title: "Service Categories — Admin Sanctuary",
  description: "Organize treatment categories and luxury salon collections.",
};

export default function AdminCategoriesPage() {
  return <AdminCategories />;
}

