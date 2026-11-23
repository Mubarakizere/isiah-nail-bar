import type { Metadata } from "next";
import Link from "next/link";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";

export const metadata: Metadata = {
  title: "FAQ | Isaiah Nail Bar Kigali",
  description: "Answers to common questions about booking, payment, and appointment policies at Isaiah Nail Bar Kigali.",
};

const questions = [
  ["Is a deposit required to book an appointment?", "Yes. A non-refundable deposit of 40% secures your booking and is deducted from your final payment."],
  ["What payment methods are available?", "We accept MTN Mobile Money, cash, or bank transfer depending on the service. Payment instructions are provided during booking."],
  ["What happens if I am late?", "If you are more than 15 minutes late, your appointment may be cancelled and your deposit forfeited. Please arrive on time."],
  ["Can I cancel or reschedule?", "Yes. Please contact us at least 48 hours before your appointment. Later cancellations may forfeit the deposit."],
  ["Can I bring someone with me?", "You may bring one guest. Please ask them to remain quiet and respectful so everyone can enjoy a relaxing visit."],
];

export default function FaqPage() {
  return (
    <>
      <Header />
      <main className="min-h-screen bg-[#faf8f7] text-stone-900">
        <section className="bg-stone-950 px-6 py-24 text-center text-white">
          <p className="mb-3 text-sm uppercase tracking-[0.25em] text-rose-300">Need answers?</p>
          <h1 className="font-serif text-4xl md:text-6xl">Frequently Asked Questions</h1>
          <p className="mx-auto mt-5 max-w-2xl text-lg text-stone-300">Find answers about booking, payments, and policies at Isaiah Nail Bar Kigali.</p>
        </section>
        <section className="mx-auto max-w-4xl px-5 py-14">
          <div className="mb-8 rounded-xl border border-amber-300 bg-amber-50 p-6 text-center font-semibold text-amber-950">
            First-time clients receive 10% off their first appointment.
          </div>
          <div className="space-y-4">
            {questions.map(([question, answer]) => (
              <details key={question} className="group rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                <summary className="cursor-pointer list-none font-semibold marker:hidden">
                  <span className="flex items-center justify-between gap-4">{question}<span className="text-xl text-rose-700 transition-transform group-open:rotate-45">+</span></span>
                </summary>
                <p className="mt-4 border-t border-stone-100 pt-4 leading-7 text-stone-600">{answer}</p>
              </details>
            ))}
          </div>
          <div className="mt-12 text-center">
            <p className="mb-5 text-stone-600">Still have questions?</p>
            <div className="flex justify-center gap-3">
              <Link href="/contact" className="rounded-lg bg-rose-800 px-5 py-3 font-semibold text-white">Contact us</Link>
              <Link href="/booking" className="rounded-lg bg-stone-200 px-5 py-3 font-semibold text-stone-800">Book now</Link>
            </div>
          </div>
        </section>
      </main>
      <Footer />
    </>
  );
}
