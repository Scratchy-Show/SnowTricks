<?php


namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\Column(type="string")
     */
    protected $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Picture", mappedBy="trick", cascade={"persist", "remove"})
     * @Assert\Valid
     */
    protected $pictures;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Picture", cascade={"persist", "remove"})
     * @Assert\Valid
     */
    protected $mainPicture;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="tricks", cascade={"persist"})
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * @Assert\NotBlank(message = "Une catégorie doit être indiquée")
     */
    protected $category;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="trick", orphanRemoval=true)
     */
    private $comments;


    public function __construct()
    {
        // Définit le fuseau horaire
        date_default_timezone_set('Europe/Paris');
        // Par défaut, la date est la date d'aujourd'hui
        $this->setDate(new DateTime());
        $this->pictures = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        $this->setSlug($this->name);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getUpdateDate(): ?DateTimeInterface
    {
        return $this->updateDate;
    }

    public function setUpdateDate(?DateTimeInterface $updateDate): self
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $this->slugify($slug);

        return $this;
    }

    public function slugify($text)
    {
        // Remplace les non lettres ou chiffres par un -
        $text = preg_replace('#[^\\pL\d]+#u', '-', $text);

        // Supprime les espaces en début et fin de chaîne
        $text = trim($text, '-');

        // Translittérer - Nettoie les accents
        if (function_exists('iconv')) {
            // Convertit la chaîne $text depuis utf-8 vers us-ascii//TRANSLIT
            $text = iconv('utf-8', 'ASCII//TRANSLIT', $text);
        }

        // Renvoie la chaîne $text en minuscules
        $text = strtolower($text);

        // Supprimer les caractères indésirables
        $text = preg_replace('#[^-\w]+#', '', $text);

        return $text;
    }

    /**
     * @return Collection|Picture[]
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): self
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures[] = $picture;
            $picture->setTrick($this);
        }

        return $this;
    }

    public function removePicture(Picture $picture): self
    {
        if ($this->pictures->contains($picture)) {
            $this->pictures->removeElement($picture);
            // set the owning side to null (unless already changed)
            if ($picture->getTrick() === $this) {
                $picture->setTrick(null);
            }
        }

        return $this;
    }

    public function getMainPicture(): ?Picture
    {
        return $this->mainPicture;
    }

    public function setMainPicture(?Picture $mainPicture): self
    {
        $this->mainPicture = $mainPicture;

        return $this;
    }

    /**
     * @return Collection|Video[]
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos[] = $video;
            $video->setTrick($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): self
    {
        if ($this->videos->contains($video)) {
            $this->videos->removeElement($video);
            // set the owning side to null (unless already changed)
            if ($video->getTrick() === $this) {
                $video->setTrick(null);
            }
        }

        return $this;
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setTrick($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getTrick() === $this) {
                $comment->setTrick(null);
            }
        }

        return $this;
    }
}
