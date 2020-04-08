<?php


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *  fields = {"username"},
 *  message = "Ce pseudo est déjà utilisé"
 * )
 * @UniqueEntity(
 *  fields = {"email"},
 *  message = "Cet email est déjà utilisé"
 * )
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(name="pseudo", type="string", length=25, unique=true)
     * @Assert\NotBlank(message = "Un pseudo doit être indiqué")
     * @Assert\Length(
     *      min = 2,
     *      max = 25,
     *      minMessage = "Votre pseudo doit contenir au moins {{ limit }} caractères",
     *      maxMessage = "Votre pseudo ne peut doit pas dépasser les {{ limit }} caractères"
     * )
     */
    protected $username;

    /**
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\NotBlank(message = "Un email doit être indiqué")
     * @Assert\Email(message = "Le format de l'email attendu est nom@exemple.fr")
     */
    protected $email;

    /**
     * @Assert\DateTime
     * @ORM\Column(type="datetime", name="date")
     */
    protected $date;

    /**
     * @ORM\Column(type="string", name="password", length=255)
     * @Assert\NotBlank(message = "Un mot de passe doit être indiqué")
     * @Assert\Length(
     *     min = 8,
     *     minMessage = "Le mot de passe doit faire au moins {{ limit }} caractères")
     */
    protected $password;

    /**
     * @Assert\EqualTo(
     *     propertyPath = "password",
     *     message = "Les mots de passe ne correspondent pas")
     */
    protected $passwordConfirm;

    /**
     * @Assert\NotBlank(message = "Une image doit être indiquée")
     * @Assert\Image(
     *  mimeTypes        = {"image/jpeg", "image/jpg", "image/png"},
     *  mimeTypesMessage = "Le fichier ne possède pas une extension valide. Veuillez insérer une image en .jpg, .jpeg ou .png",
     *  minWidth         = 200,
     *  minWidthMessage  = "La largeur de cette image est trop petite. Elle doit faire minimum {{ min_width }} pixels",
     *  maxWidth         = 400,
     *  maxWidthMessage  = "La largeur de cette image est trop grande. Elle doit faire maximum {{ max_width }} pixels",
     *  minHeight        = 200,
     *  minHeightMessage = "La hauteur de cette image est trop petite. Elle doit faire minimum {{ min_height }} pixels",
     *  maxHeight        = 400,
     *  maxHeightMessage = "La hauteur de cette image est trop grande. Elle doit faire maximum {{ max_height }} pixels",
     *  allowLandscape   = false,
     *  allowLandscapeMessage = "L'image doit être un carré",
     *  allowPortrait    = false,
     *  allowPortraitMessage = "L'image doit être un carré",
     *  )
     */
    protected $profilPicture;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $pictureName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $profilPicturePath;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $activated;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Trick", mappedBy="user")
     */
    protected $tricks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="user", orphanRemoval=true)
     */
    private $comments;

    public function __construct()
    {
        // Par défaut, la date d'inscription est celle d'aujourd'hui
        $this->setDate(new \DateTime("now"));
        // Par défaut, le compte n'est pas activé
        $this->setActivated(false);
        $this->tricks = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function eraseCredentials() {}

    public function getSalt() {}

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            ) = unserialize($serialized, array('allowed_classes' => false));
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPasswordConfirm()
    {
        return $this->passwordConfirm;
    }

    public function setPasswordConfirm($passwordConfirm)
    {
        $this->passwordConfirm = $passwordConfirm;

        return $passwordConfirm;
    }

    public function getProfilPicture()
    {
        return $this->profilPicture;
    }

    public function setProfilPicture($profilPicture)
    {
        $this->profilPicture = $profilPicture;

        return $profilPicture;
    }

    public function getPictureName(): ?string
    {
        return $this->pictureName;
    }

    public function setPictureName(string $pictureName): self
    {
        $this->pictureName = $pictureName;

        return $this;
    }

    public function getProfilPicturePath(): ?string
    {
        return $this->profilPicturePath;
    }

    public function setProfilPicturePath(string $profilPicturePath): self
    {
        $this->profilPicturePath = $profilPicturePath;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getActivated(): ?bool
    {
        return $this->activated;
    }

    public function setActivated(bool $activated): self
    {
        $this->activated = $activated;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return Collection|Trick[]
     */
    public function getTricks(): Collection
    {
        return $this->tricks;
    }

    public function addTrick(Trick $trick): self
    {
        if (!$this->tricks->contains($trick)) {
            $this->tricks[] = $trick;
            $trick->setUser($this);
        }

        return $this;
    }

    public function removeTrick(Trick $trick): self
    {
        if ($this->tricks->contains($trick)) {
            $this->tricks->removeElement($trick);
            // set the owning side to null (unless already changed)
            if ($trick->getUser() === $this) {
                $trick->setUser(null);
            }
        }

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
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }


}