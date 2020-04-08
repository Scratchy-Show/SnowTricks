<?php


namespace App\Controller;


use App\Form\ProfileEditType;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController // Permet d'utiliser la méthode render
{
    /**
     * @Route("/profil", name="profile")
     * @IsGranted("ROLE_USER")
     */
    public function profilePage()
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

        // Création du formulaire avec les données de l'utilisateur
        $form = $this->createForm(ProfileType::class, $user);

        // Affiche la page du profil de l'utilisateur avec le formulaire
        return $this->render('profile/profile.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/modifier-profil/{token}", name="profile_edit")
     * @IsGranted("ROLE_USER")
     * @throws \Exception
     */
    public function profileEdit(Request $request, EntityManagerInterface $manager, $token)
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

        // Récupère sont email
        $email = $user->getEmail();

        // Création du formulaire avec les données de l'utilisateur
        $form = $this->createForm(ProfileEditType::class, $user);

        // Met à jour le formulaire à l'aide des infos reçues de l'utilisateur
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid())
        {
            // Si le jeton de l'utilisateur correspond au jeton du lien
            if ($user->getToken() == $token)
            {
                // Récupère l'image du formulaire
                $pictureFile = $form->get('profilPicture')->getData();

                // Chemin du fichier
                $destination = $this->getParameter('profil_picture_directory');

                // Récupère le chemin complet de l'image actuelle du profil
                $oldPicture = $destination . '/' . $user->getpictureName();

                // Supprime l'ancienne image de profil
                unlink($oldPicture);

                // Redéfini le nom du fichier
                $newFilename = $user->getUsername() . '-' . uniqid() . '.' . $pictureFile->guessExtension();

                // Déplace le fichier dans le dossier uploads en le renommant
                $pictureFile->move($destination, $newFilename);

                // Attribution des valeurs
                $user->setPictureName($newFilename);
                $user->setProfilPicturePath('uploads/profil');

                // Attribution d'un nouveau token
                $user->setToken(bin2hex(random_bytes(64)));

                // Sauvegarde les modifications
                $manager->flush();

                // Message de confirmation
                $this->addFlash(
                    'success',
                    "Votre profil à bien été modifié."
                );

                // Redirection vers la page du profil
                return $this->redirectToRoute('profile');
            }
            else // Si les jetons ne correspondent pas
            {
                $this->addFlash(
                    'danger',
                    "La modification du profil n'a pas pu être effectué"
                );

                // Redirection vers la page du profil
                return $this->redirectToRoute('profile');
            }
        }

        // Affiche la page de modification du profil de l'utilisateur avec le formulaire
        return $this->render('profile/profileEdit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}