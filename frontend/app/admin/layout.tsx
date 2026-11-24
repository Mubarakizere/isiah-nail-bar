import type { Metadata } from "next";
import AdminClientLayout from "./AdminClientLayout";

export const dynamic = "force-dynamic";
export const revalidate = 0;

export const metadata: Metadata = {
  title: {
    default: "Admin Sanctuary Console | Isaiah Nail Bar",
    template: "%s | Isaiah Nail Bar Admin",
  },
  robots: { index: false, follow: false },
};

export default function AdminLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return <AdminClientLayout>{children}</AdminClientLayout>;
}
