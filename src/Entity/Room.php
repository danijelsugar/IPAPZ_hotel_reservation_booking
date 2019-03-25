<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Review as Review;
use App\Entity\Reservation as Reservation;

/**
 * @Doctrine\ORM\Mapping\Entity(repositoryClass="App\Repository\RoomRepository")
 */
class Room
{

    /**
     * @Doctrine\ORM\Mapping\Id()
     * @Doctrine\ORM\Mapping\GeneratedValue()
     * @Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $id;

    /**
     * @Doctrine\ORM\Mapping\Column(type="text")
     * @Symfony\Component\Validator\Constraints\NotBlank()
     */
    private $description;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(targetEntity="App\Entity\Category")
     * @Doctrine\ORM\Mapping\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(targetEntity="App\Entity\SubCategory")
     * @Doctrine\ORM\Mapping\JoinColumn(nullable=false)
     */
    private $subcategory;

    /**
     * @Doctrine\ORM\Mapping\Column(type="string")
     * @Symfony\Component\Validator\Constraints\NotBlank(message="Please, upload the image.")
     * @Symfony\Component\Validator\Constraints\File(mimeTypes={          "image/jpg", "image/jpeg" })
     */
    private $image;

    /**
     * @Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $capacity;

    /**
     * @Doctrine\ORM\Mapping\Column(type="boolean")
     */
    private $status = 0;

    /**
     * @Doctrine\ORM\Mapping\OneToMany(targetEntity="App\Entity\Review", mappedBy="room", orphanRemoval=true)
     */
    private $reviews;

    /**
     * @Doctrine\ORM\Mapping\OneToMany(targetEntity="App\Entity\Reservation", mappedBy="room", orphanRemoval=true)
     */
    private $reservations;

    /**
     * @Doctrine\ORM\Mapping\Column(type="decimal", scale=2)
     */
    private $cost;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category): void
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getSubcategory()
    {
        return $this->subcategory;
    }

    /**
     * @param mixed $subcategory
     */
    public function setSubcategory($subcategory): void
    {
        $this->subcategory = $subcategory;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image): void
    {
        $this->image = $image;
    }

    /**
     * @param mixed $capacity
     */
    public function setCapacity($capacity): void
    {
        $this->capacity = $capacity;
    }

    /**
     * @return mixed
     */
    public function getCapacity()
    {
        return $this->capacity;
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
     * @return Collection|Review[]
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setRoom($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->contains($review)) {
            $this->reviews->removeElement($review);
            // set the owning side to null (unless already changed)
            if ($review->getRoom() === $this) {
                $review->setRoom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Reservation[]
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setRoom($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->contains($reservation)) {
            $this->reservations->removeElement($reservation);
            // set the owning side to null (unless already changed)
            if ($reservation->getRoom() === $this) {
                $reservation->setRoom(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param mixed $cost
     */
    public function setCost($cost): void
    {
        $this->cost = $cost;
    }
}
