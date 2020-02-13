<?php


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Trick", mappedBy="category")
     */
    private $tricks;

    public function __construct()
    {
        $this->tricks = new ArrayCollection();
    }

    public function addTrick(Trick $trick)
    {
        if (!$this->tricks->contains($trick)) {
            $this->tricks[] = $trick;
            $trick->setCategory($this);
        }

        return $this;
    }

    public function removeTrick(Trick $trick)
    {
        if ($this->tricks->contains($trick)) {
            $this->tricks->removeElement($trick);
            // Définit le côté propriétaire sur null (sauf si déjà changé)
            if ($trick->getCategory() === $this) {
                $trick->setCategory(null);
            }
        }

        return $this;
    }

    // Getters //

    public function getId()
    {
        return $this->id;
    }


    public function getName()
    {
        return $this->name;
    }


    public function getTricks()
    {
        return $this->tricks;
    }

    // Setters //

    public function setName($name)
    {
        $this->name = $name;

        return $name;
    }


    public function setTricks($tricks)
    {
        $this->tricks = $tricks;
    }
}