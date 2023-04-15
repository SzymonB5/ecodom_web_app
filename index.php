<!DOCTYPE html>
<html>
<head>
  <title>Ecodom+</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="style.css">
<body>
  <div class="container">
    <div class="menu">
      <ul>
        <li><a href="#"><i class="fas fa-home"></i>Strona główna</a></li>
        <li><a href="#"><i class="fas fa-info-circle"></i>O nas</a></li>
        <li><a href="#"><i class="fas fa-cogs"></i>Usługi</a></li>
        <li><a href="#"><i class="fas fa-envelope"></i>Kontakt</a></li>
      </ul>
    </div>
    <div class="content">
      <?php
        $conn = mysqli_connect('localhost', 'root', '', 'ecodomDB');
        if ($conn->connect_error) {
            echo "Nie udało się połączyć z bazą danych: " . mysqli_connect_error();
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
          if (isset($_POST["usun_urzadzenie"])) {
              $urzadzenie_id = $_POST["urzadzenie_id"];
      
              $conn = mysqli_connect('localhost', 'root', '', 'ecodomDB');
              if (!$conn) {
                  echo "Nie udało się połączyć z bazą danych: " . mysqli_connect_error();
                  exit();
              }
      
              $query = "DELETE FROM Urzadzenia WHERE id = ?;";
              $stmt = mysqli_prepare($conn, $query);
              mysqli_stmt_bind_param($stmt, "i", $urzadzenie_id);
              mysqli_stmt_execute($stmt);
          }
      }

        $query = "SELECT p.id AS pid, p.nazwa AS pnazwa, u.id AS uid, u.nazwa AS unazwa, u.moc, u.harmonogram, ob.suma_kosztow
                  FROM Urzadzenia AS u LEFT JOIN Pomieszczenia AS p ON u.id_pomieszczenia = p.id 
                  LEFT JOIN (SELECT ze.id_urzadzenia, SUM(
                    CASE 
                      WHEN ze.godzina BETWEEN '07:00:00' AND '21:59:59' THEN (kp.taryfa_dzienna + kp.koszt_jednostkowy) * ze.zuzycie
                      ELSE (kp.taryfa_nocna + kp.koszt_jednostkowy) * ze.zuzycie
                    END) AS suma_kosztow
                  FROM ZuzycieEnergii AS ze JOIN KosztyPradu AS kp ON ze.data = kp.data
                  GROUP BY ze.id_urzadzenia) AS ob ON ob.id_urzadzenia = u.id;";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<div class="urzadzenie">';
                echo '<div class="urzadzenie_info">';
                echo getIcon($row['pid']);
                echo '<p>' . $row['unazwa'] . '</p>';
                echo '</div>';
                echo '<div class="urzadzenie_info">';
                echo '<h3>Room</h3>';
                echo '<p>' . $row['pnazwa'] . '</p>';
                echo '</div>';
                echo '<div class="urzadzenie_info">';
                echo '<h3>Power usage (kWh)</h3>';
                echo '<p>' . $row['moc']/1000 . '</p>';
                echo '</div>';
                echo '<div class="urzadzenie_info">';
                echo '<h3>Electricity cost</h3>';
                echo '<p>' . round($row['suma_kosztow'], 2) . '</p>';
                echo '</div>';
                echo '<div class="urzadzenie_info">';
                echo '<form method="post" action="index.php">';
                echo '<input type="hidden" name="urzadzenie_id" value="' . $row['uid'] . '">';
                echo '<input type="submit" name="usun_urzadzenie" value="Usuń urządzenie">';
                echo '</form>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo 'Brak urządzeń w bazie danych.';
        }

        mysqli_close($conn);
      ?>
    </div>
  </div>
</body>
</html>

<?php
function getIcon($id_pomieszczenia) {
    switch ($id_pomieszczenia) {
        case 1:
            return '<i class="fas fa-television"></i>';
        case 2:
            return '<i class="fas fa-cutlery"></i>';
        case 3:
            return '<i class="fas fa-bed"></i>'; 
        case 4:
            return '<i class="fas fa-bathtub"></i>'; 
        default:
            return '<i class="fas fa-laptop"></i>';
    }
}
?>