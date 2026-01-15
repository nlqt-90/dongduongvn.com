<?php

// --- CORE FUNCTION (used internally by CMS or via HTTP) ---
if (!function_exists('cms_upload_image')) {
    function slugify_simple($t){$t=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$t);$t=strtolower(trim($t));$t=preg_replace('/[^a-z0-9]+/','-',$t);$t=preg_replace('/-+/','-',$t);return trim($t,'-');}

    /** Path tới file watermark PNG (nền trong suốt) */
    define('CMS_WATERMARK_PNG', realpath(__DIR__ . '/../public/assets/img/watermark.png'));

    /**
     * Thêm watermark (GD) đặt giữa ảnh, giữ tỷ lệ 30% chiều rộng ảnh.
     */
    function gd_add_watermark(&$dstImg, int $dstW, int $dstH): void {
        if (!CMS_WATERMARK_PNG || !is_file(CMS_WATERMARK_PNG)) return; // không có watermark
        $wmSrc = imagecreatefrompng(CMS_WATERMARK_PNG);
        if (!$wmSrc) return;
        imagesavealpha($wmSrc, true);
        $wmW = imagesx($wmSrc);
        $wmH = imagesy($wmSrc);
        // target width 30% ảnh, giữ tỷ lệ
        $targetW = (int)($dstW * 0.3);
        if ($targetW <=0) { imagedestroy($wmSrc); return; }
        $scale = $targetW / $wmW;
        $targetH = (int)($wmH * $scale);
        $wmResized = imagecreatetruecolor($targetW, $targetH);
        imagealphablending($wmResized, false);
        imagesavealpha($wmResized, true);
        imagecopyresampled($wmResized, $wmSrc, 0,0,0,0, $targetW,$targetH, $wmW,$wmH);
        // vị trí center
        $dstX = (int)(($dstW - $targetW)/2);
        $dstY = (int)(($dstH - $targetH)/2);
        imagecopy($dstImg, $wmResized, $dstX, $dstY, 0,0, $targetW,$targetH);
        imagedestroy($wmSrc);
        imagedestroy($wmResized);
    }

    function cms_upload_image(string $type, string $tmpFile, string $originalName, string $mimeType, string $forcedName = null): array {
        require_once __DIR__ . "/config.php";

        if ($type === 'project') {
            $baseDir  = UPLOAD_PROJECT_DIR;
            $baseUrl  = UPLOAD_PROJECT_URL;
            if (!empty($_POST['project_slug'])) {
                $sub = slugify_simple($_POST['project_slug']);
                $baseDir .= $sub . '/';
                $baseUrl .= $sub . '/';
            }
        } else {
            $baseDir = UPLOAD_POPUP_DIR;
            $baseUrl = UPLOAD_POPUP_URL;
        }

        if (!is_dir($baseDir) && !mkdir($baseDir, 0775, true)) {
            return ["error" => "cannot create upload dir"];
        }

        list($width,$height,$imageType)=getimagesize($tmpFile);
        $maxWidth=1200;
        if($width>$maxWidth){$ratio=$maxWidth/$width;$newWidth=$maxWidth;$newHeight=(int)($height*$ratio);}else{$newWidth=$width;$newHeight=$height;}

        switch($imageType){
            case IMAGETYPE_JPEG:$source=imagecreatefromjpeg($tmpFile);break;
            case IMAGETYPE_PNG:$source=imagecreatefrompng($tmpFile);break;
            case IMAGETYPE_WEBP:$source=imagecreatefromwebp($tmpFile);break;
            default:return["error"=>"unsupported format"];
        }

        $resized=imagecreatetruecolor($newWidth,$newHeight);
        imagealphablending($resized,false);imagesavealpha($resized,true);
        imagecopyresampled($resized,$source,0,0,0,0,$newWidth,$newHeight,$width,$height);

        // Thêm watermark cho gallery dự án
        if($type==='project_gallery'){
            gd_add_watermark($resized, $newWidth, $newHeight);
        }

        $safeName=$forcedName??($_POST['slug']??pathinfo($originalName,PATHINFO_FILENAME));
        $safeName=slugify_simple($safeName);
        if($safeName==='')$safeName=uniqid();
        $filename=$safeName.'.webp';
        imagewebp($resized,$baseDir.$filename,80);
        imagedestroy($source);imagedestroy($resized);

        return ["status"=>"ok","path"=>$baseUrl.$filename];
    }
}

// ---------------------------------------------------------

if (realpath(__FILE__) !== realpath($_SERVER['SCRIPT_FILENAME'])) {
    return;
}

// HTTP upload handler (giữ nguyên)...
