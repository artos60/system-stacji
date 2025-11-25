<?php
require 'config.php';
session_start();

// --- KONFIGURACJA ---
$haslo_dostepu = "szef123"; 

// --- LOGIKA GENEROWANIA PLIKU CSV (Excel) ---
if (isset($_POST['export_csv']) && isset($_POST['ids']) && is_array($_POST['ids'])) {
    if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
        
        $ids = $_POST['ids'];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $stmt = $pdo->prepare("SELECT * FROM zamowienia WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $wyniki = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($wyniki) {
            // Ustawienia nagłówków dla pliku CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="zamowienia_export_' . date('Y-m-d_H-i') . '.csv"');
            
            // Otwieramy strumień wyjścia
            $output = fopen('php://output', 'w');
            
            // Dodajemy BOM dla Excela (żeby polskie znaki działały poprawnie)
            fputs($output, "\xEF\xBB\xBF");
            
            // Nagłówki kolumn w Excelu
            fputcsv($output, ['ID Zamówienia', 'Data', 'Pracownik', 'Stacja', 'Produkt', 'Ilość'], ';');
            
            foreach ($wyniki as $z) {
                // Musimy "rozpakować" string z produktami
                // Format w bazie: "Olej (2 szt), Kawa (5 szt)"
                $produkty_array = explode(", ", $z['produkty']);
                
                foreach ($produkty_array as $item) {
                    // Próbujemy wyciągnąć nazwę i ilość osobno używając wyrażenia regularnego
                    // Szukamy wzorca: "Nazwa Produktu (Liczba szt)"
                    if (preg_match('/^(.*) \((\d+) szt\)$/', $item, $matches)) {
                        $nazwa_produktu = $matches[1];
                        $ilosc = $matches[2];
                    } else {
                        // Jeśli format jest inny (np. puste zamówienie lub stary format)
                        $nazwa_produktu = $item;
                        $ilosc = "-";
                    }
                    
                    // Zapisujemy wiersz do CSV
                    // Każdy produkt z zamówienia to osobny wiersz w Excelu!
                    fputcsv($output, [
                        $z['id'], 
                        $z['data_zamowienia'], 
                        $z['imie_nazwisko'], 
                        $z['stacja'], 
                        $nazwa_produktu, 
                        $ilosc
                    ], ';');
                }
            }
            
            fclose($output);
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
    // ... (Tu kod formularza logowania - skrócony dla czytelności, jest taki sam jak wcześniej) ...
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
        .error { color: red; margin-bottom: 10px; }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Panel Szefa</h2>
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        <input type="password" name="pass" placeholder="Podaj hasło" required autofocus>
        <button type="submit" name="login">Zaloguj</button>
        <a href="index.php" style="display:block; margin-top:10px; color:#666; text-decoration:none;">Wróć</a>
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
        
        .btn-bulk-csv { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: bold; }
        .btn-bulk-csv:hover { background: #218838; }
        
        .toolbar { background: #fff; padding: 15px; margin-bottom: 15px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 10px; }

        /* --- STYL DO DUŻYCH CHECKBOXÓW --- */
        input[type="checkbox"] {
            transform: scale(2); /* Powiększenie 2x */
            margin: 10px;
            cursor: pointer;
        }

        /* --- STYL DO LISTY PRODUKTÓW W TABELI --- */
        .product-list-item {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed #ccc;
            padding: 3px 0;
        }
        .product-list-item:last-child { border-bottom: none; }
        .p-name { font-weight: 500; }
        .p-qty { font-weight: bold; background: #eee; padding: 0 6px; border-radius: 3px; }

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
        <button type="submit" name="export_csv" class="btn-bulk-csv">⬇ Pobierz CSV (Excel)</button>
    </div>

    <?php if (count($zamowienia) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">V</th>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Pracownik / Stacja</th>
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
                        <td>
                            <strong><?php echo htmlspecialchars($z['imie_nazwisko']); ?></strong><br>
                            <small><?php echo htmlspecialchars($z['stacja']); ?></small>
                        </td>
                        <td>
                            <?php 
                                $produkty_raw = explode(", ", $z['produkty']);
                                foreach($produkty_raw as $item) {
                                    // Rozbijamy nazwę od ilości
                                    if (preg_match('/^(.*) \((\d+) szt\)$/', $item, $matches)) {
                                        echo "<div class='product-list-item'>";
                                        echo "<span class='p-name'>" . htmlspecialchars($matches[1]) . "</span>";
                                        echo "<span class='p-qty'>" . htmlspecialchars($matches[2]) . " szt</span>";
                                        echo "</div>";
                                    } else {
                                        // Fallback dla starych danych
                                        echo "<div>" . htmlspecialchars($item) . "</div>";
                                    }
                                }
                            ?>
                        </td>
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
