import { defineCollection, z } from "astro:content";

const projects = defineCollection({
  type: "data", // front-matter only, no body rendering required
  schema: z.object({
    slug: z.string(),
    title: z.string(),
    location: z.string(),
    thumbnail: z.string(),
    mainImage: z.string(),
    description: z.array(z.string()),
    info: z.object({
      location: z.string(),
      sector: z.string(),
      technology: z.string(),
      scope: z.string(),
      completionDate: z.string()
    }),
    gallery: z.array(z.string())
  })
});

export const collections = { projects };

