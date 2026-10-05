# ToneForge API

🚧 Work in Progress

A small PHP/Symfony backend that recommends guitar amps, cabinets, and effects based on a guitarist's gear and the tone they're going for.

## Why This Project Exists

ToneForge is a backend engineering portfolio project — the primary goal is **learning and demonstrating backend architecture**, not building a feature-complete product. It's deliberately scoped small, and the guitar domain exists mainly because it's a personally interesting space that makes backend concepts easier to reason about and discuss in an interview setting.

Concepts this project is specifically built to demonstrate: Symfony conventions, Doctrine ORM, REST API design, dependency injection, the Repository pattern, validation and error handling, automated testing (unit + planned integration), and deliberate architectural tradeoffs — not pattern-collecting for its own sake.

## AI-Assisted Development

This project uses Claude Code as a learning-oriented pair programmer and coding assistant.

The project's Claude Code instructions incorporate guidelines adapted from [multica-ai/andrej-karpathy-skills](https://github.com/multica-ai/andrej-karpathy-skills), licensed under the MIT License. The guidelines are derived from Andrej Karpathy's public observations on LLM-assisted software development.

## Architecture

```text
HTTP Request
     |
     v
Controller
     |
     v
DTO / Validation  (#[MapRequestPayload] + Symfony Validator constraints)
     |
     v
Application Service   (RecommendationService — the only real business logic in this app)
     |
     v
Repository            (Doctrine ServiceEntityRepository per entity)
     |
     v
MySQL
```

Errors flow through a single boundary rather than being handled ad hoc per controller: any uncaught exception under `/api/*` is caught by `ApiExceptionListener` (a `kernel.exception` listener) and converted to a JSON response with the right status code — including `RecommendationService`'s own HTTP-agnostic domain exceptions (`GuitarNotFoundException`, `ToneProfileNotFoundException`), which it throws without knowing anything about HTTP at all.

See [`docs/architecture.md`](docs/architecture.md) for the full, continuously-updated log of *why* each of these decisions was made — simpler alternatives considered, tradeoffs, and the reasoning behind picking one approach over another.

## Domain Overview

| Entity | Purpose | Persisted? |
|---|---|---|
| `Guitar` | A guitar's name, pickup type (enum), and tuning | Yes — created via API only |
| `ToneProfile` | A target sound: genre, gain, and brightness (0–10 scale) | Yes — created via API only |
| `Equipment` | A catalog item — amplifier, cabinet, or effect — with genre/gain/brightness ratings and an optional preferred pickup type | Yes — **read-only**, seeded via Doctrine Fixtures, no write endpoint by design |
| `Recommendation` | The computed result of scoring `Equipment` against a `Guitar` + `ToneProfile` | **No** — a plain DTO (`RecommendationResult`), never written to the database; see `docs/architecture.md` for why |

There are deliberately **no foreign-key relationships** between these tables — the relationship between a `Guitar`, a `ToneProfile`, and a set of `Equipment` only exists transiently, computed at request time by `RecommendationService`. Not every conceptual relationship needs to be a database relationship.

`PickupType`, `Genre`, and `EquipmentType` are PHP 8.1+ backed enums rather than plain strings — enforced at the type level rather than relying solely on runtime validation, since mismatches in these values would otherwise silently corrupt the recommendation scoring.

## API Endpoints

All endpoints return/accept JSON.

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/v1/guitars` | List all guitars |
| `POST` | `/api/v1/guitars` | Create a guitar |
| `GET` | `/api/v1/guitars/{id}` | Get a single guitar |
| `GET` | `/api/v1/tone-profiles` | List all tone profiles |
| `POST` | `/api/v1/tone-profiles` | Create a tone profile |
| `GET` | `/api/v1/tone-profiles/{id}` | Get a single tone profile |
| `GET` | `/api/v1/equipment` | List the full equipment catalog (read-only, seeded) |
| `GET` | `/api/v1/equipment/{id}` | Get a single equipment item |
| `POST` | `/api/v1/recommendations` | Get a recommendation for a guitar + tone profile |

### Example: create a guitar

```http
POST /api/v1/guitars
Content-Type: application/json

{
  "name": "My Strat",
  "pickupType": "single-coil",
  "tuning": "E-standard"
}
```

`pickupType` must be one of: `single-coil`, `humbucker`, `p90`.

### Example: create a tone profile

```http
POST /api/v1/tone-profiles
Content-Type: application/json

{
  "name": "Blues Lead",
  "genre": "blues",
  "gain": 7,
  "brightness": 6
}
```

`genre` must be one of: `jazz`, `blues`, `rock`, `metal`. `gain`/`brightness` must be integers `0`–`10`.

### Example: request a recommendation

```http
POST /api/v1/recommendations
Content-Type: application/json

