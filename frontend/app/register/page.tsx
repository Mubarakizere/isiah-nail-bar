import { Metadata } from "next";
import { Suspense } from "react";
import RegisterClient from "@/components/auth/RegisterClient";

export const dynamic = "force-dynamic";
export const revalidate = 0;

export const metadata: Metadata = {
  title: "Create Account | Isaiah Nail Bar Kigali",
  description: "Join Isaiah Nail Bar's community of elegance and easily reserve luxury nail rituals in Kigali.",
};

export default function RegisterPage() {
  return (
    <Suspense
      fallback={
        <div className="min-h-screen bg-[#FAF7F2] flex items-center justify-center text-xs text-[#8E857F]">
          Loading registration...
        </div>
      }
    >
      <RegisterClient />
    </Suspense>
  );
}

