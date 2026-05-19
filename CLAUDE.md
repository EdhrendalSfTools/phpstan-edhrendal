# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

This is a PHPStan extension package (`edhrendal/phpstan-edhrendal`) providing custom static analysis rules for Edhrendal projects. It targets PHP 8.1+ and PHPStan 2.x.

## Commands

```bash
# Install dependencies
composer install

# Run tests
vendor/bin/phpunit

# Run a single test file
vendor/bin/phpunit tests/Rules/ExampleRuleTest.php

# Run PHPStan on the extension's own source
vendor/bin/phpstan analyse --configuration phpstan.neon.dist
```

## Architecture

**Rule registration** — `extension.neon` is the PHPStan extension entry point. Every new rule class must be registered here as a service tagged with `phpstan.rules.rule`. The `composer.json` `extra.phpstan.includes` field points to this file so that Composer-installed consumers pick it up automatically.

**Rule pattern** — Rules live in `src/Rules/` and implement `PHPStan\Rules\Rule<TNode>`. Each rule declares which AST node type it handles via `getNodeType()` and performs analysis in `processNode()`. See `ExampleRule` for the skeleton.

**Test pattern** — Rule tests live in `tests/Rules/` and extend `PHPStan\Testing\RuleTestCase<TRule>`. Each test provides a fixture file under `tests/Rules/data/` containing PHP code that triggers (or intentionally does not trigger) the rule, and passes expected `[message, line]` pairs to `$this->analyse()`. The `phpstan/phpstan-phpunit` dev dependency provides PHPStan type support for this test harness.

**Namespace mapping:**
- `Edhrendal\PHPStan\` → `src/`
- `Edhrendal\PHPStan\Tests\` → `tests/`
