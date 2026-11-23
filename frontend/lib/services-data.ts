import { resolveServiceImage } from "@/lib/service-image";

export interface ServiceCategory {
  id: number;
  name: string;
  slug: string;
  description?: string;
  count?: number;
}

export interface ServiceItem {
  id: number;
  name: string;
  slug?: string;
  description: string;
  price: number;
  duration_minutes: number;
  category_id: number;
  category_name: string;
  image: string;
  featured?: boolean;
  benefits?: string[];
  includes?: string[];
}

export const SERVICE_CATEGORIES: ServiceCategory[] = [
  {
    id: 1,
    name: "Russian & Precision",
    slug: "russian-precision",
    description: "Flawless dry e-file cuticular alignment and long-lasting apex reinforcement.",
  },
  {
    id: 2,
    name: "Gel & Extensions",
    slug: "gel-extensions",
    description: "Custom-sculpted lightweight length crafted with premium hard gel and soft gel systems.",
  },
  {
    id: 3,
    name: "Spa Pedicures",
    slug: "spa-pedicures",
    description: "Restorative herbal soaks, intensive callus care, and therapeutic foot massage.",
  },
  {
    id: 4,
    name: "Nail Art & Accents",
    slug: "nail-art",
    description: "Hand-painted minimalist line art, French ombré, glazed chrome, and fine embellishments.",
  },
  {
    id: 5,
    name: "Care & Restoration",
    slug: "care-restoration",
    description: "Non-damaging e-file removals, deep keratin infusions, and IBX nail repair.",
  },
];

