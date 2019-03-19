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
            ->where('r.datefrom between :datefrom and :dateto')
            ->orWhere('r.dateto between :datefrom and :dateto')
            ->orWhere('r.datefrom <= :datefrom and r.dateto >= :dateto')
            ->andWhere('r.room=:roomId')
            ->setParameter(':datefrom', $dateFrom)
            ->setParameter(':dateto', $dateTo)
            ->setParameter(':roomId', $roomId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function editReservationNum($dateFrom,$dateTo,$roomId,$id)
    {
        return (int) $this->createQueryBuilder('r')
            ->select('count(r.room)')
            ->where('r.datefrom between :datefrom and :dateto')
            ->orWhere('r.dateto between :datefrom and :dateto')
            ->orWhere('r.datefrom <= :datefrom and r.dateto >= :dateto')
            ->andWhere('r.room=:roomId')
            ->andWhere('r.id != :id')
            ->setParameter(':datefrom', $dateFrom)
            ->setParameter(':dateto', $dateTo)
            ->setParameter(':roomId', $roomId)
            ->setParameter(':id', $id)
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