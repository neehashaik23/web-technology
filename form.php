<?php
// experiment09/list.php
// Shows saved submissions and supports CSV download

try{
  $dbPath = __DIR__ . '/database.sqlite';
  $pdo = new PDO('sqlite:'.$dbPath);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $stmt = $pdo->query('SELECT id,name,email,age,country,comments,created_at FROM submissions ORDER BY id DESC');
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e){
  $rows = [];
  $error = $e->getMessage();
}

if (!empty($_GET['download']) && strtolower($_GET['download'])==='csv'){
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=submissions.csv');
  $out = fopen('php://output','w');
  fputcsv($out, ['ID','Name','Email','Age','Country','Comments','Created At']);
  foreach($rows as $r) fputcsv($out, [$r['id'],$r['name'],$r['email'],$r['age'],$r['country'],$r['comments'],$r['created_at']]);
  exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Saved Submissions</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;padding:20px}
    table{border-collapse:collapse;width:100%}
    th,td{border:1px solid #ddd;padding:8px;text-align:left}
    th{background:#f2f4f7}
    .small{font-size:0.9rem;padding:6px 8px}
  </style>
</head>
<body>
  <h1>Saved submissions</h1>
  <p><a href="form.php">Back to form</a> — <a href="?download=csv">Download CSV</a></p>

  <?php if(!empty($error)): ?>
    <div style="color:#a00">Error: <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <table>
    <thead>
      <tr><th>#</th><th>Name</th><th>Email</th><th>Age</th><th>Country</th><th>Comments</th><th>Created</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['id']) ?></td>
          <td><?= htmlspecialchars($r['name']) ?></td>
          <td><?= htmlspecialchars($r['email']) ?></td>
          <td><?= htmlspecialchars($r['age']) ?></td>
          <td><?= htmlspecialchars($r['country']) ?></td>
          <td><?= htmlspecialchars($r['comments']) ?></td>
          <td><?= htmlspecialchars($r['created_at']) ?></td>
          <td><button class="small" data-id="<?= $r['id'] ?>">Copy</button></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <script>
    document.querySelectorAll('button[data-id]').forEach(btn => {
      btn.addEventListener('click', ()=>{
        const tr = btn.closest('tr');
        const cells = Array.from(tr.querySelectorAll('td')).slice(1,7).map(td=>td.textContent.trim());
        const text = cells.join('\t');
        navigator.clipboard.writeText(text).then(()=> alert('Row copied to clipboard')).catch(()=> alert('Copy failed'));
      });
    });
  </script>
</body>
</html>
