/**
 * WPGraphQL client + content fetching for the Astro build.
 *
 * Strategy:
 *   1. Try the configured WPGraphQL endpoint.
 *   2. On any failure (network, schema, empty result), fall back to local
 *      seed data so `astro build` always succeeds and the starter ships
 *      pre-populated with a Hebrew/RTL marketing site (Home, About,
 *      Services, Contact).
 */

export type WpNodeType = "Page" | "Post";

export interface WpSeoData {
  title?: string;
  description?: string;
  ogImage?: string;
}

export interface WpNode {
  uri: string;
  slug: string;
  title: string;
  content: string;
  excerpt?: string;
  date?: string;
  __typename: WpNodeType;
  seo?: WpSeoData;
  featuredImage?: { url: string; alt: string } | null;
  template?: string;
}

const WP_ENDPOINT = import.meta.env.WP_GRAPHQL_URL || process.env.WP_GRAPHQL_URL;
const WP_TOKEN = import.meta.env.WP_GRAPHQL_TOKEN || process.env.WP_GRAPHQL_TOKEN;

const ALL_CONTENT_QUERY = /* GraphQL */ `
  query AllContent {
    pages(first: 1000, where: { status: PUBLISH }) {
      nodes {
        __typename
        id
        uri
        slug
        title
        content
        template { templateName }
        featuredImage {
          node { sourceUrl altText }
        }
      }
    }
    posts(first: 1000, where: { status: PUBLISH }) {
      nodes {
        __typename
        id
        uri
        slug
        title
        content
        excerpt
        date
        featuredImage {
          node { sourceUrl altText }
        }
      }
    }
  }
`;

interface RawWpNode {
  __typename: WpNodeType;
  uri: string;
  slug: string;
  title: string;
  content: string;
  excerpt?: string;
  date?: string;
  template?: { templateName?: string };
  featuredImage?: { node?: { sourceUrl?: string; altText?: string } } | null;
}

async function fetchFromWp(): Promise<WpNode[] | null> {
  if (!WP_ENDPOINT || WP_ENDPOINT.includes("example.com")) {
    return null;
  }

  try {
    const headers: Record<string, string> = {
      "Content-Type": "application/json",
    };
    if (WP_TOKEN) headers.Authorization = `Bearer ${WP_TOKEN}`;

    const res = await fetch(WP_ENDPOINT, {
      method: "POST",
      headers,
      body: JSON.stringify({ query: ALL_CONTENT_QUERY }),
    });

    if (!res.ok) {
      console.warn(`[wp] GraphQL endpoint returned ${res.status} — using seed data.`);
      return null;
    }

    const json = (await res.json()) as {
      data?: { pages?: { nodes: RawWpNode[] }; posts?: { nodes: RawWpNode[] } };
      errors?: Array<{ message: string }>;
    };

    if (json.errors?.length) {
      console.warn("[wp] GraphQL errors:", json.errors.map((e) => e.message).join("; "));
      return null;
    }

    const raw = [
      ...(json.data?.pages?.nodes ?? []),
      ...(json.data?.posts?.nodes ?? []),
    ];

    if (raw.length === 0) return null;

    return raw.map(normalize);
  } catch (err) {
    console.warn("[wp] fetch failed — using seed data:", (err as Error).message);
    return null;
  }
}

function normalize(raw: RawWpNode): WpNode {
  return {
    __typename: raw.__typename,
    uri: sanitizeUri(raw.uri),
    slug: raw.slug,
    title: raw.title,
    content: raw.content ?? "",
    excerpt: raw.excerpt,
    date: raw.date,
    template: raw.template?.templateName,
    featuredImage: raw.featuredImage?.node?.sourceUrl
      ? {
          url: raw.featuredImage.node.sourceUrl,
          alt: raw.featuredImage.node.altText ?? "",
        }
      : null,
  };
}

/**
 * Normalize WP URIs to match Astro's `trailingSlash: 'never'` config.
 * - "/"   → ""        (home maps to the root `index`)
 * - "/about/" → "about"
 */
export function sanitizeUri(uri: string): string {
  if (!uri || uri === "/") return "";
  return uri.replace(/^\/+/, "").replace(/\/+$/, "");
}

/**
 * Public entry point. Returns merged content from WP (when reachable)
 * with seed content filling in any of the four core marketing pages
 * that are missing from the CMS.
 */
export async function getAllContent(): Promise<WpNode[]> {
  const remote = await fetchFromWp();
  const merged: WpNode[] = remote ?? [];

  const haveUri = new Set(merged.map((n) => n.uri));
  for (const seed of SEED_CONTENT) {
    if (!haveUri.has(seed.uri)) merged.push(seed);
  }
  return merged;
}

