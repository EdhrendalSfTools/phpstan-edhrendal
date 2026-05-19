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
final class NoRepositoryMagicMethodRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoRepositoryMagicMethodRule(
            reflectionProvider: static::getContainer()->getByType(ReflectionProvider::class),
            strict: false,
        );
    }

    public function testForbiddenMethods(): void
    {
        $this->analyse(
            [
                __DIR__ . '/../../data/Doctrine/Repository/EntityRepositoryStub.php',
                __DIR__ . '/../../data/Doctrine/Repository/no_magic_method.php',
            ],
            [
                [
                    'Calling findBy() on a Doctrine repository is forbidden. Create an explicit method in the repository.',
                    26,
                ],
                [
                    'Calling findOneBy() on a Doctrine repository is forbidden. Create an explicit method in the repository.',
                    27,
                ],
                [
                    'Magic method findByUsername() is not explicitly defined in Tests\Edhrendal\PHPStan\Rules\Doctrine\Repository\data\UserRepository. Create an explicit method in the repository instead of relying on Doctrine magic.',
                    28,
                ],
                [
                    'Magic method findByEmail() is not explicitly defined in Tests\Edhrendal\PHPStan\Rules\Doctrine\Repository\data\UserRepository. Create an explicit method in the repository instead of relying on Doctrine magic.',
                    29,
                ],
                [
                    'Magic method findOneByEmail() is not explicitly defined in Tests\Edhrendal\PHPStan\Rules\Doctrine\Repository\data\UserRepository. Create an explicit method in the repository instead of relying on Doctrine magic.',
                    30,
                ],
            ]
        );
    }
}
