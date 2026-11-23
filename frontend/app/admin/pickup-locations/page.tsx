import type { Metadata } from "next";
import AdminPickupLocations from "@/components/admin/AdminPickupLocations";

export const metadata: Metadata = {
  title: "Pickup Locations — Admin Sanctuary",
  description: "Manage pickup routes and transport fees for bookings.",
};

export default function AdminPickupLocationsPage() {
  return <AdminPickupLocations />;
}
