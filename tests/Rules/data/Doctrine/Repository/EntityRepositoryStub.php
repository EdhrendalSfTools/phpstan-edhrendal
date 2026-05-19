<?php

declare(strict_types=1);

// Stub — doctrine/orm is not a dependency of this package
namespace Doctrine\ORM;

class EntityRepository
{
    public function find(mixed $id): mixed
    {
        return null;
    }

    /** @return array<object> */
    public function findAll(): array
    {
        return [];
    }

    /** @return array<object> */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return [];
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): mixed
    {
        return null;
    }

    public function __call(string $method, array $arguments): mixed
    {
        return null;
    }
}
