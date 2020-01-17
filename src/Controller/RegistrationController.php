<?php


namespace App\Controller;


use App\Entity\User;
use App\Form\RegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController // Permet d'utiliser la méthode render
{
    /**
     * @Route("/inscription")
     */
    public function registrationPage(Request $request)
    {
        // Crée une instance de User
        $user = new User();

        // Création du formulaire d'inscription
        $registrationForm = $this->createForm(RegistrationType::class, $user);

        // Met à jour le formulaire à l'aide des infos reçues de l'utilisateur
        $registrationForm->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            dump($user);
        }

        // Affiche la page d'inscription avec le formulaire
        return $this->render('registration.html.twig', array(
            'registrationForm' => $registrationForm->createView(),
        ));
    }

}