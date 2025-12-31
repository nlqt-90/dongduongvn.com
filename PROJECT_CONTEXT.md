# Bối cảnh Dự án: Chuyển đổi Website sang Astro

*Cập nhật lần cuối: 2025-12-31*

Tài liệu này là nguồn thông tin chính (single source of truth) cho bối cảnh, mục tiêu, các giai đoạn và quy ước kỹ thuật của dự án. File này được dùng làm "trí nhớ dự án" cho tất cả các phiên làm việc sau này.

## 1. Mục tiêu Dự án

**Chuyển đổi website công ty từ một theme HTML tĩnh sang nền tảng Astro hiện đại, dễ bảo trì và hiệu năng cao.** Mục tiêu dài hạn là tái cấu trúc để sẵn sàng tích hợp với một Headless CMS.

## 2. Các Giai đoạn của Dự án (Phases)

Dự án được chia thành các giai đoạn tuần tự:

-   **Phase 1 (Đã hoàn thành):** Khởi tạo dự án, phân tích cấu trúc theme gốc, và refactor cơ bản các trang tĩnh sang layout Astro.
-   **Phase 2 (Giai đoạn hiện tại):** **Việt hóa & Chuẩn hóa Nội dung.** Viết lại toàn bộ nội dung placeholder sang tiếng Việt chuyên nghiệp, phù hợp với website B2B và cấu trúc sẵn cho CMS.
-   **Phase 3 (Sắp tới):** **Tích hợp Headless CMS.** Phân tích nội dung, đề xuất schema cho các loại content (Pages, Services, Projects), và refactor các component để trở thành data-driven.
-   **Phase 4 (Sắp tới):** **Tính năng Popup Trang chủ.** Xây dựng một popup thông báo trên trang chủ có thể quản lý (bật/tắt, nội dung, hình ảnh) từ CMS.
-   **Phase 5 (Sắp tới):** **Tối ưu hóa Astro.** Áp dụng các best practices của Astro, bao gồm component hóa sâu hơn, scoped CSS, tối ưu hình ảnh, và dọn dẹp code để sẵn sàng cho production.

## 3. Trạng thái Hiện tại của Dự án

-   **Layouts:** `BaseLayout.astro` (cấu trúc HTML gốc) và `PageLayout.astro` (cho các trang con) đã ổn định.
-   **Pages:** Hầu hết các trang chính (`about`, `contact`, `projects`, `services` và các trang con) đã được refactor để sử dụng `PageLayout`.
-   **Nội dung:** Toàn bộ nội dung text hiện là placeholder tiếng Anh từ theme gốc.
-   **Dữ liệu:** Trang chi tiết dự án được render động từ file `src/data/projects.json`. Các trang dịch vụ là các file `.astro` riêng lẻ với cấu trúc lặp lại.
-   **Styling:** CSS đang được load toàn cục từ thư mục `public/assets/css/`.

## 4. Quy ước Làm việc

-   **Bối cảnh xuyên suốt:** Dự án được xem là một thể thống nhất, không cần giải thích lại từ đầu trong mỗi phiên làm việc.
-   **Thay đổi nhỏ, an toàn:** Mọi thay đổi phải nhỏ, có thể rollback. Không refactor lớn hoặc thay đổi kiến trúc mà không có sự đồng ý.
-   **Workflow Commit:** Chỉ thực hiện `git commit` sau khi nhận được xác nhận "OK, commit".
-   **Nội dung là trên hết:** Trong Phase 2, ưu tiên hàng đầu là chuẩn hóa nội dung tiếng Việt. Không thay đổi layout, class CSS, hay cấu trúc HTML.

## 5. Nguyên tắc Kỹ thuật & Nội dung

-   **Astro Best Practices:** Luôn ưu tiên các giải pháp và kỹ thuật được Astro khuyến khích.
-   **Tái sử dụng:** Tối đa hóa việc tái sử dụng component để giảm trùng lặp code.
-   **Kiến trúc cho CMS:** Cấu trúc component và luồng dữ liệu phải được thiết kế để dễ dàng tích hợp với CMS sau này.
-   **Nội dung tiếng Việt:** Chuyên nghiệp, rõ ràng, tập trung vào B2B, không dịch máy.

## 6. Danh sách Trang Dịch vụ & Mapping Link

| Tên Dịch vụ             | Đường dẫn (href)                  |
| ----------------------- | ---------------------------------- |
| Cẩu tháp                | `/services/tower-crane`            |
| Vận thăng               | `/services/hoist`                  |
| Giàn giáo               | `/services/scaffolding`            |
| Cốp pha                 | `/services/formwork`               |
| Vận chuyển & Lắp đặt   | `#` (chưa có trang riêng)         |
| Khán đài                | `https://khandaivn.com` (link ngoài) |

## 7. Ghi chú Kỹ thuật

-   **CSS Load Order:** CSS toàn cục (`main.css`, `libs.min.css`) được load trong `BaseLayout`. CSS của từng trang được load sau đó thông qua `<slot name="head" />`.
-   **JS Dependencies:** Một số file JS (ví dụ: `singleservice.min.js`) có logic phụ thuộc vào cấu trúc DOM và class cụ thể. Cần cẩn thận khi thay đổi HTML của các section này.
-   **Astro Slots:** Các thẻ `<link>` và `<script>` có thể được đặt trực tiếp vào slot với thuộc tính `slot="..."`, không cần dùng `Fragment`.