export const ALL_SERVICES: ServiceItem[] = [
  // 1. Russian & Precision
  {
    id: 1,
    name: "Russian Gel Manicure",
    slug: "russian-gel-manicure",
    category_id: 1,
    category_name: "Russian & Precision",
    price: 35000,
    duration_minutes: 75,
    image: "/services/2fefdFyQnIi6810B9JwmtPspYVvKKFJ7stxSN02Y.jpg",
    featured: true,
    description: "Our signature dry manicure using micro-diamond e-file bits for pristine cuticular alignment, nail plate leveling, and deep under-cuticle gel polish with 4+ weeks retention.",
    benefits: ["Zero-lift 4+ week retention", "Clean cuticular line", "Hospital-grade sterile instruments"],
    includes: ["E-file dry cuticle detailing", "Nail shaping & apex reinforcement", "Multi-coat zero-toxin gel", "Organic cuticle oil massage"],
  },
  {
    id: 2,
    name: "Classic Clean Russian Manicure",
    slug: "classic-clean-russian-manicure",
    category_id: 1,
    category_name: "Russian & Precision",
    price: 20000,
    duration_minutes: 50,
    image: "/services/4yBwHnTzTfDykKVpHIZgeVPM37Tj6IuPbXxa9xrf.jpg",
    description: "Meticulous dry cuticular grooming, natural nail shaping, light buffing, and a high-shine nourishing treatment without gel color for a minimalist, immaculate finish.",
    benefits: ["Natural nail enhancement", "Gentle non-invasive process", "Immaculate neat edges"],
    includes: ["Dry machine cuticle care", "Nail arch shaping", "Keratin strengthening base", "Hydrating hand lotion"],
  },
  {
    id: 3,
    name: "BIAB Builder Gel Overlay",
    slug: "biab-builder-gel-overlay",
    category_id: 1,
    category_name: "Russian & Precision",
    price: 40000,
    duration_minutes: 80,
    image: "/services/5ArcbuV0XOjczPMeXdKMjKBCFwzh0cUR6QgxWZmh.jpg",
    featured: true,
    description: "Builder-In-A-Bottle reinforcement formulated to shield brittle or bendable natural nails, encouraging healthy growth with an ultra-durable, natural aesthetic.",
    benefits: ["Promotes natural nail length", "Reinforces weak nails", "Flexible yet shatterproof"],
    includes: ["Russian cuticle preparation", "BIAB arch building & balance", "Gloss or matte shield top coat", "Intensive botanical oil"],
  },
  {
    id: 4,
    name: "Gentlemen's Precision Manicure",
    slug: "gentlemens-precision-manicure",
    category_id: 1,
    category_name: "Russian & Precision",
    price: 15000,
    duration_minutes: 40,
    image: "/services/7sNXviKlkcqaxEl8VawIZvEzvYO8jFIivqV1lsST.jpg",
    description: "Understated, hygienic grooming tailored for men. Includes e-file cuticle cleanup, tidy nail length shaping, matte buffing, and tension-relieving hand massage.",
    benefits: ["Clean executive appearance", "Hangnail prevention", "Hydrated cuticles"],
    includes: ["Tidy nail trim & contour", "Cuticle smoothing", "Matte buff or satin coat", "Therapeutic hand balm"],
  },

  // 2. Gel & Extensions
  {
    id: 5,
    name: "Sculpted Hard Gel Extensions",
    slug: "sculpted-hard-gel-extensions",
    category_id: 2,
    category_name: "Gel & Extensions",
    price: 45000,
    duration_minutes: 90,
    image: "/services/2nktLbUqZnQWq2J7otghlcStfsL4bVepkg6vjTLK.jpg",
    featured: true,
    description: "Custom sculpted forms tailored to your finger geometry for natural curves, lightweight comfort, and remarkable longevity without damaging glue tips.",
    benefits: ["Custom-fit without plastic tips", "Ultra-lightweight feel", "Crystal clear or blush tint"],
    includes: ["Custom form tailoring", "Hard gel sculpting", "Apex architecture balancing", "Full gel polish finish"],
  },
  {
    id: 6,
    name: "Gel-X Soft Gel Extension System",
    slug: "gel-x-soft-gel-extension-system",
    category_id: 2,
    category_name: "Gel & Extensions",
    price: 50000,
    duration_minutes: 85,
    image: "/services/65BQabDs2CyL2u8It2PAr9JVAZayUXpgSHAOVln5.jpg",
    featured: true,
    description: "Full-cover 100% soak-off soft gel tips adhered with nourishing gel adhesive for instant elegant length, flawless shapes (Almond, Coffin, Square), and easy wear.",
    benefits: ["Zero dust and odor", "Pre-shaped perfection", "Gentle soak-off removal"],
    includes: ["Full Russian cuticle prep", "Custom tip sizing", "Soft gel adhesion", "Gel color of choice"],
  },
  {
    id: 7,
    name: "Full Set Acrylic Extensions",
    slug: "full-set-acrylic-extensions",
    category_id: 2,
    category_name: "Gel & Extensions",
    price: 40000,
    duration_minutes: 90,
    image: "/services/8rh2OG7MxHSWXVrJCPZMdxS5yg6kJ962m7jfT3Yl.jpg",
    description: "Classic high-durability acrylic powder overlay with tip extensions, shaped to perfection and sealed with high-gloss UV gel polish.",
    benefits: ["Maximum impact durability", "Flexible length choices", "Chip-resistant finish"],
    includes: ["Tip placement & contouring", "Even acrylic powder overlay", "High-speed smoothing", "Gel color & shine seal"],
  },
  {
    id: 8,
    name: "Infill Refill (Hard Gel / Acrylic)",
    slug: "infill-refill-hard-gel-acrylic",
    category_id: 2,
    category_name: "Gel & Extensions",
    price: 30000,
    duration_minutes: 65,
    image: "/services/9CPampvlD4gOCntomR5voIOleSiNRG5jP3LAdhc1.jpg",
    description: "Maintain your extensions every 3 to 4 weeks. Rebalances the apex growth, re-sculpts cuticle borders, and updates your shade seamlessly.",
    benefits: ["Maintains structural integrity", "Refreshes fresh polish", "Cost-effective extension care"],
    includes: ["Old top-coat buffing", "New growth fill & re-arch", "Shape refinement", "Fresh gel polish"],
  },

  // 3. Spa Pedicures
  {
    id: 9,
    name: "Luxury Botanical Spa Pedicure",
    slug: "luxury-botanical-spa-pedicure",
    category_id: 3,
    category_name: "Spa Pedicures",
    price: 30000,
    duration_minutes: 60,
    image: "/services/2Q9T51JCvUkDyTfrgfwhLgtl6j3Wf9yXXdgEfuHN.jpg",
    featured: true,
    description: "Warm organic botanical soak, invigorating raw sugar exfoliation, gentle heel smoothing, tension-melting massage, and high-shine gel polish.",
    benefits: ["Deeply relaxing ritual", "Silky soft heels", "Long-lasting pedicure shine"],
    includes: ["Aromatherapeutic foot soak", "Exfoliating botanical scrub", "Heel smoothing", "15-minute calf & foot massage", "Gel polish"],
  },
  {
    id: 10,
    name: "Medical Callus Elimination Pedicure",
    slug: "medical-callus-elimination-pedicure",
    category_id: 3,
    category_name: "Spa Pedicures",
    price: 35000,
    duration_minutes: 65,
    image: "/services/ajPoB3tzQCUMLYrqQVyRQWwmwHdpPZkqRoRvqlQ5.png",
    description: "Specialized clinical pedicure utilizing pharmaceutical fruit enzymes and safe rasping to gently break down thick heels and cracked calluses.",
    benefits: ["Rejuvenates cracked dry skin", "Pain-free callus removal", "Safe sterile implements"],
    includes: ["Callus softening enzyme wrap", "Precision dead skin removal", "Intensive urea treatment balm", "Nail grooming & color"],
  },
  {
    id: 11,
    name: "Russian Dry Precision Pedicure",
    slug: "russian-dry-precision-pedicure",
    category_id: 3,
    category_name: "Spa Pedicures",
    price: 32000,
    duration_minutes: 60,
    image: "/services/AlMra9fbMjdS5m4pTy2gmRHOV3FqWWetV2Lw060x.jpg",
    description: "Waterless European technique focusing on microscopic toenail cuticle clearing and disc-rotary heel smoothing for superior hygiene and polish adhesion.",
    benefits: ["100% waterless sanitary technique", "Clean toenail border lines", "Extends pedicure wear"],
    includes: ["Dry rotary disc smoothing", "E-file toenail cuticle alignment", "Deep hydration application", "Zero-lift gel polish"],
  },

  // 4. Nail Art & Accents
  {
    id: 12,
    name: "French Ombré / Micro-French",
    slug: "french-ombre-micro-french",
    category_id: 4,
    category_name: "Nail Art & Accents",
    price: 10000,
    duration_minutes: 30,
    image: "/services/AQ4K6lsLcUM5wBEOhkcY70T5EIl6bWg882t449jE.jpg",
    featured: true,
    description: "Contemporary reinterpretation of the French manicure featuring soft baby-boomer airbrush gradients or delicate micro-fine smile lines.",
    benefits: ["Timeless luxury aesthetic", "Complements any outfit", "Elongates finger silhouette"],
    includes: ["Color blending consultation", "Hand-painted precision tips", "Ultra-gloss top coat seal"],
  },
  {
    id: 13,
    name: "Chrome & Glazed Pearl Finish",
    slug: "chrome-glazed-pearl-finish",
    category_id: 4,
    category_name: "Nail Art & Accents",
    price: 8000,
    duration_minutes: 20,
    image: "/services/B6DSWUGEXEFRE5IJ9Qc64Q1Y5CrpoWTVwX6U59Ee.jpg",
    description: "Fine cosmetic powder buffed over gel base for a mirror-like chrome or subtle iridescent Hailey Bieber glazed-donut shimmer.",
    benefits: ["High-impact reflective glow", "Smooth glass-like texture", "Multiple chrome tone options"],
    includes: ["Base color matching", "Burnished mirror chrome powder", "Chip-guard sealing layer"],
  },
  {
    id: 14,
    name: "Bespoke Hand-Painted Art (Per Accent)",
    slug: "bespoke-hand-painted-art",
    category_id: 4,
    category_name: "Nail Art & Accents",
    price: 3000,
    duration_minutes: 15,
    image: "/services/83LHc90fsBYuX2RlanyvpPj6I0gD3dgPOZcYJkhI.jpg",
    description: "Intricate miniature painting on individual accent nails. Custom swirls, geometric lines, delicate florals, or seasonal patterns.",
    benefits: ["Unique one-of-a-kind design", "Painted by senior nail artists", "Fine-line precision"],
    includes: ["Art consultation", "Detail-brush hand application", "Double-sealed top coat"],
  },

  // 5. Care & Restoration
  {
    id: 15,
    name: "Safe E-File Removal Ritual",
    slug: "safe-e-file-removal-ritual",
    category_id: 5,
    category_name: "Care & Restoration",
    price: 8000,
    duration_minutes: 30,
    image: "/services/7fJnaNW50ECHuekxoY9DczD9tRxbxmyXHcgGIOK5.png",
    description: "Gentle, non-damaging mechanical removal of old gel, BIAB, or acrylic without harsh acetone soaks, leaving natural nail beds intact and healthy.",
    benefits: ["Zero nail plate thinning", "No painful scraping", "Includes conditioning oil"],
    includes: ["Safe ceramic bit de-bulking", "Natural plate buffing", "Strengthening protein coat"],
  },
  {
    id: 16,
    name: "IBX Deep Repair & Strengthening",
    slug: "ibx-deep-repair-strengthening",
    category_id: 5,
    category_name: "Care & Restoration",
    price: 12000,
    duration_minutes: 25,
    image: "/services/2fefdFyQnIi6810B9JwmtPspYVvKKFJ7stxSN02Y.jpg",
    description: "Penetrating cross-linking monomer treatment that fuses upper layers of the nail plate together from within to heal delamination and peeling.",
    benefits: ["Repairs severe peeling", "Acts inside the natural nail", "Can be worn alone or under gel"],
    includes: ["Heat penetration infusion", "UV cure cross-linking", "Protective nail nourishment"],
  },
];

