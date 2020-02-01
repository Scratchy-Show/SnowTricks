<?php


namespace App\Controller;


use App\Form\ProfileEditType;
use App\Form\ProfileType;
use App\Repository\UserRepository;
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
    public function profileEdit(Request $request, EntityManagerInterface $manager, UserRepository $repository, $token)
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

        // Création du formulaire avec les données de l'utilisateur
        $form = $this->createForm(ProfileEditType::class, $user);

        // Met à jour le formulaire à l'aide des infos reçues de l'utilisateur
        $form->handleRequest($request);

        // Si le formulaire est soumis
        if ($form->isSubmitted())
        {
            // Si le jeton de l'utilisateur correspond au jeton du lien
            if ($user->getToken() == $token)
            {
                // Récupère l'email dans le formulaire
                $email = $form->get('email')->getData();

                // Récupère l'image du formulaire
                $pictureFile = $form->get('profilPicture')->getData();

                // Si les valeurs du formulaire ne sont pas vident
                if (!empty($email) && !empty($pictureFile))
                {
                    // Vérifie si un utilisateur à la même adresse email
                    $findEmail = $repository->findOneBy(['email' => $email]);

                    // Si l'adresse email n'existe pas
                    if ($findEmail == null)
                    {
                        // Récupère l'adresse de l'image actuelle du profil
                        $oldPicture = $user->getprofilPicturePath() . '/' . $user->getpictureName();

                        // Supprime l'ancienne image de profil
                        unlink($oldPicture);

                        // Chemin de destination du fichier
                        $destination = $this->getParameter('kernel.project_dir') . '/public/assets/uploads';

                        // Redéfini le nom du fichier
                        $newFilename = $user->getUsername() . '-' . uniqid() . '.' . $pictureFile->guessExtension();

                        // Déplace le fichier dans le dossier uploads en le renommant
                        $pictureFile->move($destination, $newFilename);

                        // Attribution des valeurs
                        $user->setPictureName($newFilename);
                        $user->setProfilPicturePath($destination);

                        // Attribution d'un nouveau token
                        $user->setToken(bin2hex(random_bytes(64)));

                        // Sauvegarde les modifications
                        $manager->flush();

                        // Message de confirmation
                        $this->addFlash(
                            'success',
                            "Votre profil à bien été modifié."
                        );

                        // Renvoie vers la page du profil
                        header("Location: /profil");

                        // Empêche l'exécution du reste du script
                        die();
                    }
                    else // Si l'adresse mail existe déjà
                    {
                        $this->addFlash(
                            'danger',
                            "Cet email est déjà utilisé"
                        );

                        // Renvoie vers la page du profil
                        header("Location: /profil");

                        // Empêche l'exécution du reste du script
                        die();
                    }
                }
                else // Si une des valeurs du formulaire est vide
                {
                    $this->addFlash(
                        'danger',
                        "Tous les champs n'ont pas été renseignés"
                    );

                    // Renvoie vers la page du profil
                    header("Location: /profil");

                    // Empêche l'exécution du reste du script
                    die();
                }
            }
            else // Si les jetons ne correspondent pas
            {
                $this->addFlash(
                    'danger',
                    "La modification du profil n'a pas pu être effectué"
                );

                // Renvoie vers la page du profil
                header("Location: /profil");

                // Empêche l'exécution du reste du script
                die();
            }
        }

        // Affiche la page de modification du profil de l'utilisateur avec le formulaire
        return $this->render('profile/profileEdit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}