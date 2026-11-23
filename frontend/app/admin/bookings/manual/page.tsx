import type { Metadata } from "next";
import AdminManualBooking from "@/components/admin/AdminManualBooking";

export const metadata: Metadata = {
  title: "Manual Booking — Admin Sanctuary",
  description: "Create a customer booking from the admin dashboard.",
};

export default function AdminManualBookingPage() {
  return <AdminManualBooking />;
}
