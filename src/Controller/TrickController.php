<?php


namespace App\Controller;

use App\Entity\Category;
use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\Video;
use App\Form\PictureType;
use App\Form\TrickType;
use App\Form\VideoType;
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
            // Chemin de destination de l'image
            $destination = $this->getParameter('trick_picture_directory') . '/' . $trick->getName();

            /*



            // Récupère l'image principal
            $mainPicture = $form->get('mainPicture')->getData();

            // Défini son nom et la déplace dans le dossier cible
            $fileName = $fileUploader->upload($mainPicture->getFile(), $destination);

            // Attribution des valeurs
            $mainPicture->setName($fileName);
            $mainPicture->setPath($destination);
            $mainPicture->setTrick($trick);



            */

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
    public function editTrick(Request $request, $id)
    {
        // Récupère le gestionnaire d'entités
        $entityManager = $this->getDoctrine()->getManager();

        // Récupère la figure
        $trick = $entityManager->getRepository(Trick::class)->find($id);

        // Si une figure correspond à l'id
        if ($trick != null)
        {
            // Création du formulaire de figure
            $formTrickType = $this->createForm(TrickType::class, $trick);
            // Création du formulaire d'image
            $formPictureType = $this->createForm(PictureType::class);
            // Création du formulaire des videos
            $formVideoType = $this->createForm(VideoType::class);

            // Met à jour le formulaire à l'aide des infos reçues de l'utilisateur
            $formTrickType->handleRequest($request);
            // Met à jour le formulaire à l'aide des infos reçues de l'utilisateur
            $formPictureType->handleRequest($request);
            // Met à jour le formulaire à l'aide des infos reçues de l'utilisateur
            $formVideoType->handleRequest($request);

            // Récupère les images liées à la figure
            $pictures = $entityManager->getRepository(Picture::class)->findBy(['trick' => $id]);
            // Récupère les urls liées à la figure
            $videos = $entityManager->getRepository(Video::class)->findBy(['trick' => $id]);

            /*

            // Défini la première image de la collection comme image principal
            $mainPicture = $pictures[0];

            // Si il n'y a pas d'image
            if ($pictures == null)
            {
                // L'image principal est l'image par défaut
                $mainPicture = 'trick_picture_directory' . '/' . 'default.jpg';

                return $mainPicture;
            }

            */

            // Si le formulaire d'ajout d'image est soumis et valide
            if ($formPictureType->isSubmitted() && $formPictureType->isValid())
            {
                // Récupère la nouvelle image
                $pictureFile = $formPictureType->get('file')->getData();
                // Récupère l'id de la figure
                $trickId = $_POST['trickId'];

                // Transfère ces deux variables à la méthode addPictureTrick
                $response = $this->forward('App\Controller\TrickController::addPictureTrick', [
                    'pictureFile'  => $pictureFile,
                    'trickId' => $trickId,
                ]);

                return $response;
            }
            elseif ($formPictureType->isSubmitted() && ($formPictureType->isValid() == false)) // Si le formulaire contient des erreurs
            {
                // Message d'erreur
                $this->addFlash(
                    'danger',
                    "L'image doit être de type jpeg, jpg ou png.
                    Elle doit faire minimum 500px de large et 300px de haut.
                    Et faire maximum 1500px de large et 1200px de haut."
                );
            }

            // Si le formulaire d'ajout de vidéo est soumis et valide
            if ($formVideoType->isSubmitted() && $formVideoType->isValid())
            {
                // Récupère l'Url
                $url = $formVideoType->get('url')->getData();
                // Récupère l'id de la figure
                $trickId = $_POST['trickId'];

                // Transfère ces deux variables à la méthode addVideoTrick
                $response = $this->forward('App\Controller\TrickController::addVideoTrick', [
                    'url'  => $url,
                    'trickId' => $trickId,
                ]);

                return $response;
            }

            // Si le formulaire de modification d'une figure est soumis
            if ($formTrickType->isSubmitted())
            {
                // Récupère l'id de la figure
                $trickId = $_POST['trickId'];

                // Récupère le nom de la figure
                $trickName = $formTrickType->get('name')->getData();

                // Récupère le gestionnaire d'entités
                $entityManager = $this->getDoctrine()->getManager();

                // Récupère la figure
                $trick = $entityManager->getRepository(Trick::class)->findOneBy(
                    ['id' => $trickId, 'name' => $trickName],
                    []
                );

                // Si la figure est trouvé
                if ($trick != null)
                {
                    // Récupère la description
                    $description = $formTrickType->get('description')->getData();

                    // Récupère l'id de la catégorie
                    $categoryId = $formTrickType->get('category')->getData()->getId();

                    // Récupère la catégorie
                    $category = $entityManager->getRepository(Category::class)->find($categoryId);

                    // Si $description et $category ne sont pas vident
                    if (!empty($description) && !empty($category))
                    {

                        $arrayPicture = $trick->getPictures();

                        $arrayVideo = $trick->getVideos();

                        dump($arrayPicture);
                        dump($arrayVideo);

                        foreach($arrayPicture as $picture)
                        {
                            dump($picture->setTrick($trick));
                            $picture->setTrick($trick);
                            $entityManager->flush();
                        }

                        foreach($arrayVideo as $video)
                        {
                            dump($video->setTrick($trick));
                            $video->setTrick($trick);
                            $entityManager->flush();
                        }








                        // Par défaut, l'utilisateur est celui connecté
                        $user = $this->getUser();

                        // Attribution des valeurs
                        $trick->setDescription($description);
                        $trick->setCategory($category->getName());
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

                        // Redirection vers la page listant les figures
                        return $this->redirectToRoute('home');
                    }
                    else // Si $description ou $category sont vident
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
                else // Si Si la figure n'est pas trouvée
                {
                    // Message d'erreur
                    $this->addFlash(
                        'danger',
                        "On ne touche pas aux champs cachés."
                    );

                    // Redirection vers la page d'accueil
                    return $this->redirectToRoute('home');
                }
            }

            // Affiche par défaut la page de création d'une figure
            return $this->render('trick/edit.html.twig', [
                'formTrickType' => $formTrickType->createView(),
                'formPictureType' => $formPictureType->createView(),
                'formVideoType' => $formVideoType->createView(),
                'trick' => $trick,
                'pictures' => $pictures,
                'mainPicture' => $mainPicture,
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
     * @Route("/figure/ajouter/image/{trick}", name="trick_picture_add")
     * @IsGranted("ROLE_USER")
     */
    public function addPictureTrick(FileUploader $fileUploader, $trickId, $pictureFile)
    {
        // Si $trick n'est pas vide et est numérique
        if ((!empty($trickId)) && (is_numeric($trickId)))
        {
            // Récupère le gestionnaire d'entités
            $entityManager = $this->getDoctrine()->getManager();

            // Récupère la figure
            $trick = $entityManager->getRepository(Trick::class)->find($trickId);

            // Si la figure est trouvé
            if ($trick != null)
            {
                // Chemin de destination de l'image
                $destination = $this->getParameter('trick_picture_directory') . '/' . $trick->getName();

                // Défini son nom et la déplace dans le dossier cible
                $fileName = $fileUploader->upload($pictureFile, $destination);

                // Crée une instance de Picture
                $picture = new Picture();

                // Attribution des valeurs
                $picture->setName($fileName);
                $picture->setPath($destination);
                $picture->setTrick($trick);

                // Doctrine gère maintenant l'objet
                $entityManager->persist($picture);

                // Insère une nouvelle ligne dans la table Trick
                $entityManager->flush();

                // Message de confirmation
                $this->addFlash(
                    'success',
                    "L'image a bien été ajouté"
                );

                // Renvoie vers la page du profil
                header("Location: /figure/modifier/" . $trickId);

                // Empêche l'exécution du reste du script
                die();
            }
            else // Si il n'y a pas de correspondance
            {
                // Message d'erreur
                $this->addFlash(
                    'danger',
                    "La figure n'a pas été trouvé."
                );

                // Redirection vers la page d'accueil
                return $this->redirectToRoute('home');
            }
        }
        else // Si $trick est vide ou n'est pas numérique
        {
            // Message d'erreur
            $this->addFlash(
                'danger',
                "Veuiller ne pas modifier l'Url."
            );

            // Redirection vers la page d'accueil
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/figure/supprimer/image/{picture}/{trick}", name="trick_picture_delete")
     * @IsGranted("ROLE_USER")
     */
    public function deletePictureTrick($pictureId, $trickId)
    {
        // Si $picture et $trick ne sont pas vident et sont numérique
        if ((!empty($pictureId)) && (is_numeric($pictureId)) && (!empty($trickId)) && (is_numeric($trickId)))
        {
            // Récupère l'image
            $picture = $this->getDoctrine()->getRepository(Picture::class)->find($pictureId);

            // Récupère la figure
            $trick = $this->getDoctrine()->getRepository(Trick::class)->find($trickId);

            // Si la figure éxiste
            if ($trick != null)
            {
                // Si l'image est trouvée
                if ($picture != null)
                {
                    // Récupère le chemin de l'image
                    $path = $picture->getPath();

                    // Récupère le nom de l'image
                    $name = $picture->getName();

                    // Concatène le chemin et le nom
                    $filePicture = $path . '/' . $name;

                    // Crée une instance de Filesystem
                    $filesystem = new Filesystem();

                    // Supprime le fichier
                    $filesystem->remove($filePicture);

                    // Récupère le gestionnaire d'entités
                    $entityManager = $this->getDoctrine()->getManager();

                    // Supprime l'image de la table Picture
                    $entityManager->remove($picture);

                    // Persiste les données dans la BDD
                    $entityManager->flush();

                    // Message de confirmation
                    $this->addFlash(
                        'success',
                        "L'image a bien été supprimé"
                    );

                    // Renvoie vers la page du profil
                    header("Location: /figure/modifier/" . $trickId);

                    // Empêche l'exécution du reste du script
                    die();
                }
                else // Si l'image n'est pas trouvé
                {
                    // Message d'erreur
                    $this->addFlash(
                        'danger',
                        "L'image n'a pas été trouvé."
                    );

                    // Renvoie vers la page du profil
                    header("Location: /figure/modifier/" . $trickId);

                    // Empêche l'exécution du reste du script
                    die();
                }
            }
            else
            {
                // Message d'erreur
                $this->addFlash(
                    'danger',
                    "La figure n'existe pas."
                );

                // Redirection vers la page d'accueil
                return $this->redirectToRoute('home');
            }
        }
        else // Si $picture et $trick sont vident ou non numérique
        {
            // Message d'erreur
            $this->addFlash(
                'danger',
                "Veuiller ne pas modifier l'Url."
            );

            // Redirection vers la page d'accueil
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/figure/ajouter/video/{url}/{trickId}", name="trick_video_add")
     * @IsGranted("ROLE_USER")
     */
    public function addVideoTrick($url, $trickId)
    {
        // Si l'Url n'est pas vide
        if (!empty($url))
        {
            // Récupère le gestionnaire d'entités
            $entityManager = $this->getDoctrine()->getManager();

            // Récupère la figure
            $trick = $entityManager->getRepository(Trick::class)->find($trickId);

            // Si la figure est trouvé
            if ($trick != null)
            {
                // Crée une instance de Video
                $video = new Video();

                // Attribution des valeurs
                $video->setUrl($url);
                $video->setTrick($trick);

                // Doctrine gère maintenant l'objet
                $entityManager->persist($video);

                // Insère une nouvelle ligne dans la table Video
                $entityManager->flush();

                // Message de confirmation
                $this->addFlash(
                    'success',
                    "La vidéo a bien été ajouté"
                );

                // Renvoie vers la page du profil
                header("Location: /figure/modifier/" . $trickId);

                // Empêche l'exécution du reste du script
                die();
            }
            else // Si il n'y a pas de correspondance
            {
                // Message d'erreur
                $this->addFlash(
                    'danger',
                    "La figure n'a pas été trouvé."
                );

                // Redirection vers la page d'accueil
                return $this->redirectToRoute('home');
            }
        }
        else
        {
            // Message d'erreur
            $this->addFlash(
                'danger',
                "Veuiller indiquer une Url."
            );

            // Renvoie vers la page du profil
            header("Location: /figure/modifier/" . $trickId);

            // Empêche l'exécution du reste du script
            die();
        }
    }

    /**
     * @Route("/figure/supprimer/video/{videoId}/{trickIid}", name="trick_video_delete")
     * @IsGranted("ROLE_USER")
     */
    public function deleteVideoTrick($videoId, $trickId)
    {

        // Si $videoId et $trick ne sont pas vident et $trick est bien numérique
        if ((!empty($videoId)) && (!empty($trickId)) && (is_numeric($trickId)))
        {
            // Récupère la vidéo
            $url = $this->getDoctrine()->getRepository(Video::class)->find($videoId);

            // Récupère la figure
            $trick = $this->getDoctrine()->getRepository(Trick::class)->find($trickId);

            // Si la figure éxiste
            if ($trick != null)
            {
                // Si l'Url est trouvée
                if ($url != null)
                {
                    // Récupère le gestionnaire d'entités
                    $entityManager = $this->getDoctrine()->getManager();

                    // Supprime la vidéo de la table Video
                    $entityManager->remove($url);

                    // Persiste les données dans la BDD
                    $entityManager->flush();

                    // Message de confirmation
                    $this->addFlash(
                        'success',
                        "La vidéo a bien été supprimé"
                    );

                    // Renvoie vers la page du profil
                    header("Location: /figure/modifier/" . $trickId);

                    // Empêche l'exécution du reste du script
                    die();
                }
                else // Si l'Url n'est pas trouvé
                {
                    // Message d'erreur
                    $this->addFlash(
                        'danger',
                        "La vidéo n'a pas été trouvé."
                    );

                    // Renvoie vers la page du profil
                    header("Location: /figure/modifier/" . $trickId);

                    // Empêche l'exécution du reste du script
                    die();
                }
            }
            else
            {
                // Message d'erreur
                $this->addFlash(
                    'danger',
                    "La figure n'existe pas."
                );

                // Redirection vers la page d'accueil
                return $this->redirectToRoute('home');
            }
        }
        else // Si $picture et $trick sont vident ou non numérique
        {
            // Message d'erreur
            $this->addFlash(
                'danger',
                "Veuiller ne pas modifier l'Url."
            );

            // Redirection vers la page d'accueil
            return $this->redirectToRoute('home');
        }
    }
}