<?php
require 'config.php';
session_start();

// --- KONFIGURACJA ---
$haslo_dostepu = "szef123"; // ZMIEŃ TO HASŁO!

// --- LOGOWANIE ---
if (isset($_POST['login'])) {
    if ($_POST['pass'] === $haslo_dostepu) {
        $_SESSION['admin_logged'] = true;
    } else {
        $error = "Błędne hasło!";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Jeśli nie zalogowany, pokaż formularz logowania
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Szefa - Logowanie</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f0f2f5; margin: 0; }
        form { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center; }
        input { padding: 10px; margin-bottom: 10px; width: 200px; display: block; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background: #0056b3; }
        .error { color: red; margin-bottom: 10px; }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Panel Szefa</h2>
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        <input type="password" name="pass" placeholder="Podaj hasło" required autofocus>
        <button type="submit" name="login">Zaloguj</button>
    </form>
</body>
</html>
<?php
    exit;
}

// --- ZMIANA STATUSU (Realizacja) ---
if (isset($_GET['zrealizuj'])) {
    $id = (int)$_GET['zrealizuj'];
    $stmt = $pdo->prepare("UPDATE zamowienia SET status = 'zrealizowane' WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit;
}

// --- POBIERANIE DANYCH ---
// Pobieramy 100 ostatnich zamówień, najnowsze na górze
$stmt = $pdo->query("SELECT * FROM zamowienia ORDER BY id DESC LIMIT 100");
$zamowienia = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Szefa - Lista Zamówień</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f9f9f9; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-logout { background: #dc3545; color: white; text-decoration: none; padding: 8px 15px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #343a40; color: white; }
        tr:hover { background-color: #f1f1f1; }
        .status-nowe { background-color: #e8f5e9; font-weight: bold; color: #2e7d32; }
        .status-zrealizowane { color: #888; text-decoration: line-through; }
        .btn-ok { background: #28a745; color: white; text-decoration: none; padding: 5px 10px; border-radius: 3px; font-size: 12px; }
        
        /* RWD dla tabeli na telefonach */
        @media (max-width: 600px) {
            table, thead, tbody, th, td, tr { display: block; }
            thead tr { position: absolute; top: -9999px; left: -9999px; }
            tr { margin-bottom: 15px; border: 1px solid #ccc; background: white; }
            td { border: none; border-bottom: 1px solid #eee; position: relative; padding-left: 50%; }
            td:before { position: absolute; top: 12px; left: 6px; width: 45%; padding-right: 10px; white-space: nowrap; font-weight: bold; content: attr(data-label); }
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Lista Zamówień (<?php echo count($zamowienia); ?>)</h1>
    <a href="admin.php?logout=1" class="btn-logout">Wyloguj</a>
</div>

<?php if (count($zamowienia) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Data</th>
                <th>Pracownik</th>
                <th>Stacja</th>
                <th>Produkty</th>
                <th>Akcja</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($zamowienia as $z): ?>
                <tr class="<?php echo $z['status'] == 'nowe' ? 'status-nowe' : 'status-zrealizowane'; ?>">
                    <td data-label="ID"><?php echo $z['id']; ?></td>
                    <td data-label="Data"><?php echo $z['data_zamowienia']; ?></td>
                    <td data-label="Pracownik"><?php echo htmlspecialchars($z['imie_nazwisko']); ?></td>
                    <td data-label="Stacja"><?php echo htmlspecialchars($z['stacja']); ?></td>
                    <td data-label="Produkty"><?php echo htmlspecialchars($z['produkty']); ?></td>
                    <td data-label="Akcja">
                        <?php if ($z['status'] == 'nowe'): ?>
                            <a href="admin.php?zrealizuj=<?php echo $z['id']; ?>" class="btn-ok">✓ Oznacz jako gotowe</a>
                        <?php else: ?>
                            Zrobione
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Brak zamówień w bazie.</p>
<?php endif; ?>

</body>
</html>
