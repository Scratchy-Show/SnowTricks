<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VideoRepository")
 * @UniqueEntity(
 *  fields={"url"},
 *  message="Une vidéo possède déjà cette url"
 * )
 */
class Video
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $url;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Trick", inversedBy="videos")
     * @ORM\JoinColumn(name="trick_id", referencedColumnName="id")
     */
    protected $trick;

    // Getters //

    public function getId()
    {
        return $this->id;
    }


    public function getUrl()
    {
        return $this->url;
    }


    public function getTrick()
    {
        return $this->trick;
    }

    // Setters //

    public function setUrl($url)
    {
        $this->url = $url;

        return $url;
    }


    public function setTrick($trick)
    {
        $this->trick = $trick;

        return $trick;
    }
}