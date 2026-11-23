"use client";

import { Check } from "@phosphor-icons/react";

interface Step {
  number: number;
  label: string;
  sublabel: string;
}

const STEPS: Step[] = [
  { number: 1, label: "Services", sublabel: "Curate Rituals" },
  { number: 2, label: "Artist", sublabel: "Select Specialist" },
  { number: 3, label: "Date & Time", sublabel: "Reserve Slot" },
  { number: 4, label: "Guest Info", sublabel: "Contact & Notes" },
  { number: 5, label: "Confirm", sublabel: "Payment & Guarantee" },
];

interface BookingStepperProps {
  currentStep?: number;
}

export default function BookingStepper({ currentStep = 1 }: BookingStepperProps) {
  return (
    <div className="w-full bg-[#181514] border-b border-white/10 py-4 px-4 sm:px-6 lg:px-8">
      <div className="max-w-4xl mx-auto">
        <nav aria-label="Booking Progress">
          <ol className="flex items-center justify-between relative">
            {/* Connecting Progress Line */}
            <div className="absolute left-6 right-6 top-4 sm:top-5 h-0.5 bg-white/10 -z-0" />
            <div
              className="absolute left-6 top-4 sm:top-5 h-0.5 bg-gradient-to-r from-[#B8975A] to-[#D4AF37] -z-0 transition-all duration-500"
              style={{
                width: `${((currentStep - 1) / (STEPS.length - 1)) * 100}%`,
              }}
            />

            {STEPS.map((step) => {
              const isCompleted = step.number < currentStep;
              const isCurrent = step.number === currentStep;

              return (
                <li key={step.number} className="relative z-10 flex flex-col items-center">
                  {/* Step Bubble */}
                  <div
                    className={`w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center text-xs font-semibold transition-all duration-300 ${
                      isCompleted
                        ? "bg-[#B8975A] text-white shadow-md shadow-[#B8975A]/30"
                        : isCurrent
                        ? "bg-white text-[#221C1A] ring-4 ring-[#B8975A]/40 shadow-lg"
                        : "bg-[#25201E] text-white/40 border border-white/10"
                    }`}
                  >
                    {isCompleted ? (
                      <Check size={16} weight="bold" />
                    ) : (
                      <span>{step.number}</span>
                    )}
                  </div>

                  {/* Step Labels */}
                  <div className="mt-2 text-center">
                    <span
                      className={`block text-[11px] sm:text-xs font-medium tracking-wide uppercase transition-colors ${
                        isCurrent
                          ? "text-[#E8C99B] font-semibold"
                          : isCompleted
                          ? "text-white"
                          : "text-white/40"
                      }`}
                    >
                      {step.label}
                    </span>
                    <span className="hidden md:block text-[10px] text-white/40 font-light">
                      {step.sublabel}
                    </span>
                  </div>
                </li>
              );
            })}
          </ol>
        </nav>
      </div>
    </div>
  );
}
