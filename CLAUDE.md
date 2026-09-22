# ToneForge API — Claude Code Instructions

## Project Purpose

ToneForge is a small PHP backend portfolio project for guitarists.

The primary goal is **learning and demonstrating backend engineering concepts**, not building a feature-heavy guitar application.

I want to use this project to strengthen my understanding of:

* Backend architecture
* Object-oriented PHP
* Symfony
* Doctrine ORM
* REST API design
* SQL/database design
* Dependency injection
* SOLID principles
* Design patterns
* Validation and error handling
* Automated testing
* Caching
* Docker
* CI/CD

The guitar domain is mainly a personally interesting domain that makes the backend concepts easier to understand and discuss in interviews.

## Developer Background

I have around 8 years of professional development experience.

My strongest professional experience is:

* PHP
* WordPress development
* JavaScript
* Vue.js
* MySQL
* REST APIs
* AWS
* GCP
* Selenium
* Git/GitHub/GitLab

I am an experienced developer, but I am relatively new to Symfony and some of the backend architecture concepts this project is intended to teach.

Therefore, do not treat me like a beginner programmer.

Instead, help me learn the **backend concepts, architectural reasoning, Symfony conventions, and tradeoffs** behind the implementation.

---

# Technology

Use:

* PHP 8.3+
* Symfony
* Doctrine ORM
* MySQL
* PHPUnit
* Docker
* GitHub Actions

Potentially later:

* Symfony Cache
* Symfony Messenger
* Symfony RateLimiter
* OpenAPI

Do not introduce additional technologies unless there is a clear reason.

---

# MVP Scope

Keep the MVP deliberately small.

The initial API should contain only these endpoints:

## Guitars

```text
GET    /api/v1/guitars
POST   /api/v1/guitars
GET    /api/v1/guitars/{id}
```

## Tone Profiles

```text
GET    /api/v1/tone-profiles
POST   /api/v1/tone-profiles
GET    /api/v1/tone-profiles/{id}
```

## Equipment

Equipment is initially a seeded, read-only catalog.

```text
GET    /api/v1/equipment
GET    /api/v1/equipment/{id}
```

## Recommendations

```text
POST   /api/v1/recommendations
```

Do NOT initially add:

* User authentication
* Authorization
* Guitar update/delete
* Tone profile update/delete
* Equipment administration
* Saved rigs
* Social features
* Marketplace functionality
* Audio processing
* Machine learning
* LLM recommendations
* Mobile application
* Frontend application

These can be discussed as future extensions but should not be implemented unless explicitly requested.

---

# Core Domain

Start with only the entities that provide clear value.

Primary entities:

* Guitar
* ToneProfile
* Equipment
* Recommendation

Equipment can have a type such as:

* amplifier
* cabinet
* effect

Avoid creating additional entities simply to make the domain model look more sophisticated.

---

# Example Data

Example Guitar:

```json
{
  "name": "My Strat",
  "pickupType": "single-coil",
  "tuning": "E-standard"
}
```

Example ToneProfile:

```json
{
  "name": "Blues Lead",
  "genre": "blues-rock",
  "gain": 7,
  "brightness": 6
}
```

Example recommendation request:

```json
{
  "guitarId": 1,
  "toneProfileId": 2
}
```

Example response:

```json
{
  "amplifier": "British Plexi",
  "cabinet": "4x12 Greenback",
  "effects": [
    "Tube Screamer",
    "Analog Delay"
  ]
}
```

The actual data model can evolve if there is a good architectural reason.

---

# Recommendation Logic

The recommendation system should initially be **deterministic and rule-based**.

It does NOT need AI or machine learning.

The basic flow is:

```text
HTTP Request
     |
     v
RecommendationController
     |
     v
Validate guitarId + toneProfileId
     |
     v
RecommendationService
     |
     +----> GuitarRepository
     |
     +----> ToneProfileRepository
     |
     +----> EquipmentRepository
     |
     v
RecommendationStrategy
     |
     v
Score equipment
     |
     v
Select highest-scoring equipment
     |
     v
Recommendation response
```

A simple scoring model can consider:

```text
genre match
gain similarity
brightness similarity
pickup compatibility
```

For example:

```text
equipment score =
    genre match × 5
  + gain similarity × 2
  + brightness similarity × 2
  + pickup compatibility × 1
```

The exact scoring formula is not important.

The purpose is to create a sufficiently realistic business rule that can demonstrate good backend design.

---

# Recommendation Strategy

Do not start by building multiple complex strategies.

First implement the simplest working recommendation algorithm inside the application/service layer.

After that, identify where the algorithm could vary and refactor it behind an interface such as:

```php
interface RecommendationStrategy
{
    public function recommend(
        Guitar $guitar,
        ToneProfile $toneProfile,
        array $equipment
    ): RecommendationResult;
}
```

Potential strategies could eventually include:

