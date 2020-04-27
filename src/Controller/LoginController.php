<?php


namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController // Permet d'utiliser la méthode render
{
    /**
     * @Route("/connexion", name="login")
     * @param AuthenticationUtils $authenticationUtils
     * @return RedirectResponse|Response
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {

        // Si l'utilisateur est déjà connecté
        if ($this->getUser() != null) {
            // Redirection vers la page d'accueil
            return $this->redirectToRoute('home');
        }

        // Retrouve une erreur d'authentification s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
        // Retrouve le dernier identifiant de connexion utilisé
        $lastUsername = $authenticationUtils->getLastUsername();

        // Affiche la page de connexion
        return $this->render('login/login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
        ));
    }

    /**
     * @Route("/deconnexion", name="logout")
     * @throws Exception
     */
    public function logout()
    {
        throw new Exception('Cela ne devrait jamais être atteint !');
    }
}
