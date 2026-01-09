import { defineConfig } from 'astro/config';

// NOTE: đổi 'dongduong' thành tên sub-folder bạn muốn
export default defineConfig({
  site : 'http://localhost:4321/dongduong/',   // URL đầy đủ dùng khi preview
  base : '/dongduong/',                         // sub-folder
});