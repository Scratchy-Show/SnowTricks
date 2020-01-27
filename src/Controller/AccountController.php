<?php


namespace App\Controller;


use App\Entity\User;
use App\Form\PasswordForgotType;
use App\Form\PasswordResetType;
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
    public function registrationPage(Request $request, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer, MailController $mail)
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

            // Message d'en-tête du formulaire
            $swiftMessage = 'Validation du compte SnowTricks';

            // Modèle de l'email
            $view = 'emails/validateAccount.html.twig';

            // Envoie du mail de validation
            $mail->sendMail($user, $mailer, $swiftMessage, $view);

            // Message de confirmation
            $this->addFlash(
                'success',
                "Compte créé avec succès. Veuillez l'activer via le mail qui vous a été envoyé."
            );

            // Redirection vers la page d'accueil
            return $this->redirectToRoute('home');
        }

        // Affiche la page d'inscription avec le formulaire
        return $this->render('account/registration.html.twig', array(
            'registrationForm' => $registrationForm->createView(),
        ));
    }


    /**
     * @Route("/mot-de-passe-oublie", name="password_forgot")
     * @throws \Exception
     */
    public function passwordForgot(Request $request, EntityManagerInterface $manager, UserRepository $repository, \Swift_Mailer $mailer, MailController $mail)
    {
        // Création du formulaire
        $form = $this->createForm(PasswordForgotType::class);

        // Met à jour le formulaire à l'aide des infos reçues de l'utilisateur
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid())
        {
            // Récupère le pseudo dans le formulaire
            $username = $form->get('username')->getData();

            // Récupère l'utilisateur correspondant au pseudo
            $user = $repository->findOneBy(['username' => $username]);

            // Si l'utilisateur est trouvé
            if ($user != null)
            {
                // Attribution d'un nouveau token pour le reset
                $user->setToken(bin2hex(random_bytes(64)));

                // Sauvegarde les modifications
                $manager->flush();

                // Message d'en-tête du formulaire
                $swiftMessage = 'Réinitialisation du mot de passe de votre compte SnowTricks';

                // Modèle de l'email
                $view = 'emails/resetPassword.html.twig';

                // Envoie du mail de validation
                $mail->sendMail($user, $mailer, $swiftMessage, $view);

                // Message de confirmation
                $this->addFlash(
                    'success',
                    "Un email de réinitilisation de votre mot de passe a été envoyé sur votre boîte mail."
                );

                // Redirection vers la page d'accueil
                return $this->redirectToRoute('home');
            }
            else // Si l'utilisateur n'est pas trouvé
            {
                $this->addFlash(
                    'danger',
                    "Ce pseudo n'existe pas"
                );
            }
        }

        // Affiche la page d'oublie de mot de passe avec le formulaire
        return $this->render('account/passwordForgot.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/mot-de-passe-reinitialise/{token}", name="password_reset")
     */
    public function passwordReset(Request $request, EntityManagerInterface $manager, UserRepository $repository, UserPasswordEncoderInterface $encoder, $token)
    {
        // Création du formulaire
        $form = $this->createForm(PasswordResetType::class);

        // Met à jour le formulaire à l'aide des infos reçues de l'utilisateur
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid())
        {
            // Récupère le pseudo dans le formulaire
            $username = $form->get('username')->getData();

            // Récupère l'utilisateur correspondant au pseudo
            $user = $repository->findOneBy(['username' => $username]);

            // Si l'utilisateur est trouvé
            if ($user != null)
            {
                // Si le jeton de l'utilisateur correspond au jeton du formulaire
                if ($user->getToken() === $token)
                {
                    // Récupère le mot de passe dans le formulaire
                    $password = $form->get('password')->getData();

                    // Hachage du mot de passe
                    $hashedPassword = $encoder->encodePassword($user, $password);

                    // Attribution de la valeur
                    $user->setPassword($hashedPassword);

                    // Sauvegarde la modification
                    $manager->flush();

                    // Message de confirmation
                    $this->addFlash(
                        'success',
                        "Votre mot de passe à bien été modifié."
                    );

                    // Redirection vers la page d'accueil
                    return $this->redirectToRoute('home');
                }
                else // Si les jetons ne correspondent pas
                {
                    $this->addFlash(
                        'danger',
                        "La modification du mot de passe n'a pas pu être effectué"
                    );
                }
            }
            else // Si l'utilisateur n'est pas trouvé
            {
                $this->addFlash(
                    'danger',
                    "Nous n'avons pas réussi à vous identifier"
                );
            }
        }

        // Affiche la page réinitialisation du mot de passe avec le formulaire
        return $this->render('account/passwordReset.html.twig', [
            'form' => $form->createView()
        ]);
    }
}