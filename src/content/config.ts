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

export const collections = {
  projects,
};
