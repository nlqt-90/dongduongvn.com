<?php
require_once __DIR__ . '/config.php';
if (empty($_SESSION['logged_in'])) {
  header('Location: login.php');
  exit;
}
$files = glob(POPUP_DIR . '*.md');

function fm($c)
{
  if (preg_match('/^---\s*(.*?)\s*---/s', $c, $m)) {
    foreach (explode("\n", trim($m[1])) as $l) {
      if (str_contains($l, ':')) {
        [$k, $v] = explode(':', $l, 2);
        $d[trim($k)] = trim($v);
      }
    }
    return $d ?? [];
  }
  return [];
}

function dmy($iso)
{
  $dt = DateTime::createFromFormat('Y-m-d', $iso);
  return $dt ? $dt->format('d/m/Y') : '';
}
?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Quản lý Popups</title><link rel="stylesheet" href="https://unpkg.com/@picocss/pico@1.*/css/pico.min.css"><style>
main{max-width:1000px;margin:56px auto;padding:0 1rem;}
.toolbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;}
thead th{background:var(--contrast);color:white;padding:1rem;
th,td{padding:.9rem 1rem;vertical-align:middle;}
.image{text-align:center;} .image img{max-width:120px;border-radius:6px;}
.status{text-align:center;font-size:.9rem;}
.actions{text-align:center;white-space:nowrap;} .actions a{text-decoration:none;} .actions a:hover{text-decoration:underline;}
</style></head><body>
<main>
<h1>Danh sách thông báo</h1>
<div class="toolbar">
<a href="index.php" role="button" class="contrast">🔙 Bảng điều khiển</a>
<a href="popup_edit.php" role="button" class="contrast">➕ Thêm thông báo mới</a>
</div>
<div class="table-wrap"> 
  <table>
  <thead>
    <tr>
  <th>Tiêu đề thông báo</th>
  <th style="width:140px;text-align:center">Trạng thái</th>
  <th style="width:200px;text-align:center">Thời gian</th>
  <th style="width:160px;text-align:center">Hình</th>
  <th style="width:140px;text-align:center">Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($files as $f):
  $p = fm(file_get_contents($f));
  $name = basename($f);
  $status = ($p['active'] ?? 'false') === 'true' ? '<span style="color:var(--pico-success)">✅ On</span>' : '<span style="color:var(--pico-muted-color)">❌ Off</span>';
  $time = dmy($p['startDate'] ?? '') . ' → ' . dmy($p['endDate'] ?? ''); ?>
<tr>
  <td><?= htmlspecialchars($p['title'] ?? '(không có)') ?></td>
  <td class="status"><?= $status ?></td>
  <td style="text-align:center;white-space:nowrap;"><?= $time ?></td>
  <td class="image"><?php if (!empty($p['image'])): ?><img src="<?= htmlspecialchars($p['image']) ?>" loading="lazy"><?php endif; ?></td>
  <td class="actions"><a href="popup_edit.php?file=<?= urlencode($name) ?>">Sửa</a> | <a href="popup_delete.php?file=<?= urlencode($name) ?>" onclick="return confirm('Xóa popup này?')">Xóa</a></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
</main>
</body></html>