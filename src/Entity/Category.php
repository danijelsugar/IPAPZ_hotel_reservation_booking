<?php


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Doctrine\ORM\Mapping\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category
{

    public function __construct()
    {
        $this->rooms = new ArrayCollection();
    }

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
    private $name;


    private $rooms;

    /**
     * @return \Doctrine\Common\Collections\Collection|Room[]
     */
    public function getRooms()
    {
        return $this->rooms;
    }

    /**
     * @param mixed $rooms
     */
    public function setRooms($rooms): void
    {
        $this->rooms = $rooms;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }
}
