import { defineCollection, z } from "astro:content";

const projects = defineCollection({
  type: "content",
  schema: z.object({
    title: z.string(),
    location: z.string(),

    // Thời gian bắt đầu cho thuê thiết bị (YYYY-MM)
    startDate: z.string(),

    // Danh mục thiết bị (slug list)
    categories: z.array(z.string()),

    thumbnail: z.string(),
    mainImage: z.string(),

    description: z.array(z.string()),

    info: z.object({
      location: z.string(),
      scope: z.string(),
    }),

    gallery: z.array(z.string()),
  }),
});

// --------------- NEW COLLECTION: popups -----------------
const popups = defineCollection({
  type: "content",
  schema: z.object({
    title: z.string(),
    active: z.boolean().default(true),
    startDate: z.union([z.string(), z.date()]).optional(),
    endDate: z.union([z.string(), z.date()]).optional(),
    image: z.string(),               // path to image in /assets/
  }),
});

export const collections = {
  projects,
  popups,
};
