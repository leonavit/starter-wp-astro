/**
 * Fetch the Theme Options payload from the WP × Astro Starter theme.
 *
 * Endpoint: `${WP_REST_URL}/wpas/v1/options`
 *
 * Strategy:
 *   1. If `WP_REST_URL` is unset or still the placeholder, skip the fetch.
 *   2. Otherwise hit the endpoint and merge the response over the local
 *      `siteConfig` defaults so missing keys never produce undefined.
 *   3. On any failure, return the local defaults.
 */

import { siteConfig } from "../config/site";

export interface ThemeOptions {
  general: {
    name: string;
    legalName: string;
    tagline: string;
    description: string;
    foundingYear: number;
    themeColor: string;
    logoUrl: string;
    ogImageUrl: string;
  };
  contact: {
    phone: string;
    phoneDisplay: string;
    whatsapp: string;
    whatsappMessage: string;
    email: string;
    addressStreet: string;
    addressCity: string;
    addressZip: string;
    addressCountry: string;
    hours: Array<{ days: string; time: string }>;
  };
  social: {
    facebook: string;
    instagram: string;
    linkedin: string;
    youtube: string;
    tiktok: string;
    x: string;
  };
  seo: {
    ga4Id: string;
    gtmId: string;
    facebookPixel: string;
    verificationGoogle: string;
  };
  site: {
    url: string;
    locale: string;
  };
}

const WP_REST_URL = import.meta.env.WP_REST_URL || process.env.WP_REST_URL;

/** snake_case → camelCase shallow remap for the WP payload. */
type RawPayload = Record<string, Record<string, unknown>>;

function mapWpResponse(raw: RawPayload): ThemeOptions {
  const g = (raw.general ?? {}) as Record<string, unknown>;
  const c = (raw.contact ?? {}) as Record<string, unknown>;
  const s = (raw.social ?? {}) as Record<string, unknown>;
  const e = (raw.seo ?? {}) as Record<string, unknown>;
  const site = (raw.site ?? {}) as Record<string, unknown>;

  const str = (v: unknown, fallback = ""): string => (typeof v === "string" ? v : fallback);
  const num = (v: unknown, fallback = 0): number => (typeof v === "number" ? v : fallback);

  return {
    general: {
      name: str(g.name, siteConfig.name),
      legalName: str(g.legal_name, siteConfig.business.legalName),
      tagline: str(g.tagline, siteConfig.description),
      description: str(g.description, siteConfig.description),
      foundingYear: num(g.founding_year, siteConfig.business.foundingYear),
      themeColor: str(g.theme_color, siteConfig.themeColor),
      logoUrl: str(g.logo_url, ""),
      ogImageUrl: str(g.og_image_url, ""),
    },
    contact: {
      phone: str(c.phone, siteConfig.business.phone),
      phoneDisplay: str(c.phone_display, siteConfig.business.phoneDisplay),
      whatsapp: str(c.whatsapp, siteConfig.business.whatsapp),
      whatsappMessage: str(c.whatsapp_message, siteConfig.business.whatsappMessage),
      email: str(c.email, siteConfig.business.email),
      addressStreet: str(c.address_street, siteConfig.business.address.street),
      addressCity: str(c.address_city, siteConfig.business.address.city),
      addressZip: str(c.address_zip, siteConfig.business.address.zip),
      addressCountry: str(c.address_country, siteConfig.business.address.country),
      hours: Array.isArray(c.hours)
        ? (c.hours as Array<Record<string, unknown>>).map((h) => ({
            days: str(h.days, ""),
            time: str(h.time, ""),
          }))
        : [...siteConfig.business.hours],
    },
    social: {
      facebook: str(s.facebook, siteConfig.social.facebook),
      instagram: str(s.instagram, siteConfig.social.instagram),
      linkedin: str(s.linkedin, siteConfig.social.linkedin),
      youtube: str(s.youtube, ""),
      tiktok: str(s.tiktok, ""),
      x: str(s.x, ""),
    },
    seo: {
      ga4Id: str(e.ga4_id, siteConfig.analytics.ga4),
      gtmId: str(e.gtm_id, ""),
      facebookPixel: str(e.facebook_pixel, ""),
      verificationGoogle: str(e.verification_google, ""),
    },
    site: {
      url: str(site.url, siteConfig.url),
      locale: str(site.locale, "he-IL"),
    },
  };
}

/** Defaults derived from `siteConfig` — used when WP is unreachable. */
function defaultsFromSiteConfig(): ThemeOptions {
  return {
    general: {
      name: siteConfig.name,
      legalName: siteConfig.business.legalName,
      tagline: siteConfig.description,
      description: siteConfig.description,
      foundingYear: siteConfig.business.foundingYear,
      themeColor: siteConfig.themeColor,
      logoUrl: "",
      ogImageUrl: "",
    },
    contact: {
      phone: siteConfig.business.phone,
      phoneDisplay: siteConfig.business.phoneDisplay,
      whatsapp: siteConfig.business.whatsapp,
      whatsappMessage: siteConfig.business.whatsappMessage,
      email: siteConfig.business.email,
      addressStreet: siteConfig.business.address.street,
      addressCity: siteConfig.business.address.city,
      addressZip: siteConfig.business.address.zip,
      addressCountry: siteConfig.business.address.country,
      hours: [...siteConfig.business.hours],
    },
    social: {
      facebook: siteConfig.social.facebook,
      instagram: siteConfig.social.instagram,
      linkedin: siteConfig.social.linkedin,
      youtube: "",
      tiktok: "",
      x: "",
    },
    seo: {
      ga4Id: siteConfig.analytics.ga4,
      gtmId: "",
      facebookPixel: "",
      verificationGoogle: "",
    },
    site: {
      url: siteConfig.url,
      locale: "he-IL",
    },
  };
}

let cached: ThemeOptions | null = null;

/**
 * Fetch theme options (cached per build). Always resolves to a usable
 * object — either from WP, or from the local site config.
 */
export async function getThemeOptions(): Promise<ThemeOptions> {
  if (cached) return cached;

  if (!WP_REST_URL || WP_REST_URL.includes("example.com")) {
    cached = defaultsFromSiteConfig();
    return cached;
  }

  const url = `${WP_REST_URL.replace(/\/$/, "")}/wpas/v1/options`;

  try {
    const res = await fetch(url, { headers: { Accept: "application/json" } });
    if (!res.ok) {
      console.warn(`[wpas] options endpoint returned ${res.status} — using site.ts.`);
      cached = defaultsFromSiteConfig();
      return cached;
    }
    const raw = (await res.json()) as RawPayload;
    cached = mapWpResponse(raw);
    return cached;
  } catch (err) {
    console.warn("[wpas] options fetch failed — using site.ts:", (err as Error).message);
    cached = defaultsFromSiteConfig();
    return cached;
  }
}
