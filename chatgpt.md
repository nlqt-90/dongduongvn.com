📘 TÀI LIỆU TỔNG HỢP DỰ ÁN CMS + ASTRO + GITHUB API

(Bản lưu trữ để tiếp tục làm việc ngày mai)

🧱 1. Mục tiêu hệ thống

Website chính build bằng Astro (static site)

Hosting DirectAdmin chứa:

/public_html/dist → file build của Astro

/public_html/cms → CMS PHP

/public_html/uploads → ảnh upload từ user

/public_html/src/content → bản sao markdown để CMS đọc/sửa

CMS nhập nội dung → commit lên GitHub bằng GitHub API

GitHub Actions build lại → deploy dist về hosting

📂 2. Cấu trúc thư mục trên host
public_html/
│
├── cms/                   
│    ├── config.php
│    ├── login.php
│    ├── logout.php
│    ├── index.php
│    ├── popups.php
│    ├── popup_edit.php
│    ├── popup_save.php
│    ├── popup_delete.php
│    ├── projects.php
│    ├── project_edit.php
│    ├── project_save.php
│    ├── project_delete.php
│    ├── upload.php
│    ├── github_commit.php
│    └── .env.php              # chứa token GitHub (KHÔNG nằm trong repo)
│
├── uploads/
│    ├── popups/
│    └── projects/
│
└── src/
     └── content/
         ├── popups/
         └── projects/

🔐 3. Bảo mật

File .env.php chứa token GitHub:

<?php
return [
  "GITHUB_TOKEN" => "TOKEN_THAT",
  "GITHUB_REPO" => "username/repo"
];


Không commit lên GitHub

Được tạo trực tiếp trên host

Được thêm vào .gitignore

🔄 4. Quy trình hoạt động
1) User login vào CMS

→ qua session PHP

2) User nhập nội dung popup/project

→ upload ảnh → resize → webp → lưu uploads/

3) CMS tạo file .md tại host

→ /public_html/src/content/popups/*.md
→ /public_html/src/content/projects/*.md

4) CMS gọi GitHub API

→ commit file .md lên repo

5) GitHub Actions chạy build

→ Astro xuất dist/

6) GitHub Actions deploy FTP

→ đẩy dist/ xuống host

7) Website cập nhật
🧩 5. Danh sách file CMS đã tạo
✔ config.php

Các đường dẫn như POPUP_DIR, PROJECT_DIR, UPLOAD_DIR.

✔ login.php / logout.php

Xác thực người dùng bằng session.

✔ index.php

Trang dashboard cho CMS.

✔ popups.php

Danh sách popup + nút sửa/xoá.

✔ popup_edit.php

Form nhập popup (đã sửa lỗi regex + active).

✔ popup_save.php

Lưu popup, upload ảnh, commit GitHub (đã sửa lỗi cú pháp).

✔ popup_delete.php

Xoá file .md và commit GitHub.

✔ projects.php

Danh sách dự án.

✔ project_edit.php

Form đầy đủ (title, location, thumbnail, gallery…).

✔ project_save.php

Lưu dự án, upload ảnh gallery, commit GitHub.

✔ project_delete.php

Xoá file .md dự án.

✔ upload.php

Resize ảnh về 1200px + convert WebP.

✔ github_commit.php

Đẩy file Markdown lên GitHub qua REST API.

🔧 6. Những lỗi đã sửa
❌ Lỗi 1 — popup_save.php sai cú pháp
@endDate = ...


➡️ ĐÃ sửa thành:

$endDate = $_POST['endDate'] ?? "";

❌ Lỗi 2 — Regex parse frontmatter popup sai

Đã sửa từ:

/^---(.*?)---/s


thành:

/^---\s*(.*?)\s*---/s

❌ Lỗi 3 — Checkbox active không hoạt động

Đã cập nhật:

<?= ($data['active'] == "true") ? "checked" : "" ?>

🧪 7. Checklist để kiểm tra vào ngày mai
✔ 1. Kiểm tra thư mục src/content/ có tồn tại trên host không
✔ 2. Test upload ảnh popup
✔ 3. Test GitHub API commit
✔ 4. Xem commit log trên GitHub
✔ 5. Xem GitHub Actions có build + FTP hay không
✔ 6. Kiểm tra quyền 755 cho thư mục uploads
✔ 7. Kiểm tra file .env.php trên host có đúng token không
🚀 8. Việc tiếp theo có thể triển khai

Thiết kế giao diện CMS đẹp hơn

Thêm trình soạn thảo WYSIWYG

Tự động xóa ảnh cũ khi cập nhật nội dung

Thêm tính năng clone dự án

Thêm phân trang danh sách dự án/popup

Lưu bản nháp thay vì publish ngay

Kiểm tra xung đột commit GitHub

📌 9. Gợi ý commit messages đã dùng
feat(cms): add popup listing page with frontmatter parser
feat(cms): add popup_edit page with form
feat(cms): add popup_save with upload + markdown
fix(cms): correct parse error in popup_save.php
fix(cms): update regex in popup_edit.php
feat(cms): add github_commit.php via GitHub API
chore: ignore cms/.env.php for security