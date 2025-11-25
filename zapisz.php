<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $pracownik = isset($_POST['pracownik']) ? trim($_POST['pracownik']) : '';
    $stacja = isset($_POST['stacja']) ? trim($_POST['stacja']) : '';
    
    // --- NOWA LOGIKA DLA ILOŚCI ---
    $lista_zamowien = [];
    
    if (isset($_POST['ilosc']) && is_array($_POST['ilosc'])) {
        foreach ($_POST['ilosc'] as $nazwa_produktu => $ilosc) {
            // Zamieniamy na liczbę całkowitą
            $ilosc = (int)$ilosc;
            // Jeśli ktoś wpisał więcej niż 0, dodajemy do listy
            if ($ilosc > 0) {
                // Usuwamy ewentualne znaki specjalne z nazwy produktu dla bezpieczeństwa
                $nazwa_czysta = htmlspecialchars($nazwa_produktu);
                $lista_zamowien[] = "{$nazwa_czysta} ({$ilosc} szt)";
            }
        }
    }

    // Zamieniamy tablicę na jeden długi tekst po przecinku
    if (count($lista_zamowien) > 0) {
        $wybrane_produkty = implode(", ", $lista_zamowien);
    } else {
        $wybrane_produkty = "PUSTE ZAMÓWIENIE (wpisano same zera)";
    }
    // -----------------------------

    if (!empty($pracownik) && !empty($stacja)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO zamowienia (imie_nazwisko, stacja, produkty) VALUES (?, ?, ?)");
            $stmt->execute([$pracownik, $stacja, $wybrane_produkty]);
            
            ?>
            <!DOCTYPE html>
            <html lang="pl">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Wysłano</title>
                <style>
                    body { font-family: sans-serif; text-align: center; padding: 50px; background-color: #e8f5e9; }
                    .box { background: white; padding: 30px; border-radius: 10px; display: inline-block; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                    h1 { color: #28a745; }
                    a { display: inline-block; margin-top: 20px; text-decoration: none; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; }
                </style>
            </head>
            <body>
                <div class="box">
                    <h1>✅ Sukces!</h1>
                    <p>Twoje zamówienie zostało wysłane.</p>
                    <a href="index.php">Wróć do strony głównej</a>
                </div>
            </body>
            </html>
            <?php
            
        } catch (PDOException $e) {
            echo "Wystąpił błąd podczas zapisu: " . $e->getMessage();
        }
    } else {
        echo "Błąd: Nie podano imienia lub stacji. <a href='index.php'>Wróć</a>";
    }
} else {
    header("Location: index.php");
}
?>
