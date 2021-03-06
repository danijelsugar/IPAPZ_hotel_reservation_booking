<?php

namespace App\Entity;

use App\Entity\Room as Room;
use App\Entity\User as User;
use App\Entity\Reservation as Reservation;

/**
 * @Doctrine\ORM\Mapping\Entity(repositoryClass="App\Repository\TransactionRepository")
 */
class Transaction
{
    /**
     * @Doctrine\ORM\Mapping\Id()
     * @Doctrine\ORM\Mapping\GeneratedValue()
     * @Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $id;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(targetEntity="App\Entity\User", inversedBy="transactions")
     * @Doctrine\ORM\Mapping\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(targetEntity="App\Entity\Room")
     * @Doctrine\ORM\Mapping\JoinColumn(nullable=false)
     */
    private $room;

    /**
     * @Doctrine\ORM\Mapping\Column(type="string", length=255)
     */
    private $method;

    /**
     * @Doctrine\ORM\Mapping\Column(type="string", length=255, nullable=true)
     */
    private $transactionId;

    /**
     * @Doctrine\ORM\Mapping\Column(type="datetime", nullable=true)
     */
    private $chosenAt;

    /**
     * @Doctrine\ORM\Mapping\Column(type="datetime", nullable=true)
     */
    private $paidAt;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(targetEntity="App\Entity\Reservation")
     * @Doctrine\ORM\Mapping\JoinColumn(nullable=false)
     */
    private $reservation;

    /**
     * @Doctrine\ORM\Mapping\Column(type="string", length=255, nullable=true)
     */
    private $fileName;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(string $transactionId): self
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    public function getChosenAt(): ?\DateTimeInterface
    {
        return $this->chosenAt;
    }

    public function setChosenAt(?\DateTimeInterface $chosenAt): self
    {
        $this->chosenAt = $chosenAt;

        return $this;
    }

    /**
     * @\Doctrine\ORM\Mapping\PrePersist()
     * @throws \Exception
     */
    public function onPrePersistChosenAt()
    {
        $this->chosenAt = new \DateTime('now');
    }

    public function getPaidAt(): ?\DateTimeInterface
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeInterface $paidAt): self
    {
        $this->paidAt = $paidAt;

        return $this;
    }

    /**
     * @\Doctrine\ORM\Mapping\PrePersist()
     * @throws \Exception
     */
    public function onPrePersistPaidAt()
    {
        $this->paidAt = new \DateTime('now');
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): self
    {
        $this->reservation = $reservation;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }
}
