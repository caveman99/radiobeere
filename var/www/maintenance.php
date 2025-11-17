<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>RadioBeere - Wartung</title>
    <meta name="viewport"
          content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <?php
    include("include/styling.php");
    ?>
</head>

<body>
    <div data-role="page"
         class="ui-responsive-panel"
         id="panel"
         data-title="RadioBeere">

        <div data-role="header">
            <a href="#nav-panel"
                 data-icon="bars"
                 data-iconpos="notext">Menü</a>
            <h1>RadioBeere</h1>
            <a href="/"
                 data-icon="home"
                 data-iconpos="notext">Startseite</a>
        </div>

        <div data-role="main"
             class="ui-content">
            
            <h2>Einstellungen</h2>
                <form method="post" id="verwalten_einstellungen" enctype="multipart/form-data">
                    <?php
                        include("include/db-connect.php");

                        $speichern = $_POST["speichern"];
                        $fqdn = $_POST["FQDN"];
                        $prot = $_POST["Protokoll"];
                        // Checkbox value: if not set, default to "0"
                        $auto_delete_enabled = isset($_POST["auto_delete_enabled"]) ? $_POST["auto_delete_enabled"] : "0";
                        $auto_delete_months = $_POST["auto_delete_months"];

                        if ($speichern == "save") {
                            setSettings($verbindung, $fqdn, $prot, $auto_delete_enabled, $auto_delete_months);
                            exec("sudo /radiobeere/podcast.py all");
                        }

                        // Display basic settings (FQDN and Protokoll)
                        $abfrage = "SELECT * FROM settings WHERE name IN ('FQDN', 'Protokoll');";
                        $ergebnis = mysqli_query($verbindung, $abfrage);
                        while($row = mysqli_fetch_object($ergebnis)) {
                            echo "  <label for=\"" . $row->name . "\">" . $row->name . ":
                                    <input type=\"text\" name=\"" . $row->name . "\" id=\"" . $row->name . "\" value=\"" . $row->wert . "\"/>
                                    </label>\n";
                        }
                    ?>

                    <h3>Automatisches Löschen alter Aufnahmen</h3>

                    <?php
                        $delete_enabled = getSetting($verbindung, 'auto_delete_enabled', '0');
                        $delete_months = getSetting($verbindung, 'auto_delete_months', '3');
                    ?>

                    <fieldset data-role="controlgroup">
                        <label for="auto_delete_enabled">
                            <input type="checkbox" name="auto_delete_enabled" id="auto_delete_enabled" value="1" <?php echo ($delete_enabled == '1') ? 'checked' : ''; ?>>
                            Alte Aufnahmen automatisch löschen
                        </label>
                    </fieldset>

                    <label for="auto_delete_months">Aufnahmen löschen, die älter sind als:
                        <select name="auto_delete_months" id="auto_delete_months">
                            <option value="1" <?php echo ($delete_months == '1') ? 'selected' : ''; ?>>1 Monat</option>
                            <option value="2" <?php echo ($delete_months == '2') ? 'selected' : ''; ?>>2 Monate</option>
                            <option value="3" <?php echo ($delete_months == '3') ? 'selected' : ''; ?>>3 Monate</option>
                            <option value="6" <?php echo ($delete_months == '6') ? 'selected' : ''; ?>>6 Monate</option>
                            <option value="12" <?php echo ($delete_months == '12') ? 'selected' : ''; ?>>12 Monate</option>
                            <option value="24" <?php echo ($delete_months == '24') ? 'selected' : ''; ?>>24 Monate</option>
                        </select>
                    </label>

                    <input type="hidden" name="speichern" id="speichern" value="save" />
                    <input type="submit" value="Einstellungen speichern" form="verwalten_einstellungen" />
                </form>


            

            <h2>Update</h2>

            <?php

            if($_POST['update'] == "true")
                {
                echo "<p><strong>Die Software wird nun aktualisiert ...</strong></p>";
                exec("sudo /radiobeere/setup/update-radiobeere");
                $logfile = file("radiobeere-update.log");
                foreach ($logfile AS $logfile_output)
                    {
                    echo $logfile_output."<br />";
                    }
                echo "<p><strong>Fertig!</strong></p>";
                unset($_POST);
                }

            $version_url = "https://raw.githubusercontent.com/moppi4483/radiobeere/master/var/www/version.txt";
            $version_remote = file_get_contents($version_url);
            $version_file = fopen("version.txt","r");
            $version_local = fgets($version_file);
            fclose($version_file);

            if($version_remote > $version_local)
                {
                echo "<p>Es gibt eine neuere Version der RadioBeere-Software. Willst du aktualisieren?</p>";
                echo "<form method=\"post\">";
                echo "<button type=\"submit\" name=\"update\" value=\"true\">Update starten</button>";
                echo "</form>";
                }
            else
                {
                echo "<p>Deine RadioBeere-Software ist auf dem aktuellen Stand.</p>";
                }

            echo "Installierte Version:<br />";
            echo date("d.m.Y, H:i:s", $version_local)." Uhr";

            if($version_remote > $version_local)
                {
                echo "<br /><br />";
                echo "Aktuelle Version auf dem Server:<br />";
                echo date("d.m.Y, H:i:s", $version_remote)." Uhr";
                }

            ?>

            <h2>Speicherplatz</h2>

            <?php

            function getByte($bytes)
                {
                $symbol = " Bytes";
                if ($bytes > 1024)
                    {
                    $symbol = " Kilobyte";
                    $bytes /= 1024;
                    }
                if ($bytes > 1024)
                    {
                    $symbol = " Megabyte";
                    $bytes /= 1024;
                    }
                if ($bytes > 1024)
                    {
                    $symbol = " Gigabyte";
                    $bytes /= 1024;
                    }
                $bytes = round($bytes, 2);
                return $bytes.$symbol;
                }

            function getFreespace($path)
                {
                $freeBytes = disk_free_space($path);
                $totalBytes = disk_total_space($path);
                $usedBytes = $totalBytes - $freeBytes;
                $percentFree = 100 / $totalBytes * $freeBytes;

                echo "Speicher gesamt:<br /><strong>".getByte($totalBytes)."</strong><br /><br />";
                echo "Freier Speicher:<br /><strong>".getByte($freeBytes);
                printf(" (%01.2f%%)", $percentFree);
                echo "</strong>";
                }

            getFreespace(".");

            ?>

            <h2>System-Update-Protokoll</h2>

            <p>Die Länge der Log-Datei ist auf 1.000 Zeilen begrenzt.</p>
            <a href="dist-upgrade.log"
                 target="_blank"
                 class="ui-btn">Log-Datei ansehen</a>

            <div class="illu-content-wrapper">
                <div class="illu-content illu-maintenance">
                </div>
            </div>
        </div>
        <?php
        include("include/navigation.php");
        ?>
    </div>
    <?php
    include("include/jquery.php");
    ?>
</body>
</html>
