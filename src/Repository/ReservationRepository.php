<?php


namespace App\Repository;

use App\Entity\Category;
use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\Expr;

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

    public function reservationNum($dateFrom, $dateTo, $roomId)
    {
        return (int)$this->createQueryBuilder('r')
            ->select('count(r.room)')
            ->where('r.datefrom between :datefrom and :dateto')
            ->orWhere('r.dateto between :datefrom and :dateto')
            ->orWhere('r.datefrom <= :datefrom and r.dateto >= :dateto')
            ->andWhere('r.status=1')
            ->andWhere('r.room=:roomId')
            ->setParameter(':datefrom', $dateFrom)
            ->setParameter(':dateto', $dateTo)
            ->setParameter(':roomId', $roomId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function editReservationNum($dateFrom, $dateTo, $roomId, $id)
    {
        return (int)$this->createQueryBuilder('r')
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

    public function orderReservations($condition)
    {
        return $this->createQueryBuilder('r')
            ->select('r.id, r.datefrom,r.dateto,ro.id as room,c.name,u.email,r.status,r.declined,r.paymentMethod')
            ->join(
                User::class,
                'u',
                Expr\Join::WITH,
                'r.user=u.id'
            )
            ->join(
                Room::class,
                'ro',
                Expr\Join::WITH,
                'r.room=ro.id'
            )
            ->join(
                Category::class,
                'c',
                Expr\Join::WITH,
                'ro.category=c.id'
            )
            ->groupBy('r.id, r.datefrom,r.dateto,ro.id,u.email,c.name,r.status,r.declined,r.paymentMethod')
            ->orderBy($condition, 'asc')
            ->getQuery()
            ->getResult();
    }

    public function getClosest($dateFromMinus, $dateFromPlus, $dateToMinus, $dateToPlus, $roomId)
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.room)')
            ->where('r.datefrom > :dateFromMinus and r.datefrom < :dateFromPlus')
            ->andWhere('r.dateto > :dateToMinus and r.dateto < :dateToPlus')
            ->andWhere('r.room=:roomId')
            ->andWhere('r.status=1')
            ->setParameter('dateFromMinus', $dateFromMinus)
            ->setParameter('dateFromPlus', $dateFromPlus)
            ->setParameter('dateToMinus', $dateToMinus)
            ->setParameter('dateToPlus', $dateToPlus)
            ->setParameter('roomId', $roomId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
