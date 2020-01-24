<?php


namespace App\Entity;

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
class User implements UserInterface
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
     * @Assert\Length(
     *     min = 8,
     *     minMessage = "Le mot de passe doit faire au moins {{ limit }} caractères")
     */
    protected $password;

    /**
     * @Assert\EqualTo(
     *     propertyPath = "password",
     *     message = "Les deux mot de passe ne correspondent pas")
     */
    protected $passwordConfirm;

    /**
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

    public function __construct()
    {
        // Par défaut, la date d'inscription est celle d'aujourd'hui
        $this->setDate(new \DateTime("now"));
        // Par défaut, le compte n'est pas activé
        $this->setActivated(false);
    }

    public function eraseCredentials() {}

    // Getters //

    public function getId()
    {
        return $this->id;
    }


    public function getUsername()
    {
        return $this->username;
    }


    public function getEmail()
    {
        return $this->email;
    }


    public function getDate()
    {
        return $this->date;
    }


    public function getPassword()
    {
        return $this->password;
    }


    public function getPasswordConfirm()
    {
        return $this->passwordConfirm;
    }


    public function getProfilPicture()
    {
        return $this->profilPicture;
    }


    public function getPictureName()
    {
        return $this->pictureName;
    }


    public function getProfilPicturePath()
    {
        return $this->profilPicturePath;
    }


    public function getToken()
    {
        return $this->token;
    }


    public function getActivated()
    {
        return $this->activated;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function getSalt() {}

        // Setters //

    public function setUsername($username)
    {
        $this->username = $username;

        return $username;
    }


    public function setEmail($email)
    {
        $this->email = $email;

        return $email;
    }


    public function setDate($date)
    {
        $this->date = $date;

        return $date;
    }


    public function setPassword($password)
    {
        $this->password = $password;

        return $password;
    }

    public function setPasswordConfirm($passwordConfirm)
    {
        $this->passwordConfirm = $passwordConfirm;

        return $passwordConfirm;
    }


    public function setProfilPicture($profilPicture)
    {
        $this->profilPicture = $profilPicture;

        return $profilPicture;
    }


    public function setPictureName($pictureName)
    {
        $this->pictureName = $pictureName;

        return $pictureName;
    }


    public function setProfilPicturePath($profilPicturePath)
    {
        $this->profilPicturePath = $profilPicturePath;

        return $profilPicturePath;
    }


    public function setToken($token)
    {
        $this->token = $token;

        return $token;
    }


    public function setActivated($activated)
    {
        $this->activated = $activated;

        return $activated;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }
}