import type { Metadata } from "next";
import { notFound } from "next/navigation";
import ErrorLanding from "@/components/errors/ErrorLanding";

export const metadata: Metadata = {
  title: "Error page preview | Isaiah Nail Bar",
  robots: { index: false, follow: false },
};

export const dynamicParams = false;

const errorPages: Record<string, { title: string; message: string; primaryHref?: string; primaryLabel?: string }> = {
  "400": { title: "We couldn’t process that request", message: "Check the information and try again, or return to the homepage." },
  "401": { title: "Please sign in to continue", message: "This part of your account needs a verified sign-in.", primaryHref: "/login", primaryLabel: "Sign in" },
  "403": { title: "You don’t have access to this page", message: "Your account does not have permission to open this page. Contact us if you think this is a mistake." },
  "404": { title: "We couldn’t find that page", message: "The address may have changed, or the page may have moved." },
  "408": { title: "That took too long", message: "The request timed out before it finished. Please try again." },
  "409": { title: "This request conflicts with current information", message: "Refresh the page and review the latest details before trying again." },
  "410": { title: "This page is no longer available", message: "The link may have expired. Return home to find the latest information." },
  "413": { title: "That file is too large", message: "Choose a smaller file and try uploading it again." },
  "419": { title: "Your session has expired", message: "Sign in again to continue safely.", primaryHref: "/login", primaryLabel: "Sign in again" },
  "422": { title: "Some information needs attention", message: "Review the fields on the page and correct anything that is missing or invalid." },
  "429": { title: "Please wait before trying again", message: "We received several requests in a short time. Give it a moment, then try again." },
  "500": { title: "Something went wrong", message: "We hit an unexpected problem. Please try again in a moment." },
  "502": { title: "We lost the connection briefly", message: "A service we rely on did not respond correctly. Please try again shortly." },
  "503": { title: "We’re taking a short break", message: "The service is temporarily unavailable. Please come back in a little while." },
  "504": { title: "The service took too long to respond", message: "Please try again. If the problem continues, call our concierge." },
};

export function generateStaticParams() {
  return Object.keys(errorPages).map((code) => ({ code }));
}

export default async function ErrorPreviewPage({
  params,
}: {
  params: Promise<{ code: string }>;
}) {
  const { code } = await params;
  const errorPage = errorPages[code];
  if (!errorPage) notFound();

  return <ErrorLanding code={code} {...errorPage} />;
}
