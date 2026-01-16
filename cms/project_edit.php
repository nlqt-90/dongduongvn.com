<?php
require_once __DIR__ . "/config.php";
if (empty($_SESSION['logged_in'])) {header("Location: login.php");exit;}
$file=$_GET['file']??null;$data=["title"=>"","location"=>"","startDate"=>"","categories"=>[],"thumbnail"=>"","mainImage"=>"","description"=>"","info_location"=>"","info_scope"=>"","gallery"=>[]];
if($file&&file_exists(PROJECT_DIR.$file)){
  $c=file_get_contents(PROJECT_DIR.$file);
  if(preg_match('/^---\s*([\s\S]*?)\s*---/',$c,$m)){
    $yaml=$m[1];$cur=null;foreach(explode("\n",$yaml) as $l){$t=preg_replace('/\s+#.*$/','',trim($l)); // remove inline comments
      if($t==='') continue;
      // detect new top-level keys (including gallery) unless still inside 'info' block
      if(preg_match('/^([A-Za-z0-9_]+):\s*(.*)$/',$t,$kv)){
        $key=$kv[1];
        // Nếu đang ở trong khối info thì bỏ qua location|scope, các key khác sẽ thoát khỏi info
        if($cur!=='info' || ($cur==='info' && !in_array($key,['location','scope']))){
          $cur=$key;
          $v=trim($kv[2]," \"'");
          $data[$cur]=$v!==''?$v:[];
          continue;
        }
      }
      // list item cho current key
      if($cur && preg_match('/^-\s*(.+)$/', $t, $li)) {
          $data[$cur][] = trim($li[1], " \"'");
          continue;
        }
      // thông tin con trong info
      if($cur==='info'&&preg_match('/^([A-Za-z0-9_]+):\s*(.+)$/',$t,$s)){$k=$s[1];$v=trim($s[2]," \"'");if($k==='location')$data['info_location']=$v;if($k==='scope')$data['info_scope']=$v;}
    }
  }
}
$provinces=$provinces = [
  'An Giang','Bắc Ninh','Cà Mau','Cao Bằng','Cần Thơ',
  'Đà Nẵng','Đắk Lắk','Điện Biên','Đồng Nai','Đồng Tháp',
  'Gia Lai','Hà Nội','Hà Tĩnh','Hải Phòng','Hồ Chí Minh',
  'Huế','Hưng Yên','Khánh Hòa','Lai Châu','Lạng Sơn',
  'Lào Cai','Lâm Đồng','Nghệ An','Ninh Bình','Phú Thọ',
  'Quảng Ngãi','Quảng Ninh','Quảng Trị','Sơn La','Tây Ninh',
  'Thái Nguyên','Thanh Hóa','Tuyên Quang','Vĩnh Long'
];?>
<!doctype html><html lang="vi"><head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $file?"Sửa dự án":"Thêm dự án mới" ?></title>
  <link rel="stylesheet" href="https://unpkg.com/@picocss/pico@1.*/css/pico.min.css" />
  <style>
    body{min-height:100vh;display:flex;justify-content:center;align-items:center;padding:1rem;background:var(--pico-background-color);}  
    article.card{width:100%;max-width: 70vw;background:#fff;padding:2rem 2.25rem;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,.06);}  
    a.back{display:inline-block;margin-bottom:1rem;font-size:.9rem;color:var(--pico-muted-color);text-decoration:none;}a.back:hover{text-decoration:underline;}
    h2{margin:0 0 1.5rem;text-align:center;font-weight:700;}
    form>*{margin-bottom:1.1rem;}
    .grid2{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
    input[type="text"],input[type="date"],input[type="file"],textarea{padding:.7rem .9rem;}
    textarea{min-height:110px;}
    .preview{max-width:120px;border-radius:6px;margin-bottom:.5rem;}
    button.primary{width:100%;border-radius:8px;}
    .thumb{position:relative;display:inline-block;}
    .thumb .remove-btn{position:absolute;top:6px;right:6px;border:none;background:rgba(0,0,0,.6);color:#fff;border-radius:50%;width:24px;height:24px;font-size:16px;display:flex;align-items:center;justify-content:center;padding:0;line-height:1;cursor:pointer;transition:background .2s ease;}
    .thumb .remove-btn:hover{background:rgba(0,0,0,.8);}
  </style>
</head><body>
<article class="card">
  <a href="projects.php" role="button" class="contrast">🔙 Danh sách dự án</a>
  <h2><?= $file?"Sửa dự án":"Thêm dự án mới" ?></h2>

  <form action="project_save.php" method="post" enctype="multipart/form-data">
    <?php if($file):?><input type="hidden" name="file" value="<?=htmlspecialchars($file)?>"><?php endif;?>

    <label>Tên dự án
      <input type="text" name="title" value="<?=htmlspecialchars($data['title'])?>" <?= $file?'readonly':'' ?> required>
    </label>

    <div class="grid2">
      <label>Vị trí (tỉnh/thành)
      <select name="location">
          <?php foreach($provinces as $pr): ?>
            <option value="<?= $pr ?>" <?= $pr==$data['location']? 'selected':'' ?>><?= $pr ?></option>
          <?php endforeach; ?>
    </select>
      </label>
      <label>Thời điểm thuê
        <input type="date" name="startDate" value="<?=htmlspecialchars($data['startDate'])?>">
      </label>
    </div>



    <!-- Danh mục -->
    <?php $opts=["cau-thap"=>"Cẩu tháp","van-thang"=>"Vận thăng","gian-giao"=>"Giàn giáo","cop-pha"=>"Cốp pha","cot-chong"=>"Cột chống","tam-san-thep"=>"Tấm sàn thép"]; ?>
    <label>Máy hoặc thiết bị xây dựng cho thuê</label>
    <div class="grid2" style="grid-template-columns:repeat(2,1fr);gap:.5rem;">
      <?php foreach($opts as $k=>$lab):?>
        <label><input type="checkbox" name="categories[]" value="<?=$k?>" <?=in_array($k,$data['categories'])?'checked':''?>> <?=$lab?></label>
      <?php endforeach;?>
    </div>

    <label>Mô tả dự án (mỗi dòng 1 đoạn)
      <textarea name="description"><?=htmlspecialchars(is_array($data['description'])?implode("\n",$data['description']):$data['description'])?></textarea>
    </label>

    <div class="grid2">
      <label>Địa điểm chi tiết
        <input type="text" name="info_location" value="<?=htmlspecialchars($data['info_location'])?>">
      </label>
      <label>Quy mô
        <input type="text" name="info_scope" value="<?=htmlspecialchars($data['info_scope'])?>">
      </label>
    </div>

    <fieldset>
      <label>Hình thu nhỏ giới thiệu dự án </label>
      <?php if($data['thumbnail']):?><img class="preview" src="<?=$data['thumbnail']?>"><?php endif;?>
      <input type="text" name="thumbnail" value="<?=$data['thumbnail']?>" readonly>
      <input type="file" name="thumbnail_upload" accept="image/*">
    </fieldset>

    <fieldset>
      <label>Hình giới thiệu dự án</label>
      <?php if($data['mainImage']):?><img class="preview" src="<?=$data['mainImage']?>"><?php endif;?>
      <input type="text" name="mainImage" value="<?=$data['mainImage']?>" readonly>
      <input type="file" name="mainImage_upload" accept="image/*">
    </fieldset>

    <fieldset>
      <label>Thư viện hình ảnh (chọn 4 hoặc 8 ảnh)</label>
      <div style="display:flex; flex-wrap:wrap; gap:8px;">
        <?php foreach($data['gallery'] as $g): ?>
          <div class="thumb">
            <img class="preview" src="<?= $g ?>">
            <button type="button" class="remove-btn" aria-label="Xóa ảnh">✕</button>
            <input type="hidden" name="gallery[]" value="<?= htmlspecialchars($g) ?>">
          </div>
        <?php endforeach; ?>
      </div>
      <input type="file" name="gallery_upload[]" accept="image/*" multiple>
    </fieldset>

    <button type="submit" class="contrast">Lưu dự án</button>
  </form>
</article>
<script>
  document.addEventListener('click',function(e){
    if(e.target.classList.contains('remove-btn')){
      const thumbs=document.querySelectorAll('.thumb');
      if(thumbs.length<=1){
        alert('Phải giữ lại ít nhất 1 ảnh thư viện');
        return;
      }
      const thumb=e.target.closest('.thumb');
      if(thumb) thumb.remove();
    }
  });
</script>
</body></html> 