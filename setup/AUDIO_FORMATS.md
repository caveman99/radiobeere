# Unterstützte Audio-Formate

RadioBeere unterstützt drei Audio-Formate für Aufnahmen:

## MP3 (.mp3)
- **Container**: MP3 (MPEG Audio Layer 3)
- **Metadaten**: ✅ ID3-Tags (v2.3)
- **Cover-Art**: ✅ Unterstützt
- **Kompression**: Verlustbehaftet
- **Kompatibilität**: Universal unterstützt

Die MP3-Dateien erhalten automatisch folgende Metadaten:
- Titel: Sendername, Datum und Uhrzeit
- Interpret: Sendername
- Album: Aufnahmedatum
- Cover-Art: Sender-Logo oder Standard-Logo

## M4A (.m4a)
- **Container**: MP4 (MPEG-4 Part 14)
- **Codec**: AAC (Advanced Audio Coding)
- **Metadaten**: ✅ MP4-Tags
- **Cover-Art**: ✅ Unterstützt
- **Kompression**: Verlustbehaftet, bessere Qualität als MP3 bei gleicher Bitrate
- **Kompatibilität**: Sehr gut (iTunes, moderne Player)

Die M4A-Dateien erhalten automatisch folgende Metadaten:
- Name (©nam): Sendername, Datum und Uhrzeit
- Interpret (©ART): Sendername
- Album (©alb): Aufnahmedatum
- Cover-Art: Sender-Logo oder Standard-Logo

## AAC (.aac)
- **Container**: Keiner (Raw AAC/ADTS)
- **Codec**: AAC (Advanced Audio Coding)
- **Metadaten**: ❌ **NICHT** unterstützt (kein Container für Tags)
- **Cover-Art**: ❌ **NICHT** unterstützt
- **Kompression**: Verlustbehaftet, bessere Qualität als MP3 bei gleicher Bitrate
- **Kompatibilität**: Gut, aber weniger universal als MP3/M4A

**Wichtig**: Raw AAC-Dateien (.aac) sind reine Audio-Streams ohne Container-Format.
Sie unterstützen keine eingebetteten Metadaten oder Cover-Art. Wenn Sie Metadaten
benötigen, verwenden Sie stattdessen das .m4a Format (AAC in MP4-Container).

## Empfehlung

- **Für beste Kompatibilität**: MP3 (.mp3)
- **Für beste Qualität bei gleicher Dateigröße**: M4A (.m4a)
- **Nur wenn Metadaten unwichtig sind**: AAC (.aac)

Alle drei Formate werden von RadioBeere vollständig unterstützt:
- Aufnahme ✅
- Wiedergabe ✅
- Download ✅
- Podcast-Feeds ✅
- DLNA-Streaming ✅
- Automatisches Löschen ✅
