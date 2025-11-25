<?php
require 'config.php'; 

// --- KONFIGURACJA PRODUKTÓW ---
$produkty_lista = [
    "Płyn do spryskiwaczy zimowy 5L", 
    "Olej silnikowy 5W40", 
    "Hot-dog parówka", 
    "Kawa ziarnista 1kg", 
    "Rękawice robocze",
    "Papier toaletowy (zgrzewka)", 
    "Worki na śmieci 120L", 
    "Płyn do podłóg", 
    "Baterie AA (paczka)", 
    "Żarówki H7"
];

// Wczytywanie stacji z pliku CSV
$stacje = [];
if (file_exists("stacje.csv")) {
    if (($handle = fopen("stacje.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $nazwa = isset($data[0]) ? $data[0] : "Stacja bez nazwy";
            $adres = isset($data[1]) ? $data[1] : "";
            $stacje[] = $nazwa . " (" . $adres . ")";
        }
        fclose($handle);
    }
} else {
    $stacje[] = "Błąd: Brak pliku stacje.csv";
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zamówienie Towaru</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        select, input[type="text"] { width: 100%; padding: 12px; font-size: 16px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        
        .product-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #eee; }
        .product-item:last-child { border-bottom: none; }
        .product-name { flex-grow: 1; margin-right: 10px; }
        .product-qty { width: 80px !important; text-align: center; padding: 8px !important; }

        button { background-color: #28a745; color: white; padding: 15px; border: none; font-size: 18px; cursor: pointer; width: 100%; border-radius: 4px; margin-top: 20px; }
        button:hover { background-color: #218838; }
        
        .footer-link { text-align: center; margin-top: 30px; }
        .footer-link a { color: #999; text-decoration: none; font-size: 12px; }
    </style>
</head>
<body>

<div class="container">
    <h1>Zamówienie do Centrali</h1>

    <form action="zapisz.php" method="POST">
        
        <div class="form-group">
            <label for="pracownik">Imię i Nazwisko pracownika:</label>
            <input type="text" id="pracownik" name="pracownik" required placeholder="np. Jan Kowalski">
        </div>

        <div class="form-group">
            <label for="stacja">Wybierz swoją stację:</label>
            <select id="stacja" name="stacja" required>
                <option value="">-- Wybierz z listy --</option>
                <?php foreach ($stacje as $stacja): ?>
                    <option value="<?php echo htmlspecialchars($stacja); ?>">
                        <?php echo htmlspecialchars($stacja); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Wpisz ilość potrzebnych produktów:</label>
            <div style="background: #fff; border: 1px solid #ddd; border-radius: 4px;">
                <?php foreach ($produkty_lista as $produkt): ?>
                    <div class="product-item">
                        <span class="product-name"><?php echo htmlspecialchars($produkt); ?></span>
                        <input type="number" class="product-qty" name="ilosc[<?php echo htmlspecialchars($produkt); ?>]" placeholder="0" min="0">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit">WYŚLIJ ZAMÓWIENIE</button>
    </form>

    <div class="footer-link">
        <a href="admin.php">Panel Szefa (Logowanie)</a>
    </div>
</div>

</body>
</html>
