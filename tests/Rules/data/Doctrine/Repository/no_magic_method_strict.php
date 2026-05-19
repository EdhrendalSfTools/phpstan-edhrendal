<?php

declare(strict_types=1);

namespace Tests\EdhrendalSfTools\PHPStan\Rules\Doctrine\Repository\dataStrict;

use Doctrine\ORM\EntityRepository;

/** @extends EntityRepository<object> */
class ProductRepository extends EntityRepository {}

function testStrict(ProductRepository $repo): void
{
    $repo->find(1);
    $repo->findAll();
    $repo->findBy(['active' => true]);
}
