"use client";

import { useEffect, useState } from "react";
import Image from "next/image";
import {
  resolveServiceImage,
  SERVICE_IMAGE_FALLBACK,
} from "@/lib/service-image";

interface ServiceImageProps {
  src?: string | null;
  alt: string;
  sizes?: string;
  className?: string;
  priority?: boolean;
}

export default function ServiceImage({
  src,
  alt,
  sizes,
  className,
  priority = false,
}: ServiceImageProps) {
  const resolvedSrc = resolveServiceImage(src);
  const [currentSrc, setCurrentSrc] = useState(resolvedSrc);

  useEffect(() => {
    setCurrentSrc(resolvedSrc);
  }, [resolvedSrc]);

  return (
    <Image
      src={currentSrc}
      alt={alt}
      fill
      sizes={sizes}
      priority={priority}
      unoptimized
      onError={() => {
        if (currentSrc !== SERVICE_IMAGE_FALLBACK) {
          setCurrentSrc(SERVICE_IMAGE_FALLBACK);
        }
      }}
      className={className}
    />
  );
}
