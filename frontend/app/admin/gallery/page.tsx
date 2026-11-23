import type { Metadata } from "next";
import AdminGallery from "@/components/admin/AdminGallery";

export const metadata: Metadata = {
  title: "Instagram Gallery — Admin Sanctuary",
  description: "Manage Instagram posts displayed in the website gallery.",
};

export default function AdminGalleryPage() {
  return <AdminGallery />;
}
