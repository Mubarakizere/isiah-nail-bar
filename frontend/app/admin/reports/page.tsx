import { Metadata } from "next";
import AdminReports from "@/components/admin/AdminReports";

export const metadata: Metadata = {
  title: "Admin Sanctuary Console — Revenue Reports | Isaiah Nail Bar",
  description: "Revenue audit, financial performance metrics, and PDF exports.",
};

export default function AdminReportsPage() {
  return <AdminReports />;
}

