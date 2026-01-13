/**
 * Xử lý ảnh tự động:
 * - Resize nếu > 1200px
 * - Convert sang WebP
 * - Tự động sinh tên file mới theo hash + random ID
 * - Xóa file gốc sau khi xử lý
 */



// Chỉ chạy khi CI (GitHub Action) bật
if (process.env.CI !== "true") {
  console.log("⚠️ Skip image processing (not on CI)");
  process.exit(0);
}

const fs = require("fs");
const path = require("path");
const crypto = require("crypto");
const sharp = require("sharp");

const UPLOAD_DIR = "public/uploads";

// Tạo random ID 6 ký tự
function randomID() {
  return crypto.randomBytes(3).toString("hex");
}

// Tạo hash từ tên file
function hashName(str) {
  return crypto.createHash("md5").update(str).digest("hex").substring(0, 6);
}

function processImage(filePath) {
  const ext = path.extname(filePath).toLowerCase();
  if (![".jpg", ".jpeg", ".png"].includes(ext)) return;

  const dir = path.dirname(filePath);
  const base = path.basename(filePath, ext);

  const newName = `img_${hashName(base)}_${randomID()}.webp`;
  const outputPath = path.join(dir, newName);

  console.log("🔧 Đang xử lý:", base + ext);

  sharp(filePath)
    .metadata()
    .then(meta => {
      let pipeline = sharp(filePath);

      if (meta.width > 1200) {
        pipeline = pipeline.resize(1200);
      }

      return pipeline.webp({ quality: 82 }).toFile(outputPath);
    })
    .then(() => {
      console.log("👉 Xuất file:", outputPath);
      fs.unlinkSync(filePath); // xóa file gốc
      console.log("🗑️ Đã xóa file gốc:", filePath);
    })
    .catch(err => console.error("❌ Lỗi xử lý:", err));
}

function walkDir(dir) {
  fs.readdirSync(dir).forEach(file => {
    const fullPath = path.join(dir, file);
    const stat = fs.statSync(fullPath);

    if (stat.isDirectory()) {
      walkDir(fullPath);
    } else {
      processImage(fullPath);
    }
  });
}

console.log("🚀 Bắt đầu xử lý ảnh…");
walkDir(UPLOAD_DIR);
console.log("🎉 Hoàn tất xử lý ảnh!");
