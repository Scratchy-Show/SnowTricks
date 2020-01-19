<?php


namespace App\Controller;


use App\Entity\User;
use App\Form\RegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController // Permet d'utiliser la méthode render
{
    /**
     * @Route("/inscription")
     */
    public function registrationPage(Request $request, UserPasswordEncoderInterface $encoder)
    {
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

            // Récupère le gestionnaire d'entités
            $entityManager = $this->getDoctrine()->getManager();

            // Doctrine gère maintenant l'objet
            $entityManager->persist($user);

            // Insère une nouvelle ligne dans la table User
            $entityManager->flush();

            // Message de confirmation
            $this->addFlash(
                'success',
                "Compte créé avec succès ! Veuillez l'activer via le mail qui vous a été envoyé."
            );

           // $this->sendMail($user);
        }

        // Affiche la page d'inscription avec le formulaire
        return $this->render('registration.html.twig', array(
            'registrationForm' => $registrationForm->createView(),
        ));
    }
/*
    public function sendMail($user)
    {
        $message = (new \Swift_Message('Validation du compte SnowTricks'))
            ->setFrom('noreply@snowtricks.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView('emails/validation.html.twig', [
                    'user' => $user
                ]),
                'text/html'
            )
        ;

        $mailer->send($message);
    }
*/
}