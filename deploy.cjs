const FtpDeploy = require("ftp-deploy");
const ftpDeploy = new FtpDeploy();
const path = require("path");

const config = {
    user: "ftp@giangiao.com.vn", // Thay bằng User DirectAdmin của bạn
    password: "YdEMmRxChCjFkCLNpHcv",    // Thay bằng Pass DirectAdmin của bạn
    host: "42.112.30.41",
    port: 21,
    localRoot: path.join(__dirname, "dist"),
    remoteRoot: "public_html/", // Đường dẫn tương đối chuẩn cho DirectAdmin
    include: ["*", "**/*"],
    deleteRemote: false,
    forcePasv: true
};

// --- HIỂN THỊ TIẾN TRÌNH UPLOAD ---
ftpDeploy.on("uploading", function (data) {
    const percent = Math.round((data.transferredFileCount / data.totalFilesCount) * 100);
    // Xóa dòng cũ và ghi đè dòng mới để Terminal gọn gàng
    process.stdout.clearLine(0);
    process.stdout.cursorTo(0);
    process.stdout.write(`🚀 Đang upload: ${percent}% [${data.transferredFileCount}/${data.totalFilesCount}] - ${data.filename}`);
});

ftpDeploy.on("upload-error", function (data) {
    console.error(`\n❌ Lỗi file: ${data.filename} -> ${data.err}`);
});

// --- THỰC THI UPLOAD ---
console.log("📡 Đang kết nối tới host 42.112.30.41...");

ftpDeploy.deploy(config)
    .then(() => console.log("\n\n✨ HOÀN TẤT: Đã cập nhật xong giangiao.com.vn!"))
    .catch(err => console.error("\n💥 Lỗi Deploy:", err));