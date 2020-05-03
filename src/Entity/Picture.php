<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PictureRepository")
 */
class Picture
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @Assert\Image(
     *  mimeTypes        = {"image/jpeg", "image/jpg", "image/png"},
     *  mimeTypesMessage = "Le fichier ne possède pas une extension valide.
     *                      Veuillez insérer une image en .jpg, .jpeg ou .png",
     *  minWidth         = 480,
     *  minWidthMessage  = "La largeur de cette image est trop petite. Elle doit faire minimum {{ min_width }} pixels",
     *  maxWidth         = 1500,
     *  maxWidthMessage  = "La largeur de cette image est trop grande. Elle doit faire maximum {{ max_width }} pixels",
     *  minHeight        = 270,
     *  minHeightMessage = "La hauteur de cette image est trop petite. Elle doit faire minimum {{ min_height }} pixels",
     *  maxHeight        = 750,
     *  maxHeightMessage = "La hauteur de cette image est trop grande. Elle doit faire maximum {{ max_height }} pixels",
     *  )
     */
    protected $file;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $path;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Trick", inversedBy="pictures")
     * @ORM\JoinColumn(name="trick_id", referencedColumnName="id", onDelete = "CASCADE")
     */
    protected $trick;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;

        return $file;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): self
    {
        $this->trick = $trick;

        return $this;
    }
}
