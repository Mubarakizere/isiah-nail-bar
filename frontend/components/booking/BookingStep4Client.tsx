"use client";

import { useState, useEffect, useMemo } from "react";
import Image from "next/image";
import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import {
  ArrowLeft,
  ArrowRight,
  CalendarCheck,
  Clock,
  MapPin,
  House,
  Storefront,
  Car,
  Check,
  Info,
  WhatsappLogo,
  ShieldCheck,
  User,
  PhoneCall,
  EnvelopeSimple,
  CreditCard,
  DeviceMobile,
  CurrencyCircleDollar,
  Receipt,
  CheckCircle,
  LockKey,
  Eye,
  EyeSlash,
  SignIn,
  UserPlus,
  SignOut,
  Sparkle,
  Crown,
} from "@phosphor-icons/react";
import { TeamProvider } from "@/lib/team-data";
import { ServiceItem } from "@/lib/services-data";
import { useAuth } from "@/lib/auth-context";

interface BookingStep4ClientProps {
  providers: TeamProvider[];
  allServices: ServiceItem[];
}

export default function BookingStep4Client({
  providers,
  allServices,
}: BookingStep4ClientProps) {
  const router = useRouter();
  const searchParams = useSearchParams();
  const { user, login, register: authRegister, logout } = useAuth();

  // State inherited from Steps 1, 2, 3
  const [selectedServices, setSelectedServices] = useState<ServiceItem[]>([]);
  const [selectedProviderId, setSelectedProviderId] = useState<number | null>(null);
  const [selectedDate, setSelectedDate] = useState<string>("");
  const [selectedTime, setSelectedTime] = useState<string>("");
  const [locationType, setLocationType] = useState<"salon" | "home">("salon");
  const [homeAddress, setHomeAddress] = useState<string>("");
  const [wantsPickup, setWantsPickup] = useState<boolean>(false);
  const [pickupFee, setPickupFee] = useState<number>(0);
  const [pickupAddress, setPickupAddress] = useState<string>("");

  // Step 4 Form State
  const [guestName, setGuestName] = useState("");
  const [guestPhone, setGuestPhone] = useState("");
  const [guestEmail, setGuestEmail] = useState("");
  const [specialNotes, setSpecialNotes] = useState("");
  const [silentAppointment, setSilentAppointment] = useState(false);
  const [paymentOption, setPaymentOption] = useState<"full" | "deposit">("full");
  const [paymentMethod, setPaymentMethod] = useState<"momo" | "card">("momo");

  // Authentication Mode & Form States
  const [authTab, setAuthTab] = useState<"register" | "signin" | "guest">("register");
  const [showPassword, setShowPassword] = useState(false);
  const [loginInput, setLoginInput] = useState("");
  const [loginPassword, setLoginPassword] = useState("");
  const [loginError, setLoginError] = useState<string | null>(null);
  const [isLoggingIn, setIsLoggingIn] = useState(false);

  const [registerPassword, setRegisterPassword] = useState("");
  const [registerConfirmPassword, setRegisterConfirmPassword] = useState("");
  const [registerError, setRegisterError] = useState<string | null>(null);
  const [isRegistering, setIsRegistering] = useState(false);

  // Auto-fill from authenticated user
  useEffect(() => {
    if (user) {
      if (user.name) setGuestName(user.name);
      if (user.email) setGuestEmail(user.email);
      if (user.phone) setGuestPhone(user.phone);
    }
  }, [user]);

  // Load prior data
  useEffect(() => {
    // 1. Services
    const servicesParam = searchParams.get("services");
    let serviceIds: number[] = [];
    if (servicesParam) {
      serviceIds = servicesParam
        .split(",")
        .map((s) => parseInt(s.trim(), 10))
        .filter((n) => !isNaN(n));
    } else {
      try {
        const saved = localStorage.getItem("isaiah_booking_selection");
        if (saved) serviceIds = JSON.parse(saved);
      } catch {
        // Ignore
      }
    }
    if (serviceIds.length > 0) {
      const matched = allServices.filter((s) => serviceIds.includes(s.id));
      setSelectedServices(matched);
    }

    // 2. Provider
    const providerParam = searchParams.get("provider");
    if (providerParam !== null) {
      const pid = parseInt(providerParam, 10);
      if (!isNaN(pid)) setSelectedProviderId(pid);
    } else {
      try {
        const savedProvider = localStorage.getItem("isaiah_booking_provider");
        if (savedProvider !== null) {
          const pid = parseInt(savedProvider, 10);
          if (!isNaN(pid)) setSelectedProviderId(pid);
        }
      } catch {
        // Ignore
      }
    }

    // 3. Date & Time from URL or LocalStorage
    const dateParam = searchParams.get("date");
    if (dateParam) setSelectedDate(dateParam);
    const timeParam = searchParams.get("time");
    if (timeParam) setSelectedTime(timeParam);
    const locParam = searchParams.get("location");
    if (locParam === "home") setLocationType("home");

    // Check step 3 saved data
    try {
      const savedStep3 = localStorage.getItem("isaiah_booking_step3_data");
      if (savedStep3) {
        const parsed = JSON.parse(savedStep3);
        if (parsed.date && !dateParam) setSelectedDate(parsed.date);
        if (parsed.time && !timeParam) setSelectedTime(parsed.time);
        if (parsed.locationType) setLocationType(parsed.locationType);
        if (parsed.homeAddress) setHomeAddress(parsed.homeAddress);
        if (parsed.wantsPickup) {
          setWantsPickup(true);
          setPickupFee(parsed.pickupFee || 5000);
          if (parsed.pickupAddress) setPickupAddress(parsed.pickupAddress);
        }
      }
    } catch {
      // Ignore
    }

    // Check guest info in localstorage if previously filled and user is not logged in
    try {
      const savedGuest = localStorage.getItem("isaiah_booking_guest_info");
      if (savedGuest) {
        const parsed = JSON.parse(savedGuest);
        if (parsed.name && !user) setGuestName(parsed.name);
        if (parsed.phone && !user) setGuestPhone(parsed.phone);
        if (parsed.email && !user) setGuestEmail(parsed.email);
        if (parsed.notes) setSpecialNotes(parsed.notes);
      }
    } catch {
      // Ignore
    }
  }, [searchParams, allServices, user]);

  // Selected Provider Object
  const selectedProvider = useMemo(() => {
    if (selectedProviderId === null) return null;
    if (selectedProviderId === 0) {
      return {
        id: 0,
        name: "First Available Artist",
        role: "Optimal Availability",
        photo: "/banner.jpg",
      };
    }
    return providers.find((p) => p.id === selectedProviderId) || null;
  }, [selectedProviderId, providers]);

  // Pricing Calculations
  const baseServicePrice = useMemo(() => {
    return selectedServices.reduce((sum, s) => sum + s.price, 0);
  }, [selectedServices]);

  const totalDurationMinutes = useMemo(() => {
    return selectedServices.reduce((sum, s) => sum + (s.duration_minutes || 60), 0);
  }, [selectedServices]);

  const formattedDuration = useMemo(() => {
    const hours = Math.floor(totalDurationMinutes / 60);
    const mins = totalDurationMinutes % 60;
    if (hours > 0 && mins > 0) return `${hours} hr ${mins} min`;
    if (hours > 0) return `${hours} hr`;
    return `${mins} min`;
  }, [totalDurationMinutes]);

  const locationMultiplier = locationType === "home" ? 2 : 1;
  const currentPickupFee = wantsPickup && locationType === "salon" ? pickupFee : 0;
  const totalInvestment = baseServicePrice * locationMultiplier + currentPickupFee;

  // Deposit is 40% of total
  const depositAmount = useMemo(() => {
    return Math.round(totalInvestment * 0.4);
  }, [totalInvestment]);

  const payableToday = paymentOption === "full" ? totalInvestment : depositAmount;
  const remainingAtSalon = paymentOption === "full" ? 0 : totalInvestment - depositAmount;

  const formatRwf = (amount: number) => {
    return new Intl.NumberFormat("en-RW", {
      style: "decimal",
      maximumFractionDigits: 0,
    }).format(amount);
  };

  // Formatted date string
  const formattedSelectedDate = useMemo(() => {
    if (!selectedDate) return "Date to be scheduled";
    const [y, m, d] = selectedDate.split("-").map(Number);
    const dateObj = new Date(y, m - 1, d);
    return dateObj.toLocaleDateString("en-US", {
      weekday: "short",
      month: "short",
      day: "numeric",
      year: "numeric",
    });
  }, [selectedDate]);

  // Handle Returning User Sign In
  const handleInlineLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!loginInput.trim() || !loginPassword) {
      setLoginError("Please enter both your email/phone and password.");
      return;
    }

    setIsLoggingIn(true);
    setLoginError(null);

    try {
      const res = await login(loginInput.trim(), loginPassword);
      if (res.success && res.user) {
        setGuestName(res.user.name);
        setGuestEmail(res.user.email);
        if (res.user.phone) setGuestPhone(res.user.phone);
        setLoginPassword("");
      } else {
        setLoginError(res.message || "Invalid credentials. Please verify your email or phone and password.");
      }
    } catch {
      setLoginError("Could not reach authentication service. Please try again.");
    } finally {
      setIsLoggingIn(false);
    }
  };

  // Handle Proceed to Step 5 (Includes registration if new account selected)
  const handleProceedToStep5 = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!guestName.trim()) {
      alert("Please provide your full name.");
      return;
    }
    if (!guestPhone.trim()) {
      alert("Please provide your contact phone number for SMS & WhatsApp confirmation.");
      return;
    }

    // If new user tab is active and not logged in, perform registration
    if (!user && authTab === "register") {
      if (!guestEmail.trim()) {
        setRegisterError("An email address is required to create your client account.");
        return;
      }
      if (!registerPassword || registerPassword.length < 6) {
        setRegisterError("Please choose a password with at least 6 characters.");
        return;
      }
      if (registerPassword !== registerConfirmPassword) {
        setRegisterError("Passwords do not match. Please re-enter.");
        return;
      }

      setIsRegistering(true);
      setRegisterError(null);

      try {
        const regRes = await authRegister(
          guestName.trim(),
          guestEmail.trim(),
          registerPassword,
          registerConfirmPassword,
          guestPhone.trim()
        );

        if (!regRes.success) {
          setRegisterError(regRes.message || "Could not create account. Please check your details.");
          setIsRegistering(false);
          return;
        }
      } catch {
        setRegisterError("Could not create account. Please try again.");
        setIsRegistering(false);
        return;
      } finally {
        setIsRegistering(false);
      }
    }

    const guestData = {
      name: guestName.trim(),
      phone: guestPhone.trim(),
      email: guestEmail.trim(),
      notes: specialNotes.trim(),
      silentAppointment,
      paymentOption,
      paymentMethod,
      payableToday,
      remainingAtSalon,
      totalInvestment,
    };

    try {
      localStorage.setItem("isaiah_booking_guest_info", JSON.stringify(guestData));
    } catch {
      // Ignore
    }

    const servicesQuery = selectedServices.map((s) => s.id).join(",");
    const providerQuery = selectedProviderId !== null ? `&provider=${selectedProviderId}` : "";
    const dateQuery = `&date=${encodeURIComponent(selectedDate)}`;
    const timeQuery = `&time=${encodeURIComponent(selectedTime)}`;
    const locQuery = `&location=${locationType}`;

    router.push(
      `/booking/step5?services=${servicesQuery}${providerQuery}${dateQuery}${timeQuery}${locQuery}`
    );
  };

  return (
    <div className="bg-[#FAF7F2] min-h-screen py-10 lg:py-16">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form onSubmit={handleProceedToStep5}>
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            {/* ========================================================= */}
            {/* Left Column: Guest Info & Payment Preference (8 cols) */}
            {/* ========================================================= */}
            <div className="lg:col-span-8 space-y-10">

              {/* 1. Sanctuary Identification & Access */}
              <div className="bg-white rounded-3xl p-6 sm:p-8 border border-[#E5DFD5] shadow-xs">
                <div className="flex items-center justify-between pb-4 border-b border-[#E5DFD5] mb-6">
                  <div>
                    <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-1">
                      01 &bull; Sanctuary Access &amp; Identification
                    </span>
                    <h2 className="font-serif text-2xl sm:text-3xl text-[#221C1A] font-normal">
                      Client Profile &amp; Contact
                    </h2>
                  </div>
                  <span className="text-xs text-[#8E857F] font-light hidden sm:inline-block">
                    Instant confirmation via SMS &amp; WhatsApp
                  </span>
                </div>

                {/* Case A: User Already Authenticated */}
                {user ? (
                  <div className="mb-6 p-5 sm:p-6 bg-[#FAF7F2] rounded-2xl border border-[#E5DFD5]">
                    <div className="flex items-start justify-between gap-4">
                      <div className="flex items-center gap-3.5">
                        <div className="w-12 h-12 rounded-full bg-[#221C1A] text-[#B8975A] font-serif font-bold text-lg flex items-center justify-center shrink-0 border border-[#B8975A]/30">
                          {user.name.split(" ").map((n) => n[0]).slice(0, 2).join("").toUpperCase()}
                        </div>
                        <div>
                          <div className="flex items-center gap-2">
                            <span className="text-sm font-semibold text-[#221C1A]">{user.name}</span>
                            <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                              <Crown size={11} weight="fill" className="text-[#B8975A]" />
                              Member
                            </span>
                          </div>
                          <p className="text-xs text-[#5C5550] mt-0.5">{user.email}</p>
                          <p className="text-[11px] text-emerald-700 font-medium mt-1 flex items-center gap-1">
                            <CheckCircle size={13} weight="fill" className="text-emerald-600" />
                            This reservation will be saved directly to your Sanctuary client dashboard.
                          </p>
                        </div>
                      </div>

                      <button
                        type="button"
                        onClick={logout}
                        className="text-xs text-[#8E857F] hover:text-[#221C1A] underline transition-colors shrink-0 cursor-pointer"
                      >
                        Sign out
                      </button>
                    </div>

                    {/* Pre-filled editable contact fields */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-5 pt-5 border-t border-[#E5DFD5]">
                      <div>
                        <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                          Name on Reservation
                        </label>
                        <input
                          type="text"
                          required
                          value={guestName}
                          onChange={(e) => setGuestName(e.target.value)}
                          className="w-full px-3.5 py-2.5 bg-white border border-[#E5DFD5] rounded-xl text-xs sm:text-sm text-[#221C1A] outline-none"
                        />
                      </div>
                      <div>
                        <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                          Phone Number (MoMo / WhatsApp) <span className="text-[#C4847A]">*</span>
                        </label>
                        <input
                          type="tel"
                          required
                          value={guestPhone}
                          onChange={(e) => setGuestPhone(e.target.value)}
                          placeholder="e.g. 0788 123 456"
                          className="w-full px-3.5 py-2.5 bg-white border border-[#E5DFD5] rounded-xl text-xs sm:text-sm text-[#221C1A] outline-none font-medium"
                        />
                      </div>
                    </div>
                  </div>
                ) : (
                  /* Case B: User NOT Authenticated — Sanctuary Access Tabs */
                  <div>
                    {/* Mode Navigation Tabs */}
                    <div className="grid grid-cols-3 gap-2 p-1.5 bg-[#FAF7F2] rounded-2xl border border-[#E5DFD5] mb-6">
                      <button
                        type="button"
                        onClick={() => {
                          setAuthTab("register");
                          setLoginError(null);
                          setRegisterError(null);
                        }}
                        className={`py-2.5 px-3 rounded-xl text-xs font-semibold uppercase tracking-wider flex items-center justify-center gap-1.5 transition-all cursor-pointer ${
                          authTab === "register"
                            ? "bg-white text-[#221C1A] shadow-sm border border-[#E5DFD5]"
                            : "text-[#8E857F] hover:text-[#221C1A]"
                        }`}
                      >
                        <UserPlus size={15} weight={authTab === "register" ? "bold" : "regular"} className="text-[#B8975A]" />
                        <span>New Client</span>
                      </button>

                      <button
                        type="button"
                        onClick={() => {
                          setAuthTab("signin");
                          setLoginError(null);
                          setRegisterError(null);
                        }}
                        className={`py-2.5 px-3 rounded-xl text-xs font-semibold uppercase tracking-wider flex items-center justify-center gap-1.5 transition-all cursor-pointer ${
                          authTab === "signin"
                            ? "bg-white text-[#221C1A] shadow-sm border border-[#E5DFD5]"
                            : "text-[#8E857F] hover:text-[#221C1A]"
                        }`}
                      >
                        <SignIn size={15} weight={authTab === "signin" ? "bold" : "regular"} className="text-[#B8975A]" />
                        <span>Sign In</span>
                      </button>

                      <button
                        type="button"
                        onClick={() => {
                          setAuthTab("guest");
                          setLoginError(null);
                          setRegisterError(null);
                        }}
                        className={`py-2.5 px-3 rounded-xl text-xs font-semibold uppercase tracking-wider flex items-center justify-center gap-1.5 transition-all cursor-pointer ${
                          authTab === "guest"
                            ? "bg-white text-[#221C1A] shadow-sm border border-[#E5DFD5]"
                            : "text-[#8E857F] hover:text-[#221C1A]"
                        }`}
                      >
                        <User size={15} weight={authTab === "guest" ? "bold" : "regular"} />
                        <span>Fast Guest</span>
                      </button>
                    </div>

                    {/* TAB 1: RETURNING CLIENT (SIGN IN) */}
                    {authTab === "signin" && (
                      <div className="p-5 sm:p-6 bg-[#FAF7F2] rounded-2xl border border-[#E5DFD5] mb-6">
                        <div className="mb-4">
                          <h3 className="font-serif text-base sm:text-lg text-[#221C1A] font-semibold">
                            Welcome Back to Sanctuary Access
                          </h3>
                          <p className="text-xs text-[#5C5550] mt-0.5 font-light">
                            Enter your email or phone number to auto-fill your contact details and view previous treatments.
                          </p>
                        </div>

                        {loginError && (
                          <div className="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-center gap-2">
                            <span>{loginError}</span>
                          </div>
                        )}

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                          <div>
                            <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                              Email Address or Phone
                            </label>
                            <div className="relative">
                              <EnvelopeSimple size={16} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8E857F]" />
                              <input
                                type="text"
                                value={loginInput}
                                onChange={(e) => setLoginInput(e.target.value)}
                                placeholder="e.g. marie@example.com or 078..."
                                className="w-full pl-10 pr-3.5 py-2.5 bg-white border border-[#E5DFD5] rounded-xl text-xs sm:text-sm text-[#221C1A] outline-none"
                              />
                            </div>
                          </div>

                          <div>
                            <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-1.5">
                              Password
                            </label>
                            <div className="relative">
                              <LockKey size={16} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8E857F]" />
                              <input
                                type={showPassword ? "text" : "password"}
                                value={loginPassword}
                                onChange={(e) => setLoginPassword(e.target.value)}
                                placeholder="Your password"
                                className="w-full pl-10 pr-10 py-2.5 bg-white border border-[#E5DFD5] rounded-xl text-xs sm:text-sm text-[#221C1A] outline-none"
                              />
                              <button
                                type="button"
                                onClick={() => setShowPassword(!showPassword)}
                                className="absolute right-3 top-1/2 -translate-y-1/2 text-[#8E857F] hover:text-[#221C1A] cursor-pointer"
                              >
                                {showPassword ? <EyeSlash size={16} /> : <Eye size={16} />}
                              </button>
                            </div>
                          </div>
                        </div>

                        <div className="mt-4 flex items-center justify-between">
                          <button
                            type="button"
                            onClick={handleInlineLogin}
                            disabled={isLoggingIn}
                            className="px-5 py-2.5 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-all disabled:opacity-50 cursor-pointer flex items-center gap-1.5"
                          >
                            <SignIn size={14} weight="bold" />
                            <span>{isLoggingIn ? "Verifying..." : "Sign In & Auto-fill"}</span>
                          </button>

                          <Link
                            href="/login"
                            target="_blank"
                            className="text-xs text-[#8E857F] hover:text-[#221C1A] underline"
                          >
                            Forgot password?
                          </Link>
                        </div>
                      </div>
                    )}

                    {/* TAB 2 & 3: FORM INPUTS (REGISTER & GUEST) */}
                    {authTab !== "signin" && (
                      <div>
                        {authTab === "register" && (
                          <div className="mb-5 p-3.5 rounded-2xl bg-amber-50/60 border border-amber-200/60 text-xs text-amber-900 flex items-center gap-2.5">
                            <Sparkle size={18} weight="fill" className="text-[#B8975A] shrink-0" />
                            <span>
                              Setting a password creates your client account so you can <strong>view upcoming appointments in your dashboard</strong> and earn <strong>loyalty stamps</strong>.
                            </span>
                          </div>
                        )}

                        {registerError && (
                          <div className="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800">
                            {registerError}
                          </div>
                        )}

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                          {/* Full Name */}
                          <div>
                            <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-2">
                              Full Name <span className="text-[#C4847A]">*</span>
                            </label>
                            <div className="relative">
                              <User size={16} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8E857F]" />
                              <input
                                type="text"
                                required
                                value={guestName}
                                onChange={(e) => setGuestName(e.target.value)}
                                placeholder="e.g. Marie Claire"
                                className="w-full pl-10 pr-4 py-3 bg-[#FAF7F2] border border-[#E5DFD5] focus:border-[#221C1A] rounded-2xl text-xs sm:text-sm text-[#221C1A] placeholder-[#8E857F] outline-none transition-colors"
                              />
                            </div>
                          </div>

                          {/* Phone Number */}
                          <div>
                            <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-2">
                              Phone Number (MoMo / WhatsApp) <span className="text-[#C4847A]">*</span>
                            </label>
                            <div className="relative">
                              <span className="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-medium text-[#8E857F] border-r border-[#E5DFD5] pr-2 flex items-center gap-1">
                                <PhoneCall size={14} />
                                <span>+250</span>
                              </span>
                              <input
                                type="tel"
                                required
                                value={guestPhone}
                                onChange={(e) => setGuestPhone(e.target.value)}
                                placeholder="788 123 456"
                                className="w-full pl-22 pr-4 py-3 bg-[#FAF7F2] border border-[#E5DFD5] focus:border-[#221C1A] rounded-2xl text-xs sm:text-sm text-[#221C1A] placeholder-[#8E857F] outline-none transition-colors font-medium"
                              />
                            </div>
                          </div>
                        </div>

                        {/* Email Address */}
                        <div className="mb-5">
                          <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-2">
                            Email Address {authTab === "register" ? <span className="text-[#C4847A]">*</span> : <span className="text-[#8E857F] font-light">(For Calendar Invite &amp; Digital Receipt)</span>}
                          </label>
                          <div className="relative">
                            <EnvelopeSimple size={16} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8E857F]" />
                            <input
                              type="email"
                              required={authTab === "register"}
                              value={guestEmail}
                              onChange={(e) => setGuestEmail(e.target.value)}
                              placeholder="marieclaire@example.com"
                              className="w-full pl-10 pr-4 py-3 bg-[#FAF7F2] border border-[#E5DFD5] focus:border-[#221C1A] rounded-2xl text-xs sm:text-sm text-[#221C1A] placeholder-[#8E857F] outline-none transition-colors"
                            />
                          </div>
                        </div>

                        {/* Password fields for New Client Registration */}
                        {authTab === "register" && (
                          <div className="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5 p-4 sm:p-5 bg-[#FAF7F2] rounded-2xl border border-[#E5DFD5]">
                            <div>
                              <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-2">
                                Create Sanctuary Password <span className="text-[#C4847A]">*</span>
                              </label>
                              <div className="relative">
                                <LockKey size={16} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8E857F]" />
                                <input
                                  type={showPassword ? "text" : "password"}
                                  required
                                  value={registerPassword}
                                  onChange={(e) => setRegisterPassword(e.target.value)}
                                  placeholder="At least 6 characters"
                                  className="w-full pl-10 pr-10 py-2.5 bg-white border border-[#E5DFD5] focus:border-[#221C1A] rounded-xl text-xs sm:text-sm text-[#221C1A] outline-none"
                                />
                                <button
                                  type="button"
                                  onClick={() => setShowPassword(!showPassword)}
                                  className="absolute right-3 top-1/2 -translate-y-1/2 text-[#8E857F] hover:text-[#221C1A] cursor-pointer"
                                >
                                  {showPassword ? <EyeSlash size={16} /> : <Eye size={16} />}
                                </button>
                              </div>
                            </div>

                            <div>
                              <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-2">
                                Confirm Password <span className="text-[#C4847A]">*</span>
                              </label>
                              <div className="relative">
                                <LockKey size={16} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8E857F]" />
                                <input
                                  type={showPassword ? "text" : "password"}
                                  required
                                  value={registerConfirmPassword}
                                  onChange={(e) => setRegisterConfirmPassword(e.target.value)}
                                  placeholder="Re-enter password"
                                  className="w-full pl-10 pr-4 py-2.5 bg-white border border-[#E5DFD5] focus:border-[#221C1A] rounded-xl text-xs sm:text-sm text-[#221C1A] outline-none"
                                />
                              </div>
                            </div>
                          </div>
                        )}
                      </div>
                    )}
                  </div>
                )}

                {/* Special Requests or Allergy Notes */}
                <div className="pt-4 border-t border-[#E5DFD5]">
                  <label className="block text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-2">
                    Special Notes, Inquiries or Nail Sensitivities <span className="text-[#8E857F] font-light">(Optional)</span>
                  </label>
                  <textarea
                    rows={2}
                    value={specialNotes}
                    onChange={(e) => setSpecialNotes(e.target.value)}
                    placeholder="Mention previous gel extensions needing removal, sensitive cuticles, color preferences, or bridal inspiration..."
                    className="w-full px-4 py-3 bg-[#FAF7F2] border border-[#E5DFD5] focus:border-[#221C1A] rounded-2xl text-xs sm:text-sm text-[#221C1A] placeholder-[#8E857F] outline-none transition-colors resize-none"
                  />

                  {/* Silent Appointment Option */}
                  <label className="mt-3 flex items-center gap-2.5 cursor-pointer text-xs text-[#5C5550]">
                    <input
                      type="checkbox"
                      checked={silentAppointment}
                      onChange={(e) => setSilentAppointment(e.target.checked)}
                      className="w-4 h-4 rounded text-[#B8975A] border-[#E5DFD5] focus:ring-[#B8975A]"
                    />
                    <span>Request a Silent Appointment (Quiet ritual with minimal conversation)</span>
                  </label>
                </div>
              </div>

              {/* 2. Payment Amount Selection */}
              <div>
                <div className="flex items-center justify-between pb-3 border-b border-[#E5DFD5] mb-5">
                  <div>
                    <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-1">
                      02 &bull; Payment Amount
                    </span>
                    <h2 className="font-serif text-2xl sm:text-3xl text-[#221C1A] font-normal">
                      Select Payment Option
                    </h2>
                  </div>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  {/* Full Payment */}
                  <div
                    onClick={() => setPaymentOption("full")}
                    className={`p-6 rounded-3xl border transition-all duration-200 cursor-pointer flex flex-col justify-between ${
                      paymentOption === "full"
                        ? "bg-white border-[#B8975A] ring-2 ring-[#B8975A]/25 shadow-md bg-gradient-to-b from-[#FAF7F2] to-white"
                        : "bg-white border-[#E5DFD5] hover:border-[#221C1A]/40"
                    }`}
                  >
                    <div>
                      <div className="flex items-center justify-between mb-4">
                        <div className="w-10 h-10 rounded-2xl bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center">
                          <CheckCircle size={22} weight="light" />
                        </div>
                        <div
                          className={`w-6 h-6 rounded-full flex items-center justify-center transition-all ${
                            paymentOption === "full"
                              ? "bg-[#B8975A] text-white shadow-xs"
                              : "border border-black/15 bg-white text-transparent"
                          }`}
                        >
                          <Check size={14} weight="bold" />
                        </div>
                      </div>

                      <h3 className="font-serif text-lg text-[#221C1A] font-semibold mb-1">
                        Pay in Full (100%)
                      </h3>
                      <p className="text-xs text-[#5C5550] font-light leading-relaxed mb-4">
                        Settle your complete appointment in advance for a tranquil, seamless departure upon ritual conclusion.
                      </p>
                    </div>

                    <div className="pt-3 border-t border-[#E5DFD5] flex items-baseline justify-between">
                      <span className="text-xs text-[#8E857F]">Due Now</span>
                      <span className="font-serif text-xl font-bold text-[#221C1A]">
                        {formatRwf(totalInvestment)} RWF
                      </span>
                    </div>
                  </div>

                  {/* 40% Deposit */}
                  <div
                    onClick={() => setPaymentOption("deposit")}
                    className={`p-6 rounded-3xl border transition-all duration-200 cursor-pointer flex flex-col justify-between ${
                      paymentOption === "deposit"
                        ? "bg-white border-[#B8975A] ring-2 ring-[#B8975A]/25 shadow-md bg-gradient-to-b from-[#FAF7F2] to-white"
                        : "bg-white border-[#E5DFD5] hover:border-[#221C1A]/40"
                    }`}
                  >
                    <div>
                      <div className="flex items-center justify-between mb-4">
                        <div className="w-10 h-10 rounded-2xl bg-[#FAF7F2] text-[#B8975A] border border-[#B8975A]/20 flex items-center justify-center">
                          <CurrencyCircleDollar size={22} weight="light" />
                        </div>
                        <div
                          className={`w-6 h-6 rounded-full flex items-center justify-center transition-all ${
                            paymentOption === "deposit"
                              ? "bg-[#B8975A] text-white shadow-xs"
                              : "border border-black/15 bg-white text-transparent"
                          }`}
                        >
                          <Check size={14} weight="bold" />
                        </div>
                      </div>

                      <h3 className="font-serif text-lg text-[#221C1A] font-semibold mb-1">
                        Sanctuary Deposit (40%)
                      </h3>
                      <p className="text-xs text-[#5C5550] font-light leading-relaxed mb-4">
                        Secure your master artist&apos;s calendar slot now. The remaining 60% balance is payable after treatment at the studio.
                      </p>
                    </div>

                    <div className="pt-3 border-t border-[#E5DFD5] flex items-baseline justify-between">
                      <span className="text-xs text-[#8E857F]">Deposit Due Now</span>
                      <span className="font-serif text-xl font-bold text-[#B8975A]">
                        {formatRwf(depositAmount)} RWF
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              {/* 3. Payment Method */}
              <div>
                <div className="flex items-center justify-between pb-3 border-b border-[#E5DFD5] mb-5">
                  <div>
                    <span className="text-[10px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-1">
                      03 &bull; Payment Channel
                    </span>
                    <h2 className="font-serif text-2xl sm:text-3xl text-[#221C1A] font-normal">
                      Select Preferred Gateway
                    </h2>
                  </div>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  {/* MTN / Airtel Mobile Money */}
                  <div
                    onClick={() => setPaymentMethod("momo")}
                    className={`p-5 rounded-3xl border transition-all duration-200 cursor-pointer flex items-center justify-between ${
                      paymentMethod === "momo"
                        ? "bg-white border-[#B8975A] ring-2 ring-[#B8975A]/25 shadow-md"
                        : "bg-white border-[#E5DFD5] hover:border-[#221C1A]/40"
                    }`}
                  >
                    <div className="flex items-center gap-3">
                      <div className="w-10 h-10 rounded-2xl bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center font-bold text-xs">
                        MoMo
                      </div>
                      <div>
                        <h4 className="text-sm font-semibold text-[#221C1A]">Mobile Money</h4>
                        <p className="text-xs text-[#8E857F]">MTN &amp; Airtel Rwanda</p>
                      </div>
                    </div>
                    <div
                      className={`w-5 h-5 rounded-full flex items-center justify-center transition-all ${
                        paymentMethod === "momo"
                          ? "bg-[#B8975A] text-white"
                          : "border border-black/15 bg-white text-transparent"
                      }`}
                    >
                      <Check size={12} weight="bold" />
                    </div>
                  </div>

                  {/* Credit / Debit Card */}
                  <div
                    onClick={() => setPaymentMethod("card")}
                    className={`p-5 rounded-3xl border transition-all duration-200 cursor-pointer flex items-center justify-between ${
                      paymentMethod === "card"
                        ? "bg-white border-[#B8975A] ring-2 ring-[#B8975A]/25 shadow-md"
                        : "bg-white border-[#E5DFD5] hover:border-[#221C1A]/40"
                    }`}
                  >
                    <div className="flex items-center gap-3">
                      <div className="w-10 h-10 rounded-2xl bg-slate-50 text-slate-700 border border-slate-200 flex items-center justify-center">
                        <CreditCard size={20} weight="bold" />
                      </div>
                      <div>
                        <h4 className="text-sm font-semibold text-[#221C1A]">Credit / Debit Card</h4>
                        <p className="text-xs text-[#8E857F]">Visa &amp; Mastercard</p>
                      </div>
                    </div>
                    <div
                      className={`w-5 h-5 rounded-full flex items-center justify-center transition-all ${
                        paymentMethod === "card"
                          ? "bg-[#B8975A] text-white"
                          : "border border-black/15 bg-white text-transparent"
                      }`}
                    >
                      <Check size={12} weight="bold" />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* ========================================================= */}
            {/* Right Column: Appointment Summary & Submit (4 cols) */}
            {/* ========================================================= */}
            <div className="lg:col-span-4 lg:sticky lg:top-28 space-y-6">
              <div className="bg-white rounded-3xl p-6 sm:p-7 border border-[#E5DFD5] shadow-xs">
                <h3 className="font-serif text-xl text-[#221C1A] font-normal mb-5 pb-4 border-b border-[#E5DFD5]">
                  Reservation Overview
                </h3>

                {/* Selected Artist */}
                {selectedProvider && (
                  <div className="flex items-center gap-3 pb-4 border-b border-[#E5DFD5] mb-4">
                    <div className="w-12 h-12 rounded-full overflow-hidden bg-[#FAF7F2] relative shrink-0 border border-[#B8975A]/30">
                      <Image
                        src={selectedProvider.photo || "/banner.jpg"}
                        alt={selectedProvider.name}
                        fill
                        className="object-cover"
                      />
                    </div>
                    <div>
                      <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block">Master Artist</span>
                      <h4 className="text-sm font-semibold text-[#221C1A]">{selectedProvider.name}</h4>
                      <p className="text-xs text-[#B8975A]">{selectedProvider.role}</p>
                    </div>
                  </div>
                )}

                {/* Date & Time */}
                <div className="space-y-2.5 pb-4 border-b border-[#E5DFD5] text-xs mb-4">
                  <div className="flex items-center justify-between">
                    <span className="text-[#8E857F] flex items-center gap-1.5">
                      <CalendarCheck size={14} /> Date
                    </span>
                    <span className="font-medium text-[#221C1A]">{formattedSelectedDate}</span>
                  </div>
                  <div className="flex items-center justify-between">
                    <span className="text-[#8E857F] flex items-center gap-1.5">
                      <Clock size={14} /> Time
                    </span>
                    <span className="font-medium text-[#221C1A]">{selectedTime || "To be confirmed"}</span>
                  </div>
                  <div className="flex items-center justify-between">
                    <span className="text-[#8E857F] flex items-center gap-1.5">
                      <MapPin size={14} /> Location
                    </span>
                    <span className="font-medium text-[#221C1A] capitalize">
                      {locationType === "home" ? "Home VIP Service" : "Kigali Atelier (Studio)"}
                    </span>
                  </div>
                </div>

                {/* Selected Services */}
                <div className="space-y-2 pb-4 border-b border-[#E5DFD5] mb-4">
                  <span className="text-[10px] uppercase tracking-wider text-[#8E857F] block">
                    Curated Rituals ({selectedServices.length})
                  </span>
                  {selectedServices.map((service) => (
                    <div key={service.id} className="flex items-center justify-between text-xs">
                      <span className="text-[#221C1A] font-medium pr-2 truncate">{service.name}</span>
                      <span className="text-[#5C5550] shrink-0">{formatRwf(service.price)} RWF</span>
                    </div>
                  ))}
                  {wantsPickup && locationType === "salon" && (
                    <div className="flex items-center justify-between text-xs text-[#B8975A]">
                      <span className="flex items-center gap-1">
                        <Car size={12} /> Chauffeur Pickup
                      </span>
                      <span>{formatRwf(currentPickupFee)} RWF</span>
                    </div>
                  )}
                </div>

                {/* Total & Due Now */}
                <div className="space-y-2 text-xs mb-6">
                  <div className="flex items-center justify-between text-[#8E857F]">
                    <span>Total Investment</span>
                    <span>{formatRwf(totalInvestment)} RWF</span>
                  </div>
                  <div className="flex items-center justify-between text-base font-bold text-[#221C1A] pt-2 border-t border-[#E5DFD5]">
                    <span>Amount Due Now</span>
                    <span className="font-serif text-lg text-[#B8975A]">
                      {formatRwf(payableToday)} RWF
                    </span>
                  </div>
                  {remainingAtSalon > 0 && (
                    <div className="flex items-center justify-between text-[11px] text-[#8E857F]">
                      <span>Remaining at Studio</span>
                      <span>{formatRwf(remainingAtSalon)} RWF</span>
                    </div>
                  )}
                </div>

                {/* Submit to Step 5 */}
                <button
                  type="submit"
                  disabled={isRegistering}
                  className="w-full py-4 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-all duration-300 shadow-xl hover:shadow-2xl flex items-center justify-center gap-2 group cursor-pointer disabled:opacity-50"
                >
                  <span>{isRegistering ? "Securing Access..." : "Proceed to Payment Review"}</span>
                  <ArrowRight size={15} weight="bold" className="group-hover:translate-x-1 transition-transform" />
                </button>

                {/* Back to Step 3 */}
                <div className="mt-4 text-center">
                  <button
                    type="button"
                    onClick={() => router.back()}
                    className="text-xs text-[#8E857F] hover:text-[#221C1A] transition-colors inline-flex items-center gap-1 cursor-pointer"
                  >
                    <ArrowLeft size={13} />
                    <span>Back to Date &amp; Time</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  );
}
