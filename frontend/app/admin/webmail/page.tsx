import type { Metadata } from "next";
import AdminWebmail from "@/components/admin/AdminWebmail";

export const metadata: Metadata = {
  title: "Webmail Inbox — Admin Sanctuary",
  description: "Read and reply to business email.",
};

export default function AdminWebmailPage() {
  return <AdminWebmail />;
}