{
  "guitarId": 1,
  "toneProfileId": 2
}
```

```json
{
  "guitar": { "id": 1, "name": "My Strat", "pickupType": "single-coil", "tuning": "E-standard" },
  "toneProfile": { "id": 2, "name": "Blues Lead", "genre": "blues", "gain": 7, "brightness": 6 },
  "amplifier": { "id": 7, "name": "Fender Super Reverb", "type": "amplifier", "genre": "blues", "gainRating": 6, "brightnessRating": 7, "preferredPickupType": null },
  "cabinet": { "id": 8, "name": "Fender Openback 4x12", "type": "cabinet", "genre": "blues", "gainRating": 6, "brightnessRating": 8, "preferredPickupType": null },
  "effects": [
    { "id": 9, "name": "Boss BD-2 Blues Driver", "type": "effect", "genre": "blues", "gainRating": 6, "brightnessRating": 8, "preferredPickupType": null }
  ]
}
```

`amplifier`/`cabinet` can be `null` if no equipment of that type exists in the catalog. `effects` normally returns 2 items, but can return more if there's a genuine score tie at the cutoff — every tied item is included rather than arbitrarily dropping some.

### Error responses

All errors return JSON with the appropriate status code — `404` for a nonexistent resource, `422` for failed validation (with a per-field `errors` object), `400` for a malformed request body:

```json
{ "errors": { "tuning": "This value should not be blank." } }
```

## Design Patterns Used

| Pattern | Where | Why |
|---|---|---|
| Dependency Injection | Every controller/service | Symfony's container autowires dependencies by type-hint |
| Repository | `GuitarRepository`, `ToneProfileRepository`, `EquipmentRepository` | Doctrine's `ServiceEntityRepository`, centralizes data-access logic |
| Observer | `ApiExceptionListener` via `kernel.exception` | Symfony's `EventDispatcher` — a real Observer implementation |
| Value Object | `RecommendationResult` | Immutable (`readonly`), no identity, contrasted against mutable entities |
| DTO | `CreateGuitarRequest`, `CreateToneProfileRequest`, `CreateRecommendationRequest` | Decouples API input contract from persistence entities |
| Exception Translation | `GuitarNotFoundException`/`ToneProfileNotFoundException` → `ApiExceptionListener` | Keeps the service layer HTTP-agnostic and independently testable |

**Deliberately not built**: Strategy and Factory patterns for the recommendation algorithm. CLAUDE.md's own guidance — and this project's philosophy — is that Strategy should exist because business logic benefits from interchangeable algorithms, not as decoration. There's currently only one scoring algorithm; it would be extracted behind an interface only if/when a second, genuinely different one is needed.

Full reasoning for every decision — not just the patterns — lives in [`docs/architecture.md`](docs/architecture.md).

## Important Design Decisions (highlights)

- **`Recommendation` is a DTO, never persisted** — the MVP has no `GET /recommendations/{id}`, so there's no consumer for history yet.
- **`RecommendationService` resolves `guitarId`/`toneProfileId` itself** (not the controller), throwing HTTP-agnostic domain exceptions on failure — keeps it unit-testable with zero HTTP simulation.
- **Genre acts as a soft signal in scoring, not a hard filter** — confirmed intentional after real testing surfaced a cross-genre recommendation; genre isn't actually a hard compatibility constraint on real guitar gear.
- **`Equipment.preferredPickupType` is nullable** — decided from real guitarist domain knowledge: pickup type doesn't meaningfully correlate with genre.

See `docs/architecture.md` for the complete, ongoing log, including corrections made along the way (e.g. an early imprecise claim about why repository mocking requires an interface, corrected once actually tested).

## Testing Strategy

**Unit tests** (`tests/Service/RecommendationServiceTest.php`) cover `RecommendationService` — the only substantial business logic in this app — using PHPUnit test doubles instead of a real database:

- **State-based tests** (the majority): given fixed inputs, assert the method returns/throws the right thing. Uses `createStub()` for fake repositories that just hand back canned data.
- **One interaction-based test**: verifies `RecommendationService` calls `find()` on the correct repository with the *exact* ID it was given, using a real `createMock()` with `->expects()`/`->with()`. This catches a class of bug state-based tests can't — e.g. accidentally swapping `$guitarId` and `$toneProfileId` in a lookup call, which a stub (which returns the same canned value regardless of argument) wouldn't notice.

The default is state-based/classicist testing; interaction verification is reserved for cases where *how* a dependency is called is itself part of what "correct" means, not used as a blanket habit. See Martin Fowler's [Mocks Aren't Stubs](https://martinfowler.com/articles/mocksArentStubs.html) for the canonical treatment of this distinction.

**Not yet built**: API/integration tests (`WebTestCase`, testing full HTTP request/response cycles against real routes).

## Local Setup

Requires Docker and Composer.

```bash
composer install
docker compose up -d
docker compose exec php php bin/console doctrine:migrations:migrate
docker compose exec php php bin/console doctrine:fixtures:load
```

The API is then available at `http://localhost:8081/api/v1/...`.

Run tests:

```bash
docker compose exec php php bin/phpunit
```

## Docker

Three services, defined in `compose.yaml`:

- **`nginx`** — reverse proxy, the only service exposing a port to the host (`8081`)
- **`php`** — PHP 8.4-FPM running the Symfony application
- **`mysql`** — MySQL 8.0, data persisted in a named volume (`db_data`)

`nginx` talks to `php` over the internal Docker network (`php:9000`) — nothing on the host ever needs to reach `php` directly, which is why only `nginx` and `mysql` publish host ports.

## CI/CD

Not yet implemented. Planned to use GitHub Actions, gated on pull requests rather than running only on pushes to `main` — this project currently commits directly to `main` (a deliberate choice for a solo, time-boxed MVP), with a feature-branch + PR workflow planned specifically to coincide with adding CI, so the branch structure exists to serve a real purpose (a CI gate) rather than as process for its own sake.

## Future Improvements

- Strategy pattern for recommendation scoring — only if a second, genuinely different algorithm (e.g. per-genre weighting) is introduced
- Narrow repository interfaces (`EquipmentRepositoryInterface`, etc.) for `RecommendationService`, refactored from the current direct-concrete-class approach
- API/integration tests for all controllers
- Caching for recommendation calculations (repeated work on identical guitar/tone-profile pairs)
- Rate limiting
- `Genre` promoted from enum to a full entity, if/when genres need to be admin-manageable without a code deploy
- Optional `Recommendation` persistence, if a history/`GET /recommendations/{id}` feature is ever requested
- GitHub Actions CI/CD, PR-gated
