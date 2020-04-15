<?php


namespace App\Controller;


use App\Entity\Trick;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController // Permet d'utiliser la méthode render
{
    /**
     * @Route("/", name="home")
     */
    public function homePage()
    {
        // Récupère le gestionnaire d'entités
        $entityManager = $this->getDoctrine()->getManager();

        // Récupère toutes les figures
        $tricks =  $entityManager->getRepository(Trick::class)->findAll();

        return $this->render('home/index.html.twig', [
            'tricks' => $tricks
        ]);
    }
}