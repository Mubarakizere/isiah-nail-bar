export const SERVICE_IMAGE_FALLBACK = "/images/service-placeholder.svg";

/** Resolve both Laravel storage paths and images bundled in frontend/public. */
export function resolveServiceImage(image?: string | null): string {
  const source = image?.trim().replace(/\\/g, "/");
  if (!source) return SERVICE_IMAGE_FALLBACK;

  if (/^(https?:)?\/\//i.test(source) || /^data:image\//i.test(source)) {
    return source;
  }

  // Rooted paths such as /services/example.jpg are frontend/public assets.
  // A rooted /storage/... path is already ready for the Laravel rewrite.
  if (source.startsWith("/")) return source;

  return `/storage/${source.replace(/^storage\//i, "").replace(/^\/+/, "")}`;
}
