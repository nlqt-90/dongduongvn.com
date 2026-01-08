// src/utils/asset.ts
export function asset(relative: string): string {
    const base = import.meta.env.BASE_URL ?? "/";
    return `${base}assets/${relative}`.replace(/\/+/g, "/");
  }
  