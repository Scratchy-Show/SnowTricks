<?php


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use \DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TrickRepository")
 * @UniqueEntity(
 *      fields={"name"},
 *      message="Une figure possède déjà ce nom"
 * )
 */
class Trick
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     * @Assert\NotBlank(message = "Un nom doit être indiqué")
     * @Assert\Length(
     *      min = 2,
     *      max = 25,
     *      minMessage = "Le nom doit contenir au moins {{ limit }} caractères",
     *      maxMessage = "Le nom ne peut doit pas dépasser les {{ limit }} caractères"
     * )
     */
    protected $name;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message = "Une desciption doit être indiqué")
     * @Assert\Length(
     *     min = 25,
     *     minMessage="La description doit faire au moins {{ limit }} caractères"
     * )
     */
    protected $description;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updateDate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Picture", mappedBy="trick", cascade={"persist", "remove"})
     */
    protected $pictures;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Video", mappedBy="trick", cascade={"persist", "remove"})
     */
    protected $videos;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="tricks")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="tricks")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;


    public function __construct()
    {
        // Définit le fuseau horaire
        date_default_timezone_set('Europe/Paris');
        // Par défaut, la date est la date d'aujourd'hui
        $this->setDate(new DateTime());
        $this->pictures = new ArrayCollection();
        $this->videos = new ArrayCollection();
    }

    public function addPicture(Picture $picture)
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures[] = $picture;
            $picture->setTrick($this);
        }

        return $this;
    }

    public function removePicture(Picture $picture)
    {
        if ($this->pictures->contains($picture)) {
            $this->pictures->removeElement($picture);
            // Définit le côté propriétaire sur null (sauf si déjà changé)
            if ($picture->getTrick() === $this) {
                $picture->setTrick(null);
            }
        }

        return $this;
    }

    public function addVideo(Video $video)
    {
        if (!$this->videos->contains($video)) {
            $this->videos[] = $video;
            $video->setTrick($this);
        }

        return $this;
    }

    public function removeVideo(Video $video)
    {
        if ($this->videos->contains($video)) {
            $this->videos->removeElement($video);
            // Définit le côté propriétaire sur null (sauf si déjà changé)
            if ($video->getTrick() === $this) {
                $video->setTrick(null);
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


    public function getDescription()
    {
        return $this->description;
    }


    public function getDate()
    {
        return $this->date;
    }


    public function getUpdateDate()
    {
        return $this->updateDate;
    }


    public function getPictures()
    {
        return $this->pictures;
    }


    public function getVideos()
    {
        return $this->videos;
    }


    public function getUser()
    {
        return $this->user;
    }

    // Setters //

    public function setName($name)
    {
        $this->name = $name;

        return $name;
    }


    public function setDescription($description)
    {
        $this->description = $description;

        return $description;
    }


    public function setDate($date)
    {
        $this->date = $date;

        return $date;
    }


    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;

        return $updateDate;
    }


    public function setPictures($pictures)
    {
        $this->pictures = $pictures;

        return $pictures;
    }

    public function setVideos($videos)
    {
        $this->videos = $videos;

        return $videos;
    }


    public function setUser($user)
    {
        $this->user = $user;

        return $user;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }
}