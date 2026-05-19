<?php

declare(strict_types=1);

namespace EdhrendalSfTools\PHPStan\Rules\Doctrine\Repository;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Forbids the use of generic or magic Doctrine repository methods in favor of
 * explicit, named methods declared directly in the repository class.
 *
 * The following calls are always reported:
 *   - findBy(array $criteria)   — use a named method instead
 *   - findOneBy(array $criteria) — use a named method instead
 *   - findBy*(…) / findOneBy*(…) — Doctrine magic methods handled via __call
 *     that are NOT explicitly declared in the repository class
 *
 * In strict mode (edhrendal.doctrine.repository.strict: true), these are also reported:
 *   - find($id)
 *   - findAll()
 *
 * This rule is opt-in. Include it explicitly in your phpstan.neon:
 * ```neon
 * includes:
 *     - vendor/edhrendal-sf-tools/phpstan-edhrendal/rules/doctrine/no-repository-magic-method.neon
 *
 * parameters:
 *     edhrendal:
 *         doctrine:
 *             repository:
 *                 strict: true   # default: false
 * ```
 *
 * @implements Rule<Node\Expr\MethodCall>
 */
final class NoRepositoryMagicMethodRule implements Rule
{
    private const string ENTITY_REPOSITORY_CLASS = 'Doctrine\ORM\EntityRepository';

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly bool $strict = false,
    ) {}

    public function getNodeType(): string
    {
        return Node\Expr\MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->name instanceof Node\Identifier === false) {
            return [];
        }

        $methodName = $node->name->name;
        $classNames = $scope->getType($node->var)->getObjectClassNames();
        $className = $classNames[0] ?? '';

        if ($this->isDoctrineRepository($className) === false) {
            return [];
        }

        if ($this->isBaseForbidden(methodName: $methodName)) {
            return [
                RuleErrorBuilder::message(
                    sprintf('Calling %s() on a Doctrine repository is forbidden. Create an explicit method in the repository.', $methodName)
                )
                ->identifier('edhrendal.doctrine.repository.forbiddenMethod')
                ->build(),
            ];
        }

        if ($this->isStrictForbidden(methodName: $methodName)) {
            return [
                RuleErrorBuilder::message(
                    sprintf('Calling %s() on a Doctrine repository is forbidden in strict mode. Create an explicit method in the repository.', $methodName)
                )
                ->identifier('edhrendal.doctrine.repository.strictForbiddenMethod')
                ->build(),
            ];
        }

        if ($this->isMagicMethod(className: $className, methodName: $methodName)) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Magic method %s() is not explicitly defined in %s. Create an explicit method in the repository instead of relying on Doctrine magic.',
                        $methodName,
                        $className
                    )
                )
                    ->identifier('edhrendal.doctrine.repository.magicMethod')
                    ->build(),
            ];
        }

        return [];
    }

    private function isDoctrineRepository(string $className): bool
    {
        if ($this->reflectionProvider->hasClass($className) === false) {
            return false;
        }
        $reflection = $this->reflectionProvider->getClass($className);

        return $reflection->isSubclassOf(static::ENTITY_REPOSITORY_CLASS);
    }

    private function isBaseForbidden(string $methodName): bool
    {
        return in_array(
            needle: $methodName,
            haystack: [
                'findBy',
                'findOneBy',
            ],
            strict: true
        );
    }

    private function isStrictForbidden(string $methodName): bool
    {
        return $this->strict
            && in_array(
                needle: $methodName,
                haystack: [
                    'find',
                    'findAll',
                ],
                strict: true
            );
    }

    private function isMagicMethod(string $className, string $methodName): bool
    {
        // findOneBy must be checked before findBy — it's the longer prefix
        if (str_starts_with(haystack: $methodName, needle: 'findOneBy')) {
            $suffix = substr(string: $methodName, offset: 9);
        } elseif (str_starts_with(haystack: $methodName, needle: 'findBy')) {
            $suffix = substr(string: $methodName, offset: 6);
        } else {
            return false;
        }

        if ($suffix === '' || ctype_upper($suffix[0]) === false) {
            return false;
        }

        return $this->isExplicitlyDefinedBelowEntityRepository(
            className: $className,
            methodName: $methodName
        ) === false;
    }

    private function isExplicitlyDefinedBelowEntityRepository(string $className, string $methodName): bool
    {
        if ($this->reflectionProvider->hasClass($className) === false) {
            return false;
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        if ($classReflection->hasNativeMethod($methodName) === false) {
            return false;
        }

        $declaringClass = $classReflection->getNativeMethod($methodName)->getDeclaringClass();

        // The method is in user code when declared in a subclass of EntityRepository (not EntityRepository itself)
        return $declaringClass->getName() !== static::ENTITY_REPOSITORY_CLASS
            && $declaringClass->isSubclassOf(static::ENTITY_REPOSITORY_CLASS);
    }
}
