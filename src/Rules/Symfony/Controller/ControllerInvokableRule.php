<?php

declare(strict_types=1);

namespace EdhrendalSfTools\PHPStan\Rules\Symfony\Controller;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Enforces Symfony invokable controller conventions:
 *   1. Naming: {Domain}{HttpMethod}Controller (e.g. UserListGetController)
 *   2. Must implement __invoke()
 *   3. No public methods except magic methods (those starting with __)
 *   4. Must not be at the root of the Controller namespace, except for
 *      index-like controllers whose domain matches rootAllowedDomains
 *
 * This rule is opt-in. Include it explicitly in your phpstan.neon:
 * ```neon
 * includes:
 *     - vendor/edhrendal-sf-tools/phpstan-edhrendal/rules/symfony/controller-invokable.neon
 *
 * parameters:
 *     edhrendal:
 *         symfony:
 *             controller:
 *                 rootAllowedDomains:
 *                     - index
 *                     - home
 *                 excludedClassesFile: null  # absolute path, or %rootDir%/path/to/file.php
 * ```
 *
 * excludedClassesFile — PHP file returning a string[] of FQCNs to skip entirely:
 * ```php
 * <?php
 * return [
 *     App\Controller\SomeLegacyController::class,
 * ];
 * ```
 *
 * @implements Rule<Node\Stmt\Class_>
 */
final class ControllerInvokableRule implements Rule
{
    private const array HTTP_METHODS = [
        'Get', 'Post', 'Put', 'Patch', 'Delete', 'Head', 'Options', 'Connect', 'Trace', 'Any',
    ];

    /** @var list<string>|null */
    private ?array $excludedClasses = null;

    /**
     * @param list<string> $rootAllowedDomains
     */
    public function __construct(
        private readonly array $rootAllowedDomains = ['index', 'home'],
        private readonly ?string $excludedClassesFile = null,
    ) {}

    public function getNodeType(): string
    {
        return Node\Stmt\Class_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->name === null) {
            return [];
        }

        $className = $node->name->name;

        if (str_ends_with($className, 'Controller') === false) {
            return [];
        }

        $namespace = $scope->getNamespace();
        $fqcn = $namespace !== null ? $namespace . '\\' . $className : $className;

        if ($this->isExcluded($fqcn) === true) {
            return [];
        }

        $errors = [];

        if ($this->matchesNamingConvention($className) === false) {
            $errors[] = RuleErrorBuilder::message(
                sprintf(
                    'Controller "%s" does not follow the {Domain}{HttpMethod}Controller naming convention (e.g. UserGetController, PeriodeCalculPostController).',
                    $className
                )
            )
                ->identifier('edhrendal.symfony.controller.naming')
                ->build();
        }

        $hasInvoke = false;
        $publicNonMagicMethods = [];

        foreach ($node->getMethods() as $method) {
            $methodName = $method->name->name;
            if ($methodName === '__invoke') {
                $hasInvoke = true;
            }
            if ($method->isPublic() === true && str_starts_with($methodName, '__') === false) {
                $publicNonMagicMethods[] = $method;
            }
        }

        if ($hasInvoke === false) {
            $errors[] = RuleErrorBuilder::message(
                sprintf('Controller "%s" must implement a __invoke() method.', $className)
            )
                ->identifier('edhrendal.symfony.controller.missingInvoke')
                ->build();
        }

        foreach ($publicNonMagicMethods as $method) {
            $errors[] = RuleErrorBuilder::message(
                sprintf(
                    'Controller "%s" declares public method "%s()" which is not a magic method. Controllers must only expose __invoke().',
                    $className,
                    $method->name->name
                )
            )
                ->identifier('edhrendal.symfony.controller.publicMethod')
                ->line($method->getStartLine())
                ->build();
        }

        if ($this->isAtControllerRoot($namespace)) {
            $domain = $this->extractDomain($className);
            if ($domain === null || $this->isDomainRootAllowed($domain) === false) {
                $errors[] = RuleErrorBuilder::message(
                    sprintf(
                        'Controller "%s" must not be placed at the root of the Controller namespace. Move it to a sub-namespace (or add its domain to rootAllowedDomains if it is an index-like controller).',
                        $className
                    )
                )
                    ->identifier('edhrendal.symfony.controller.rootPlacement')
                    ->build();
            }
        }

        return $errors;
    }

    private function matchesNamingConvention(string $className): bool
    {
        return (bool) preg_match($this->buildNamingPattern(), $className);
    }

    private function extractDomain(string $className): ?string
    {
        if (preg_match($this->buildNamingPattern(), $className, $matches) === 0) {
            return null;
        }

        return $matches[1];
    }

    private function buildNamingPattern(): string
    {
        return '/^([A-Z].+)(' . implode('|', self::HTTP_METHODS) . ')Controller$/';
    }

    private function isDomainRootAllowed(string $domain): bool
    {
        $lowerDomain = strtolower($domain);

        foreach ($this->rootAllowedDomains as $allowed) {
            if ($lowerDomain === strtolower($allowed)) {
                return true;
            }
        }

        return false;
    }

    private function isAtControllerRoot(?string $namespace): bool
    {
        if ($namespace === null) {
            return false;
        }

        $parts = explode('\\', $namespace);
        $controllerIndex = array_search('Controller', $parts, strict: true);

        if ($controllerIndex === false) {
            return false;
        }

        // The class is at the root when Controller is the last namespace segment
        return $controllerIndex === count($parts) - 1;
    }

    private function isExcluded(string $fqcn): bool
    {
        return in_array($fqcn, $this->loadExcludedClasses(), strict: true);
    }

    /** @return list<string> */
    private function loadExcludedClasses(): array
    {
        if ($this->excludedClasses !== null) {
            return $this->excludedClasses;
        }

        if ($this->excludedClassesFile === null || is_file($this->excludedClassesFile) === false) {
            return $this->excludedClasses = [];
        }

        $result = require $this->excludedClassesFile;

        return $this->excludedClasses = is_array($result)
            ? array_values(array_filter($result, 'is_string'))
            : [];
    }
}
