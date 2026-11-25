import { cache } from "react";

export interface TeamProvider {
  id: number;
  name: string;
  role: string;
  specialties: string[];
  bio: string;
  photo: string;
  status: "active" | "away";
  experience_years?: number;
}

export interface TeamSupportMember {
  id: number;
  name: string;
  role: string;
  bio: string;
  photo: string;
}

export function resolvePhoto(photo?: string | null, fallback = "/images/logo.png"): string {
  const source = photo?.trim().replace(/\\/g, "/");
  if (!source) return fallback;
  if (/^(https?:)?\/\//i.test(source) || /^data:image\//i.test(source)) {
    return source;
  }
  if (source.startsWith("/storage/")) return source;
  if (source.startsWith("/")) return source;
  return `/storage/${source.replace(/^storage\//i, "").replace(/^\/+/, "")}`;
}

export const DEFAULT_SUPPORT_TEAM: TeamSupportMember[] = [
  {
    id: 1,
    name: "Sandrine B.",
    role: "Sanctuary Manager",
    bio: "Oversees daily autoclave sterilization protocols, booking flow, and uncompromised guest hospitality.",
    photo: "/storage/providers/oNIDo2QtAR5giQWiOG9iqgtlq0qBjYLemAiGOaq1.jpg",
  },
  {
    id: 2,
    name: "Kevine T.",
    role: "Concierge & Guest Relations",
    bio: "Ensures seamless appointment scheduling, bespoke refreshments, and personalized client consultations.",
    photo: "/storage/providers/W6iGbjJkD9WHbX0gBL20yX0kGAUJaUsspUGBO6ch.jpg",
  },
];

export const getTeamData = cache(async function getTeamData(): Promise<{
  providers: TeamProvider[];
  support: TeamSupportMember[];
}> {
  const apiUrl = process.env.LARAVEL_API_URL || "http://127.0.0.1:8000";

  try {
    const res = await fetch(`${apiUrl}/api/v1/providers`, {
      cache: "no-store",
      // Local Laravel cold starts can exceed 800ms. Keep the API response
      // authoritative instead of silently showing hardcoded fake providers.
      signal: AbortSignal.timeout(6000),
    });

    if (res.ok) {
      const json = await res.json();
      const apiData = json?.data;

      if (Array.isArray(apiData?.providers)) {
        const mappedProviders = apiData.providers.map((p: any) => ({
          id: p.id,
          name: p.name,
          role: p.role || "Nail Specialist",
          specialties: Array.isArray(p.specialties)
            ? p.specialties
            : (Array.isArray(p.services) ? p.services.map((service: any) => service.name) : []),
          bio: p.bio || "",
          photo: resolvePhoto(p.photo, "/images/logo.png"),
          status: p.active ? "active" : "away",
          ...(p.experience_years ? { experience_years: p.experience_years } : {}),
        }));

        const mappedSupport = Array.isArray(apiData?.team_members) && apiData.team_members.length > 0
          ? apiData.team_members.map((m: any, idx: number) => ({
            id: m.id,
            name: m.name,
            role: m.role || "Salon Coordinator",
            bio: m.bio || DEFAULT_SUPPORT_TEAM[idx % DEFAULT_SUPPORT_TEAM.length].bio,
            photo: resolvePhoto(m.photo, DEFAULT_SUPPORT_TEAM[idx % DEFAULT_SUPPORT_TEAM.length].photo),
          }))
          : DEFAULT_SUPPORT_TEAM;

        return {
          providers: mappedProviders,
          support: mappedSupport,
        };
      }
    }
  } catch {
    // Fallback when backend is offline or slow
  }

  return {
    providers: [],
    support: DEFAULT_SUPPORT_TEAM,
  };
});
