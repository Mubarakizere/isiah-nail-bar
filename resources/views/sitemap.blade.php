{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    {{-- Homepage --}}
    <url>
        <loc>{{ url('/') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.00</priority>
    </url>

    {{-- Services Listing --}}
    <url>
        <loc>{{ url('/services') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.90</priority>
    </url>

    {{-- Booking --}}
    <url>
        <loc>{{ url('/booking') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.90</priority>
    </url>

    {{-- Individual Services --}}
    @foreach($services as $service)
    <url>
        <loc>{{ url('/services/' . $service->id) }}</loc>
        <lastmod>{{ $service->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.80</priority>
    </url>
    @endforeach

    {{-- About --}}
    <url>
        <loc>{{ url('/about') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.70</priority>
    </url>

    {{-- Gallery --}}
    <url>
        <loc>{{ url('/gallery') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.70</priority>
    </url>

    {{-- Contact --}}
    <url>
        <loc>{{ url('/contact') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.70</priority>
    </url>

    {{-- FAQ --}}
    <url>
        <loc>{{ url('/faq') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.70</priority>
    </url>

    {{-- Providers / Team --}}
    <url>
        <loc>{{ url('/providers') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.60</priority>
    </url>

</urlset>
