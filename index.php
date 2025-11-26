<?php
require 'config.php'; 

// --- KONFIGURACJA PRODUKTÓW ---
$produkty_lista = [
    [
        "nazwa" => "TEC 2000 Engine Flush", 
        "opis" => "Płukanka silnika"
    ],
    [
        "nazwa" => "TEC 2000 Diesel System Cleaner", 
        "opis" => "Dodatek do paliwa - Diesel (czyści wtryskiwacze i usuwa wodę)"
    ],
    [
        "nazwa" => "TEC 2000 Fuel System Cleaner", 
        "opis" => "Dodatek do paliwa - Benzyna (czyści wtryskiwacze i usuwa wodę)"
    ],
    [
        "nazwa" => "TEC 2000 Oil Booster", 
        "opis" => "Dodatek do oleju silnikowego"
    ],
    [
        "nazwa" => "TEC 2000 Induction Cleaner", 
        "opis" => "Czyszczenie dolotu powietrza - spray"
    ],
    [
        "nazwa" => "TEC 2000 Airco Freshener", 
        "opis" => "Granat antybakteryjny zapachowy - rozpylacz"
    ],
    [
        "nazwa" => "TEC 2000 Diesel Injector Cleaner", 
        "opis" => "Odblokowywanie i czyszczenie wtryskiwaczy - Diesel (pominąć zbiornik paliwa)"
    ],
    [
        "nazwa" => "TEC 2000 Fuel Injector Cleaner", 
        "opis" => "Odblokowywanie i czyszczenie wtryskiwaczy - Benzyna (pominąć zbiornik paliwa)"
    ],
    [
        "nazwa" => "TEC 2000 Radiator Flush", 
        "opis" => "Płukanie układu chłodzenia - odkamieniacz"
    ],
    [
        "nazwa" => "TEC 2000 Radiator Stop Leak", 
        "opis" => "Dodatek uszczelniający układ chłodzenia"
    ],
    // Duże opakowania
    [
        "nazwa" => "TEC 2000 Engine Flush 2.5L", 
        "opis" => "Płukanka silnika 2.5L (serwisowa)"
    ],
    [
        "nazwa" => "TEC 2000 Diesel System Cleaner 2.5L", 
        "opis" => "Dodatek do paliwa - Diesel 2.5L"
    ],
    [
        "nazwa" => "TEC 2000 Diesel Injector Flush 2.5L", 
        "opis" => "Czyszczenie wtryskiwaczy maszyn budowlanych/rolniczych"
    ]
];

