<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController // Permet d'utiliser la méthode render
{
    /**
     * @Route("/inscription")
     */
    public function registrationPage()
    {
        return $this->render('registration.html.twig');
    }

}