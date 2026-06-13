/**
 * Central business configuration.
 *
 * Edit this one file to update the site's name, contact details,
 * social links, and tracking IDs everywhere. Components import
 * `siteConfig` directly — no env-var juggling.
 */

export const siteConfig = {
  name: "WP × Astro",
  shortName: "WP×A",
  url: "https://example.com",
  description: "ערכת התחלה מקצועית לאתרי עסקים בעברית: Astro + WordPress Headless.",
  locale: "he_IL",
  themeColor: "#1d4ed8",

  business: {
    legalName: "החברה שלכם בע״מ",
    foundingYear: 2024,
    phone: "+972500000000",
    phoneDisplay: "050-000-0000",
    /** WhatsApp number in international format without `+`, used in wa.me links. */
    whatsapp: "972500000000",
    whatsappMessage: "שלום, הגעתי דרך האתר ואשמח לקבל פרטים",
    email: "hello@example.com",
    address: {
      street: "רחוב הרצל 1",
      city: "תל אביב",
      region: "IL",
      zip: "6100000",
      country: "IL",
    },
    /** Opening hours strings used for display + JSON-LD. */
    hours: [
      { days: "א׳–ה׳", time: "09:00 – 18:00" },
      { days: "ו׳", time: "09:00 – 13:00" },
    ],
  },

  social: {
    facebook: "https://facebook.com/your-page",
    instagram: "https://instagram.com/your-handle",
    linkedin: "https://linkedin.com/company/your-company",
  },

  analytics: {
    /** GA4 measurement ID, e.g. "G-XXXXXXX". Empty disables the snippet. */
    ga4: "",
  },

  /** Primary nav, shared by Header and Footer. */
  nav: [
    { href: "/", label: "בית" },
    { href: "/about", label: "אודות" },
    { href: "/services", label: "שירותים" },
    { href: "/blog", label: "בלוג" },
    { href: "/contact", label: "צור קשר" },
  ],

  /** Legal / utility links rendered in the footer. */
  legalNav: [
    { href: "/privacy", label: "מדיניות פרטיות" },
    { href: "/terms", label: "תנאי שימוש" },
    { href: "/accessibility", label: "הצהרת נגישות" },
  ],
} as const;

export type SiteConfig = typeof siteConfig;
