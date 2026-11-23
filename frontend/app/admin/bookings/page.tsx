import { Metadata } from "next";
import AdminBookings from "@/components/admin/AdminBookings";

export const metadata: Metadata = {
  title: "Admin Sanctuary Console — All Bookings | Isaiah Nail Bar",
  description: "Manage, filter, confirm, and review customer bookings and payments.",
};

export default function AdminBookingsPage() {
  return <AdminBookings />;
}

