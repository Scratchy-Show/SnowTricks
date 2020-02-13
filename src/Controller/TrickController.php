<?php


namespace App\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController // Permet d'utiliser la méthode render
{
    /**
     * @Route("/figure/creer", name="trick_create")
     * @IsGranted("ROLE_USER")
     */
    public function createTrick()
    {

    }
}