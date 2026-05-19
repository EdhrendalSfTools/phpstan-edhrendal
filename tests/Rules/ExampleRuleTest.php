<?php

declare(strict_types=1);

namespace Edhrendal\PHPStan\Tests\Rules;

use Edhrendal\PHPStan\Rules\ExampleRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ExampleRule>
 */
final class ExampleRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ExampleRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/example.php'], [
            // Add expected errors here:
            // ['Error message', line_number],
        ]);
    }
}
