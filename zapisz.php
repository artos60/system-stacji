<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Pobieramy dane z formularza
    $pracownik = isset($_POST['pracownik']) ? trim($_POST['pracownik']) : '';
    $stacja = isset($_POST['stacja']) ? trim($_POST['stacja']) : '';
    
    // Obsługa produktów (łączy zaznaczone w jeden tekst po przecinku)
    if (isset($_POST['produkty']) && is_array($_POST['produkty'])) {
        $wybrane_produkty = implode(", ", $_POST['produkty']);
    } else {
        $wybrane_produkty = "Brak (nie zaznaczono nic)";
    }

    // Walidacja - czy pola nie są puste
    if (!empty($pracownik) && !empty($stacja)) {
        try {
            // Zapytanie SQL wstawiające dane
            $stmt = $pdo->prepare("INSERT INTO zamowienia (imie_nazwisko, stacja, produkty) VALUES (?, ?, ?)");
            $stmt->execute([$pracownik, $stacja, $wybrane_produkty]);
            
            // Sukces - wyświetlamy komunikat
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
                    <p>Twoje zamówienie zostało wysłane do centrali.</p>
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
    // Jeśli ktoś otworzy ten plik bezpośrednio, a nie przez formularz
    header("Location: index.php");
}
?>
