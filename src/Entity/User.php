<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @UniqueEntity(
 *  fields = {"username"},
 *  message = "Ce pseudo est déjà utilisé"
 * )
 * @UniqueEntity(
 *  fields = {"email"},
 *  message = "Cet email est déjà utilisé"
 * )
 */
class User
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
     * @var string A "d-m-Y H:i:s" formatted value
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
    protected $profilPicturePath;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $activated;

    public function __construct()
    {

    }

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


    public function getProfilPicturePath()
    {
        return $this->profilPicturePath;
    }


    public function getActivated()
    {
        return $this->activated;
    }

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


    public function setDate(string $date)
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


    public function setProfilPicturePath($profilPicturePath)
    {
        $this->profilPicturePath = $profilPicturePath;

        return $profilPicturePath;
    }


    public function setActivated($activated)
    {
        $this->activated = $activated;

        return $activated;
    }
}