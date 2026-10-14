# Architecture Decisions

A running log of the real design decisions made while building ToneForge, kept so I can explain and defend them later (interview prep), not just describe what the code does. Updated as the project progresses — most recent decisions are added at the bottom of each section, or as new sections when a new area of the project starts.

Each entry aims to capture: **what problem it solves**, **the simpler alternative**, **the tradeoff**, and **why this was chosen here** — same framework CLAUDE.md asks for on every pattern.

---

## Design Patterns Used

Quick-reference index for interview prep. Full reasoning for each lives in the dated sections further down — this table just catalogs *which* patterns exist in the codebase, where, and their status. Kept up to date as new patterns are added or deferred ones get built.

| Pattern | Where | Status |
|---|---|---|
| Dependency Injection | Every controller/service takes its dependencies as typed constructor/method parameters, wired automatically by Symfony's container (e.g. `RecommendationService`'s 3 injected repositories) | ✅ Done |
| Repository (generic) | `GuitarRepository`, `ToneProfileRepository`, `EquipmentRepository` — Doctrine's `ServiceEntityRepository`, essentially free | ✅ Done |
| Repository (narrow interface, for testability) | Planned for `RecommendationService`'s unit tests — a small interface Doctrine's repository implements, so tests can substitute an in-memory fake with no real database | ⬜ Deferred until the first unit test needs it |
| Strategy | Would live in `RecommendationService`'s scoring algorithm, behind a `RecommendationStrategy` interface | ⬜ Deliberately not built — only one scoring algorithm exists; CLAUDE.md is explicit that this pattern earns its place only once there's a second, genuinely different algorithm to switch between |
| Factory | Would pair with Strategy, to select an algorithm at runtime (e.g. by genre) | ⬜ Deliberately not built, same reasoning as Strategy |
| Observer | `ApiExceptionListener`, via Symfony's `EventDispatcher`/`kernel.exception` event and `#[AsEventListener]` | ✅ Done |
| Value Object | `RecommendationResult` — `readonly` properties, constructor-populated, no identity, contrasted against the mutable/identity-bearing entities | ✅ Done |
| DTO | `CreateGuitarRequest`, `CreateToneProfileRequest`, `CreateRecommendationRequest` — decouples the API's input contract from persistence entities | ✅ Done |
| Exception Translation *(not classic GoF, but a recognized layering technique)* | `GuitarNotFoundException`/`ToneProfileNotFoundException`, thrown HTTP-agnostic from `RecommendationService`, mapped to a status code only inside `ApiExceptionListener` | ✅ Done |

---

## Project setup

### Docker: nginx + php-fpm split, not FrankenPHP
Symfony's modern default Docker recipe uses FrankenPHP (single container). Chose the traditional nginx + php-fpm two-container split instead — more config to write by hand, but it's the pattern most PHP shops (including prior WordPress-hosting experience) still run in production, so it's more broadly recognizable and maps onto existing ops knowledge.

### PHP 8.4 in the container, not 8.3
CLAUDE.md's stack specifies "PHP 8.3+". Attempting to install dependencies inside a PHP 8.3 container failed — current versions of `doctrine/collections`, `doctrine/orm`, etc. now require PHP ≥8.4. Bumped the container to 8.4 rather than pinning older Doctrine versions to force 8.3 compatibility; 8.4 still satisfies the "8.3+" floor.

### MySQL over the Doctrine recipe's default Postgres
`doctrine/doctrine-bundle`'s Flex recipe defaults `DATABASE_URL` and `compose.yaml` to Postgres. Manually corrected `.env`, `config/packages/doctrine.yaml`, and replaced the auto-generated `compose.yaml`/`compose.override.yaml` entirely, since the project's tech stack specifies MySQL.

### Migrations (`doctrine/doctrine-migrations-bundle`), not `doctrine:schema:update`
`schema:update` applies schema changes directly with no history and no rollback path. Migrations give a versioned, reviewable, reversible record of every schema change — the SQL is generated into a file and read *before* it's ever run, not applied blindly.

---

## Domain modeling

