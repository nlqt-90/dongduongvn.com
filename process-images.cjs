const sharp = require('sharp');
const fs = require('fs-extra');
const path = require('path');

const IN_DIR = 'public/uploads/originals';
const OUT_DIR = 'public/uploads/processed';
const WATERMARK_PATH = 'public/assets/img/watermark.png'; // Đảm bảo file này tồn tại

async function processImages() {
  // Kiểm tra thư mục đầu vào có tồn tại không
  if (!(await fs.pathExists(IN_DIR))) {
    console.log('Chưa có ảnh gốc nào để xử lý.');
    return;
  }

  // Hàm quét đệ quy tất cả thư mục con
  async function scan(directory) {
    const items = await fs.readdir(directory);

    for (const item of items) {
      const fullPath = path.join(directory, item);
      const stat = await fs.stat(fullPath);

      if (stat.isDirectory()) {
        await scan(fullPath);
      } else if (item.match(/\.(jpg|jpeg|png)$/i)) {
        // Tính toán đường dẫn đầu ra tương ứng
        const relPath = path.relative(IN_DIR, directory);
        const targetDir = path.join(OUT_DIR, relPath);
        await fs.ensureDir(targetDir);

        const outputPath = path.join(targetDir, `${path.parse(item).name}.webp`);
        
        let img = sharp(fullPath);

        // --- LOGIC PHÂN LUỒNG THEO YÊU CẦU CỦA BẠN ---
        
        if (directory.includes('popups')) {
          // 1. POPUP: Resize 1200 + Convert WebP
          img = img.resize(1200, null, {withoutEnlargement: true});
          console.log(`-> Đang nén Popup: ${item}`);
        } 
        else if (directory.includes('gallery')) {
          // 2. GALLERY DỰ ÁN: Resize 1200 + Convert WebP + Watermark
          img = img.resize(1200, null, {withoutEnlargement: true});
          if (await fs.pathExists(WATERMARK_PATH)) {
            img = img.composite([{ input: WATERMARK_PATH, gravity: 'southeast' }]);
          }
          console.log(`-> Đang đóng dấu Gallery: ${item}`);
        } 
        else {
          // 3. THUMB/MAIN DỰ ÁN: Chỉ Convert WebP (Không resize)
          console.log(`-> Đang chuyển đổi WebP (giữ size): ${item}`);
        }

        await img.webp({ quality: 85 }).toFile(outputPath);
      }
    }
  }

  await scan(IN_DIR);

  // --- BƯỚC SỬA FILE .MD ĐỂ ĐỔI ĐUÔI FILE SANG .WEBP ---
  const contentDir = 'src/content';
  async function updateMarkdown(dir) {
    const items = await fs.readdir(dir);
    for (const item of items) {
      const p = path.join(dir, item);
      if ((await fs.stat(p)).isDirectory()) {
        await updateMarkdown(p);
      } else if (item.endsWith('.md')) {
        let content = await fs.readFile(p, 'utf8');
        // Tìm các đường dẫn ảnh gốc (.jpg, .png...) và đổi thành .webp
        let newContent = content.replace(/\/uploads\/originals\//g, '/uploads/processed/');
        newContent = content.replace(/\.(jpg|jpeg|png)/g, '.webp');
        await fs.writeFile(p, newContent);
        console.log(`-> Đã cập nhật link trong: ${item}`);
      }
    }
  }
  await updateMarkdown(contentDir);
}

processImages().catch(err => console.error('Lỗi xử lý:', err));