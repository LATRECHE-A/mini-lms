# Mini LMS

Laravel 11 learning-management platform with AI content generation (Gemini), spaced-repetition flashcards (SM-2), automatic enrichment (Wikimedia/Pexels images, validated sources, Mermaid diagrams), and voice input.

**Production** -> [latreche-lms.alwaysdata.net](https://latreche-lms.alwaysdata.net)

---

## Stack

| Layer | Tech |
|---|---|
| Backend | Laravel 11 · PHP 8.2+ |
| DB | MySQL 8 (prod) · SQLite (dev) |
| Frontend | Blade · TailwindCSS (CDN) · Alpine.js |
| Diagrams | Mermaid.js (client-side, `securityLevel: strict`) |
| Voice | Web Speech API (FR/EN/AR) |
| AI | Google Gemini API (multi-model fallback) |
| Images | Wikimedia Commons (CC) → Pexels (fallback) |
| Hosting | AlwaysData |

## Architecture

```
Request
  → Auth middleware (auth, role:admin|apprenant)
  → FormRequest (validation + authorize via Policy)
  → Controller (thin: orchestration + flash messages)
  → Service (business logic, transactions, external APIs)
  → Model + Policy
  → DB
```

Errors render through a dedicated minimal `layouts.error` blade — never inside the dashboard layout. So a 403/404 cannot leak the sidebar of the role the user does not have.

## Roles & access

Two roles: `admin` and `apprenant`. Login redirects strictly by role; no `intended()` URL is honoured, which kills the cross-role redirect bug (admin's expired session followed by student login → admin URL → 403 inside admin layout). The `RoleMiddleware` redirects wrong-role users to *their own* dashboard rather than 403'ing, so the symptom can never appear.

| Resource | Admin | Student |
|---|---|---|
| Formations (any) | full CRUD | view if enrolled & published |
| Formations (own AI-generated) | n/a | full CRUD (chapters, sub-chapters, edit/delete) |
| Quizzes per sub-chapter | generate, edit, delete | take & score only |
| Flashcards | template + personal | personal only (auto-cloned from templates on enrollment) |
| AI generation | generate → import as official formation | generate → validate as personal formation (auto-enroll, `created_by` = student) |

Ownership is recorded via `formations.created_by` and enforced by `FormationPolicy` / `SubChapterPolicy`. Edit affordances in views are surfaced via `auth()->user()->can('update', $model)`, never role checks.

## Data model

```
Users (admin | apprenant)
 ├── Formations (created_by → User, soft-deleted)
 │    └── Chapters
 │         ├── (image_url, image_credit, sources JSON, mermaid_code)
 │         └── SubChapters
 │              ├── (image_url, image_credit, sources JSON, mermaid_code)
 │              ├── Quiz (1:1) → Questions → Answers
 │              ├── Flashcards (templates + personal copies, SM-2 fields)
 │              └── PersonalNotes (per student)
 ├── QuizAttempts (score, answers_given JSON)
 ├── Notes (/20 grades, admin-issued)
 ├── Todos
 ├── AiGenerations (draft → published | validated, type ∈ {course, quiz, mixed, full})
 └── ActivityLogs (polymorphic subject, used to backfill formation ownership)
```

14 models · 14 migrations · 16 tables · cascades, soft deletes, composite indexes.

## Flashcards (SM-2 spaced repetition)

`Flashcard` rows split into two kinds via `is_template`:

- **Template** (`is_template = true`) - admin's master deck. Cloned to each student on enrollment. Not studyable.
- **Personal** (`is_template = false`) - studyable card with full SM-2 state (`ease_factor`, `interval_days`, `next_review_at`, `review_count`).

Invariant guaranteed by `FlashcardService`: every template owned by an admin has a matching personal copy by the same admin. So the admin's "Study" mode always works regardless of generation history. Sync is enforced on every entry point — generate, store, update, delete — so templates and admin-personal copies live and die together.

`generateFromSubChapter()` calls Gemini for a JSON array of `{question, answer}`, deduplicates by question text, creates the right rows for the user's role. Review uses standard SM-2 with `quality ∈ [0..5]`, capped at 180-day intervals.

## AI module (Gemini)

| Param | Value |
|---|---|
| Models | `gemini-2.0-flash` → `gemini-1.5-flash` → `gemini-1.5-pro` (fallback chain) |
| Temperature | 0.2 (instruction-following), 0.3 (flashcards), 0.4 (rewrite) |
| Chapters | 1–10, enforced 4× in prompt + post-trim by `repairChapterCount()` |
| Depth | Standard (300 w) · Detailed (600 w) · Exhaustive (1000+ w) |
| Type | `course` · `quiz` · `mixed` (course + quiz) · `full` (course + quiz + auto-flashcards on import) |
| File attachments | Up to 5 (PDF, DOCX, PPTX, images), 25 MB total, via Gemini Files API |
| URLs in prompt | Auto-extracted via Readability.php; failed URLs trigger Google Search grounding |
| Voice | Web Speech API, appends to textarea (FR/EN/AR) |
| Sources | AI-suggested → HEAD-validated → Wikipedia fallback |
| Diagrams | AI-emitted Mermaid, rendered client-side |

The `full` type is stored on `ai_generations.type` as a marker (CHECK constraint includes it) so the importer knows to also seed flashcards for every sub-chapter. Per-sub-chapter quiz generation is admin-only — students can't generate quizzes on existing sub-chapters because a quiz is bound 1:1 to a sub-chapter and would be visible to every enrolled student.

## Security

CSRF on every mutation · `ContentSanitizer` HTML allow-list (anti-XSS) · IDOR-blocked via Policies + `Gate::authorize()` on every action · `$fillable` whitelisting · quiz anti-cheat (correct answers stripped from client payload + pessimistic locking on attempt creation) · MIME whitelist on uploads · Mermaid `securityLevel: strict` · `.env`-isolated API keys · rate-limiting on AI routes (`throttle:10,1` and `throttle:20,1` for rewrite).

## Project structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/         10 controllers (CRUD + AI + Flashcards)
│   │   ├── Student/        9 controllers (read + own-formation CRUD + AI + Flashcards + Todos)
│   │   └── AuthController.php
│   ├── Middleware/RoleMiddleware.php
│   └── Requests/           9 form requests (policy-based authorize)
├── Models/                14 Eloquent models
├── Policies/               6 policies (Formation, SubChapter, Flashcard, Quiz, Note, AiGeneration)
├── Providers/AppServiceProvider.php   (policy registration)
└── Services/              13 services
database/migrations/        14 migrations
resources/views/
├── layouts/                app.blade.php (dashboard) + error.blade.php (minimal)
├── errors/                 403, 404, 500 (all extend layouts.error)
├── admin/                  formations, chapters, subchapters, quizzes, questions, users, notes, ai, flashcards
├── student/                formations, subchapters, chapters, quizzes, ai, flashcards, notes, todos
└── auth/                   login, register
routes/web.php              role-prefixed, role-middlewared groups
```

## Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite      # for SQLite dev
php artisan migrate --seed
php artisan serve
```

Required `.env`:

```
GEMINI_API_KEY=...
GEMINI_MODEL=gemini-2.0-flash       # optional, this is the default
PEXELS_API_KEY=...                  # optional, image fallback
```

Default seeded users: an `admin@…` and a couple of `apprenant@…` accounts (see `DatabaseSeeder.php`). Two formations are seeded — *English Irregular Verbs* and *Introduction to HTML* — both with chapters, sub-chapters, quizzes, and template flashcards.
