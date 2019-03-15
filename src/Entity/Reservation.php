<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReservationRepository")
 */
class Reservation
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Room")
     */
    private $room;

    /**
     * @ORM\Column(type="datetime")
     */
    private $datefrom;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateto;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $declined = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="reservation")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

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
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * @param mixed $room
     */
    public function setRoom($room): void
    {
        $this->room = $room;
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


}