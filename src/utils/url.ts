// src/utils/url.ts
// Helper to prepend Astro BASE_URL to any path.
// Works both in dev (BASE_URL = "/") and when site is deployed under a sub-folder.
// Usage: url('/assets/img/logo.svg') -> "/dongduong/assets/img/logo.svg" (if base="/dongduong")
//        url('/contact')            -> "/dongduong/contact"
export const BASE = import.meta.env.BASE_URL ?? "/";

export function url(path: string): string {
  // Ensure we don't end up with double slashes
  const clean = path.startsWith("/") ? path.slice(1) : path;
  return `${BASE}${clean}`.replace(/\/+/g, "/");
}

