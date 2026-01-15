<?php

// --- CORE FUNCTION (used internally by CMS or via HTTP) ---
if (!function_exists('cms_upload_image')) {
    function slugify_simple($t){$t=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$t);$t=strtolower(trim($t));$t=preg_replace('/[^a-z0-9]+/','-',$t);$t=preg_replace('/-+/','-',$t);return trim($t,'-');}

    function cms_upload_image(string $type, string $tmpFile, string $originalName, string $mimeType, string $forcedName = null): array {
        require_once __DIR__ . "/config.php";

        if ($type === 'project') {
            $baseDir  = UPLOAD_PROJECT_DIR;
            $baseUrl  = UPLOAD_PROJECT_URL;
            // nếu truyền slug dự án → tạo thư mục con
            if (!empty($_POST['project_slug'])) {
                $sub = slugify_simple($_POST['project_slug']);
                $baseDir .= $sub . '/';
                $baseUrl .= $sub . '/';
            }
        } else {
            $baseDir = UPLOAD_POPUP_DIR;
            $baseUrl = UPLOAD_POPUP_URL;
        }

        // đảm bảo thư mục tồn tại
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

// Trả về ngay nếu file được include để tránh chạy logic HTTP bên dưới
if (realpath(__FILE__) !== realpath($_SERVER['SCRIPT_FILENAME'])) {
    return;
}

// phần còn lại giữ nguyên (HTTP upload)...
