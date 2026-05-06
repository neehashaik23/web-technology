<?php
// experiment09/process.php
// Processes POSTed form and saves to SQLite database (database.sqlite in same folder)

function redirect_with($params=[]){
    $qs = http_build_query($params);
    header('Location: form.php'.($qs?"?$qs":""));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_with(['err'=>'invalid_method']);

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$age = $_POST['age'] ?? null;
$country = trim($_POST['country'] ?? '');
$comments = trim($_POST['comments'] ?? '');

if ($name === '') redirect_with(['err'=>'name_required']);

try {
    $dbPath = __DIR__ . '/database.sqlite';
    $pdo = new PDO('sqlite:'.$dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // create table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS submissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT,
        age INTEGER,
        country TEXT,
        comments TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = $pdo->prepare('INSERT INTO submissions (name,email,age,country,comments) VALUES (?,?,?,?,?)');
    $stmt->execute([$name,$email,($age === ''?null:(int)$age),$country,$comments]);

    redirect_with(['ok'=>1]);

} catch (Exception $e){
    redirect_with(['err'=>substr($e->getMessage(),0,200)]);
}
