"use client";

import ErrorLanding from "@/components/errors/ErrorLanding";

export default function GlobalError({ reset }: { error: Error & { digest?: string }; reset: () => void }) {
  return (
    <html lang="en">
      <body>
        <ErrorLanding
          code="500"
          title="The site needs a moment"
          message="A site-wide error interrupted this page. Try again in a moment, or return to the homepage."
          primaryHref="/"
          primaryLabel="Return home"
          reset={reset}
        />
      </body>
    </html>
  );
}
