<?php

namespace App\Entity;

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
     * @Doctrine\ORM\Mapping\Column(type="string", length=255)
     */
    private $transactionId;

    /**
     * @Doctrine\ORM\Mapping\Column(type="datetime", nullable=true)
     */
    private $choosenAt;

    /**
     * @Doctrine\ORM\Mapping\Column(type="datetime", nullable=true)
     */
    private $paidAt;

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

    public function getChoosenAt(): ?\DateTimeInterface
    {
        return $this->choosenAt;
    }

    public function setChoosenAt(?\DateTimeInterface $choosenAt): self
    {
        $this->choosenAt = $choosenAt;

        return $this;
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
}