// Wczytywanie stacji
$stacje = [];
if (file_exists("stacje.csv")) {
    if (($handle = fopen("stacje.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $nazwa = isset($data[0]) ? $data[0] : "Stacja";
            $adres = isset($data[1]) ? $data[1] : "";
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
        /* --- KOLORYSTYKA --- */
        :root {
            --accent-green: #a1d052; /* Limonka */
            --bg-gray: #667180;      /* Szary, którego chciałeś wszędzie */
            --text-white: #ffffff;
            --text-desc: #dcdcdc;    /* Lekko szary dla opisów */
        }

        body { 
            font-family: 'Oxygen', 'Segoe UI', sans-serif; 
            margin: 0; 
            padding: 0; 
            background-color: var(--bg-gray); 
            color: var(--text-white);
        }

        /* --- NAGŁÓWEK (Musi być biały ze względu na loga JPG) --- */
        .header-bar {
            background-color: #ffffff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 4px solid var(--accent-green);
            height: 80px;
        }

        .logo-img {
            height: 60px;
            width: auto;
            object-fit: contain;
        }

        /* --- KONTENER GŁÓWNY --- */
        .container { 
            max-width: 700px;
            margin: 30px auto; 
            padding: 0 15px;
        }

        /* Karta formularza - teraz w kolorze tła */
        .card {
            background-color: var(--bg-gray); /* ZMIANA NA SZARY */
            padding: 20px;
            border-radius: 10px;
            /* Subtelny cień, żeby odciąć kartę od tła strony, ale kolor ten sam */
            box-shadow: 0 0 20px rgba(0,0,0,0.15); 
            border: 2px solid var(--accent-green);
        }

        h1 { 
            text-align: center; 
            color: var(--text-white); 
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
            font-weight: 700; 
            color: var(--text-white);
            font-size: 14px;
            text-transform: uppercase;
        }
        
        /* Inputy i Selecty - SZARE TŁO */
        select, input[type="text"] { 
            width: 100%; 
            padding: 14px; 
            font-size: 16px; 
            border: 2px solid var(--accent-green);
            border-radius: 6px; 
            box-sizing: border-box; 
            
            background-color: var(--bg-gray); /* ZMIANA NA SZARY */
            color: var(--text-white);         /* Biały tekst */
        }
        
        /* Kolor placeholder (tekst podpowiedzi) */
        ::placeholder {
            color: #b0b0b0;
            opacity: 1;
        }

        select:focus, input:focus {
            outline: none;
            box-shadow: 0 0 10px rgba(161, 208, 82, 0.4);
        }
        
        /* --- LISTA PRODUKTÓW --- */
        .products-wrapper {
            background-color: var(--bg-gray); /* ZMIANA NA SZARY */
            border: 2px solid var(--accent-green);
            border-radius: 6px; 
            overflow: hidden;
        }

        .product-item { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 15px; 
            /* Linia oddzielająca produkty - też limonkowa, bo na szarym słabo widać szarą */
            border-bottom: 1px solid rgba(161, 208, 82, 0.3); 
            color: var(--text-white);
        }
        
        .product-item:last-child { border-bottom: none; }
        .product-item:hover { background-color: rgba(255,255,255,0.05); } /* Lekkie rozjaśnienie po najechaniu */
        
        .product-info {
            flex-grow: 1;
            margin-right: 15px;
        }

        .product-name { 
            display: block;
            font-size: 16px; 
            font-weight: 700;
            color: var(--text-white);
            margin-bottom: 4px;
        }

        .product-desc {
            display: block;
            font-size: 13px;
            color: var(--text-desc); /* Jasnoszary opis */
            font-style: italic;
        }
        
        /* Pole ilości */
        .product-qty { 
            width: 70px !important; 
            text-align: center; 
            padding: 10px !important; 
            border: 2px solid var(--accent-green) !important;
            font-weight: bold;
            font-size: 16px;
            
            background-color: var(--bg-gray); /* ZMIANA NA SZARY */
            color: var(--text-white);         /* Biały tekst */
        }

        /* --- PRZYCISK --- */
        button { 
            background-color: var(--accent-green);
            color: white; 
            padding: 18px; 
            border: none; 
            font-size: 18px; 
            font-weight: 800;
            cursor: pointer; 
            width: 100%; 
            border-radius: 6px; 
            margin-top: 25px; 
            text-transform: uppercase;
            box-shadow: 0 4px 0px #8eb846;
            transition: transform 0.1s, box-shadow 0.1s;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
        
        button:hover { 
            background-color: #94c04b; 
            transform: translateY(2px);
            box-shadow: 0 2px 0px #8eb846;
        }
        
        .footer-link { text-align: center; margin-top: 30px; margin-bottom: 50px;}
        .footer-link a { color: rgba(255,255,255,0.5); text-decoration: none; font-size: 13px; font-weight: 500; }
        .footer-link a:hover { color: var(--accent-green); }

        /* RWD */
        @media (max-width: 480px) {
            .header-bar { padding: 10px; height: 70px; }
            .logo-img { height: 45px; } 
            .card { padding: 15px; border-width: 1px; } /* Mniejsza ramka na tel */
            .product-item { align-items: flex-start; }
            .product-qty { margin-top: 5px; }
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
                    <label>Produkty (wpisz ilość):</label>
                    <div class="products-wrapper">
                        <?php foreach ($produkty_lista as $produkt): ?>
                            <div class="product-item">
                                <div class="product-info">
                                    <span class="product-name"><?php echo htmlspecialchars($produkt['nazwa']); ?></span>
                                    <span class="product-desc"><?php echo htmlspecialchars($produkt['opis']); ?></span>
                                </div>
                                <input type="number" class="product-qty" name="ilosc[<?php echo htmlspecialchars($produkt['nazwa']); ?>]" placeholder="0" min="0">
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
