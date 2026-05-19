<?php

declare(strict_types=1);

namespace Edhrendal\PHPStan\Tests\Rules\Doctrine\Repository;

use Edhrendal\PHPStan\Rules\Doctrine\Repository\NoRepositoryMagicMethodRule;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NoRepositoryMagicMethodRule>
 */
final class NoRepositoryMagicMethodStrictRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoRepositoryMagicMethodRule(
            reflectionProvider: static::getContainer()->getByType(ReflectionProvider::class),
            strict: true,
        );
    }

    public function testStrictForbiddenMethods(): void
    {
        $this->analyse(
            [
                __DIR__ . '/../../data/Doctrine/Repository/EntityRepositoryStub.php',
                __DIR__ . '/../../data/Doctrine/Repository/no_magic_method_strict.php',
            ],
            [
                [
                    'Calling find() on a Doctrine repository is forbidden in strict mode. Create an explicit method in the repository.',
                    14,
                ],
                [
                    'Calling findAll() on a Doctrine repository is forbidden in strict mode. Create an explicit method in the repository.',
                    15,
                ],
                [
                    'Calling findBy() on a Doctrine repository is forbidden. Create an explicit method in the repository.',
                    16,
                ],
            ]
        );
    }
}
