import type { Metadata } from "next";
import AdminTags from "@/components/admin/AdminTags";

export const metadata: Metadata = {
  title: "Tags — Admin Sanctuary",
  description: "Manage saved service tag names.",
};

export default function AdminTagsPage() {
  return <AdminTags />;
}
