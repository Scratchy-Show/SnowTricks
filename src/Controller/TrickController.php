<?php


namespace App\Controller;

use App\Entity\Category;
use App\Entity\Trick;
use App\Form\TrickType;
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
}