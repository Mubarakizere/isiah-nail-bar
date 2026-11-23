"use client";

import { useState, useEffect } from "react";
import Image from "next/image";
import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { motion } from "framer-motion";
import {
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

export default function LoginClient() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const redirectUrl = searchParams.get("redirect") || "/dashboard";

  const { login } = useAuth();

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [rememberMe, setRememberMe] = useState(false);
  const [showPassword, setShowPassword] = useState(false);

  const [submitting, setSubmitting] = useState(false);
  const [errorMsg, setErrorMsg] = useState("");
  const [successMsg, setSuccessMsg] = useState("");
  const [showForgotModal, setShowForgotModal] = useState(false);
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
  }, []);

  useEffect(() => {
    if (searchParams.get("forgot") === "1") setShowForgotModal(true);
  }, [searchParams]);

  if (!mounted) {
    return null;
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg("");
    setSuccessMsg("");

    if (!email.trim() || !password) {
      setErrorMsg("Please enter both email and password.");
      return;
    }

    setSubmitting(true);

    try {
      const res = await login(email.trim(), password);

      if (res.success) {
        setSuccessMsg(res.message || "Signed in successfully. Redirecting...");
        const targetUrl =
          searchParams.get("redirect") ||
          (res.user?.role === "admin"
            ? "/admin"
            : res.user?.role === "provider"
              ? "/dashboard/provider"
              : "/dashboard");
        setTimeout(() => {
          router.push(targetUrl);
        }, 800);
      } else {
        setErrorMsg(res.message || "Invalid email or password.");
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
            <span>Kigali&apos;s Premier Boutique Sanctuary</span>
          </div>

          <h2 className="font-serif text-3xl xl:text-4xl text-white font-normal leading-tight">
            &ldquo;Self-care is not a luxury. It&apos;s a necessity.&rdquo;
          </h2>

          <div className="flex items-center gap-4">
            <div className="h-px bg-[#B8975A] w-12" />
            <p className="text-[#E8C99B] uppercase tracking-[0.22em] text-xs font-semibold">
              Isaiah Nail Bar
            </p>
          </div>

          <div className="pt-1 flex items-center gap-6 text-xs text-white/70">
            <div className="flex items-center gap-2">
              <ShieldCheck size={16} className="text-[#34A853]" />
              <span>Autoclave Sterilization</span>
            </div>
            <div className="flex items-center gap-2">
              <span className="w-1.5 h-1.5 rounded-full bg-[#B8975A]" />
              <span>HEMA-Free Gels</span>
            </div>
          </div>
        </div>
      </div>

      {/* ========================================================= */}
      {/* Right Column: Borderless Clean Sign In Form (7 cols on lg) */}
      {/* ========================================================= */}
      <div className="lg:col-span-7 flex flex-col justify-center px-6 sm:px-12 lg:px-16 xl:px-20 py-8 lg:py-6 h-full overflow-y-auto lg:overflow-visible">
        <motion.div
          initial={{ opacity: 0, y: 12 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.35 }}
          className="w-full max-w-lg mx-auto"
        >
          {/* Header sitting directly on the canvas */}
          <div className="mb-6 sm:mb-7">
            <Link href="/" className="inline-flex items-center gap-2.5 mb-3.5 group">
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
              Sanctuary Access
            </span>

            <h1 className="font-serif text-2xl sm:text-3xl text-[#221C1A] font-normal tracking-tight">
              Welcome Back
            </h1>

            <p className="text-xs text-[#6E6660] font-light mt-1">
              Sign in to manage your appointments, view history, and reserve treatments.
            </p>
          </div>

          {/* Feedback alerts */}
          {errorMsg && (
            <div className="mb-4 p-3.5 rounded-xl bg-[#FDEDEC] border border-[#F5B7B1] text-[#C0392B] text-xs flex items-start gap-2.5">
              <WarningCircle size={16} className="shrink-0 mt-0.5" />
              <span>{errorMsg}</span>
            </div>
          )}

          {successMsg && (
            <div className="mb-4 p-3.5 rounded-xl bg-[#E6F4EA] border border-[#34A853]/30 text-[#2D8E47] text-xs flex items-start gap-2.5">
              <CheckCircle size={16} className="shrink-0 mt-0.5" weight="fill" />
              <span>{successMsg}</span>
            </div>
          )}

          {/* Borderless clean form */}
          <form onSubmit={handleSubmit} className="space-y-4">
            {/* Email Address */}
            <div>
              <label
                htmlFor="email"
                className="block text-[11px] font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5"
              >
                Email Address
              </label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8E857F]">
                  <EnvelopeSimple size={17} />
                </div>
                <input
                  id="email"
                  type="email"
                  required
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="marieclaire@example.com"
                  autoComplete="email"
                  className="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-white border border-[#E2DBD1] focus:border-[#221C1A] rounded-xl text-xs sm:text-sm text-[#221C1A] placeholder-[#9E958E] outline-none transition-colors"
                />
              </div>
            </div>

            {/* Password */}
            <div>
              <div className="flex items-center justify-between mb-1.5">
                <label
                  htmlFor="password"
                  className="block text-[11px] font-semibold uppercase tracking-wider text-[#221C1A]"
                >
                  Password
                </label>
                <button
                  type="button"
                  onClick={() => setShowForgotModal(true)}
                  className="text-xs text-[#8E857F] hover:text-[#B8975A] transition-colors cursor-pointer"
                >
                  Forgot password?
                </button>
              </div>

              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8E857F]">
                  <LockKey size={17} />
                </div>
                <input
                  id="password"
                  type={showPassword ? "text" : "password"}
                  required
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                  autoComplete="current-password"
                  className="w-full pl-10 pr-10 py-2.5 sm:py-3 bg-white border border-[#E2DBD1] focus:border-[#221C1A] rounded-xl text-xs sm:text-sm text-[#221C1A] placeholder-[#9E958E] outline-none transition-colors font-mono"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#8E857F] hover:text-[#221C1A] transition-colors cursor-pointer"
                  tabIndex={-1}
                >
                  {showPassword ? <EyeSlash size={17} /> : <Eye size={17} />}
                </button>
              </div>
            </div>

            {/* Remember Me */}
            <div className="flex items-center justify-between text-xs pt-0.5">
              <label className="flex items-center gap-2 cursor-pointer text-[#5C5550]">
                <input
                  type="checkbox"
                  checked={rememberMe}
                  onChange={(e) => setRememberMe(e.target.checked)}
                  className="w-4 h-4 accent-[#221C1A] cursor-pointer"
                />
                <span>Remember this device</span>
              </label>
            </div>

            {/* Submit Button */}
            <div className="pt-2">
              <button
                type="submit"
                disabled={submitting}
                className="w-full py-3.5 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-all duration-300 shadow-sm flex items-center justify-center gap-2 group cursor-pointer disabled:opacity-50"
              >
                <span>{submitting ? "Signing in..." : "Sign In to Sanctuary"}</span>
                <ArrowRight
                  size={16}
                  className="group-hover:translate-x-1 transition-transform"
                />
              </button>
            </div>
          </form>

          {/* Switch to Register */}
          <div className="mt-6 pt-5 border-t border-[#E8E2D9] text-center text-xs text-[#5C5550]">
            <p>
              Don&apos;t have an account yet?{" "}
              <Link
                href={`/register${redirectUrl !== "/" ? `?redirect=${encodeURIComponent(redirectUrl)}` : ""}`}
                className="font-bold text-[#221C1A] hover:text-[#B8975A] transition-colors ml-1"
              >
                Create an account &rarr;
              </Link>
            </p>
          </div>
        </motion.div>
      </div>

      {/* Forgot Password Modal */}
      {showForgotModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            className="w-full max-w-sm bg-white rounded-3xl p-6 sm:p-8 shadow-2xl text-center space-y-4"
          >
            <div className="w-12 h-12 rounded-2xl bg-[#FAF7F2] border border-[#B8975A]/30 text-[#B8975A] flex items-center justify-center mx-auto">
              <LockKey size={24} />
            </div>
            <h3 className="font-serif text-xl text-[#221C1A]">Password Assistance</h3>
            <p className="text-xs text-[#5C5550] leading-relaxed">
              For security, account resets are handled directly through our concierge team or via your registered email.
            </p>
            <div className="p-3 rounded-2xl bg-[#FAF7F2] border border-[#E5DFD5] text-xs text-[#221C1A] font-medium">
              Call Concierge: <a href="tel:0790395169" className="text-[#B8975A] font-bold underline">+250 790 395 169</a>
            </div>
            <button
              type="button"
              onClick={() => setShowForgotModal(false)}
              className="w-full py-2.5 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-[#FAF7F2] hover:bg-[#E5DFD5] transition-colors cursor-pointer"
            >
              Close
            </button>
          </motion.div>
        </div>
      )}
    </div>
  );
}
