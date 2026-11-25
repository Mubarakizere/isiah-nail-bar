import type { NextConfig } from "next";
import path from "path";

const nextConfig: NextConfig = {
  output: "standalone",
  outputFileTracingRoot: path.resolve(__dirname),
  // Upstream Laravel on 127.0.0.1:8000 sends `Connection: close`; disable HTTP
  // keep-alive so Next.js rewrites and SSR fetches never hit stale closed sockets.
  httpAgentOptions: {
    keepAlive: false,
  },

  typescript: {
    ignoreBuildErrors: true,
  },
  eslint: {
    ignoreDuringBuilds: true,
  },

  experimental: {
    optimizePackageImports: [
      "@phosphor-icons/react",
      "@phosphor-icons/react/dist/ssr",
      "framer-motion",
      "lucide-react",
    ],
  },

  images: {
    unoptimized: true,
    remotePatterns: [
      {
        protocol: "https",
        hostname: "isaiahnailbar.com",
      },
      {
        protocol: "http",
        hostname: "localhost",
      },
      {
        protocol: "http",
        hostname: "127.0.0.1",
      },
      {
        protocol: "https",
        hostname: "images.unsplash.com",
      },
    ],
  },

  async rewrites() {
    const apiUrl = process.env.LARAVEL_API_URL || "http://127.0.0.1:8000";
    return [
      {
        source: "/sitemap.xml",
        destination: `${apiUrl}/sitemap.xml`,
      },
      {
        source: "/api/:path*",
        destination: `${apiUrl}/api/:path*`,
      },
      {
        source: "/webhook/:path*",
        destination: `${apiUrl}/webhook/:path*`,
      },
      {
        source: "/payment/callback",
        destination: `${apiUrl}/payment/callback`,
      },
      {
        source: "/storage/:path*",
        destination: `${apiUrl}/storage/:path*`,
      },
    ];
  },
};

export default nextConfig;
