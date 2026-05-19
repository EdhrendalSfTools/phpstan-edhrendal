<?php

declare(strict_types=1);

namespace Tests\Edhrendal\PHPStan\Rules\Doctrine\Repository\data;

use Doctrine\ORM\EntityRepository;

/** @extends EntityRepository<object> */
class UserRepository extends EntityRepository
{
    /** @return array<object> */
    public function findByExplicitCustom(): array
    {
        return [];
    }

    public function findOneByExplicitCustom(): ?object
    {
        return null;
    }
}

function testNoMagicMethod(UserRepository $repo): void
{
    $repo->findBy(['username' => 'john']);
    $repo->findOneBy(['email' => 'john@example.com']);
    $repo->findByUsername('john');
    $repo->findByEmail('john@example.com');
    $repo->findOneByEmail('john@example.com');
    $repo->findByExplicitCustom();
    $repo->findOneByExplicitCustom();
    $repo->find(1);
    $repo->findAll();
}
