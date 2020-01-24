<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController // Permet d'utiliser la méthode render
{
    /**
     * @Route("/", name="home")
     */
    public function homePage()
    {
        return $this->render('home/index.html.twig');
    }
}