import type { Metadata } from "next";
import AdminHeroSlides from "@/components/admin/AdminHeroSlides";

export const metadata: Metadata = {
  title: "Hero Slides — Admin Sanctuary",
  description: "Manage homepage carousel images and content.",
};

export default function AdminHeroSlidesPage() {
  return <AdminHeroSlides />;
}
