import { defineConfig } from "astro/config";
import tailwind from "@astrojs/tailwind";

// https://astro.build/config
export default defineConfig({
  site: process.env.SITE_URL || "https://example.com",
  output: "static",
  trailingSlash: "never",
  build: {
    format: "directory",
  },
  // applyBaseStyles is false because src/styles/global.css imports
  // `@tailwind base/components/utilities` itself.
  integrations: [tailwind({ applyBaseStyles: false })],
});