### `PickupType`, `Genre`, `EquipmentType` as PHP backed enums, not plain strings
**Problem:** plain strings for values compared or matched programmatically (not just displayed) risk silent typo/casing mismatches with no error — especially `Genre`, which is directly string-compared between `ToneProfile` and `Equipment` in the future scoring algorithm (`genre match × 5`). A `PickupType`/`Genre` typo wouldn't crash anything; it would just silently produce a wrong recommendation score.
**Simpler alternative:** plain `string` columns, validated only by Symfony Validator `#[Assert\Choice]`.
**Tradeoff:** enums are a closed set — adding a new genre later is a code change, not just new data. `tuning` was deliberately kept as a plain string instead, since tunings are a much more open-ended set than pickup types or genres.
**Why chosen:** the cost of a silent mismatch in scoring logic outweighs the flexibility cost, for a small, known, MVP-scoped set of values.

### `Genre` as an enum now, not a full entity — but flagged as a real future migration
Considered making `Genre` a full Doctrine entity/table (admin-manageable, no code deploy needed to add a genre). Deferred — no concrete need for that flexibility yet, only a hypothetical one; CLAUDE.md's own philosophy is to build the simple version until a real need appears. Documented that promoting enum → entity later isn't a small edit: it requires a new `Genre` entity, changing `ToneProfile.genre`/`Equipment.genre` from a scalar column to a `ManyToOne` relation, *and* a data migration converting every existing enum string value into a foreign key — not just a schema change.

