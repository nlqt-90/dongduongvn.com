<?php
require_once __DIR__ . "/config.php";
if (empty($_SESSION['logged_in'])) {header("Location: login.php");exit;}
$files=glob(PROJECT_DIR."*.md");
function fm($c){if(preg_match('/^---\s*(.*?)\s*---/s',$c,$m)){foreach(explode("\n",trim($m[1])) as $l){if(str_contains($l,':')){[$k,$v]=explode(':',$l,2);$d[trim($k)]=trim($v);} }return$d??[];}return[];}
function dmy($iso){$iso=trim($iso," \"'");if(!$iso)return '';$d=DateTime::createFromFormat('Y-m-d',$iso);return $d?$d->format('m/Y'):'';}
?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Quản lý Dự án</title><link rel="stylesheet" href="https://unpkg.com/@picocss/pico@1.*/css/pico.min.css"><style>
main{max-width:1000px;margin:56px auto;padding:0 1rem;}
h1{margin:0 0 1rem;font-weight:700;}
.toolbar{display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:.5rem;}
thead th{background:var(--contrast);color:white;padding:1rem;white-space:nowrap;}
th,td{padding:.9rem 1rem;vertical-align:middle;}
.thumb{text-align:center;} .thumb img{max-width:120px;border-radius:6px;}
.actions{text-align:center;white-space:nowrap;} .actions a{text-decoration:none;} .actions a:hover{text-decoration:underline;}
</style></head><body>
<main>
  <h1>Danh sách Dự án</h1>
  <div class="toolbar"><a href="index.php" role="button" class="contrast">🔙 Bảng điều khiển</a><a href="project_edit.php" role="button" class="contrast">➕ Thêm dự án mới</a></div>
  <div class="table-wrap">
    <table class="striped">
      <thead><tr>
        <th>Tiêu đề</th>
        <th>Vị trí</th>
        <th style="width:140px;text-align:center">Thời gian triển khai</th>
        <th style="width:160px;text-align:center">Thumbnail</th>
        <th style="width:140px;text-align:center">Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach($files as $f):$p=fm(file_get_contents($f));$name=basename($f); ?>
        <tr>
          <td><?=htmlspecialchars(trim($p['title']??'(không có)',' "\''))?></td>
          <td><?=htmlspecialchars(trim($p['location']??'',' "\''));?></td>
          <td style="text-align:center;white-space:nowrap;"><?=dmy($p['startDate']??'');?></td>
          <td class="thumb"><?php if(!empty($p['thumbnail'])):?><img src="<?=htmlspecialchars($p['thumbnail'])?>" loading="lazy"><?php endif;?></td>
          <td class="actions"><a href="project_edit.php?file=<?=urlencode($name)?>">Sửa</a> | <a href="project_delete.php?file=<?=urlencode($name)?>" onclick="return confirm('⚠️ Bạn chắc chắn muốn xoá DỰ ÁN này cùng mọi hình ảnh?')">Xóa</a></td>
        </tr>
      <?php endforeach;?>
      </tbody>
    </table>
  </div>
</main>
</body></html>