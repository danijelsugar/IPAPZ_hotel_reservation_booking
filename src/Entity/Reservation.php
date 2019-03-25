<?php


namespace App\Entity;

use App\Entity\User as User;
use App\Entity\Room as Room;

/**
 * @Doctrine\ORM\Mapping\Entity(repositoryClass="App\Repository\ReservationRepository")
 */
class Reservation
{

    /**
     * @Doctrine\ORM\Mapping\Id()
     * @Doctrine\ORM\Mapping\GeneratedValue()
     * @Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $id;

    /**
     * @Doctrine\ORM\Mapping\Column(type="datetime")
     */
    private $datefrom;

    /**
     * @Doctrine\ORM\Mapping\Column(type="datetime")
     */
    private $dateto;

    /**
     * @Doctrine\ORM\Mapping\Column(type="boolean")
     */
    private $status = 0;

    /**
     * @Doctrine\ORM\Mapping\Column(type="boolean")
     */
    private $declined = 0;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(targetEntity="App\Entity\User", inversedBy="reservation")
     * @Doctrine\ORM\Mapping\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(targetEntity="App\Entity\Room", inversedBy="reservations")
     * @Doctrine\ORM\Mapping\JoinColumn(nullable=false)
     */
    private $room;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getDatefrom()
    {
        return $this->datefrom;
    }

    /**
     * @param mixed $datefrom
     */
    public function setDatefrom($datefrom): void
    {
        $this->datefrom = $datefrom;
    }

    /**
     * @return mixed
     */
    public function getDateto()
    {
        return $this->dateto;
    }

    /**
     * @param mixed $dateto
     */
    public function setDateto($dateto): void
    {
        $this->dateto = $dateto;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getDeclined()
    {
        return $this->declined;
    }

    /**
     * @param mixed $declined
     */
    public function setDeclined($declined): void
    {
        $this->declined = $declined;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): self
    {
        $this->room = $room;

        return $this;
    }
}
