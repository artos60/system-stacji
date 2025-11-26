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
            $nazwa = isset($data[0]) ? $data[0] : "Stacja";
            $adres = isset($data[1]) ? $data[1] : "";
            // Formatowanie: MIASTO (Ulica)
            $stacje[] = $nazwa . " (" . $adres . ")";
        }
        fclose($handle);
    }
} else {
    $stacje[] = "Brak pliku stacje.csv";
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zamówienie Towaru - System Wewnętrzny</title>
    <style>
        /* --- STYLISTYKA WATIS / TEZ --- */
        :root {
            --watis-red: #d71920; /* Czerwień firmowa */
            --bg-color: #f4f6f8;
            --text-color: #333;
        }

        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 0; 
            background-color: var(--bg-color); 
            color: var(--text-color);
        }

        /* --- NAGŁÓWEK --- */
        .header-bar {
            background-color: #ffffff;
            padding: 10px 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between; /* Rozrzuca loga na lewo i prawo */
            align-items: center;
            border-bottom: 4px solid var(--watis-red);
            height: 80px; /* Zwiększyłem lekko wysokość dla większych logotypów */
        }

        .logo-img {
            height: 60px; /* Wysokość logotypów */
            width: auto;
            object-fit: contain;
        }

        /* --- KONTENER GŁÓWNY --- */
        .container { 
            max-width: 600px; 
            margin: 30px auto; 
            padding: 0 15px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border-top: 5px solid var(--watis-red);
        }

        h1 { 
            text-align: center; 
            color: #333; 
            font-size: 24px; 
            margin-bottom: 30px; 
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* --- ELEMENTY FORMULARZA --- */
        .form-group { margin-bottom: 25px; }
        
        label { 
            display: block; 
            margin-bottom: 10px; 
            font-weight: 600; 
            color: #444; 
            font-size: 14px;
            text-transform: uppercase;
        }
        
        select, input[type="text"] { 
            width: 100%; 
            padding: 14px; 
            font-size: 16px; 
            border: 2px solid #e1e4e8; 
            border-radius: 6px; 
            box-sizing: border-box; 
            background-color: #fbfbfb;
            transition: all 0.3s;
        }
        
        select:focus, input:focus {
            border-color: var(--watis-red);
            background-color: #fff;
            outline: none;
        }
        
        /* --- LISTA PRODUKTÓW --- */
        .products-wrapper {
            background: #fff;
            border: 2px solid #e1e4e8;
            border-radius: 6px;
            overflow: hidden;
        }

        .product-item { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 12px 15px; 
            border-bottom: 1px solid #eee; 
            transition: background 0.2s;
        }
        
        .product-item:last-child { border-bottom: none; }
        .product-item:hover { background-color: #fafafa; }
        
        .product-name { 
            font-size: 15px; 
            font-weight: 500;
        }
        
        .product-qty { 
            width: 70px !important; 
            text-align: center; 
            padding: 8px !important; 
            border: 1px solid #ccc !important;
            font-weight: bold;
            color: var(--watis-red);
        }

        /* --- PRZYCISK --- */
        button { 
            background-color: var(--watis-red);
            color: white; 
            padding: 18px; 
            border: none; 
            font-size: 18px; 
            font-weight: 800;
            cursor: pointer; 
            width: 100%; 
            border-radius: 6px; 
            margin-top: 15px; 
            text-transform: uppercase;
            box-shadow: 0 4px 15px rgba(215, 25, 32, 0.3);
            transition: transform 0.2s, background 0.3s;
        }
        
        button:hover { 
            background-color: #b01217; 
            transform: translateY(-2px);
        }
        
        .footer-link { text-align: center; margin-top: 30px; margin-bottom: 50px;}
        .footer-link a { color: #888; text-decoration: none; font-size: 13px; font-weight: 500; }
        .footer-link a:hover { color: var(--watis-red); }

        /* RWD */
        @media (max-width: 480px) {
            .header-bar { padding: 10px; height: 70px; }
            .logo-img { height: 45px; } /* Mniejsze logo na telefonie */
            .card { padding: 20px; }
        }
    </style>
</head>
<body>

    <div class="header-bar">
        <img src="img_logo_witas.jpg" alt="Logo WATIS" class="logo-img">
        
        <img src="img_logo_tec2000.jpg" alt="Logo TEC 2000" class="logo-img">
    </div>

    <div class="container">
        <div class="card">
            <h1>Formularz Zamówienia</h1>

            <form action="zapisz.php" method="POST">
                
                <div class="form-group">
                    <label for="pracownik">Imię i Nazwisko:</label>
                    <input type="text" id="pracownik" name="pracownik" required placeholder="Wpisz imię i nazwisko...">
                </div>

                <div class="form-group">
                    <label for="stacja">Wybierz Stację:</label>
                    <select id="stacja" name="stacja" required>
                        <option value="">-- Kliknij, aby wybrać --</option>
                        <?php foreach ($stacje as $stacja): ?>
                            <option value="<?php echo htmlspecialchars($stacja); ?>">
                                <?php echo htmlspecialchars($stacja); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Lista produktów (wpisz ilość):</label>
                    <div class="products-wrapper">
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
        </div>

        <div class="footer-link">
            <a href="admin.php">Panel Szefa (Logowanie)</a>
        </div>
    </div>

</body>
</html>
