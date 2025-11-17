<?php
// Installationsverzeichnis aus Config-Datei lesen
// (geschrieben während der Installation, da bind mount die Pfadermittlung verhindert)
$config_file = __DIR__ . '/install-path.conf';
if (file_exists($config_file)) {
    define('RADIOBEERE_INSTALL_DIR', trim(file_get_contents($config_file)));
} else {
    // Fallback: Versuche den Pfad zu ermitteln (funktioniert ohne bind mount)
    define('RADIOBEERE_INSTALL_DIR', dirname(dirname(dirname(__FILE__))));
}

$verbindung = mysqli_connect("localhost","radiobeere","password");

if (!$verbindung) {
  echo "Fehler bei DB-Verbindung!";
  exit;
}

mysqli_select_db($verbindung, "radiobeere");



function getFQDN($verb) {
        $fqdn = "";

        $abfrage = "SELECT * FROM settings WHERE name = 'FQDN';";
        $ergebnis = mysqli_query($verb, $abfrage);
        while($row = mysqli_fetch_object($ergebnis)) {
                $fqdn = $row->wert;
        }

        if($fqdn == '') {
                $fqdn = gethostname();
        }

        return $fqdn;
}


function getProtokoll($verb) {
        $prot = "";
        $abfrage = "SELECT * FROM settings WHERE name = 'Protokoll';";
        $ergebnis = mysqli_query($verb, $abfrage);
        while($row = mysqli_fetch_object($ergebnis)) {
                $prot = $row->wert;
        }

        if($prot == '') {
                $prot = 'http';
        }

        return $prot;
}


function setSettings($verb, $fqdn, $prot, $auto_delete_enabled = null, $auto_delete_months = null) {
        $values = array();
        $values[] = "('FQDN', '" . mysqli_real_escape_string($verb, $fqdn) . "')";
        $values[] = "('Protokoll', '" . mysqli_real_escape_string($verb, $prot) . "')";

        if ($auto_delete_enabled !== null) {
                $values[] = "('auto_delete_enabled', '" . mysqli_real_escape_string($verb, $auto_delete_enabled) . "')";
        }
        if ($auto_delete_months !== null) {
                $values[] = "('auto_delete_months', '" . mysqli_real_escape_string($verb, $auto_delete_months) . "')";
        }

        $abfrage = "INSERT INTO settings (name, wert) VALUES " . implode(', ', $values) . " ON DUPLICATE KEY UPDATE wert=VALUES(wert);";
        try {
                if(!mysqli_query($verb, $abfrage)) {
                        throw new Exception(mysqli_error($verb));
                } else {
                        echo "<p>Einstellungen gespeichert</p>";
                }
        }
        catch (Exception $e) {
                echo $e -> getMessage();
        }

  return $ergebnis;
}


function getSetting($verb, $name, $default = '') {
        $value = $default;
        $abfrage = "SELECT wert FROM settings WHERE name = '" . mysqli_real_escape_string($verb, $name) . "';";
        $ergebnis = mysqli_query($verb, $abfrage);
        if ($ergebnis && $row = mysqli_fetch_object($ergebnis)) {
                $value = $row->wert;
        }
        return $value;
}
?>
