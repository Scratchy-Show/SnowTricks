<?php


namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\Video;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Service\FileUploader;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController // Permet d'utiliser la méthode render
{
    /**
     * @Route("/figure/creer", name="trick_create")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param FileUploader $fileUploader
     * @return RedirectResponse|Response
     */
    public function createTrick(
        Request $request,
        FileUploader $fileUploader
    ) {
        // Crée une instance de Trick
        $trick = new Trick();

        // Création du formulaire
        $form = $this->createForm(TrickType::class, $trick);

        // Met à jour le formulaire à l'aide des infos reçues de l'utilisateur
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère l'image principale
            $mainPicture = $form->get('mainPicture')->getData();

            // Chemin de destination de l'image
            $destination = $this->getParameter('trick_picture_directory');

            // Si une image principale est présente
            if ($mainPicture != null) {
                // Défini son nom et la déplace dans le dossier cible
                $fileName = $fileUploader->upload($mainPicture->getFile(), $destination);

                // Attribution des valeurs
                $mainPicture->setName($fileName);
                $mainPicture->setPath('uploads/trick');
                $mainPicture->setTrick($trick);

                // Attribut l'image principale à la figure
                $trick->setMainPicture($mainPicture);
            }

            // Pour chaque image de la collection
            foreach ($trick->getPictures() as $picture) {
                // Défini son nom et la déplace dans le dossier cible
                $fileName = $fileUploader->upload($picture->getFile(), $destination);

                // Attribution des valeurs
                $picture->setName($fileName);
                $picture->setPath('uploads/trick');
                $picture->setTrick($trick);
            }

            // Pour chaque Url de la collection
            foreach ($trick->getVideos() as $video) {
                $video->setTrick($trick);
            }

            // Récupère le gestionnaire d'entités
            $entityManager = $this->getDoctrine()->getManager();

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

            // Redirection vers la page d'accueil avec une ancre sur les figures
            return $this->redirect($this->generateUrl('home') . '#tricks');
        }

        // Affiche la page de création d'une figure avec le formulaire
        return $this->render('trick/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/figure/details/{slug}/{page}", requirements={"page" = "\d+"}, name="trick_details")
     * @param Request $request
     * @param $slug
     * @param $page
     * @return RedirectResponse|Response
     */
    public function detailsTrick(
        Request $request,
        $slug,
        $page
    ) {
        // Récupère le gestionnaire d'entités
        $entityManager = $this->getDoctrine()->getManager();

        // Récupère la figure correspondant au slug
        $trick = $entityManager->getRepository(Trick::class)->findOneBy(['slug' => $slug]);

        // Récupère les images liées à la figure
        $pictures = $entityManager->getRepository(Picture::class)->findBy(['trick' => $trick->getId()]);

        // Récupère les vidéos liées à la figure
        $videos = $entityManager->getRepository(Video::class)->findBy(['trick' => $trick->getId()]);

        // Si aucune figure ne correspond à l'id
        if ($trick == null) {
            // Message d'erreur
            $this->addFlash(
                'danger',
                "Aucune figure ne correspond."
            );

            // Redirection vers la page d'accueil
            return $this->redirectToRoute('home');
        }

        // Nombre de commentaires maximum par page
        $nbCommentsPerPage = 10;

        // Récupère les commentaires du plus récent au plus anciens avec une pagination
        $comments = $entityManager->getRepository(Comment::class)
            ->getAllCommentsWithPaging($page, $nbCommentsPerPage, $trick);

        // Pagination
        $paging = array(
            // Numéro de la page souhaitée
            'page' => $page,
            // Nombre de page total
            'nbPages' => ceil(count($comments) / $nbCommentsPerPage),
            // Le nom de la route
            'path' => 'trick_details',
            // Paramètres supplémentaire nécessaires pour la route
            'pathSettings' => array('slug' => $slug)
        );

        // Crée une instance de Comment
        $comment = new Comment();

        // Création du formulaire
        $form = $this->createForm(CommentType::class, $comment);

        // Met à jour le formulaire à l'aide des infos reçues de l'utilisateur
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Attribution des valeurs
            $comment->setTrick($trick);
            // Par défaut, l'utilisateur est celui connecté
            $comment->setUser($this->getUser());

            // Doctrine gère maintenant l'objet
            $entityManager->persist($comment);

            // Insère une nouvelle ligne dans la table Trick
            $entityManager->flush();

            // Message de confirmation
            $this->addFlash(
                'success',
                'Votre commentaire a bien été enregistré'
            );

            // Redirection vers la page de la figure avec une ancre sur les commentaires
            return $this->redirect(
                $this->generateUrl('trick_details', array('slug' => $trick->getSlug(), 'page' => $page)) . '#comments'
            );
        }
        // Affiche par défaut la page de la figure
        return $this->render('trick/details.html.twig', [
            'trick' => $trick,
            'pictures' => $pictures,
            'videos' => $videos,
            'comments' => $comments,
            'paging' => $paging,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/figure/modifier/{slug}", name="trick_edit")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param $slug
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function editTrick(
        Request $request,
        $slug
    ) {
        // Récupère le gestionnaire d'entités
        $entityManager = $this->getDoctrine()->getManager();

        // Récupère la figure
        $trick = $entityManager->getRepository(Trick::class)->findOneBy(['slug' => $slug]);

        // Si une figure correspond à l'id
        if ($trick != null) {
            // Création du formulaire de figure
            $formTrickType = $this->createForm(TrickType::class, $trick);

            // Récupère l'id de la figure
            $trickId = $trick->getId();

            // Récupère les images liées à la figure
            $pictures = $entityManager->getRepository(Picture::class)->findBy(['trick' => $trickId]);

            // Récupère le nom de la figure
            $trickName = $trick->getName();

            // Récupère les urls liées à la figure
            $videos = $entityManager->getRepository(Video::class)->findBy(['trick' => $trickId]);

            // Met à jour le formulaire à l'aide des infos reçues de l'utilisateur
            $formTrickType->handleRequest($request);

            // Si le formulaire de modification d'une figure est soumis et valide
            if ($formTrickType->isSubmitted() && $formTrickType->isValid()) {
                // Si la valeur du champ trickId est non définie
                if (!isset($_POST['trickId'])) {
                    // Message d'erreur
                    $this->addFlash(
                        'danger',
                        "On ne touche pas aux champs cachés !"
                    );

                    // Redirection vers la page d'accueil
                    return $this->redirectToRoute('home');
                }

                // Récupère l'id de la figure
                $formTrickId = $_POST['trickId'];

                // Vérifie si l'id de la figure correspond à l'id donné par le formulaire
                if ($trickId == $formTrickId) {
                    // Lie chaque image à la figure
                    foreach ($pictures as $picture) {
                        $picture->setTrick($trick);
                    }

                    // Lie chaque vidéo à la figure
                    foreach ($videos as $video) {
                        $video->setTrick($trick);
                    }
                    
                    // Récupère le nom de la figure
                    $trickName = $formTrickType->get('name')->getData();

                    // Attribution des valeurs
                    $trick->setUpdateDate(new DateTime());
                    // Par défaut, l'utilisateur est celui connecté
                    $trick->setUser($this->getUser());

                    // Modifie la figure en BDD
                    $entityManager->flush();

                    // Message de confirmation
                    $this->addFlash(
                        'success',
                        "La figure <strong>" . $trickName . "</strong> a bien été modifiée"
                    );

                    // Redirection vers la page de la figure
                    return $this->redirectToRoute('trick_details', [
                        'slug' => $trick->getSlug(),
                        'page' => 1
                    ]);
                } else // Si les id ne correspondent pas
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
            // Affiche par défaut la page de modification d'une figure
            return $this->render('trick/edit.html.twig', [
                'trick' => $trick,
                'trickName' => $trickName,
                'pictures' => $pictures,
                'videos' => $videos,
                'formTrickType' => $formTrickType->createView()
            ]);
        } else // Si aucune figure ne correspond à l'id
        {
            // Message d'erreur
            $this->addFlash(
                'danger',
                "Aucune figure ne correspond."
            );

            // Redirection vers la page d'accueil
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/figure/supprimer/{trickId}", name="trick_delete")
     * @IsGranted("ROLE_USER")
     * @param $trickId
     * @return RedirectResponse
     */
    public function deleteTrick(
        $trickId
    ) {
        // Récupère le gestionnaire d'entités
        $entityManager = $this->getDoctrine()->getManager();

        // Récupère la figure
        $trick = $entityManager->getRepository(Trick::class)->find($trickId);

        // Récupère les images liées à la figure
        $pictures = $entityManager->getRepository(Picture::class)->findBy(['trick' => $trickId]);

        // Si une figure correspond à l'id
        if ($trick != null) {
            // Supprime l'image en tant qu'image principale
            $trick->setMainPicture(null);

            // Persiste les données dans la BDD
            $entityManager->flush();

            // Pour chaque image
            foreach ($pictures as $picture) {
                // Récupère l'image
                $filePicture = $this->getParameter('trick_picture_directory') . '/' . $picture->getName();

                // Crée une instance de Filesystem
                $filesystem = new Filesystem();

                // Supprime le fichier
                $filesystem->remove($filePicture);
            }

            // Supprime la figure
            $entityManager->remove($trick);

            // Persiste les données dans la BDD
            $entityManager->flush();

            $this->addflash(
                'success',
                "La figure <strong>{$trick->getName()}</strong> a bien été supprimé"
            );

            // Redirection vers l'accueil
            return $this->redirectToRoute('home');
        } else // Si aucune figure ne correspond à l'id
        {
            // Message d'erreur
            $this->addFlash(
                'danger',
                "Aucune figure ne correspond."
            );

            // Redirection vers l'accueil
            return $this->redirectToRoute('home');
        }
    }
}
