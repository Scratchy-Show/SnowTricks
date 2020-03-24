<?php


namespace App\Controller;

use App\Entity\Category;
use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\Video;
use App\Form\TrickType;
use App\Service\FileUploader;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController // Permet d'utiliser la méthode render
{
    /**
     * @Route("/figure/creer", name="trick_create")
     * @IsGranted("ROLE_USER")
     */
    public function createTrick(Request $request, FileUploader $fileUploader)
    {
        // Crée une instance de Trick
        $trick = new Trick();

        // Création du formulaire
        $form = $this->createForm(TrickType::class, $trick);

        // Met à jour le formulaire à l'aide des infos reçues de l'utilisateur
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid())
        {
            // Récupère l'image principal
            $mainPicture = $form->get('mainPicture')->getData();

            // Chemin de destination de l'image
            $destination = $this->getParameter('trick_picture_directory') . '/' . $trick->getName();

            // Défini son nom et la déplace dans le dossier cible
            $fileName = $fileUploader->upload($mainPicture->getFile(), $destination);

            // Attribution des valeurs
            $mainPicture->setName($fileName);
            $mainPicture->setPath($destination);
            $mainPicture->setTrick($trick);

            // Attribut l'image principal à la figure
            $trick->setMainPicture($mainPicture);

            // Pour chaque image de la collection
            foreach ($trick->getPictures() as $picture)
            {
                // Défini son nom et la déplace dans le dossier cible
                $fileName = $fileUploader->upload($picture->getFile(), $destination);

                // Attribution des valeurs
                $picture->setName($fileName);
                $picture->setPath($destination);
                $picture->setTrick($trick);
            }

            // Pour chaque Url de la collection
            foreach($trick->getVideos() as $video)
            {
                // Récupère l'Url de chaque vidéo
                $url = $video->getUrl();

                // Attribution des valeurs
                $video->setUrl($url);
                $video->setTrick($trick);
            }

            // Récupère le gestionnaire d'entités
            $entityManager = $this->getDoctrine()->getManager();

            // Crée une instance de Category
            $category = new Category();

            // Récupère la nouvelle catégorie
            $newCategory = $trick->getCategory()->getAdd();

            // Si une nouvelle catégorie à été ajouté
            if ($newCategory != null )
            {
                // Attribut la valeur
                $category->setName($newCategory);

                // Doctrine gère maintenant l'objet
                $entityManager->persist($category);

                // Insère une nouvelle ligne dans la table Category
                $entityManager->flush();

                // Récupère la dernier entrée dans la table Category
                $findNewCategory = $entityManager->getRepository(Category::class)->findBy(
                    [],
                    ['id' => 'desc'],
                    1,
                    0
                );

                // Attribution de la valeur
                $trick->setCategory($findNewCategory[0]);
            }
            else // Si aucune nouvelle catégorie n'à été ajouté
            {
                // Récupère la catégorie déjà existante
                $oldCategory = $trick->getCategory();

                // Attribution de la valeur
                $trick->setCategory($oldCategory->getName());
            }

            // Par défaut, l'utilisateur est celui connecté
            $trick->setUser($this->getUser());

            // Doctrine gère maintenant l'objet
            $entityManager->persist($trick);

            // Insère une nouvelle ligne dans la table Trick
            $entityManager->flush();

            // Message de confirmation
            $this->addFlash(
                'success',
                "La figure <strong>" . $trick->getName() . "</strong> a bien été ajouté"
            );

            // Redirection vers la page listant les figures
            return $this->redirectToRoute('home');
        }

        // Affiche la page de création d'une figure avec le formulaire
        return $this->render('trick/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/figure/modifier/{id}", name="trick_edit")
     * @IsGranted("ROLE_USER")
     */
    public function editTrick(Request $request, FileUploader $fileUploader, $id)
    {
        // Récupère le gestionnaire d'entités
        $entityManager = $this->getDoctrine()->getManager();

        // Récupère la figure
        $trick = $entityManager->getRepository(Trick::class)->find($id);

        // Si une figure correspond à l'id
        if ($trick != null)
        {
            // Actuel nom du dossier où se trouve les images
            $oldNameFolder = $trick->getName();

            // Création du formulaire de figure
            $formTrickType = $this->createForm(TrickType::class, $trick);

            // Met à jour le formulaire à l'aide des infos reçues de l'utilisateur
            $formTrickType->handleRequest($request);

            // Récupère les images liées à la figure
            $pictures = $entityManager->getRepository(Picture::class)->findBy(['trick' => $id]);

            // Récupère les urls liées à la figure
            $videos = $entityManager->getRepository(Video::class)->findBy(['trick' => $id]);

            // Si le formulaire de modification d'une figure est soumis et valide
            if ($formTrickType->isSubmitted() && $formTrickType->isValid())
            {
                // Récupère l'id de la figure
                $trickId = $_POST['trickId'];

                // Vérifie si l'id de la figure correspond à l'id donné par le formulaire
                if ($trick->getId() == $trickId)
                {
                    // Récupère la description
                    $description = $formTrickType->get('description')->getData();

                    // Récupère l'id de la catégorie
                    $categoryId = $formTrickType->get('category')->getData()->getName()->getId();

                    // Récupère la catégorie
                    $category = $entityManager->getRepository(Category::class)->find($categoryId);

                    // Récupère le nom de la figure
                    $trickName = $formTrickType->get('name')->getData();

                    // Si $description, $category et $trickName ne sont pas vident
                    if (!empty($description) && !empty($category) && !empty($trickName)) {
                        // Nouveau chemin de destination des images
                        $destination = $this->getParameter('trick_picture_directory') . '/' . $trickName;

                        // Si le nom de la figure a changé
                        if ($oldNameFolder != $trick->getName())
                        {
                            // Création du nouveau dossier
                            mkdir($destination);

                            // Pour chaque image
                            foreach($pictures as $picture)
                            {
                                // Récupère l'actuel chemin de l'image
                                $oldPath = $picture->getPath();

                                // Récupère l'actuel nom de l'image
                                $name = $picture->getName();

                                // Concatène l'ancien chemin et le nom de l'image
                                $oldPathNamePicture = $oldPath . '/' . $name;

                                // Concatène le nouveau chemin et le nom de l'image
                                $newPathNamePicture = $destination . '/' . $name;

                                // Copie les fichiers dans le nouveau dossier
                                copy($oldPathNamePicture, $newPathNamePicture);

                                // Attributions des valeurs
                                $picture->setPath($destination);
                                $picture->setTrick($trick);
                                $picture->setName($name);

                                // Enregistre la modification en BDD
                                $entityManager->persist($picture);
                            }

                            // Crée une instance de Filesystem
                            $filesystem = new Filesystem();

                            // Supprime l'ancien dossier
                            $filesystem->remove($oldPath);
                        }
                        else // Si le nom de la figure n'a pas changé
                        {
                            // Pour chaque image
                            foreach($pictures as $picture)
                            {
                                // Lie l'image à la figure
                                $picture->setTrick($trick);

                                // Enregistre la modification en BDD
                                $entityManager->persist($picture);
                            }
                        }

                        // Pour chaque video
                        foreach($videos as $video)
                        {
                            // Lie la vidéo à la figure
                            $video->setTrick($trick);

                            // Enregistre la modification en BDD
                            $entityManager->persist($video);
                        }

                        // Par défaut, l'utilisateur est celui connecté
                        $user = $this->getUser();

                        // Attribution des valeurs
                        $trick->setDescription($description);
                        $trick->setCategory($category);
                        $trick->setUser($user);
                        $trick->setUpdateDate(new DateTime());
                        $trick->setName($trickName);

                        // Modifie la figure en BDD
                        $entityManager->flush();

                        // Message de confirmation
                        $this->addFlash(
                            'success',
                            "La figure <strong>" . $trickName . "</strong> a bien été modifiée"
                        );

                        // Renvoie vers la page du profil
                        header("Location: /figure/modifier/" . $trickId);

                        // Empêche l'exécution du reste du script
                        die();
                    }
                    else // Si $description, $category ou $trickName sont vident
                    {
                        // Message d'erreur
                        $this->addFlash(
                            'danger',
                            "Tous les champs n'ont pas été renseignés."
                        );

                        // Renvoie vers la page du profil
                        header("Location: /figure/modifier/" . $trickId);

                        // Empêche l'exécution du reste du script
                        die();
                    }
                }
                else // Si les id ne correspondent pas
                {
                    // Message d'erreur
                    $this->addFlash(
                        'danger',
                        "On ne touche pas aux champs cachés !"
                    );

                    // Redirection vers la page d'accueil
                    return $this->redirectToRoute('home');
                }
            }
            // Affiche par défaut la page de création d'une figure
            return $this->render('trick/edit.html.twig', [
                'formTrickType' => $formTrickType->createView(),
                'trick' => $trick,
                'pictures' => $pictures,
                'videos' => $videos
            ]);
        }
        else // Si aucune figure ne correspond à l'id
        {
            // Message d'erreur
            $this->addFlash(
                'danger',
                "Aucune figure ne correspond."
            );

            // Redirection vers la page listant les figures
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/figure/supprimer/{trickId}", name="trick_delete")
     * @IsGranted("ROLE_USER")
     */
    public function deleteTrick(Request $request, $trickId)
    {
        // Récupère le gestionnaire d'entités
        $entityManager = $this->getDoctrine()->getManager();

        // Récupère la figure
        $trick = $entityManager->getRepository(Trick::class)->find($trickId);

        // Si une figure correspond à l'id
        if ($trick != null)
        {
            // Si la figure possède une image principal
            if ($trick->getMainPicture() != null)
            {
                // Récupère l'id de l'image principal
                $mainPictureId = $trick->getMainPicture()->getId();

                // Récupère l'image principal
                $mainPicture = $entityManager->getRepository(Picture::class)->findOneBy(
                    array('id' => $mainPictureId)
                );

                // Supprime l'association de l'image principal à la figure
                $trick->setMainPicture(null);

                // Persiste les données dans la BDD
                $entityManager->flush();

                // Récupère le chemin des images
                $oldPath = $mainPicture->getPath();

                // Crée une instance de Filesystem
                $filesystem = new Filesystem();

                // Supprime l'ancien dossier
                $filesystem->remove($oldPath);
            }

            // Supprime la figure
            $entityManager->remove($trick);

            // Persiste les données dans la BDD
            $entityManager->flush();

            $this->addflash(
                'success',
                "La figure <strong>{$trick->getName()}</strong> a bien été supprimé"
            );

            // redirection vers l'accueil
            return $this->redirectToRoute('home');
        }
        else // Si aucune figure ne correspond à l'id
        {
            // Message d'erreur
            $this->addFlash(
                'danger',
                "Aucune figure ne correspond."
            );

            // Redirection vers la page listant les figures
            return $this->redirectToRoute('home');
        }
    }
}