### `Equipment.preferredPickupType` is nullable
Decided from real domain knowledge (guitarist's own judgment): pickup type doesn't meaningfully correlate with genre — you can play jazz or metal on single coils. Nullable lets equipment opt out of that scoring dimension entirely rather than forcing an arbitrary/meaningless assignment (e.g. on a delay pedal or a cabinet). Consequence: the future scoring algorithm's "pickup compatibility" comparison (`$equipment->getPreferredPickupType() === $guitar->getPickupType()`) naturally evaluates to `false`/no-bonus when null, with no special-case branching needed — a `PickupType` enum instance can never equal `null`.

### Entities have no constructor; fluent setters instead
Standard idiomatic Symfony/Doctrine shape (matches what `make:entity` generates), not just "how Symfony does it" — Doctrine hydrates entities from the database via reflection, bypassing constructors entirely, so a constructor requiring all fields up front doesn't compose well with that hydration path. Tradeoff: catches a forgotten field later (at flush time or first property read) rather than at construction time.

### `RecommendationResult` DTO *does* use a constructor (`public readonly` properties)
Opposite shape from the entities, deliberately — this is a plain value object, never hydrated by Doctrine, so the constraint that ruled out constructors for entities doesn't apply. A constructor requiring all fields, plus `readonly`, makes "this is a finished computation result, never mutated after creation" explicit at the language level.

---

## Data access

### Repository pattern: Doctrine's built-in `ServiceEntityRepository` for `Guitar`/`ToneProfile`/`Equipment`
Low-ceremony version, effectively free via Doctrine — `find()`/`findAll()`/`findBy()` provided automatically. No narrow interface built for these, since nothing currently needs to swap their data source (no test currently requires mocking them out).

### Narrow repository interface — deferred, then resolved as "Option A now, Option B later" once actually at the first unit test
Originally reasoned (before ever writing a test) that the Doctrine repositories "can't be cleanly faked" since they require a real `ManagerRegistry` to construct — **that framing turned out to be imprecise and worth correcting**: PHPUnit's `createMock()`/`createStub()` never call the real constructor at all, so mocking the concrete Doctrine repository classes directly works completely fine, database or not. The *actual* justification for a narrow interface is Dependency Inversion / Interface Segregation, not raw testability — `RecommendationService`'s constructor depending on the full `EquipmentRepository` (dozens of inherited Doctrine methods it never calls) is less honest about its real needs than depending on a 1-method interface would be.
**Decision:** given the choice explicitly, chose the straightforward option first — mock the concrete Doctrine repository classes directly (no new interface files) — get a real working test, then refactor to the narrow-interface version afterward once there's a concrete "before" to compare against, rather than deciding the abstraction upfront with nothing built yet.

---

## Testing

### `createStub()`, not `createMock()`, for `RecommendationServiceTest`'s fake repositories
PHPUnit distinguishes two kinds of test doubles: a **mock** is for verifying *interactions* — "`find()` must be called exactly once, with this exact argument," failing the test if that expectation isn't met. A **stub** is for simply providing a canned return value, with no caring about call count or arguments. `createMock()` creates the interaction-verifying kind; using it without ever setting an expectation (`->expects(...)`) triggers a PHPUnit notice, since it means the stricter tool was built but never actually used as anything more than a stub. Since `RecommendationServiceTest` only needs canned return values (e.g. "guitar lookup returns null") and never cares how many times a method is called, `createStub()` is the accurate, notice-free choice.

### Default to state-based testing; reserve interaction verification (`createMock()` + `expects()`) for real gaps
Every stub in this suite returns the same canned value regardless of what argument it's called with — which means a bug that swapped `$guitarId`/`$toneProfileId` in a repository lookup would pass every state-based test undetected. Rather than mocking every dependency defensively, the deliberate choice was to default to `createStub()`/state-based assertions (the "classicist" school — see Martin Fowler's *Mocks Aren't Stubs*), and reach for a real `createMock()` with `->expects()`/`->with()` only where the *interaction itself* is a genuine correctness property worth guaranteeing.

### One test per independently-breakable behavior — applied to entity setters, not just services
`GuitarTest`/`ToneProfileTest`/`EquipmentTest` each split their setter/getter checks into one test method per property, rather than one combined round-trip test. Reason beyond readability: PHPUnit stops executing a test method at its first failed assertion, so a combined test would only ever reveal the *first* broken property per run, silently hiding any others. Separate test methods are fully independent, so one run surfaces every broken property at once. The same "split when independently observable through the public interface" judgment was deliberately *not* applied to `RecommendationService`'s private `score()` method — isolating each scoring factor would require either making it public (letting the test suite dictate production API shape) or disproportionately contrived equipment fixtures.

### DTO validation constraints get their own tests, using a standalone `Validator` — not just manual curl checks
Modeled on `symfony/demo`'s `ValidatorTest` pattern (valid case + every failure mode, one test per scenario). Real motivating bug: the `#[Assert\NotBlank]` → `#[Assert\NotNull]` fix for `gain`/`brightness` (`NotBlank` incorrectly treats `0` as blank) was only caught once, by hand, via curl — nothing locked it in as a permanent regression test. `Symfony\Component\Validator\Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator()` builds a real validator with zero kernel/HTTP/database involvement, making this a genuine unit test of the DTOs' declared constraints, not just trusting the attributes are correct by inspection.

### Test-building helper methods, extracted after reading `symfony/demo`'s `TagArrayToStringTransformerTest`
That test's `getMockedTransformer()` helper (builds and returns the pre-wired system-under-test, parameterized for what varies per test) was the direct model for `RecommendationServiceTest`'s `buildGuitar()`/`buildToneProfile()`/`buildEquipment()` helpers — eliminating the repeated "construct entity, create stub, configure return value" boilerplate that existed across every test method before the refactor.

### Integration tests: a hand-written transaction-per-test base class (`ApiTestCase`), not `dama/doctrine-test-bundle`
**Problem:** integration tests hit a real database, so something has to stop one test's writes from polluting the next test or the dev database permanently. `dama/doctrine-test-bundle` is the standard Symfony-ecosystem tool for this.
**What actually happened:** `composer require --dev dama/doctrine-test-bundle` could not resolve on this project. No DAMA version is compatible with the exact combination already installed (`doctrine-bundle ^3.3`, `symfony/cache` pinned at `7.2.9`) — older DAMA versions want `doctrine-bundle ^2.x`, newer ones want `symfony/cache ^7.3+`. Attempting `-W` to let Composer upgrade the conflicting packages then hit a second wall: Composer's security-advisory policy blocked loading *any* version of several already-installed core Symfony packages, not something resolved by version-bumping alone.
**Decision:** rather than disabling Composer's advisory-blocking policy project-wide to force the bundle in — a blunt fix with security implications a dev-dependency isn't worth trading for — write the isolation mechanism by hand instead:
```php
protected function setUp(): void
{
    parent::setUp();
    $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    $this->entityManager->getConnection()->beginTransaction();
}

protected function tearDown(): void
{
    $this->entityManager->getConnection()->rollBack();
    parent::tearDown();
}
```
**This is not a lesser substitute — it's the same mechanism DAMA uses internally**, confirmed directly against the bundle's own README (`dmaicher/doctrine-test-bundle`, the actual GitHub repo behind the `dama/` Composer package name): *"With the help of a PHPUnit extension class it will begin a transaction before every testcase and roll it back again after the test finished for all configured DBAL connections."* DAMA wraps this in a `StaticDriver` + PHPUnit extension for convenience and handles edge cases (nested transactions, cross-connection coordination) a hand-rolled version doesn't automatically cover — but for this project's single-connection, non-nested-transaction use, the manual version achieves the identical core guarantee with zero new dependencies and zero version conflicts.

### Two non-obvious bugs hit while setting up integration tests
1. **The test database name doubled itself (`app_test_test`)** — `config/packages/doctrine.yaml`'s `when@test` block (present since the very first Doctrine install, via Symfony's own recipe) auto-appends `_test` to the configured database name whenever `APP_ENV=test` — which `phpunit.dist.xml` always forces. Manually pointing `DATABASE_URL` at a database already named `app_test` for test runs caused Doctrine to append its own `_test` on top. Fix: never override `DATABASE_URL`'s base name for `phpunit` runs — keep it as the plain `app` already set in `compose.yaml`, and let the suffix rule turn that into `app_test` automatically. (The one-off setup commands — creating/migrating/seeding `app_test` itself — correctly needed an explicit override, since those ran outside `APP_ENV=test` and the suffix rule doesn't apply there.)
2. **`LogicException: the kernel should only be booted once`** — `ApiTestCase::setUp()` called `self::getContainer()` (which boots the kernel to resolve `EntityManagerInterface`) before any test method called `static::createClient()`. `createClient()` requires being the thing that boots the kernel, specifically configured for HTTP-client use, and refuses to run if something already booted it first. Fix: call `createClient()` inside `setUp()` itself, store it as a shared `$this->client` property, and have every test method reuse that one client — also satisfies the separate constraint that `createClient()` can only be called once per test at all.

