import { notFound } from "next/navigation";

export default function ErrorBoundaryTestPage(): never {
  if (process.env.NODE_ENV !== "development") notFound();
  throw new Error("Development-only error boundary preview.");
}
