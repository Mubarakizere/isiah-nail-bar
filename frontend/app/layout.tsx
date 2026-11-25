import type { Metadata, Viewport } from "next";
import localFont from "next/font/local";
import "./globals.css";
import { AuthProvider } from "@/lib/auth-context";
import { SettingsProvider } from "@/lib/settings-context";

const playfair = localFont({
  src: "./fonts/playfair-display-latin.woff2",
  variable: "--font-heading",
  display: "swap",
  weight: "400 700",
});

const plusJakarta = localFont({
  src: "./fonts/plus-jakarta-sans-latin.woff2",
  variable: "--font-body",
  display: "swap",
  weight: "300 700",
});

const inter = localFont({
  src: "./fonts/inter-latin.woff2",
  variable: "--font-sans",
  display: "swap",
  weight: "300 800",
});

export const viewport: Viewport = {
  themeColor: "#FAF7F2",
  width: "device-width",
  initialScale: 1,
};

export const metadata: Metadata = {
  metadataBase: new URL(process.env.NEXT_PUBLIC_SITE_URL || "https://isaiahnailbar.com"),
  manifest: "/site.webmanifest",
  icons: {
    icon: [
      { url: "/favicon-32x32.png", sizes: "32x32", type: "image/png" },
      { url: "/favicon-16x16.png", sizes: "16x16", type: "image/png" },
    ],
    apple: "/apple-touch-icon.png",
  },
  title: {
    default: "Isaiah Nail Bar — Luxury Nail Artistry & Spa Sanctuary Kigali",
    template: "%s | Isaiah Nail Bar",
  },
  description:
    "Experience bespoke nail artistry, Russian manicures, gel extensions, and botanical pedicures in Kigali, Rwanda. Hospital-grade hygiene in a tranquil boutique sanctuary.",
  keywords: [
    "Isaiah Nail Bar",
    "Nail salon Kigali",
    "Best manicure Kigali",
    "Russian manicure Rwanda",
    "Gel nails Kigali",
    "Pedicure spa Gisementi",
    "Bespoke nail art Rwanda",
    "Luxury nail salon Kigali",
  ],
  authors: [{ name: "Isaiah Nail Bar" }],
  creator: "Isaiah Nail Bar",
  openGraph: {
    type: "website",
    locale: "en_US",
    url: "https://isaiahnailbar.com",
    siteName: "Isaiah Nail Bar",
    title: "Isaiah Nail Bar — Luxury Nail Artistry & Spa Sanctuary",
    description: "Elevate your nail care with master artisans, tranquil ambiance, and hospital-grade sanitization in Kigali.",
    images: [
      {
        url: "/og-image.jpg",
        width: 1200,
        height: 630,
        alt: "Isaiah Nail Bar — Bespoke Nails and Spa Kigali",
      },
    ],
  },
  twitter: {
    card: "summary_large_image",
    title: "Isaiah Nail Bar — Luxury Nail Sanctuary Kigali",
    description: "Bespoke nail design, Russian manicures, and tranquil beauty rituals in Kigali.",
  },
  robots: {
    index: true,
    follow: true,
    googleBot: {
      index: true,
      follow: true,
      "max-video-preview": -1,
      "max-image-preview": "large",
      "max-snippet": -1,
    },
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  // Pro Schema: NailSalon JSON-LD
  const jsonLd = {
    "@context": "https://schema.org",
    "@type": "NailSalon",
    name: "Isaiah Nail Bar",
    image: "https://isaiahnailbar.com/storage/logo.png",
    "@id": "https://isaiahnailbar.com/#business",
    url: "https://isaiahnailbar.com",
    telephone: "+250790395169",
    priceRange: "$$",
    currenciesAccepted: "RWF",
    paymentAccepted: "MTN Mobile Money, Cash, Bank Transfer, Credit Card",
    address: {
      "@type": "PostalAddress",
      streetAddress: "KG 4 Roundabout",
      addressLocality: "Gisementi, Kigali",
      addressRegion: "Kigali City",
      postalCode: "00000",
      addressCountry: "RW",
    },
    geo: {
      "@type": "GeoCoordinates",
      latitude: -1.954736,
      longitude: 30.108678,
    },
    openingHoursSpecification: [
      {
        "@type": "OpeningHoursSpecification",
        dayOfWeek: ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
        opens: "08:00",
        closes: "20:00",
      },
      {
        "@type": "OpeningHoursSpecification",
        dayOfWeek: ["Sunday"],
        opens: "10:00",
        closes: "18:00",
      },
    ],
    sameAs: [
      "https://www.instagram.com/isaiah_nails_art_kigali_rwanda",
      "https://www.facebook.com/isaiahnailbar",
    ],
  };

  return (
    <html
      lang="en"
      className={`${playfair.variable} ${plusJakarta.variable} ${inter.variable}`}
      suppressHydrationWarning
    >
      <head>
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }}
        />
        <script
          dangerouslySetInnerHTML={{
            __html: `
              (function() {
                try {
                  var origSetAttr = Element.prototype.setAttribute;
                  Element.prototype.setAttribute = function(name, value) {
                    if (name === 'bis_skin_checked' || name === 'bis_register') {
                      return;
                    }
                    return origSetAttr.apply(this, arguments);
                  };
                  var observer = new MutationObserver(function(mutations) {
                    for (var i = 0; i < mutations.length; i++) {
                      var m = mutations[i];
                      if (m.type === 'attributes' && (m.attributeName === 'bis_skin_checked' || m.attributeName === 'bis_register')) {
                        m.target.removeAttribute(m.attributeName);
                      }
                    }
                  });
                  observer.observe(document.documentElement, {
                    attributes: true,
                    subtree: true,
                    attributeFilter: ['bis_skin_checked', 'bis_register']
                  });
                } catch (e) {}
              })();
            `,
          }}
        />
      </head>
      <body
        className="antialiased min-h-screen flex flex-col bg-[#FAF7F2] text-[#221C1A]"
        suppressHydrationWarning
      >
        <SettingsProvider>
          <AuthProvider>{children}</AuthProvider>
        </SettingsProvider>
      </body>
    </html>
  );
}
