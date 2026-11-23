"use client";

import { useState, useEffect } from "react";
import Image from "next/image";
import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { motion } from "framer-motion";
import {
  User,
  EnvelopeSimple,
  LockKey,
  ArrowRight,
  Eye,
  EyeSlash,
  CheckCircle,
  WarningCircle,
  ShieldCheck,
  FlowerLotus,
} from "@phosphor-icons/react";
import { useAuth } from "@/lib/auth-context";

export default function RegisterClient() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const redirectUrl = searchParams.get("redirect") || "/dashboard";

  const { register } = useAuth();

  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [agreeTerms, setAgreeTerms] = useState(true);

  const [showPassword, setShowPassword] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [errorMsg, setErrorMsg] = useState("");
  const [successMsg, setSuccessMsg] = useState("");
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
  }, []);

  if (!mounted) {
    return null;
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg("");
    setSuccessMsg("");

    if (!name.trim() || !email.trim() || !password) {
      setErrorMsg("Please fill in all required fields.");
      return;
    }

    if (password.length < 6) {
      setErrorMsg("Password must be at least 6 characters long.");
      return;
    }

    if (password !== passwordConfirmation) {
      setErrorMsg("Passwords do not match. Please verify.");
      return;
    }

    if (!agreeTerms) {
      setErrorMsg("Please accept our sanctuary guidelines to continue.");
      return;
    }

    setSubmitting(true);

    try {
      const res = await register(
        name.trim(),
        email.trim(),
        password,
        passwordConfirmation
      );

      if (res.success) {
        setSuccessMsg(res.message || "Account created successfully! Welcome to Isaiah.");
        setTimeout(() => {
          router.push(redirectUrl);
        }, 800);
      } else {
        setErrorMsg(res.message || "Failed to create account. Please try again.");
        setSubmitting(false);
      }
    } catch {
      setErrorMsg("An unexpected connection error occurred. Please try again.");
      setSubmitting(false);
    }
  };

  return (
    <div className="min-h-screen lg:h-screen lg:overflow-hidden grid grid-cols-1 lg:grid-cols-12 bg-[#FAF7F2]">
      {/* ========================================================= */}
      {/* Left Column: Artistic Brand Imagery (5 cols on lg) */}
      {/* ========================================================= */}
      <div className="hidden lg:block lg:col-span-5 relative bg-[#181514] h-full overflow-hidden">
        <Image
          src="/banner.jpg"
          alt="Isaiah Nail Bar Kigali"
          fill
          priority
          sizes="50vw"
          className="object-cover opacity-60"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-[#181514] via-[#181514]/40 to-transparent" />

        {/* Brand Quote & Highlights */}
        <div className="absolute bottom-0 left-0 right-0 p-10 xl:p-12 text-white z-10 space-y-5">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-[#E8C99B] text-xs">
            <FlowerLotus size={14} weight="bold" />
            <span>Sanctuary Guest Privileges</span>
          </div>

          <h2 className="font-serif text-3xl xl:text-4xl text-white font-normal leading-tight">
            &ldquo;Join our community of elegance.&rdquo;
          </h2>

          <div className="flex items-center gap-4">
            <div className="h-px bg-[#B8975A] w-12" />
            <p className="text-[#E8C99B] uppercase tracking-[0.22em] text-xs font-semibold">
              Isaiah Nail Bar
            </p>
          </div>

          <div className="pt-1 flex flex-col gap-2 text-xs text-white/75">
            <div className="flex items-center gap-2">
              <ShieldCheck size={16} className="text-[#34A853]" />
              <span>Medical-grade autoclave sterilization for every appointment</span>
            </div>
            <div className="flex items-center gap-2">
              <span className="w-1.5 h-1.5 rounded-full bg-[#B8975A]" />
              <span>Priority scheduling with certified European nail masters</span>
            </div>
          </div>
        </div>
      </div>

      {/* ========================================================= */}
      {/* Right Column: Borderless Clean Registration Form (7 cols on lg) */}
      {/* ========================================================= */}
      <div className="lg:col-span-7 flex flex-col justify-center px-6 sm:px-12 lg:px-16 xl:px-20 py-8 lg:py-6 h-full overflow-y-auto lg:overflow-visible">
        <motion.div
          initial={{ opacity: 0, y: 12 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.35 }}
          className="w-full max-w-lg mx-auto"
        >
          {/* Header sitting directly on the canvas */}
          <div className="mb-5 sm:mb-6">
            <Link href="/" className="inline-flex items-center gap-2.5 mb-3 group">
              <div className="relative h-10 w-10 rounded-xl overflow-hidden bg-white p-1 border border-[#E5DFD5] transition-transform duration-300 group-hover:scale-105">
                <Image
                  src="/logo.png"
                  alt="Isaiah Nail Bar"
                  fill
                  sizes="40px"
                  className="object-contain"
                />
              </div>
              <span className="font-serif font-bold text-base tracking-tight text-[#221C1A]">
                Isaiah Nail Bar
              </span>
            </Link>

            <span className="text-[10px] font-semibold tracking-[0.22em] uppercase text-[#B8975A] block mb-1">
              New Guest Registration
            </span>

            <h1 className="font-serif text-2xl sm:text-3xl text-[#221C1A] font-normal tracking-tight">
              Create an Account
            </h1>

            <p className="text-xs text-[#6E6660] font-light mt-1">
              Register to book appointments, manage visits, and unlock concierge privileges.
            </p>
          </div>

          {/* Feedback alerts */}
          {errorMsg && (
            <div className="mb-4 p-3 rounded-xl bg-[#FDEDEC] border border-[#F5B7B1] text-[#C0392B] text-xs flex items-start gap-2.5">
              <WarningCircle size={16} className="shrink-0 mt-0.5" />
              <span>{errorMsg}</span>
            </div>
          )}

          {successMsg && (
            <div className="mb-4 p-3 rounded-xl bg-[#E6F4EA] border border-[#34A853]/30 text-[#2D8E47] text-xs flex items-start gap-2.5">
              <CheckCircle size={16} className="shrink-0 mt-0.5" weight="fill" />
              <span>{successMsg}</span>
            </div>
          )}

          {/* Borderless clean form */}
          <form onSubmit={handleSubmit} className="space-y-3.5">
            {/* Full Name */}
            <div>
              <label
                htmlFor="name"
                className="block text-[11px] font-semibold uppercase tracking-wider text-[#221C1A] mb-1"
              >
                Full Name
              </label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8E857F]">
                  <User size={16} />
                </div>
                <input
                  id="name"
                  type="text"
                  required
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  placeholder="Claire Mukamana"
                  autoComplete="name"
                  className="w-full pl-9 pr-4 py-2.5 bg-white border border-[#E2DBD1] focus:border-[#221C1A] rounded-xl text-xs sm:text-sm text-[#221C1A] placeholder-[#9E958E] outline-none transition-colors"
                />
              </div>
            </div>

            {/* Email Address */}
            <div>
              <label
                htmlFor="reg-email"
                className="block text-[11px] font-semibold uppercase tracking-wider text-[#221C1A] mb-1"
              >
                Email Address
              </label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8E857F]">
                  <EnvelopeSimple size={16} />
                </div>
                <input
                  id="reg-email"
                  type="email"
                  required
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="claire@example.com"
                  autoComplete="email"
                  className="w-full pl-9 pr-4 py-2.5 bg-white border border-[#E2DBD1] focus:border-[#221C1A] rounded-xl text-xs sm:text-sm text-[#221C1A] placeholder-[#9E958E] outline-none transition-colors"
                />
              </div>
            </div>

            {/* Password & Confirm Password (2-col grid on sm+) */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label
                  htmlFor="reg-password"
                  className="block text-[11px] font-semibold uppercase tracking-wider text-[#221C1A] mb-1"
                >
                  Password
                </label>
                <div className="relative">
                  <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8E857F]">
                    <LockKey size={16} />
                  </div>
                  <input
                    id="reg-password"
                    type={showPassword ? "text" : "password"}
                    required
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="Min 6 characters"
                    autoComplete="new-password"
                    className="w-full pl-9 pr-9 py-2.5 bg-white border border-[#E2DBD1] focus:border-[#221C1A] rounded-xl text-xs sm:text-sm text-[#221C1A] placeholder-[#9E958E] outline-none transition-colors font-mono"
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute inset-y-0 right-0 pr-3 flex items-center text-[#8E857F] hover:text-[#221C1A] transition-colors cursor-pointer"
                    tabIndex={-1}
                  >
                    {showPassword ? <EyeSlash size={16} /> : <Eye size={16} />}
                  </button>
                </div>
              </div>

              <div>
                <label
                  htmlFor="confirm-password"
                  className="block text-[11px] font-semibold uppercase tracking-wider text-[#221C1A] mb-1"
                >
                  Confirm Password
                </label>
                <div className="relative">
                  <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8E857F]">
                    <CheckCircle size={16} />
                  </div>
                  <input
                    id="confirm-password"
                    type={showPassword ? "text" : "password"}
                    required
                    value={passwordConfirmation}
                    onChange={(e) => setPasswordConfirmation(e.target.value)}
                    placeholder="Repeat password"
                    autoComplete="new-password"
                    className="w-full pl-9 pr-4 py-2.5 bg-white border border-[#E2DBD1] focus:border-[#221C1A] rounded-xl text-xs sm:text-sm text-[#221C1A] placeholder-[#9E958E] outline-none transition-colors font-mono"
                  />
                </div>
              </div>
            </div>

            {/* Agreement Checkbox */}
            <div className="pt-1">
              <label className="flex items-start gap-2.5 cursor-pointer text-xs text-[#5C5550]">
                <input
                  type="checkbox"
                  checked={agreeTerms}
                  onChange={(e) => setAgreeTerms(e.target.checked)}
                  className="w-4 h-4 mt-0.5 accent-[#221C1A] cursor-pointer"
                />
                <span className="leading-snug">
                  I agree to Isaiah Nail Bar&apos;s hygiene etiquette and sanctuary booking policies.
                </span>
              </label>
            </div>

            {/* Submit Button */}
            <div className="pt-1.5">
              <button
                type="submit"
                disabled={submitting}
                className="w-full py-3 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-all duration-300 shadow-sm flex items-center justify-center gap-2 group cursor-pointer disabled:opacity-50"
              >
                <span>{submitting ? "Creating Sanctuary Account..." : "Create Account"}</span>
                <ArrowRight
                  size={15}
                  className="group-hover:translate-x-1 transition-transform"
                />
              </button>
            </div>
          </form>

          {/* Switch to Login */}
          <div className="mt-5 pt-4 border-t border-[#E8E2D9] text-center text-xs text-[#5C5550]">
            <p>
              Already registered?{" "}
              <Link
                href={`/login${redirectUrl !== "/" ? `?redirect=${encodeURIComponent(redirectUrl)}` : ""}`}
                className="font-bold text-[#221C1A] hover:text-[#B8975A] transition-colors ml-1"
              >
                Sign in to your account &rarr;
              </Link>
            </p>
          </div>
        </motion.div>
      </div>
    </div>
  );
}
