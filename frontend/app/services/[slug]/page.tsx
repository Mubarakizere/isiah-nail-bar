import { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import { getServiceBySlugOrId, slugifyServiceName } from "@/lib/services-data";
import { formatCurrency } from "@/lib/utils";
import ServiceImage from "@/components/services/ServiceImage";
import {
  Clock,
  CheckCircle,
  ArrowRight,
  ShieldCheck,
  CalendarCheck,
  WhatsappLogo,
  CaretLeft,
} from "@phosphor-icons/react/dist/ssr";

export const dynamic = "force-dynamic";
export const revalidate = 0;

interface ServiceDetailPageProps {
  params: Promise<{ slug: string }>;
}

export async function generateMetadata({
  params,
}: ServiceDetailPageProps): Promise<Metadata> {
  const { slug } = await params;
  const { service } = await getServiceBySlugOrId(slug);

  if (!service) {
    return {
      title: "Service Not Found | Isaiah Nail Bar Kigali",
    };
  }

  return {
    title: `${service.name} | Isaiah Nail Bar Kigali`,
    description: `${service.description} - Price: ${formatCurrency(service.price)} (${service.duration_minutes} mins). Book online at Isaiah Nail Bar in Gisementi, Kigali.`,
    keywords: [
      service.name,
      `${service.name} Kigali`,
      `${service.name} Rwanda`,
      `${service.category_name} Kigali`,
      "Isaiah Nail Bar service",
    ],
    openGraph: {
      title: `${service.name} | Isaiah Nail Bar Kigali`,
      description: service.description,
      images: [{ url: service.image, width: 800, height: 600, alt: service.name }],
    },
  };
}

export default async function ServiceDetailPage({ params }: ServiceDetailPageProps) {
  const { slug } = await params;
  const { service, services } = await getServiceBySlugOrId(slug);

  if (!service) {
    notFound();
  }

  // Related services in the same category
  const relatedServices = services
    .filter((s) => s.category_id === service.category_id && s.id !== service.id)
    .slice(0, 3);

  const schemaImage = service.image.startsWith("http")
    ? service.image
    : `https://isaiahnailbar.com${service.image.startsWith("/") ? "" : "/"}${service.image}`;

  // Schema.org Service data
  const serviceSchema = {
    "@context": "https://schema.org",
    "@type": "Service",
    name: service.name,
    description: service.description,
    image: schemaImage,
    provider: {
      "@type": "NailSalon",
      name: "Isaiah Nail Bar",
      telephone: "+250790395169",
      address: {
        "@type": "PostalAddress",
        streetAddress: "KG 4 Roundabout, Gisementi",
        addressLocality: "Kigali",
        addressCountry: "RW",
      },
    },
    areaServed: {
      "@type": "City",
      name: "Kigali",
    },
    offers: {
      "@type": "Offer",
      price: service.price,
      priceCurrency: "RWF",
      availability: "https://schema.org/InStock",
      url: `https://isaiahnailbar.com/booking?service=${service.id}`,
    },
  };

  return (
    <div className="min-h-screen flex flex-col bg-[#FAF7F2] text-[#221C1A]">
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(serviceSchema) }}
      />

      <Header />

      <main className="flex-1">
        {/* Breadcrumb Navigation Bar */}
        <div className="bg-white border-b border-[#E5DFD5] py-4">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="flex items-center gap-2 text-xs text-[#8E857F]">
              <Link href="/" className="hover:text-[#221C1A] transition-colors">
                Home
              </Link>
              <span>/</span>
              <Link href="/services" className="hover:text-[#221C1A] transition-colors">
                Services
              </Link>
              <span>/</span>
              <span className="text-[#221C1A] font-medium truncate max-w-xs sm:max-w-none">
                {service.name}
              </span>
            </div>
          </div>
        </div>

        {/* Main Service Overview (Crisp White Section) */}
        <section className="py-12 lg:py-20 bg-white">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">
              {/* Left Column: Authentic Service Photo */}
              <div className="lg:col-span-6 sticky top-28">
                <div className="relative aspect-[4/3] sm:aspect-[16/12] rounded-3xl overflow-hidden shadow-xl border border-black/5 bg-white">
                  <ServiceImage
                    src={service.image}
                    alt={service.name}
                    priority
                    sizes="(max-width: 1024px) 100vw, 50vw"
                    className="object-cover object-center"
                  />
                  <div className="absolute top-4 left-4 flex items-center gap-2">
                    <span className="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-white/95 text-[#221C1A] backdrop-blur-md shadow-xs">
                      {service.category_name}
                    </span>
                  </div>
                </div>

                {/* Back to all services link */}
                <div className="pt-6">
                  <Link
                    href="/services"
                    className="inline-flex items-center gap-2 text-xs font-semibold text-[#8E857F] hover:text-[#221C1A] transition-colors"
                  >
                    <CaretLeft size={16} />
                    <span>Back to all treatments</span>
                  </Link>
                </div>
              </div>

              {/* Right Column: Pricing, Duration & Details */}
              <div className="lg:col-span-6 space-y-8">
                <div>
                  <span className="text-[11px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-2">
                    {service.category_name} &bull; Kigali Sanctuary
                  </span>
                  <h1 className="font-serif text-3xl sm:text-5xl font-normal text-[#221C1A] leading-tight mb-4">
                    {service.name}
                  </h1>
                  <p className="text-base sm:text-lg text-[#5C5550] font-light leading-relaxed">
                    {service.description}
                  </p>
                </div>

                {/* Key Metrics Pill Card */}
                <div className="grid grid-cols-2 gap-4 p-5 rounded-2xl bg-[#FAF7F2] border border-[#E5DFD5]">
                  <div>
                    <span className="text-[10px] text-[#8E857F] uppercase tracking-wider block">
                      Treatment Fee
                    </span>
                    <span className="text-2xl font-serif font-bold text-[#221C1A]">
                      {formatCurrency(service.price)}
                    </span>
                  </div>

                  <div>
                    <span className="text-[10px] text-[#8E857F] uppercase tracking-wider block">
                      Estimated Duration
                    </span>
                    <div className="flex items-center gap-1.5 text-base font-semibold text-[#221C1A] mt-0.5">
                      <Clock size={16} className="text-[#B8975A]" />
                      <span>{service.duration_minutes} minutes</span>
                    </div>
                  </div>
                </div>

                {/* Primary Booking Action Bar */}
                <div className="space-y-3 pt-2">
                  <Link
                    href={`/booking?service=${service.id}`}
                    className="group w-full flex items-center justify-center gap-3 py-4 rounded-full text-xs font-semibold uppercase tracking-wider text-white bg-[#221C1A] hover:bg-[#B8975A] transition-all duration-300 shadow-xl hover:-translate-y-0.5"
                  >
                    <CalendarCheck size={18} weight="bold" />
                    <span>Book This Appointment Online</span>
                    <ArrowRight size={15} className="group-hover:translate-x-1 transition-transform" />
                  </Link>

                  <a
                    href={`https://wa.me/250790395169?text=Hello%20Isaiah%20Nail%20Bar,%20I%20would%20like%20to%20book%20the%20${encodeURIComponent(
                      service.name
                    )}%20(${formatCurrency(service.price)})`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="w-full flex items-center justify-center gap-2.5 py-4 rounded-full text-xs font-semibold uppercase tracking-wider text-[#221C1A] bg-[#FAF7F2] border border-[#E5DFD5] hover:border-[#221C1A] hover:bg-white transition-all duration-300"
                  >
                    <WhatsappLogo size={18} weight="fill" className="text-[#25D366]" />
                    <span>Book via WhatsApp Concierge</span>
                  </a>
                </div>

                {/* What's Included */}
                {service.includes && service.includes.length > 0 && (
                  <div className="pt-4 border-t border-[#E5DFD5]">
                    <h3 className="text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-3">
                      Ritual Steps Included
                    </h3>
                    <div className="space-y-2.5">
                      {service.includes.map((step, idx) => (
                        <div key={idx} className="flex items-center gap-2.5 text-xs sm:text-sm text-[#5C5550]">
                          <CheckCircle size={16} weight="fill" className="text-[#34A853] shrink-0" />
                          <span>{step}</span>
                        </div>
                      ))}
                    </div>
                  </div>
                )}

                {/* Benefits */}
                {service.benefits && service.benefits.length > 0 && (
                  <div className="pt-4 border-t border-[#E5DFD5]">
                    <h3 className="text-xs font-semibold uppercase tracking-wider text-[#221C1A] mb-3">
                      Why Choose This Treatment
                    </h3>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                      {service.benefits.map((benefit, idx) => (
                        <div
                          key={idx}
                          className="p-3.5 rounded-xl bg-[#FAF7F2] border border-[#E5DFD5] text-xs text-[#5C5550] flex items-center gap-2"
                        >
                          <ShieldCheck size={16} className="text-[#B8975A] shrink-0" />
                          <span>{benefit}</span>
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            </div>
          </div>
        </section>

        {/* Related Treatments Section (Warm Cashmere Sand) */}
        {relatedServices.length > 0 && (
          <section className="py-20 bg-[#EFE8DD] border-t border-[#DDD2C2]">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
              <div className="text-center max-w-xl mx-auto mb-12">
                <span className="text-[11px] font-semibold tracking-[0.24em] uppercase text-[#B8975A] block mb-2">
                  Complementary Care
                </span>
                <h2 className="font-serif text-3xl font-normal text-[#221C1A]">
                  Related Treatments in {service.category_name}
                </h2>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                {relatedServices.map((rel) => {
                  const relSlug = rel.slug || slugifyServiceName(rel.name) || `service-${rel.id}`;
                  return (
                    <Link
                      key={rel.id}
                      href={`/services/${relSlug}`}
                      className="group bg-white rounded-3xl p-5 border border-[#DDD3C2] hover:border-[#B8975A] hover:shadow-lg transition-all duration-300 flex flex-col justify-between"
                    >
                      <div>
                        <div className="relative aspect-[4/3] rounded-2xl overflow-hidden mb-4 bg-gray-50">
                          <ServiceImage
                            src={rel.image}
                            alt={rel.name}
                            className="object-cover group-hover:scale-105 transition-transform duration-300"
                          />
                        </div>
                        <h3 className="font-serif text-lg text-[#221C1A] font-medium group-hover:text-[#B8975A] transition-colors mb-1">
                          {rel.name}
                        </h3>
                        <p className="text-xs text-[#5C5550] line-clamp-2 font-light">
                          {rel.description}
                        </p>
                      </div>

                      <div className="pt-4 mt-4 border-t border-[#E5DFD5] flex items-center justify-between text-xs">
                        <span className="font-serif font-bold text-[#221C1A]">
                          {formatCurrency(rel.price)}
                        </span>
                        <span className="text-[#8E857F]">{rel.duration_minutes} mins</span>
                      </div>
                    </Link>
                  );
                })}
              </div>
            </div>
          </section>
        )}
      </main>

      <Footer />
    </div>
  );
}
