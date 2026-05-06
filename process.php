<?php
// experiment09/form.php
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Experiment 09 — PHP Form (save to SQLite)</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;padding:20px;max-width:900px;margin:auto}
    form{background:#f9f9f9;padding:16px;border-radius:8px;border:1px solid #e6e6e6}
    .field{display:flex;flex-direction:column;margin-bottom:12px}
    label{font-weight:600;margin-bottom:6px}
    input,select,textarea,button{padding:8px;border-radius:4px;border:1px solid #ccc}
    .msg{padding:10px;margin-bottom:12px;border-radius:6px}
    .ok{background:#e6ffed;border:1px solid #bdeac4}
    .err{background:#ffebeb;border:1px solid #f1b2b2}
  </style>
</head>
<body>
  <h1>Experiment 09 — PHP Form (SQLite)</h1>

  <?php if(!empty($_GET['ok'])): ?>
    <div class="msg ok">Submission saved.</div>
  <?php elseif(!empty($_GET['err'])): ?>
    <div class="msg err">Error: <?= htmlspecialchars($_GET['err']) ?></div>
  <?php endif; ?>

  <form method="post" action="process.php" novalidate>
    <div class="field">
      <label for="name">Name</label>
      <input id="name" name="name" type="text" required>
    </div>

    <div class="field">
      <label for="email">Email</label>
      <input id="email" name="email" type="email">
    </div>

    <div class="field">
      <label for="age">Age</label>
      <input id="age" name="age" type="number" min="0">
    </div>

    <div class="field">
      <label for="country">Country</label>
      <select id="country" name="country">
        <option value="">Select</option>
        <option>India</option>
        <option>United States</option>
        <option>United Kingdom</option>
        <option>Australia</option>
      </select>
    </div>

    <div class="field">
      <label for="comments">Comments</label>
      <textarea id="comments" name="comments" rows="4"></textarea>
    </div>

    <div style="display:flex;gap:8px">
      <button type="submit">Submit</button>
      <button type="reset">Reset</button>
      <a href="list.php" style="margin-left:12px;align-self:center">View saved entries</a>
    </div>
  </form>
</body>
</html>
