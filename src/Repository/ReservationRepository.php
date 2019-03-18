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

    public function reservationNum($dateFrom,$dateTo,$roomId)
    {
        return (int) $this->createQueryBuilder('r')
            ->select('count(r.room)')
            ->where('r.datefrom=:datefrom')
            ->andWhere('r.dateto=:dateto')
            ->andWhere('r.room=:roomId')
            ->orWhere('r.datefrom=:datefrom')
            ->orWhere('r.dateto=:dateto')
            ->setParameter(':datefrom', $dateFrom)
            ->setParameter(':dateto', $dateTo)
            ->setParameter(':roomId', $roomId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAllArray($roomid)
    {
        return $this->createQueryBuilder('re')
            ->select('re')
            ->where('re.room = :roomid')
            ->andWhere('re.status = true')
            ->andWhere('re.declined = false')
            ->setParameter('roomid', $roomid)
            ->getQuery()
            ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
}