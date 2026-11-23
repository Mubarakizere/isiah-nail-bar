export interface InstagramPost {
  id: number | string;
  url: string;
  caption?: string;
}

// Default Instagram posts from @isaiah_nails_art_kigali_rwanda
export const DEFAULT_INSTAGRAM_POSTS: InstagramPost[] = [
  {
    id: 1,
    url: "https://www.instagram.com/p/DFp5jETo_fJ/",
  },
  {
    id: 2,
    url: "https://www.instagram.com/p/DFdYjEBoq7D/",
  },
  {
    id: 3,
    url: "https://www.instagram.com/p/DFCqjEBoY3F/",
  },
  {
    id: 4,
    url: "https://www.instagram.com/p/DE7qjEBoZ9K/",
  },
  {
    id: 5,
    url: "https://www.instagram.com/p/DE2qjEBoL8N/",
  },
  {
    id: 6,
    url: "https://www.instagram.com/p/DExqjEBoM1P/",
  },
];

export async function getInstagramPosts(): Promise<InstagramPost[]> {
  const apiUrl = process.env.LARAVEL_API_URL || "http://127.0.0.1:8000";

  try {
    const res = await fetch(`${apiUrl}/api/v1/gallery`, {
      cache: "no-store",
      signal: AbortSignal.timeout(3000),
    });

    if (res.ok) {
      const json = await res.json();
      if (Array.isArray(json?.data)) {
        return json.data.map((p: any) => ({
          id: p.id,
          url: p.url,
          caption: p.caption,
        }));
      }
    }
  } catch {
    // Graceful fallback to default Instagram posts
  }

  return DEFAULT_INSTAGRAM_POSTS;
}