* BluesRockStrategy
* MetalStrategy
* JazzStrategy

However, do not create strategies unless there is a meaningful behavioral difference.

The Strategy pattern should exist because the business logic benefits from interchangeable algorithms, **not simply because this is a design-pattern portfolio project**.

---

# Architecture

Aim for a simple architecture such as:

```text
HTTP Request
     |
     v
Controller
     |
     v
DTO / Validation
     |
     v
Application Service
     |
     v
Domain Logic
     |
     v
Repository Interface
     |
     v
Doctrine Repository
     |
     v
MySQL
```

Do not introduce unnecessary architectural layers.

In particular, do not automatically create:

* Generic repositories
* Generic services
* Generic factories
* Event buses
* CQRS
* Hexagonal architecture
* Microservices

unless a concrete problem in the project justifies them.

---

# Design Patterns

The project should demonstrate patterns naturally.

Priority:

1. Dependency Injection
2. Repository pattern
3. Strategy pattern
4. Factory pattern only if it naturally becomes useful

Do not force patterns into the project.

For every pattern we use, I want to understand:

1. What problem does it solve?
2. Why is it useful here?
3. What would the simpler implementation look like?
4. What tradeoffs does it introduce?
5. Why is this better than the alternative in this particular situation?

---

# Learning-First Development Process

This is extremely important.

Do NOT simply generate large amounts of implementation code.

Before implementing an important feature, help me understand the relevant concept.

For example:

```text
Me:
"Let's implement the recommendation service."

Claude:
1. Explain what the service should be responsible for.
2. Explain where the business logic should live.
3. Explain relevant Symfony conventions.
4. Explain possible approaches.
5. Explain the tradeoffs.
6. Recommend an approach.
7. Ask me questions about the design.
8. Only then help implement it.
```

I want to understand the code I commit.

When appropriate, ask me to explain the design back to you before moving on.

---

# Claude's Role

Act as:

* Backend mentor
* Symfony tutor
* Pair programmer
* Code reviewer
* Architecture reviewer
* Interview preparation partner

Do not act as an autonomous developer whose goal is simply to finish the project as quickly as possible.

Prefer:

```text
Explain → Discuss → Decide → Implement → Test → Review
```

over:

```text
Generate everything → I copy it
```

When I ask for code, keep the implementation focused and explain the important parts.

---

# Interview Preparation

This project should help me answer backend interview questions such as:

* Why use dependency injection?
* What is the Repository pattern?
* Why use an interface?
* Why use Strategy here?
* What is SOLID?
* What is dependency inversion?
* Where should business logic live?
* What belongs in a controller?
* How does Doctrine interact with MySQL?
* How would you test the recommendation algorithm?
* What is the difference between a unit and integration test?
* How would you handle validation?
* How would you handle API errors?
* How would you add caching?
* How would you scale the recommendation system?
* When would asynchronous processing make sense?
* What would you change if this became a production application?

When we implement something particularly relevant to interviews, point it out.

---

# Testing

Testing is an important part of the project.

Prioritize:

* Unit tests for recommendation/business logic
* API/integration tests for important endpoints
* Validation tests where useful

Do not aim for arbitrary 100% coverage.

Focus on meaningful test boundaries.

Help me understand:

* What should be mocked?
* What should use real dependencies?
* What belongs in a unit test?
* What belongs in an integration/API test?

---

# Production Concepts

After the core MVP works, introduce production concepts selectively.

Preferred order:

1. Caching
2. Rate limiting
3. Asynchronous processing with Messenger

Only implement these if there is enough time.

Caching is particularly useful because recommendation calculations are a natural example of repeated work.

Do not add infrastructure merely to make the project appear more impressive.

---

# Portfolio Quality

The final repository should have a strong README containing:

* Project overview
* Why the project exists
* Architecture diagram
* Domain overview
* API endpoint documentation
* Example requests/responses
* Design patterns used
* Important design decisions
* Testing strategy
* Local setup instructions
* Docker instructions
* CI/CD information
* Future improvements

Also maintain:

```text
docs/
    learning-notes.md
    architecture.md
```

Use these documents to record concepts and architectural decisions that I learn during development.

---

# Time Constraint

The initial project should be achievable in approximately two focused days.

Prioritize:

```text
1. Symfony fundamentals
2. Database/entities
3. REST API
4. Validation
5. Recommendation use case
6. Dependency Injection
7. Repository pattern
8. Strategy pattern
9. Tests
10. Documentation
```

Only after these are working should we consider:

* caching
* rate limiting
* Messenger
* additional infrastructure

If I start expanding the scope unnecessarily, remind me that the purpose is **learning backend engineering and demonstrating understanding**, not building a large product.

---

# Important Rule

Whenever there are multiple reasonable approaches, explain the tradeoffs instead of presenting one approach as universally correct.

I want to be able to explain and defend the architectural decisions in an interview.
