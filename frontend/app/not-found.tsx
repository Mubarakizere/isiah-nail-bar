import ErrorLanding from "@/components/errors/ErrorLanding";

export default function NotFound() {
  return (
    <ErrorLanding
      code="404"
      title="We couldn’t find that page"
      message="The address may have changed, or the page may have moved. Head back to the sanctuary and we’ll help you find what you need."
      primaryHref="/"
      primaryLabel="Return home"
    />
  );
}
