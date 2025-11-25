<?php
// Konfiguracja bazy danych SQLite
// Baza będzie trzymana w pliku 'baza.db' w tym samym folderze
$db_file = 'baza.db';

try {
    // Łączenie z bazą
    $pdo = new PDO("sqlite:" . $db_file);
    // Ustawienie trybu raportowania błędów
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Błąd połączenia z bazą: " . $e->getMessage());
}
?>
