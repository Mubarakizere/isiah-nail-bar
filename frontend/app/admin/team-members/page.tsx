import type { Metadata } from "next";
import AdminTeamMembers from "@/components/admin/AdminTeamMembers";

export const metadata: Metadata = {
  title: "Team Members — Admin Sanctuary",
  description: "Manage non-provider team profiles and their public display order.",
};

export default function AdminTeamMembersPage() {
  return <AdminTeamMembers />;
}