let cachedServicesResult: { services: ServiceItem[]; categories: ServiceCategory[] } | null = null;
let lastServicesFetchTime = 0;
const SERVICES_CACHE_TTL = 60 * 1000; // 60 seconds

export async function getServicesData() {
  const now = Date.now();
  if (cachedServicesResult && now - lastServicesFetchTime < SERVICES_CACHE_TTL) {
    return cachedServicesResult;
  }

  const apiUrl = process.env.LARAVEL_API_URL || "http://127.0.0.1:8000";

  try {
    const [servicesRes, categoriesRes] = await Promise.all([
      fetch(`${apiUrl}/api/v1/services`, {
        next: { revalidate: 60 },
        signal: AbortSignal.timeout(800),
      }),
      fetch(`${apiUrl}/api/v1/categories`, {
        next: { revalidate: 60 },
        signal: AbortSignal.timeout(800),
      }),
    ]);

    if (servicesRes.ok && categoriesRes.ok) {
      const servicesJson = await servicesRes.json();
      const categoriesJson = await categoriesRes.json();

      const apiServices = servicesJson?.data;
      const apiCategories = categoriesJson?.data;

      if (Array.isArray(apiServices) && apiServices.length > 0) {
        const result = {
          services: apiServices.map((s: any) => ({
            id: s.id,
            name: s.name,
            slug: s.slug || `service-${s.id}`,
            description: s.description || "",
            price: Number(s.price),
            duration_minutes: Number(s.duration_minutes || s.duration || 60),
            category_id: s.category_id || 1,
            category_name: s.category?.name || "General Care",
            image: resolveServiceImage(s.image),
            featured: Boolean(s.featured),
          })),
          categories: Array.isArray(apiCategories) && apiCategories.length > 0
            ? apiCategories.map((c: any) => ({
                id: c.id,
                name: c.name,
                slug: c.slug || `category-${c.id}`,
                count: c.services_count,
              }))
            : SERVICE_CATEGORIES,
        };

        cachedServicesResult = result;
        lastServicesFetchTime = now;
        return result;
      }
    }
  } catch {
    // Graceful fallback when backend is offline or slow
  }

  return {
    services: ALL_SERVICES,
    categories: SERVICE_CATEGORIES,
  };
}

export function getServiceByIdLocal(id: number): ServiceItem | undefined {
  return ALL_SERVICES.find((s) => s.id === id);
}

export function getServiceBySlugOrIdLocal(identifier: string): ServiceItem | undefined {
  const clean = decodeURIComponent(identifier).toLowerCase().trim();
  const numericId = parseInt(clean, 10);
  
  return ALL_SERVICES.find((s) => {
    if (!isNaN(numericId) && s.id === numericId) return true;
    if (s.slug && s.slug.toLowerCase() === clean) return true;
    const nameSlug = s.name.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/(^-|-$)+/g, "");
    return nameSlug === clean || s.name.toLowerCase() === clean;
  });
}