### The big one: a `POST` then an immediate `GET` in the same test couldn't see each other's data — two stacked root causes
**Symptom:** `POST /guitars` returned `201` with correct data; a follow-up `GET /guitars/{id}` in the *same test* returned a real `404` from `ApiExceptionListener`. Confirmed the written row was genuinely rolled back cleanly afterward (no leftover residue in `app_test.guitar`), proving the write really was part of a tracked transaction — so the read, not the write, was looking at the wrong state.

**Root cause 1 — `KernelBrowser` fully reboots the kernel between requests by default.** Confirmed directly in `vendor/symfony/framework-bundle/KernelBrowser.php`: `doRequest()` runs `$this->kernel->boot(); $this->kernel->shutdown();` before every second-and-later request unless told otherwise (`private bool $reboot = true;`). A full reboot destroys and rebuilds the *entire* container — a new `EntityManager`, a new connection — regardless of any tag configuration. This is a different, more drastic mechanism than Symfony's lighter `services_resetter`/`kernel.reset`-tag reset, and it runs first.

**Root cause 2 — even without the reboot, Doctrine's `Registry` is auto-tagged `kernel.reset`.** `FrameworkExtension` calls `$container->registerForAutoconfiguration(ResetInterface::class)`, and `Doctrine\Bundle\DoctrineBundle\Registry implements ResetInterface` — so it gets swept into Symfony's lighter between-request reset too (confirmed the explicit tag directly in `doctrine-bundle/config/dbal.php`). This is the same mechanism `dama/doctrine-test-bundle`'s `StaticDriver` is specifically built to survive.

**The fix needed both pieces together, in that order:**
1. `$this->client->disableReboot();` in `ApiTestCase::setUp()` — stops the full container rebuild, downgrading subsequent requests to the lighter reset path.
2. A compiler pass in `src/Kernel.php` (`Kernel implements CompilerPassInterface`), removing Doctrine's `kernel.reset` tag specifically in the `test` environment — confirmed as Symfony's own officially documented technique for exactly this problem (`symfony.com/doc/current/testing.html`), not an invented workaround.

Neither fix alone was sufficient — `clearTag()` had *zero* observable effect on its own (confirmed even after clearing the compiled container cache) because the reboot bypasses the tag-based reset mechanism entirely; only once the reboot was disabled did the tag removal become relevant at all.

