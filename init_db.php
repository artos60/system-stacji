<?php
require 'config.php';

try {
    // Tworzenie tabeli zamówień, jeśli jeszcze nie istnieje
    $sql = "CREATE TABLE IF NOT EXISTS zamowienia (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        data_zamowienia DATETIME DEFAULT CURRENT_TIMESTAMP,
        imie_nazwisko TEXT NOT NULL,
        stacja TEXT NOT NULL,
        produkty TEXT NOT NULL,
        status TEXT DEFAULT 'nowe'
    )";
    
    $pdo->exec($sql);
    echo "<h1>Sukces!</h1>";
    echo "<p>Baza danych została zainicjowana pomyślnie. Utworzono plik baza.db.</p>";
    echo "<p>Możesz teraz usunąć ten plik (init_db.php) z serwera dla bezpieczeństwa.</p>";
    
} catch (PDOException $e) {
    echo "Błąd przy tworzeniu tabeli: " . $e->getMessage();
}
?>