export async function getContentByUri(uri: string): Promise<WpNode | undefined> {
  const all = await getAllContent();
  return all.find((n) => n.uri === sanitizeUri(uri));
}

// ---------------------------------------------------------------------------
// Fallback seed content (Hebrew, RTL) — keeps the starter useful with no CMS.
// ---------------------------------------------------------------------------

export const SEED_CONTENT: WpNode[] = [
  {
    __typename: "Page",
    uri: "",
    slug: "home",
    title: "ברוכים הבאים",
    template: "HomeLayout",
    content: `
      <section class="wp-block-cover has-text-align-center">
        <h1>בונים אתרים מהירים עם Astro + WordPress</h1>
        <p>ערכת התחלה מוכנה לייצור עבור אתרי תוכן בעברית — מהירים, נגישים ומותאמים לסלולר.</p>
        <p><a class="wp-block-button__link" href="/contact">צרו קשר</a></p>
      </section>
      <section>
        <h2 class="has-text-align-center">למה לבחור בנו?</h2>
        <ul>
          <li>ביצועים מצוינים בזכות יצירה סטטית של דפים.</li>
          <li>תוכן שמתעדכן ישירות מתוך WordPress דרך WPGraphQL.</li>
          <li>תמיכה מלאה ב-RTL ובעברית מהרגע הראשון.</li>
        </ul>
      </section>
    `,
    seo: {
      title: "ברוכים הבאים | Starter WP × Astro",
      description: "ערכת התחלה מוכנה לייצור עבור אתרי תוכן בעברית עם Astro ו-WordPress Headless.",
    },
    featuredImage: null,
  },
  {
    __typename: "Page",
    uri: "about",
    slug: "about",
    title: "אודות",
    template: "PageLayout",
    content: `
      <h2>הסיפור שלנו</h2>
      <p>אנחנו מאמינים שאתר טוב הוא אתר מהיר, נגיש וקל לתחזוקה. שילוב של Astro ו-WordPress מאפשר לכם ליהנות מהטוב שבשני העולמות: ממשק ניהול מוכר ועריכה נוחה, יחד עם ביצועים של אתר סטטי.</p>
      <h3>הערכים שלנו</h3>
      <ul>
        <li>שקיפות מלאה מול הלקוח.</li>
        <li>קוד פתוח, נקי ומתועד.</li>
        <li>חוויית משתמש מצוינת בכל מכשיר.</li>
      </ul>
    `,
    seo: {
      title: "אודות | Starter WP × Astro",
      description: "מי אנחנו ולמה בחרנו ב-Astro יחד עם WordPress.",
    },
    featuredImage: null,
  },
  {
    __typename: "Page",
    uri: "services",
    slug: "services",
    title: "שירותים",
    template: "PageLayout",
    content: `
      <h2>השירותים שלנו</h2>
      <p>בחרו את הפתרון שמתאים לכם.</p>
      <h3>פיתוח אתרי תדמית</h3>
      <p>אתרים מהירים, נגישים ומותאמים מנועי חיפוש.</p>
      <h3>חנויות מסחר אלקטרוני</h3>
      <p>שילוב WooCommerce או Shopify עם ממשק Astro קליל ומהיר.</p>
      <h3>תחזוקה ושדרוגים</h3>
      <p>מעטפת תמיכה חודשית הכוללת גיבויים, עדכוני אבטחה ושיפורים שוטפים.</p>
    `,
    seo: {
      title: "שירותים | Starter WP × Astro",
      description: "אתרי תדמית, מסחר אלקטרוני ותחזוקה מקצועית.",
    },
    featuredImage: null,
  },
  {
    __typename: "Page",
    uri: "contact",
    slug: "contact",
    title: "צור קשר",
    template: "ContactLayout",
    content: `
      <h2>נשמח לשמוע מכם</h2>
      <p>השאירו פרטים ונחזור אליכם בהקדם, או דברו איתנו ישירות:</p>
      <ul>
        <li>אימייל: <a href="mailto:hello@example.com">hello@example.com</a></li>
        <li>טלפון: <a href="tel:+972500000000">050-000-0000</a></li>
        <li>כתובת: רחוב הרצל 1, תל אביב</li>
      </ul>
    `,
    seo: {
      title: "צור קשר | Starter WP × Astro",
      description: "דברו איתנו על הפרויקט הבא שלכם.",
    },
    featuredImage: null,
  },
];
