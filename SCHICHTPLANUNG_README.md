# Schichtplanungssystem - Dokumentation

## Übersicht

Das Schichtplanungssystem erweitert die bestehende Arbeitszeiterfassungs-Software um eine vollständige Schichtplanungs- und Anmeldefunktionalität.

## Funktionen

### 1. Jobgruppen-Verwaltung (Admin)
- Erstellen und Verwalten von Jobgruppen (z.B. "Gastro", "Office")
- Zuordnung von Mitarbeitern zu Jobgruppen
- Ein Mitarbeiter kann mehreren Jobgruppen angehören

### 2. Schichtverwaltung (Admin)
- Schichten anlegen mit:
  - Datum
  - Start- und Endzeit (volle Stunden)
  - Zugehörige Jobgruppe
  - Benötigte Mitarbeiteranzahl
  - Admin-Kommentar
- Übersicht aller Schichten mit Filterfunktion
- Anzeige von belegten/verfügbaren Plätzen
- Bearbeiten und Löschen von Schichten

### 3. Schicht-Anmeldung (API für Mitarbeiter)
- Mitarbeiter können sich über die API zu Schichten anmelden
- Automatische Validierung:
  - Nur Anmeldung zu Schichten der eigenen Jobgruppe(n)
  - Nur wenn noch Plätze verfügbar sind
  - Keine Doppel-Anmeldungen
- Mitarbeiter können einen Kommentar zur Anmeldung hinzufügen
- Abmelden von Schichten möglich

## Datenbank-Schema

### Tabellen

1. **job_groups**
   - id
   - name
   - timestamps

2. **job_group_user** (Pivot)
   - id
   - user_id
   - job_group_id
   - timestamps

3. **shifts**
   - id
   - job_group_id
   - shift_date
   - start_time
   - end_time
   - admin_comment
   - required_employees
   - timestamps

4. **shift_user** (Pivot)
   - id
   - shift_id
   - user_id
   - user_comment
   - timestamps

## Installation

1. Migrationen ausführen:
```bash
php artisan migrate
```

2. NPM Assets kompilieren:
```bash
npm run dev
# oder für Produktion
npm run build
```

## Web-Interface (Admin)

### Jobgruppen
- **Liste**: `/jobGroups`
- **Erstellen**: `/jobGroups/create`
- **Bearbeiten**: `/jobGroups/{id}/edit`

### Schichten
- **Liste**: `/shifts`
- **Erstellen**: `/shifts/create`
- **Bearbeiten**: `/shifts/{id}/edit`

### Navigation
Im Hauptmenü stehen für Administratoren folgende neue Menüpunkte zur Verfügung:
- **Jobgruppen** - Verwaltung der Jobgruppen
- **Schichten** - Verwaltung der Schichten

## API-Endpunkte

Alle API-Endpunkte erfordern Authentifizierung über Sanctum (`auth:sanctum`).

### Verfügbare Schichten abrufen
```
GET /api/shifts
```
**Response**: Liste aller verfügbaren Schichten für die Jobgruppen des Benutzers

### Schicht-Details abrufen
```
GET /api/shifts/{shift_id}
```
**Response**: Details einer spezifischen Schicht

### Zu Schicht anmelden
```
POST /api/shifts/{shift_id}/enroll
```
**Body**:
```json
{
  "user_comment": "Optional: Kommentar des Mitarbeiters"
}
```
**Response**: Bestätigung der Anmeldung

**Validierung**:
- Mitarbeiter muss zur Jobgruppe der Schicht gehören
- Schicht darf nicht voll sein
- Mitarbeiter darf nicht bereits angemeldet sein

### Von Schicht abmelden
```
DELETE /api/shifts/{shift_id}/unenroll
```
**Response**: Bestätigung der Abmeldung

## API-Beispiele

