# Schichtplan

Ein modernes Schichtplanungs- und Arbeitszeiterfassungssystem, entwickelt mit Laravel 11 und Vue 3.

## Überblick

Schichtplan ist eine webbasierte Anwendung zur Verwaltung von Arbeitsschichten und Mitarbeitereinsätzen. Das System ermöglicht es Administratoren und Teamleitern, Schichten zu planen und Mitarbeitern, sich für verfügbare Schichten einzutragen.

### Hauptfunktionen

- **Schichtverwaltung** – Erstellen, bearbeiten und löschen von Schichten mit Datum, Uhrzeit und benötigter Mitarbeiteranzahl
- **Jobgruppen** – Organisation von Mitarbeitern in verschiedene Arbeitsgruppen (z.B. Gastro, Büro)
- **Schichtanmeldung** – Mitarbeiter können sich selbstständig für Schichten eintragen oder absagen
- **Arbeitszeiterfassung** – Automatische Berechnung von Arbeitszeiten, Pausen und Überstunden
- **Minijob-Verwaltung** – Spezielle Kontingent-Verwaltung für Minijobber
- **API-Zugang** – RESTful API für mobile Apps und externe Integrationen

## Tech-Stack

| Bereich | Technologie |
|---------|-------------|
| Backend | Laravel 11, PHP 8.2+ |
| Frontend | Vue 3, Inertia.js |
| Styling | Tailwind CSS 3.0 |
| Authentifizierung | Laravel Sanctum |
| Datenbank | MySQL |
| PDF-Generierung | Laravel DomPDF |
| Benachrichtigungen | Telegram |

## Installation

### Voraussetzungen

- PHP 8.2 oder höher
- Composer
- Node.js & NPM
- MySQL

### Setup

1. **Repository klonen**
   ```bash
   git clone https://github.com/your-username/schichtplan.git
   cd schichtplan
   ```

2. **PHP-Abhängigkeiten installieren**
   ```bash
   composer install
   ```

3. **NPM-Abhängigkeiten installieren**
   ```bash
   npm install
   ```

4. **Umgebungsvariablen konfigurieren**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Datenbank einrichten**

   Erstelle eine MySQL-Datenbank und passe die `.env` Datei an:
   ```env
   DB_DATABASE=schichtplan
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Migrationen ausführen**
   ```bash
   php artisan migrate
   ```

7. **Frontend kompilieren**
   ```bash
   npm run dev
   ```

8. **Server starten**
   ```bash
   php artisan serve
   ```

Die Anwendung ist nun unter `http://localhost:8000` erreichbar.

## Projektstruktur

```
schichtplan/
├── app/
│   ├── Http/Controllers/     # Web- und API-Controller
│   ├── Models/               # Eloquent Models
│   └── Mail/                 # E-Mail-Klassen
├── database/
│   ├── migrations/           # Datenbankmigrationen
│   └── seeders/              # Datenbank-Seeder
├── resources/
│   └── js/
│       ├── Pages/            # Vue-Seiten
│       ├── Components/       # Wiederverwendbare Komponenten
│       └── Layouts/          # Layout-Komponenten
├── routes/
│   ├── web.php               # Web-Routen
│   └── api.php               # API-Routen
└── config/
    └── tenant.php            # Mandanten-Konfiguration
```

## API-Dokumentation

Die API nutzt Laravel Sanctum für die Authentifizierung. Alle Endpunkte erfordern einen gültigen API-Token.

### Schichten

| Methode | Endpunkt | Beschreibung |
|---------|----------|--------------|
| GET | `/api/shifts` | Verfügbare Schichten abrufen |
| GET | `/api/shifts/{id}` | Schichtdetails abrufen |
| POST | `/api/shifts/{id}/enroll` | Für Schicht anmelden |
| DELETE | `/api/shifts/{id}/unenroll` | Schichtanmeldung zurückziehen |
| POST | `/api/shifts/{id}/decline` | Schicht absagen |

### Beispiel-Request

```bash
curl -X POST "https://your-domain.com/api/shifts/1/enroll" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"comment": "Bin verfügbar!"}'
```

## Benutzerrollen

| Rolle | Berechtigungen |
|-------|----------------|
| **Admin** | Vollzugriff auf alle Funktionen |
| **Teamleiter** | Verwaltung von Schichten und Jobgruppen |
| **Mitarbeiter** | Anmeldung für Schichten, eigene Arbeitszeiten einsehen |
| **Minijobber** | Wie Mitarbeiter, mit Kontingent-Beschränkungen |

## Konfiguration

### Mandanten-Einstellungen

In `config/tenant.php` können folgende Features aktiviert werden:

```php
return [
    'name' => env('TENANT_NAME', 'default'),
    'features' => [
        'minijob' => env('TENANT_FEATURE_MINIJOB', false),
        'tournaments' => env('TENANT_FEATURE_TOURNAMENTS', false),
        'group_notifications' => env('TENANT_FEATURE_GROUP_NOTIFICATIONS', false),
    ],
];
```

## Entwicklung

### Frontend-Assets kompilieren

```bash
# Entwicklung mit Hot-Reload
npm run dev

# Produktion
npm run build
```

### Code-Style

```bash
# PHP Code-Style prüfen und korrigieren
./vendor/bin/pint
```

### Tests ausführen

```bash
php artisan test
```

## Lizenz

Dieses Projekt steht unter der [GNU General Public License v3.0](LICENSE).

---

Entwickelt mit Laravel und Vue.js
