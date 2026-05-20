<?php

declare(strict_types=1);

namespace EdhrendalSfTools\PHPStan\Tests\Rules\Symfony\Controller;

use EdhrendalSfTools\PHPStan\Rules\Symfony\Controller\ControllerInvokableRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ControllerInvokableRule>
 */
final class ControllerInvokableRuleExclusionTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ControllerInvokableRule(
            rootAllowedDomains: ['index', 'home'],
            excludedClassesFile: __DIR__ . '/../../data/Symfony/Controller/excluded_fqcns.php',
        );
    }

    public function testExcludedClassesAreNotReported(): void
    {
        $this->analyse(
            [__DIR__ . '/../../data/Symfony/Controller/excluded_controller.php'],
            []
        );
    }
}
