<?php


namespace App\Controller;


use App\Entity\User;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountController extends AbstractController // Permet d'utiliser la méthode render
{
    /**
     * @Route("/inscription", name="registration")
     * @throws \Exception
     */
    public function registrationPage(Request $request, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer)
    {
        // Si l'utilisateur est déjà connecté
        if ($this->getUser() != null)
        {
            // Redirection vers la page d'accueil
            return $this->redirectToRoute('home');
        }

        // Crée une instance de User
        $user = new User();

        // Création du formulaire d'inscription
        $registrationForm = $this->createForm(RegistrationType::class, $user);

        // Met à jour le formulaire à l'aide des infos reçues de l'utilisateur
        $registrationForm->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            // Récupère l'image de l'utilisateur
            $pictureFile = $registrationForm->get('profilPicture')->getData();

            // Chemin de destination du fichier
            $destination = $this->getParameter('kernel.project_dir') . '/public/uploads';

            // Redéfini le nom du fichier
            $newFilename = $user->getUsername() . '-' . uniqid() . '.' . $pictureFile->guessExtension();

            // Déplace le fichier dans le dossier uploads en le renommant
            $pictureFile->move($destination, $newFilename);

            // Hachage du mot de passe
            $hashedPassword = $encoder->encodePassword($user, $user->getPassword());

            // Attribution des valeurs
            $user->setPassword($hashedPassword);
            $user->setPictureName($newFilename);
            $user->setProfilPicturePath($destination);
            $user->setToken(bin2hex(random_bytes(64)));

            // Récupère le gestionnaire d'entités
            $entityManager = $this->getDoctrine()->getManager();

            // Doctrine gère maintenant l'objet
            $entityManager->persist($user);

            // Insère une nouvelle ligne dans la table User
            $entityManager->flush();

            // Envoie du mail de validation
            $this->sendMail($user, $mailer);

            // Message de confirmation
            $this->addFlash(
                'success',
                "Compte créé avec succès ! Veuillez l'activer via le mail qui vous a été envoyé."
            );

            // Redirection vers la page d'accueil
            return $this->redirectToRoute('home');
        }

        // Affiche la page d'inscription avec le formulaire
        return $this->render('account/registration.html.twig', array(
            'registrationForm' => $registrationForm->createView(),
        ));
    }

    // Envoie un mail pour valider le compte
    public function sendMail(User $user, \Swift_Mailer $mailer)
    {
        $message = (new \Swift_Message('Validation du compte SnowTricks'))
            ->setFrom('noreply@snowtricks.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView('emails/validateAccount.html.twig', [
                    'user' => $user
                ]),
                'text/html'
            )
        ;

        $mailer->send($message);
    }

    /**
     * @Route("/validation-email/{username}/{token}", name="validate_email")
     */
    public function validateEmail($username, $token, UserRepository $repository, EntityManagerInterface $manager)
    {
        // Récupère l'utilisateur
        $user = $repository->findOneBy(['username' => $username]);

        // Si l'utilisateur est trouvé
        if ($user != null)
        {
            // Si le jeton correspond à celui de l'utilisateur
            if ($token == $user->getToken())
            {
                // Active le compte
                $user->setActivated(true);
                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    "Votre compte a été activé ! Vous pouvez maintenant vous connecter."
                );

                return $this->redirectToRoute('home');
            }
            else // Si le jeton ne correspond pas à l'utilisateur
            {
                $this->addFlash(
                    'danger',
                    "La validation de votre compte a échoué. Nous n'avons pas pu vous identifier."
                );

                return $this->redirectToRoute('registration');
            }
        }
        else // Si l'utilisateur n'est pas trouvé
        {
            $this->addFlash(
                'danger',
                "La validation de votre compte a échoué. Nous n'avons pas pu vous identifier."
            );

            return $this->redirectToRoute('registration');
        }
    }
}