<?php
require 'config.php';
session_start();

// --- KONFIGURACJA ---
$haslo_dostepu = "szef123"; 

// --- LOGIKA GENEROWANIA ZBIORCZEGO XML ---
if (isset($_POST['export_xml']) && isset($_POST['ids']) && is_array($_POST['ids'])) {
    if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
        
        $ids = $_POST['ids'];
        // Tworzymy string ze znakami zapytania np. (?,?,?) dla SQL
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $stmt = $pdo->prepare("SELECT * FROM zamowienia WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $wyniki = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($wyniki) {
            header('Content-Type: text/xml');
            header('Content-Disposition: attachment; filename="zamowienia_export_' . date('Y-m-d_H-i') . '.xml"');
            
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            echo "<lista_zamowien>\n"; // Główny element spinający wszystkie zamówienia
            
            foreach ($wyniki as $z) {
                echo "  <zamowienie>\n";
                echo "    <id>{$z['id']}</id>\n";
                echo "    <data>{$z['data_zamowienia']}</data>\n";
                echo "    <pracownik>" . htmlspecialchars($z['imie_nazwisko']) . "</pracownik>\n";
                echo "    <stacja>" . htmlspecialchars($z['stacja']) . "</stacja>\n";
                echo "    <produkty>" . htmlspecialchars($z['produkty']) . "</produkty>\n";
                echo "    <status>{$z['status']}</status>\n";
                echo "  </zamowienie>\n";
            }
            
            echo "</lista_zamowien>";
            exit;
        }
    }
}

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
        .back-link { margin-top: 15px; display: block; color: #666; font-size: 14px; text-decoration: none; }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Panel Szefa</h2>
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        <input type="password" name="pass" placeholder="Podaj hasło" required autofocus>
        <button type="submit" name="login">Zaloguj</button>
        <a href="index.php" class="back-link">← Wróć do strony głównej</a>
    </form>
</body>
</html>
<?php
    exit;
}

// --- ZMIANA STATUSU ---
if (isset($_GET['zrealizuj'])) {
    $id = (int)$_GET['zrealizuj'];
    $stmt = $pdo->prepare("UPDATE zamowienia SET status = 'zrealizowane' WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit;
}
if (isset($_GET['przywroc'])) {
    $id = (int)$_GET['przywroc'];
    $stmt = $pdo->prepare("UPDATE zamowienia SET status = 'nowe' WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit;
}

// --- POBIERANIE DANYCH ---
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
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-logout { background: #dc3545; color: white; text-decoration: none; padding: 8px 15px; border-radius: 4px; margin-left: 10px; }
        .btn-home { background: #6c757d; color: white; text-decoration: none; padding: 8px 15px; border-radius: 4px; }
        
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; vertical-align: middle; }
        th { background-color: #343a40; color: white; }
        tr:hover { background-color: #f1f1f1; }
        
        .status-nowe { background-color: #e8f5e9; font-weight: bold; color: #2e7d32; }
        .status-zrealizowane { color: #888; background-color: #f9f9f9; }
        
        .btn-ok { background: #28a745; color: white; text-decoration: none; padding: 5px 10px; border-radius: 3px; font-size: 12px; display: inline-block; margin-right: 5px; }
        .btn-undo { background: #ffc107; color: #333; text-decoration: none; padding: 5px 10px; border-radius: 3px; font-size: 12px; display: inline-block; margin-right: 5px; }
        
        /* Styl przycisku grupowego */
        .btn-bulk-xml { background: #17a2b8; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-bulk-xml:hover { background: #138496; }
        
        .toolbar { background: #fff; padding: 15px; margin-bottom: 15px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 10px; }
    </style>
</head>
<body>

<div class="top-bar">
    <div>
        <a href="index.php" class="btn-home">← Widok Pracownika</a>
    </div>
    <div>
        <span style="margin-right: 10px;">Zalogowany jako Szef</span>
        <a href="admin.php?logout=1" class="btn-logout">Wyloguj</a>
    </div>
</div>

<h1>Lista Zamówień</h1>

<form method="POST">
    
    <div class="toolbar">
        <strong>Zaznaczone:</strong>
        <button type="submit" name="export_xml" class="btn-bulk-xml">⬇ Pobierz XML (wybrane)</button>
    </div>

    <?php if (count($zamowienia) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">V</th>
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
                        <td style="text-align: center;">
                            <input type="checkbox" name="ids[]" value="<?php echo $z['id']; ?>">
                        </td>
                        <td><?php echo $z['id']; ?></td>
                        <td><?php echo $z['data_zamowienia']; ?></td>
                        <td><?php echo htmlspecialchars($z['imie_nazwisko']); ?></td>
                        <td><?php echo htmlspecialchars($z['stacja']); ?></td>
                        <td><?php echo htmlspecialchars($z['produkty']); ?></td>
                        <td>
                            <?php if ($z['status'] == 'nowe'): ?>
                                <a href="admin.php?zrealizuj=<?php echo $z['id']; ?>" class="btn-ok">✓ Gotowe</a>
                            <?php else: ?>
                                <a href="admin.php?przywroc=<?php echo $z['id']; ?>" class="btn-undo">↩ Cofnij</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Brak zamówień w bazie.</p>
    <?php endif; ?>

</form>

</body>
</html>
