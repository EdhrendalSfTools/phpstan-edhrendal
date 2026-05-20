<?php

declare(strict_types=1);

namespace EdhrendalSfTools\PHPStan\Tests\Rules\Symfony\Controller;

use EdhrendalSfTools\PHPStan\Rules\Symfony\Controller\ControllerInvokableRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ControllerInvokableRule>
 */
final class ControllerInvokableRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ControllerInvokableRule(
            rootAllowedDomains: ['index', 'home'],
        );
    }

    public function testValidControllers(): void
    {
        $this->analyse(
            [__DIR__ . '/../../data/Symfony/Controller/valid_controller.php'],
            []
        );
    }

    public function testNamingConvention(): void
    {
        $this->analyse(
            [__DIR__ . '/../../data/Symfony/Controller/invalid_naming.php'],
            [
                [
                    'Controller "UserController" does not follow the {Domain}{HttpMethod}Controller naming convention (e.g. UserGetController, PeriodeCalculPostController).',
                    7,
                ],
                [
                    'Controller "GetUserController" does not follow the {Domain}{HttpMethod}Controller naming convention (e.g. UserGetController, PeriodeCalculPostController).',
                    12,
                ],
            ]
        );
    }

    public function testMissingInvoke(): void
    {
        $this->analyse(
            [__DIR__ . '/../../data/Symfony/Controller/missing_invoke.php'],
            [
                [
                    'Controller "UserGetController" must implement a __invoke() method.',
                    7,
                ],
            ]
        );
    }

    public function testPublicMethods(): void
    {
        $this->analyse(
            [__DIR__ . '/../../data/Symfony/Controller/public_methods.php'],
            [
                [
                    'Controller "UserPostController" declares public method "configure()" which is not a magic method. Controllers must only expose __invoke().',
                    11,
                ],
                [
                    'Controller "UserPostController" declares public method "getHelper()" which is not a magic method. Controllers must only expose __invoke().',
                    13,
                ],
            ]
        );
    }

    public function testRootPlacement(): void
    {
        $this->analyse(
            [__DIR__ . '/../../data/Symfony/Controller/root_placement.php'],
            [
                [
                    'Controller "PeriodeGetController" must not be placed at the root of the Controller namespace. Move it to a sub-namespace (or add its domain to rootAllowedDomains if it is an index-like controller).',
                    7,
                ],
            ]
        );
    }
}
