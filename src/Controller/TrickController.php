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

                // Récupère le gestionnaire d'entités
                $entityManager = $this->getDoctrine()->getManager();

                // Récupère la figure
                $trick = $entityManager->getRepository(Trick::class)->find($trickId);

                // Si la figure est trouvé
                if ($trick != null)
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
                    if (!empty($description) && !empty($category) && !empty($trickName))
                    {


                        $arrayPicture = $trick->getPictures();
                        dump($arrayPicture);

                        $arrayVideo = $trick->getVideos();
                        dump($arrayVideo);

                        $test = $arrayPicture->getValues();
                        dump($test);

                        foreach($arrayPicture->toArray() as $picture)
                        {
                            dump($picture->setTrick($trick));
                            $picture->setTrick($trick);
                            $entityManager->persist($picture);
                        }

                        foreach($trick->getVideos() as $video)
                        {

                            $video->setTrick($trick);
                            dump($video->setTrick($trick));
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
          //      'formVideoType' => $formVideoType->createView(),
                'trick' => $trick,
                'pictures' => $pictures,
           //     'mainPicture' => $mainPicture,
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

      /*

        $fileSystem = new Filesystem();

        dump($trick->getPictures());

        dump(sizeof($trick->getPictures()));
        dump($trick->getPictures());


        $array = $trick->getPictures();


        $array = $array->getValues();
        foreach ($array as $obj) {
            dump($obj);
        }

        foreach($trick->getPictures() as $picture)
        {
            dump($picture);

            $fileSystem->remove($picture->getPath() . '/' . $picture->getName());
        }
      */


/*
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


*/


        // Supprime la figure
        $entityManager->remove($trick);

        // Persiste les données dans la BDD
        $entityManager->flush();

        $this->addflash(
            'success',
            "La figure <strong>{$trick->getName()}</strong> a été supprimé"
        );

        return $this->redirectToRoute('home');
    }
}