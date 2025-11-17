# Automatisches Löschen alter Aufnahmen - Installation

## Überblick

Diese Funktion ermöglicht es, alte Radioaufnahmen automatisch nach einer konfigurierbaren Anzahl von Monaten zu löschen.

## Installation

### 1. Datenbankeinstellungen hinzufügen

Führen Sie das SQL-Migrationsskript aus (passen Sie den Pfad an Ihr Installationsverzeichnis an):

```bash
mysql -u radiobeere -p radiobeere < ~/radiobeere/setup/add_auto_delete_settings.sql
```

Oder melden Sie sich bei MySQL an und führen Sie die Befehle manuell aus:

```bash
mysql -u radiobeere -p
```

```sql
USE radiobeere;

INSERT INTO settings (name, wert) VALUES ('auto_delete_enabled', '0')
ON DUPLICATE KEY UPDATE name=name;

INSERT INTO settings (name, wert) VALUES ('auto_delete_months', '3')
ON DUPLICATE KEY UPDATE name=name;
```

### 2. Python-Abhängigkeit installieren

Das Auto-Delete-Feature benötigt das `python-dateutil` Paket:

**Debian/Ubuntu/Raspbian:**
```bash
sudo apt-get install python-dateutil
```

**Alternative mit pip:**
```bash
sudo pip install python-dateutil
```

**Hinweis:** Bei Neuinstallationen ab diesem Update wird `python-dateutil` automatisch vom Setup-Skript installiert.

### 3. Konfiguration in der Web-Oberfläche

1. Öffnen Sie die RadioBeere Web-Oberfläche
2. Navigieren Sie zu **Wartung** > **Einstellungen**
3. Im Abschnitt "Automatisches Löschen alter Aufnahmen":
   - Aktivieren Sie die Checkbox "Alte Aufnahmen automatisch löschen"
   - Wählen Sie die gewünschte Anzahl von Monaten (1-24 Monate)
4. Klicken Sie auf "Einstellungen speichern"

## Funktionsweise

- Das Cleanup-Skript (`rb-rec-cleanup.py`) prüft bei jedem Lauf, ob die Auto-Delete-Funktion aktiviert ist
- Wenn aktiviert, werden alle Aufnahmen gelöscht, deren Aufnahmedatum älter als die konfigurierte Anzahl von Monaten ist
- Sowohl die Datenbankeinträge als auch die Audiodateien werden entfernt
- Das Cleanup-Skript wird automatisch nach jeder Aufnahme ausgeführt

## Deaktivierung

Um die automatische Löschung zu deaktivieren:
1. Gehen Sie zu **Wartung** > **Einstellungen**
2. Deaktivieren Sie die Checkbox "Alte Aufnahmen automatisch löschen"
3. Speichern Sie die Einstellungen

## Manuelle Ausführung

Sie können das Cleanup-Skript auch manuell ausführen (passen Sie den Pfad an Ihr Installationsverzeichnis an):

```bash
sudo ~/radiobeere/rb-rec-cleanup.py
```

Dies führt alle Bereinigungsfunktionen aus, einschließlich des Löschens alter Aufnahmen (falls aktiviert).
