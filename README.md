# phpstan-edhrendal

PHPStan extension providing custom static analysis rules for Edhrendal projects.

Rules are **opt-in** — none are active by default. Include only the ones you need.

## Requirements

|                   | Version  |
|-------------------|----------|
| PHP               | `>= 8.5` |
| `phpstan/phpstan` | `~2.1.0` |

## Installation

> **Important — use a dedicated quality tools project**
>
> This package should **not** be installed as a dev dependency of your application.
> QA tools (PHPStan, PHP CS Fixer, etc.) belong in a separate Composer project,
> isolated from your application's dependency tree. This avoids version conflicts,
> keeps your application's `composer.lock` clean, and makes tool upgrades independent
> from application releases.
>
> A common convention is a `tools/` directory at the root of your repository with its
> own `composer.json`, or a dedicated repository shared across multiple projects.
>
> ```
> my-project/
> ├── tools/
> │   ├── composer.json   ← QA tools live here
> │   └── composer.lock
> ├── src/
> └── composer.json       ← application dependencies only
> ```

```bash
# From your QA tools project
composer require edhrendal-sf-tools/phpstan-edhrendal
```

## Available Rules

### `NoRepositoryMagicMethodRule` — Doctrine

Forbids the use of generic and magic Doctrine repository methods in favor of explicit, named methods declared directly in the repository class.

**Always reported:**

| Call                                    | Reason                                               |
|-----------------------------------------|------------------------------------------------------|
| `$repo->findBy(['field' => $value])`    | Generic criteria array — create a named method       |
| `$repo->findOneBy(['field' => $value])` | Generic criteria array — create a named method       |
| `$repo->findByFoo(…)`                   | Magic method via `__call`, not declared in the class |
| `$repo->findOneByFoo(…)`                | Magic method via `__call`, not declared in the class |

**Also reported in strict mode:**

| Call               |
|--------------------|
| `$repo->find($id)` |
| `$repo->findAll()` |

**Include:**

```neon
# phpstan.neon
includes:
    - vendor/edhrendal-sf-tools/phpstan-edhrendal/rules/doctrine/no-repository-magic-method.neon
```

**Strict mode (optional):**

```neon
# phpstan.neon
includes:
    - vendor/edhrendal-sf-tools/phpstan-edhrendal/rules/doctrine/no-repository-magic-method.neon

parameters:
    edhrendal:
        doctrine:
            repository:
                strict: true  # default: false
```

**Example — before / after:**

```php
// ❌ reported
$this->userRepository->findBy(['active' => true]);
$this->userRepository->findByEmail($email);

// ✅ ok
$this->userRepository->findActive();
$this->userRepository->findOneByEmail($email); // if declared in the class
```
