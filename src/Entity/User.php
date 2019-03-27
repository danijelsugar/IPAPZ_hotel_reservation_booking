<?php


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Reservation as Reservation;
use App\Entity\Transaction as Transaction;

/**
 * @Doctrine\ORM\Mapping\Entity(repositoryClass="App\Repository\UserRepository")
 * @Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity("email")
 */
class User implements UserInterface
{
    /**
     * @Doctrine\ORM\Mapping\Id()
     * @Doctrine\ORM\Mapping\GeneratedValue()
     * @Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $id;

    /**
     * @Doctrine\ORM\Mapping\Column(type="string")
     * @Symfony\Component\Validator\Constraints\NotBlank()
     */
    private $firstname;

    /**
     * @Doctrine\ORM\Mapping\Column(type="string")
     * @Symfony\Component\Validator\Constraints\NotBlank()
     */
    private $lastname;

    /**
     * @Doctrine\ORM\Mapping\Column(type="string", length=180, unique=true)
     * @Symfony\Component\Validator\Constraints\NotBlank()
     * @Symfony\Component\Validator\Constraints\Email()
     */
    private $email;

    /**
     * @var                       string The hashed password
     * @Doctrine\ORM\Mapping\Column(type="string")
     */
    private $password;

    /**
     * @Doctrine\ORM\Mapping\Column(type="json")
     */
    private $roles;

    /**
     * @Doctrine\ORM\Mapping\OneToMany(targetEntity="App\Entity\Reservation", mappedBy="user", orphanRemoval=true)
     */
    private $reservation;

    /**
     * @@Doctrine\ORM\Mapping\OneToMany(targetEntity="App\Entity\Transaction", mappedBy="user", orphanRemoval=true)
     */
    private $transactions;

    public function __construct()
    {
        $this->reservation = new ArrayCollection();
        $this->transactions = new ArrayCollection();
    }

    public function getFullName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstname;
    }

    /**
     * @param mixed $firstname
     */
    public function setFirstName($firstname): void
    {
        $this->firstname = $firstname;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastname;
    }

    /**
     * @param mixed $lastname
     */
    public function setLastName($lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_EMPLOYEE
        $roles[] = '';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Reservation[]
     */
    public function getReservation(): Collection
    {
        return $this->reservation;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservation->contains($reservation)) {
            $this->reservation[] = $reservation;
            $reservation->setUser($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservation->contains($reservation)) {
            $this->reservation->removeElement($reservation);
            // set the owning side to null (unless already changed)
            if ($reservation->getUser() === $this) {
                $reservation->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setUser($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->contains($transaction)) {
            $this->transactions->removeElement($transaction);
            // set the owning side to null (unless already changed)
            if ($transaction->getUser() === $this) {
                $transaction->setUser(null);
            }
        }

        return $this;
    }
}
