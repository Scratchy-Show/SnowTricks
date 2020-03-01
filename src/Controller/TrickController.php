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
            // Chemin de destination de l'image
            $destination = $this->getParameter('trick_picture_directory') . '/' . $trick->getName();

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


            // Si le formulaire de modification d'une image est soumis et valide
            if ($formPictureType->isSubmitted() && $formPictureType->isValid())
            {
                // Récupère la nouvelle image
                $pictureFile = $formPictureType->get('file')->getData();
                dump($pictureFile);
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

                // Affiche la page de création d'une figure avec le formulaire
                return $this->render('trick/edit.html.twig', [
                    'formTrickType' => $formTrickType->createView(),
                    'formPictureType' => $formPictureType->createView(),
                    'formVideoType' => $formVideoType->createView(),
                    'trick' => $trick,
                    'pictures' => $pictures,
                    'videos' => $videos
                ]);
            }

            // Affiche la page de création d'une figure avec le formulaire
            return $this->render('trick/edit.html.twig', [
                'formTrickType' => $formTrickType->createView(),
                'formPictureType' => $formPictureType->createView(),
                'formVideoType' => $formVideoType->createView(),
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
     * @Route("/figure/modifier/image/{trick}/{picture}", name="trick_picture_edit")
     * @IsGranted("ROLE_USER")
     *//*
    public function editPictureTrick(Request $request, FileUploader $fileUploader, $trick, $picture)
    {
        // Si les id $trick et $picture ne sont pas vide et correspondent à des chiffres
        if ((!empty($trick) && !empty($picture)) && (is_numeric($trick) && is_numeric($picture)))
        {
            // Récupère le gestionnaire d'entités
            $entityManager = $this->getDoctrine()->getManager();

            // Récupère la figure
            $trick = $entityManager->getRepository(Trick::class)->find($trick);

            // Récupère l'image
            $picture = $entityManager->getRepository(Picture::class)->find($picture);

            // Si une figure et une image correspondent
            if ($trick != null && $picture != null)
            {





            }
            else // Si il n'y a pas de correspondance
            {
                // Message d'erreur
                $this->addFlash(
                    'danger',
                    "La figure ou l'image non pas été trouvé."
                );

                // Redirection vers la page d'accueil
                return $this->redirectToRoute('home');
            }
        }
        else // Si l'une des deux est vide ou contient autres que des chiffres
        {
            // Message d'erreur
            $this->addFlash(
                'danger',
                "Veuiller ne pas modifier l'Url."
            );

            // Redirection vers la page d'accueil
            return $this->redirectToRoute('home');
        }
    }*/
}