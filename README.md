# Mini LMS - Learning Management System

Plateforme pédagogique Laravel avec génération de contenu IA (Gemini), diagrammes Mermaid, enrichissement automatique (images, sources) et saisie vocale.

**Production** → [latreche-lms.alwaysdata.net](https://latreche-lms.alwaysdata.net)

---

## Stack

| Couche | Technologie |
|---|---|
| Backend | Laravel 11, PHP 8.2+ |
| BDD | MySQL 8 (prod) / SQLite (dev) |
| Frontend | Blade, TailwindCSS (CDN), Alpine.js |
| Diagrammes | Mermaid.js (CDN, rendu client-side) |
| Voix | Web Speech API (FR/EN/AR) |
| IA | Google Gemini API (multi-model fallback) |
| Images | Wikimedia Commons (CC) → Pexels (fallback) |
| Hébergement | AlwaysData (mutualisé gratuit) |

## Architecture

```
Request → FormRequest (validation) → Controller (thin) → Service (logique) → Model → DB
                                         ↓
                                     Policy (autorisation)
```

**12 services** :

| Service | Rôle |
|---|---|
| `AIContentService` | Prompt Gemini, fallback multi-modèles, contrôle chapitres |
| `AIContentParserService` | Parse JSON AI → structure PHP (mermaid, sources, quiz) |
| `AIContentImportService` | Crée Formation/Chapters/SubChapters/Quiz + enrichissement |
| `GeminiFileUploadService` | Upload/delete fichiers via Gemini Files API |
| `UrlContentExtractorService` | Extraction contenu web (Readability.php) |
| `ImageEnrichmentService` | Pipeline image : Wikimedia → Pexels |
| `WikimediaImageService` | Recherche images CC/Public Domain |
| `PexelsImageService` | Fallback images paysage |
| `SourceFinderService` | Validation sources AI + fallback Wikipedia |
| `QuizService` | Scoring sécurisé, verrou pessimiste |
| `DashboardService` | Métriques admin/étudiant |
| `ContentSanitizer` | Whitelist HTML, anti-XSS |

**4 policies** · **9 Form Requests** · **13 modèles Eloquent**

## Modèle de données

```
Users (admin/apprenant)
 ├── Formations → Chapters → SubChapters → Quiz → Questions → Answers
 │                 ├── image_url, image_credit, sources (JSON), mermaid_code
 │                 └── [mêmes colonnes sur SubChapters]
 ├── QuizAttempts (score, answers_given JSON)
 ├── Notes (/20), PersonalNotes, Todos
 ├── AiGenerations (draft → published | validated, validated_at)
 └── ActivityLogs
```

13 modèles, 12 migrations, 15 tables, cascades, soft deletes, indexes.

## Fonctionnalités

### Admin
- CRUD formations / chapitres / sous-chapitres (titre, contenu, image, sources, diagramme Mermaid)
- Gestion utilisateurs, notes /20, inscription apprenants
- Génération IA → édition → import en formation officielle
- Vue du contenu validé par les étudiants

### Apprenant
- Consultation formations, quiz step-by-step avec scoring
- Notes personnelles par sous-chapitre, todos
- Génération IA → édition → validation → formation personnelle + auto-inscription

### Module IA (Gemini)

| Paramètre | Détail |
|---|---|
| Chapitres | 1-10, contrôlé via UI, renforcé 4× dans le prompt |
| Profondeur | Standard (300 mots) · Détaillé (600) · Exhaustif (1000+) |
| Type | Cours seul · Quiz seul · Cours + Quiz |
| Fichiers joints | 5 max (PDF, images, DOCX, PPTX), Gemini Files API |
| URLs | Détection auto, extraction Readability.php, fallback Google Search |
| Saisie vocale | Web Speech API (FR/EN/AR), append au textarea |
| Diagrammes | Mermaid.js généré par IA, rendu client-side |
| Sources | Suggérées par IA, validées par HEAD request, fallback Wikipedia |
| Température | 0.2 (instruction-following strict) |
| Fallback | gemini-2.0-flash → 1.5-flash → pro |

### Sécurité
CSRF, XSS (ContentSanitizer), IDOR (Policies + Gate), `$fillable` whitelist, quiz anti-triche (réponses exclues du client + pessimistic locking), MIME whitelist uploads, URL blocklist, Mermaid `securityLevel: strict`, API key isolation `.env`.

## Configuration `.env`

```env
GEMINI_API_KEY=your-key
GEMINI_MODEL=gemini-2.0-flash
GEMINI_TIMEOUT=90
PEXELS_API_KEY=your-key        # optionnel, fallback images
```

## Comptes démo

| Rôle | Email | Mot de passe |
|---|---|---|
| Admin | admin@lms.test | password |
| Apprenant | student@lms.test | password |

## Structure

```
app/
├── Http/Controllers/{Admin,Student}/   15 controllers
├── Http/Requests/                       9 form requests
├── Models/                             13 models
├── Policies/                            4 policies
├── Services/                           12 services
database/migrations/                    12 migrations
resources/views/                        50+ blade views
```
