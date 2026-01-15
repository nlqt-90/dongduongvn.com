<?php
require_once __DIR__ . "/config.php";
if (empty($_SESSION['logged_in'])) {header("Location: login.php");exit;}

$file=$_GET['file']??null;$data=["title"=>"","active"=>"false","startDate"=>"","endDate"=>"","image"=>""];
if($file&&file_exists(POPUP_DIR.$file)){
  $c=file_get_contents(POPUP_DIR.$file);
  if(preg_match('/^---\s*(.*?)\s*---/s',$c,$m)){
    foreach(explode("\n",trim($m[1])) as $l){if(str_contains($l,':')){[$k,$v]=explode(':',$l,2);$data[trim($k)]=trim($v);} }
  }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $file?"Sửa thông báo":"Thêm thông báo" ?></title>
  <link rel="stylesheet" href="https://unpkg.com/@picocss/pico@1.*/css/pico.min.css">
  <style>
    body{min-height:100vh;display:flex;justify-content:center;align-items:center;padding:1rem;background:var(--pico-background-color);}  
    article.card{width:100%;max-width:600px;background:#fff;padding:2rem 2.25rem;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,.06);}  
    h2{margin-top:0.5rem;margin-bottom:1.5rem;text-align:center;font-weight:700;}  
    form>*{margin-bottom:1rem;}  
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}  
    input[type="text"],input[type="date"],input[type="file"]{padding:.7rem .9rem;}  
    button.primary{width:100%;border-radius:8px;}  
    a.back{display:inline-block;margin-top:1rem;font-size:.9rem;color:var(--pico-muted-color);text-decoration:none;}a.back:hover{text-decoration:underline;}  
    .img-preview{max-width:120px;border-radius:6px;margin-bottom:.5rem;}  
  </style>
</head>
<body>
  <article class="card">
    <a href="popups.php" role="button" class="contrast">🔙 Trang thông báo</a></a>
    <h2><?= $file?"Sửa thông báo":"Thêm thông báo" ?></h2>

    <form action="popup_save.php" method="post" enctype="multipart/form-data">
      <?php if($file):?><input type="hidden" name="file" value="<?=htmlspecialchars($file)?>"><?php endif;?>

      <label>Tiêu đề thông báo
        <input type="text" name="title" value="<?=htmlspecialchars($data['title'])?>" required>
      </label>

      <label>
        <input type="checkbox" name="active" <?=($data['active']==='true')?"checked":""?>> Kích hoạt
      </label>

      <div class="grid">
        <label>Ngày hiển thị
          <input type="date" name="startDate" value="<?=htmlspecialchars($data['startDate'])?>" required>
        </label>
        <label>Ngày tắt hiển thị
          <input type="date" name="endDate" value="<?=htmlspecialchars($data['endDate'])?>" required>
        </label>
      </div>

      <label>Ảnh thông báo</label>
      <?php if($data['image']):?>
        <img src="<?=$data['image']?>" alt="preview" class="img-preview">
      <?php endif;?>
      <input type="text" name="image" value="<?=htmlspecialchars($data['image'])?>" readonly>
      <input type="file" name="image_upload" accept="image/*">

      <button type="submit" class="contrast">💾 Lưu</button>
    </form>
  </article>
</body>
</html>