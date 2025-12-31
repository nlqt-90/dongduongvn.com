# Trạng thái Dự án Chuyển đổi Website sang Astro

Tài liệu này tổng hợp trạng thái hiện tại của dự án, các công việc đã hoàn thành, và kế hoạch tiếp theo.
Cập nhật lần cuối: 2025-12-31

## 1. Mục tiêu Dự án

Chuyển đổi website công ty từ theme HTML tĩnh sang Astro, đồng thời tái cấu trúc để dễ bảo trì, mở rộng và tích hợp Headless CMS trong tương lai.

## 2. Những việc ĐÃ HOÀN THÀNH

### Layout & Cấu trúc
- **`src/layouts/BaseLayout.astro`**: Tạo layout gốc chứa cấu trúc `<html>`, `<head>`, `<body>` và các assets chung (CSS/JS).
- **`src/layouts/PageLayout.astro`**: Tạo layout cho các trang con, sử dụng `BaseLayout` và bao gồm các thành phần chung như `Header`, `Footer`, và `PageHeader` (với breadcrumbs).

### Refactor Trang
Các trang sau đã được chuyển đổi từ cấu trúc HTML riêng lẻ sang sử dụng `PageLayout`:
- `src/pages/about.astro`
- `src/pages/contact.astro`
- `src/pages/projects/index.astro`
- `src/pages/projects/[slug].astro`
- `src/pages/services/index.astro`
- `src/pages/services/hoist.astro`
- `src/pages/services/scaffolding.astro` (sửa lỗi typo từ `scaffoding`)
- `src/pages/services/tower-crane.astro`

### Xử lý CSS/JS
- **CSS**: Các file CSS riêng cho từng trang được load thông qua `<slot name="head" />` trong `PageLayout`.
- **JS**: Các file JS riêng cho từng trang được load thông qua `<script slot="scripts" ...>`.
- **Sửa lỗi CSS**: Đã xác định và bổ sung các đoạn CSS bị thiếu cho section "process" (`progress-tracker`) trong file `single-service.min.css` để khớp với theme gốc.
- **Sửa lỗi Astro**: Loại bỏ việc sử dụng `Fragment` không hợp lệ trong các named slots.

### Cập nhật Nội dung & Link
- **Tiêu đề trang**: Đã cập nhật `title` (SEO) và `pageTitle` (hiển thị) sang tiếng Việt cho các trang đã refactor.
- **Điều hướng**: Cập nhật lại các `href` trong menu và các card dịch vụ để trỏ đúng đến các trang Astro mới.

### File mới
- **`src/pages/services/formwork.astro`**: Đã tạo trang mới cho dịch vụ "Cốp pha" theo đúng cấu trúc của các trang dịch vụ khác.

## 3. Những việc ĐANG DỞ / CẦN LÀM TIẾP

### Thay đổi chưa commit
Hiện tại có một số thay đổi đang chờ được commit:
- `M src/components/Header.astro`
- `M src/layouts/BaseLayout.astro`
- `M src/layouts/PageLayout.astro`
- `M src/pages/index.astro`
- `D "src/pages/projects/[slug] copy.astro"`
- `?? src/pages/services/formwork.astro`

### Kế hoạch tiếp theo
1.  **Componentization**: Tách các section lặp lại (Hero, Services List, Projects Grid, FAQ, ...) thành các component Astro độc lập trong `src/components/sections/`.
2.  **Scoped CSS**: Di chuyển CSS từ các file riêng lẻ trong `public/assets/css` vào thẻ `<style>` bên trong component tương ứng để tận dụng scoped CSS của Astro.
3.  **Content Collections**: Chuyển đổi dữ liệu từ file `.json` (ví dụ: `projects.json`) sang Astro Content Collections (`src/content/projects/`) để quản lý nội dung tốt hơn và sẵn sàng cho CMS.
4.  **Asset Optimization**: Chuyển đổi các thẻ `<img>` sang component `<Image />` của Astro để tối ưu hóa hình ảnh (responsive, lazy-loading).

## 4. Cấu trúc thư mục hiện tại (tóm tắt)

```
/
├── public/                 # Assets tĩnh (css, js, img, fonts)
├── src/
│   ├── components/         # Components tái sử dụng (Header, Footer)
│   ├── data/               # Dữ liệu JSON (projects.json)
│   ├── layouts/            # Layouts chung (BaseLayout, PageLayout)
│   ├── pages/              # Các trang và routes của Astro
│   │   ├── projects/
│   │   └── services/
│   └── types/              # Định nghĩa TypeScript
├── astro.config.mjs
├── package.json
└── tsconfig.json
```

## 5. Quy ước quan trọng

- **Commit Workflow**: Mọi thay đổi phải được tóm tắt sau khi hoàn thành. Chỉ thực hiện `git commit` sau khi có xác nhận "OK, commit". Commit message phải ngắn gọn, mô tả đúng thay đổi.
- **Refactor**: Thực hiện các thay đổi nhỏ, dễ rollback. Không thực hiện refactor lớn hoặc thay đổi kiến trúc mà không thảo luận trước.
- **Nội dung**: Không tự ý thay đổi nội dung text hoặc hình ảnh trừ khi được yêu cầu.

## 6. Danh sách trang dịch vụ

| Dịch vụ                 | Link                               |
| ----------------------- | ---------------------------------- |
| Cẩu tháp                | `/services/tower-crane`            |
| Vận thăng               | `/services/hoist`                  |
| Giàn giáo               | `/services/scaffolding`            |
| Cốp pha                 | `/services/formwork`               |
| Vận chuyển & Lắp đặt   | `#` (không có trang riêng)        |
| Khán đài                | `https://khandaivn.com` (link ngoài) |

## 7. Ghi chú kỹ thuật

- **CSS Load Order**: Các file CSS toàn cục (`main.css`, `libs.min.css`) được load trong `BaseLayout`. Các file CSS cho từng trang được load sau đó thông qua `slot="head"` trong `PageLayout`.
- **JS Dependencies**: Một số file JS (`singleservice.min.js`) có logic phụ thuộc vào cấu trúc DOM và các class cụ thể (ví dụ: `progress-tracker`). Cần cẩn thận khi thay đổi HTML/class của các section này.
- **Astro Slots**: Các thẻ `<link>` và `<script>` có thể được đặt trực tiếp vào slot mà không cần `Fragment` wrapper. Ví dụ: `<link slot="head" ... />`.