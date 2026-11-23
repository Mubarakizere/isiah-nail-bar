"use client";

import ErrorLanding from "@/components/errors/ErrorLanding";

export default function Error({ reset }: { error: Error & { digest?: string }; reset: () => void }) {
  return (
    <ErrorLanding
      code="500"
      title="Something went wrong"
      message="We hit an unexpected problem while opening this page. Please try again, or return to the homepage."
      primaryHref="/"
      primaryLabel="Return home"
      reset={reset}
    />
  );
}