### Schichten abrufen mit cURL
```bash
curl -X GET "https://ihre-domain.de/api/shifts" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

### Zu Schicht anmelden
```bash
curl -X POST "https://ihre-domain.de/api/shifts/1/enroll" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"user_comment": "Ich freue mich auf die Schicht!"}'
```

### Von Schicht abmelden
```bash
curl -X DELETE "https://ihre-domain.de/api/shifts/1/unenroll" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

## Models & Beziehungen

### JobGroup Model
```php
// Beziehungen
$jobGroup->users; // Mitarbeiter in dieser Gruppe
$jobGroup->shifts; // Schichten dieser Gruppe
```

### Shift Model
```php
// Beziehungen
$shift->jobGroup; // Zugehörige Jobgruppe
$shift->users; // Angemeldete Mitarbeiter

// Berechnete Attribute
$shift->enrolled_count; // Anzahl angemeldeter Mitarbeiter
$shift->available_slots; // Anzahl freier Plätze
$shift->is_full; // Boolean: Ist die Schicht voll?

// Methoden
$shift->canUserEnroll($user); // Prüft, ob User sich anmelden kann
```

### User Model
```php
// Neue Beziehungen
$user->jobGroups; // Jobgruppen des Mitarbeiters
$user->shifts; // Angemeldete Schichten
```

## Workflow-Beispiel

1. **Admin erstellt Jobgruppe "Gastro"**
   - Navigation: Jobgruppen → Jobgruppe hinzufügen
   - Name eingeben: "Gastro"
   - Speichern

2. **Admin ordnet Mitarbeiter zu**
   - Jobgruppe bearbeiten
   - Mitarbeiter über Checkboxen auswählen
   - Speichern

3. **Admin erstellt Schicht**
   - Navigation: Schichten → Schicht hinzufügen
   - Jobgruppe: Gastro
   - Datum: 15.10.2025
   - Zeit: 08:00 - 17:00
   - Benötigte Mitarbeiter: 3
   - Kommentar: "Wichtiges Event, pünktlich erscheinen"
   - Speichern

4. **Mitarbeiter meldet sich an (via API)**
   - API-Request: POST /api/shifts/1/enroll
   - System prüft:
     - ✓ Mitarbeiter gehört zu "Gastro"
     - ✓ Nur 2 von 3 Plätzen belegt
     - ✓ Mitarbeiter noch nicht angemeldet
   - Anmeldung erfolgreich

5. **Admin sieht Anmeldungen**
   - Navigation: Schichten → Schicht bearbeiten
   - Übersicht: 3 von 3 Plätzen belegt
   - Liste der angemeldeten Mitarbeiter mit deren Kommentaren

## Sicherheit

- Alle Web-Routen sind durch Admin-Middleware geschützt
- API-Routen erfordern Sanctum-Authentifizierung
- Validierung auf Backend-Seite:
  - Jobgruppen-Zugehörigkeit wird geprüft
  - Verfügbare Plätze werden validiert
  - Doppel-Anmeldungen werden verhindert
- Cascade-Delete für referenzielle Integrität

## Erweiterungsmöglichkeiten

Mögliche zukünftige Features:
- E-Mail-Benachrichtigungen bei Schicht-Anmeldungen
- Push-Benachrichtigungen für neue Schichten
- Recurring Shifts (wiederkehrende Schichten)
- Schicht-Tausch zwischen Mitarbeitern
- Kalender-Export (iCal)
- Reporting und Statistiken

## Troubleshooting

### Migrationen schlagen fehl
```bash
# Rollback und erneut ausführen
php artisan migrate:rollback
php artisan migrate
```

### API gibt 401 zurück
- Prüfen Sie, ob der Bearer Token korrekt ist
- Token mit `php artisan sanctum:token-create {user_id}` neu erstellen

### Mitarbeiter kann sich nicht anmelden
Prüfen Sie:
1. Ist der Mitarbeiter der richtigen Jobgruppe zugeordnet?
2. Ist die Schicht bereits voll?
3. Ist der Mitarbeiter bereits angemeldet?

## Support

Bei Fragen oder Problemen wenden Sie sich an den Administrator.