<?php
session_start(); // Session indítása

// Megoldókulcs
$key = array(5, -14, 31, -9, 3);

// Az adatbázishoz való kapcsolódás
$servername = "mysql.omega:3306"; // Adatbázis szerver neve
$username_db = "gabor820"; // Felhasználónév
$password_db = "Phpsql123"; // Jelszó
$dbname = "gabor820"; // Adatbázis neve
$table = "Tábla"; // Tábla neve

// Kapcsolódás az adatbázishoz
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Kapcsolat ellenőrzése
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Felhasználói adatok ellenőrzése
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Felhasználói adatok beolvasása az űrlapról
    $username_input = $_POST["username"];
    $password_input = $_POST["password"];

    // Fájl beolvasása és dekódolása
    $file_contents = file_get_contents("password.txt");
    $lines = explode("\x0A", $file_contents); // Sorok szétválasztása EOL alapján

    $decoded_lines = array();
    foreach ($lines as $line) {
        $decoded_line = "";
        for ($i = 0; $i < strlen($line); $i++) {
            // Karakter kódjának visszafejtése a kulcs segítségével
            $decoded_char = ord($line[$i]) - $key[$i % count($key)];
            // Negatív érték esetén az ASCII határon körbe kell fordítani
            if ($decoded_char < 0) {
                $decoded_char += 256;
            }
            // Visszafejtett karakter hozzáadása a sorhoz
            $decoded_line .= chr($decoded_char);
        }
        // Sor hozzáadása a visszafejtett sorokhoz
        $decoded_lines[] = $decoded_line;
    }

    // Felhasználónév és jelszó kinyerése a visszafejtett sorokból
    $login_successful = false;
    foreach ($decoded_lines as $line) {
        list($stored_username, $stored_password) = explode("*", $line);
        // Ellenőrzés az űrlapról kapott adatokkal
        if ($stored_username === $username_input && $stored_password === $password_input) {
            $login_successful = true;
            break;
        }
    }

    if ($login_successful) {
        // Sikeres bejelentkezés
        echo "<div style='display: flex; justify-content: center; align-items: center; height: 100vh;'>
        <div style='background-color: rgba(255, 255, 255, 0.8); padding: 20px; border-radius: 10px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);'>
            <p style='font-size: 24px; font-weight: bold;'>Sikeres bejelentkezés!</p>
        </div>
    </div>";
        
        // SQL lekérdezés összeállítása és futtatása
        $sql = "SELECT Titkos FROM $table WHERE Username='$username_input'";
        $result = $conn->query($sql);

        // Ellenőrizze, hogy a lekérdezés eredménye üres-e
        if ($result->num_rows > 0) {
            // Kiírjuk a felhasználó kedvenc színét
            while($row = $result->fetch_assoc()) {
                $color = $row["Titkos"];
                switch ($color) {
                    case "piros":
                        echo "<style>body { background-color: red; }</style>";
                        break;
                    case "kek":
                        echo "<style>body { background-color: blue; }</style>";
                        break;
                    case "fekete":
                        echo "<style>body { background-color: black; }</style>";
                        break;
                    case "sarga":
                        echo "<style>body { background-color: yellow; }</style>";
                        break;
                    case "feher":
                        echo "<style>body { background-color: white; }</style>";
                        break;
                    default:
                        echo "<div>Felhasználó kedvenc színe: $color</div><br>";
                }
            }
        } else {
            echo "Nincs ilyen felhasználó.";
        }
        
        exit; // Kilépünk a kódból, hogy ne jelenjen meg a bejelentkezési űrlap
    } else {
        // Sikertelen bejelentkezés
        echo "<p>Sikertelen bejelentkezés! Kérlek, ellenőrizd a felhasználónevet és a jelszót.</p>";
        // Átirányítás a police.hu-ra
        echo "<script>window.location.href = 'https://www.police.hu';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<h2>Tóth Gábor NGZ40U</h2>
<div>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <input type="text" id="username" name="username" placeholder="Felhasználónév"><br>
        <input type="password" id="password" name="password" placeholder="Jelszó"><br><br>
        <input type="submit" value="Bejelentkezés">
    </form>
</div>

</body>
</html>
