<?php


namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class MailController extends AbstractController // Permet d'utiliser la méthode render
{
    // Envoie un mail pour valider le compte
    public function sendMail(User $user, Swift_Mailer $mailer, $swiftMessage, $view)
    {
        $message = (new Swift_Message($swiftMessage))
            ->setFrom('noreply@snowtricks.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView($view, [
                    'user' => $user
                ]),
                'text/html'
            )
        ;

        $mailer->send($message);
    }

    /**
     * @Route("/validation-email/{username}/{token}", name="validate_email")
     * @param $username
     * @param $token
     * @param UserRepository $repository
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    public function validateEmail(
        $username,
        $token,
        UserRepository $repository,
        EntityManagerInterface $manager
    ) {
        // Récupère l'utilisateur
        $user = $repository->findOneBy(['username' => $username]);

        // Si l'utilisateur est trouvé
        if ($user != null) {
            // Si le jeton correspond à celui de l'utilisateur
            if ($token == $user->getToken()) {
                // Active le compte
                $user->setActivated(true);
                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    "Votre compte a été activé. Vous pouvez maintenant vous connecter."
                );

                // Redirection vers la page de connexion
                return $this->redirectToRoute('login');
            } else // Si le jeton ne correspond pas à l'utilisateur
            {
                $this->addFlash(
                    'danger',
                    "La validation de votre compte a échoué. Nous n'avons pas pu vous identifier."
                );

                // Redirection vers la page d'inscription
                return $this->redirectToRoute('registration');
            }
        } else // Si l'utilisateur n'est pas trouvé
        {
            $this->addFlash(
                'danger',
                "La validation de votre compte a échoué. Nous n'avons pas pu vous identifier."
            );

            // Redirection vers la page d'inscription
            return $this->redirectToRoute('registration');
        }
    }
}
