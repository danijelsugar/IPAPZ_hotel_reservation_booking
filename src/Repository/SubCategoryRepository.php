<?php

namespace App\Repository;

use App\Entity\SubCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class SubCategoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SubCategory::class);
    }

    public function getAll()
    {
        return $this->createQueryBuilder('s')
            ->getQuery()
            ->getResult();
    }
}