import { Metadata } from "next";
import { Suspense } from "react";
import LoginClient from "@/components/auth/LoginClient";

export const dynamic = "force-dynamic";
export const revalidate = 0;

export const metadata: Metadata = {
  title: "Sign In | Isaiah Nail Bar Kigali",
  description: "Sign in to access your appointments and bespoke nail sanctuary rituals.",
};

export default function LoginPage() {
  return (
    <Suspense
      fallback={
        <div className="min-h-screen bg-[#FAF7F2] flex items-center justify-center text-xs text-[#8E857F]">
          Loading sanctuary access...
        </div>
      }
    >
      <LoginClient />
    </Suspense>
  );
}

