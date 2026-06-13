import type { APIRoute } from "astro";
import { getAllContent } from "../lib/wp";
import { siteConfig } from "../config/site";

export const GET: APIRoute = async () => {
  const nodes = await getAllContent();
  const base = siteConfig.url.replace(/\/$/, "");

  // Static, explicitly-routed pages that aren't in getAllContent().
  const staticPaths = ["/blog"];

  const urls = [
    ...nodes.map((n) => ({
      loc: `${base}/${n.uri}`.replace(/\/$/, "") || base,
      lastmod: n.date,
    })),
    ...staticPaths.map((p) => ({ loc: `${base}${p}` })),
  ];

  const xml =
    `<?xml version="1.0" encoding="UTF-8"?>\n` +
    `<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n` +
    urls
      .map(
        (u) =>
          `  <url>\n    <loc>${u.loc}</loc>\n` +
          (u.lastmod ? `    <lastmod>${new Date(u.lastmod).toISOString()}</lastmod>\n` : "") +
          `  </url>`,
      )
      .join("\n") +
    `\n</urlset>\n`;

  return new Response(xml, {
    headers: { "Content-Type": "application/xml; charset=utf-8" },
  });
};
