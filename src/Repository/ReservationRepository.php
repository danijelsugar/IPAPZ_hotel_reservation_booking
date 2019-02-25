<?php


namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function countReservations($id)
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.room)')
            ->where('r.room=:id')
            ->setParameter(':id', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }
}