**Debugging method worth noting for its own sake:** every step here was confirmed against real vendor source (`Kernel.php`, `KernelBrowser.php`, `FrameworkExtension.php`, `doctrine-bundle`'s config, the official Symfony testing docs) rather than guessed from memory — including one wrong turn (assuming the compiler-pass fix alone would work) caught by querying the actual `app_test` database directly rather than re-reasoning abstractly. We now understand *concretely* why a Symfony integration test can't naively assume two requests in one test share database state, where DAMA's `StaticDriver` specifically papers over both of these problems, and what each layer of the fix does independently.

---

## API layer — requests, validation, responses

### Request DTOs (`CreateGuitarRequest`, `CreateToneProfileRequest`), not binding JSON directly onto entities
**Problem:** binding incoming JSON straight onto a Doctrine entity couples the API's input contract to the persistence model, and risks mass assignment (a client setting a field that should never be client-settable, if the entity ever grows one).
**Simpler alternative:** deserialize JSON directly into the entity, validate the entity itself.
**Tradeoff:** for these specific small entities, the mass-assignment risk is close to theoretical today — the DTO is more ceremony than strictly necessary *right now*.
**Why chosen:** the pattern is already in place the moment it does matter, and matches CLAUDE.md's own architecture diagram, which names "DTO / Validation" as its own layer.

### DTO properties are nullable with `null` defaults, even for business-required fields
If a client's JSON omits a field entirely, Symfony's Serializer leaves the corresponding property untouched. A non-nullable typed property with no default would be left *uninitialized*, and reading it later throws an uncontrolled fatal `Error` — not a clean validation response. Making every DTO property nullable turns a missing field into a normal `null` value, letting `#[Assert\NotBlank]`/`#[Assert\NotNull]` catch it as a proper, controlled `422` instead.

### `#[Assert\NotNull]`, not `#[Assert\NotBlank]`, for numeric fields (`gain`, `brightness`)
Real bug caught during review: `NotBlank` treats `0` as "blank" (falsy in PHP), which would incorrectly reject a legitimately zero value on a 0–10 scale. `NotNull` only rejects a genuinely missing value. Confirmed empirically — a `gain: 0` submission was correctly accepted (`201`) only after switching to `NotNull`.

### `gain`/`brightness` range: 0–10, enforced via `#[Assert\Range]`
Matches the scale implied by CLAUDE.md's own example values (`gain: 7, brightness: 6`). Enforced at the DTO/Validator layer, not the database — the `Guitar`/`ToneProfile`/`Equipment` migrations themselves have no `CHECK` constraint on these columns, mirroring the same no-DB-level-enforcement tradeoff already accepted for enum columns (see below).

### `POST /guitars` built manually first, then refactored to `#[MapRequestPayload]`
Built the manual version first (`Request` + `SerializerInterface::deserialize` + `ValidatorInterface::validate` + manual violation formatting) specifically to see every step before reaching for the framework shortcut. Refactoring to `#[MapRequestPayload]` afterward surfaced a real, non-obvious behavioral difference (not just "same behavior, less code"): the manual version treated an invalid enum value as a deserialization failure (`400`), while `#[MapRequestPayload]`'s `RequestPayloadValueResolver` collects type/denormalization errors and merges them with regular validation violations, returning `422` instead — confirmed by reading the resolver's actual source, then verified empirically via curl. `ToneProfileController` and future controllers use `#[MapRequestPayload]` directly, since this comparison only needed to happen once.

### No `PUT`/`PATCH`, no `Guitar`/`ToneProfile` update or delete
Per CLAUDE.md's explicit MVP scope — not an oversight, a deliberate scope boundary.

### `Equipment` has no `POST`/write endpoints at all
It's a seeded, read-only catalog per CLAUDE.md's spec. The only way data enters that table is via `DoctrineFixturesBundle` (`src/DataFixtures/AppFixtures.php`), a dev-only package with zero production footprint.

---

## Error handling

### `ApiExceptionListener` — originally built to fix an HTML-vs-JSON gap, not for testability
Symfony's default exception handling renders HTML error pages even for JSON API requests, discovered concretely twice: once via `GuitarController::show()`'s 404 (status code checked, body never inspected at the time), and again via a `POST /guitars` validation failure where the HTML body and a debug-only `X-Debug-Exception` header were actually seen. Fixed with a single `#[AsEventListener]`-attributed class listening to `kernel.exception`, scoped to `/api/*` paths, mapping `HttpExceptionInterface` exceptions to their real status code and unwrapping `ValidationFailedException` (from `#[MapRequestPayload]`'s 422s) into the same structured `{"errors": {"field": "message"}}` shape the manual `POST /guitars` implementation originally produced by hand. Deliberately falls back to a generic `"Internal server error"` (never the real exception message) for anything that isn't an `HttpExceptionInterface`, to avoid leaking internals the way the dev-only debug header does.

### Domain exceptions for `RecommendationService`'s not-found cases, translated to HTTP at the `ApiExceptionListener` boundary — not thrown directly as `NotFoundHttpException`
This is the same original architecture-diagram decision from the initial planning session (`RecommendationService` takes `guitarId`/`toneProfileId`, resolves them itself, and is responsible for "does this exist" as a business-validation concern) revisited and confirmed once actually reached in implementation.
**Problem:** if `RecommendationService` threw Symfony's `NotFoundHttpException` directly, the service file would need `use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;` — a hard dependency on the HTTP Kernel component inside a class that's meant to represent pure business logic. It also means a unit test asserting this behavior is really testing "does this produce an HTTP concept correctly," not "does the business rule hold."
**Simpler alternative:** `RecommendationController` resolves `Guitar`/`ToneProfile` itself (same `find($id)` + `createNotFoundException()` pattern already used in `GuitarController`/`ToneProfileController`), and `RecommendationService::recommend()` just takes already-resolved entities as parameters.
**Tradeoff:** the domain-exception version needs one small addition to already-existing infrastructure (`ApiExceptionListener` gains an `instanceof GuitarNotFoundException => 404`-style branch) instead of zero new plumbing; `RecommendationService` needs 3 repository dependencies instead of 1.
**Why chosen:** Symfony itself has no official position on this either way (its own "Best Practices" docs are pragmatic, not prescriptive here) — this was chosen specifically because the project's stated purpose is demonstrating backend architecture reasoning for interview readiness, and this gives a genuinely testable service (`$this->expectException(GuitarNotFoundException::class)`, zero HTTP simulation needed) plus a concrete, inspectable example of a domain/delivery-layer boundary — not because it's "the" correct Symfony way.

---

## Recommendation design (in progress)

### `RecommendationResult` holds full `Equipment` entities, not bare name strings
CLAUDE.md's own example response shows plain strings (`"amplifier": "British Plexi"`). Chose to hold the actual `Equipment` entities instead, so the controller's `$this->json($result)` reuses the exact same serialization already built for `EquipmentController` — richer output (genre, ratings, etc.) for no extra code, at the cost of diverging from the literal documented example shape.

### Genre acts as a soft signal in scoring, not a hard filter — confirmed intentional after real testing
Discovered empirically while testing the expanded fixture catalog: a `blues` `ToneProfile` recommendation included `Ibanez TubeScreamer TS-9` (tagged `genre: rock`) ahead of blues-tagged effects, because a near-perfect gain/brightness match (`gain similarity × 2` and `brightness similarity × 2` can each contribute up to 20 points) outweighed the flat `genre match × 5` bonus other candidates had. Not a bug — a direct, correct consequence of CLAUDE.md's own specified weights.
**Decision:** keep the weights as-is. Confirmed deliberately, backed by real domain reasoning (guitarist's own judgment, consistent with the earlier `preferredPickupType` nullability decision): genre isn't actually a hard compatibility constraint on real guitar gear — you can legitimately play metal on an Ibanez Artcore. A hard genre filter would be *less* realistic than letting a strong numeric (gain/brightness) match outweigh a genre mismatch, so the emergent "soft signal" behavior is treated as correct, not something to fix.

### `amplifier`/`cabinet` stay singular; only `effects` can exceed its target count on a tie
Considered making every category resolve to an array to handle scoring ties uniformly. Kept `amplifier`/`cabinet` as single values (first-found wins on a tie — implemented via strictly-greater-than comparison while iterating, so an earlier item is never displaced by a later equal score) to match CLAUDE.md's documented response shape and avoid a bigger structural change for a rare edge case with this small a catalog. `effects` (already a list, target of 2) can return more than 2 if there's a genuine tie at the cutoff boundary — decided this was worth the small amount of extra logic, since it's already a list-typed field.

### `RecommendationController` never resolves `Guitar`/`ToneProfile` itself — unlike `GuitarController`/`ToneProfileController`'s `show()` methods
Deliberate asymmetry: `GuitarController::show()`/`ToneProfileController::show()` call `$repository->find($id)` directly because there's no service layer between them and the repository. `RecommendationController` has one (`RecommendationService`), and per the domain-exception decision above, that service owns "does this ID resolve to a real entity" as a business-validation concern. So `RecommendationController` just passes the validated DTO's raw `guitarId`/`toneProfileId` straight through to `RecommendationService::recommend()` and never touches a repository at all — the *only* controller in the project that doesn't. `CreateRecommendationRequest` itself only carries the two IDs (matching CLAUDE.md's example request body), not full `Guitar`/`ToneProfile` objects, since that's literally all the client ever sends.

*(To be continued as `RecommendationController`, tests, and any Strategy/Factory pattern work are built.)*
