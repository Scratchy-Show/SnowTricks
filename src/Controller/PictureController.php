<?php


namespace App\Controller;

use App\Entity\Picture;
use App\Entity\Trick;
use App\Form\PictureType;
use App\Service\FileUploader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PictureController extends AbstractController // Permet d'utiliser la méthode render
{
    /**
     * @Route("/figure/ajouter/image/{slug}", name="trick_picture_add")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param FileUploader $fileUploader
     * @param $slug
     * @return RedirectResponse|Response
     */
    public function addPictureTrick(
        Request $request,
        FileUploader $fileUploader,
        $slug
    ) {
        // Récupère le gestionnaire d'entités
        $entityManager = $this->getDoctrine()->getManager();

        // Récupère la figure
        $trick = $entityManager->getRepository(Trick::class)->findOneBy(['slug' => $slug]);

        // Si une figure correspond à l'id
        if ($trick != null) {
            // Création du formulaire d'image
            $form = $this->createForm(PictureType::class);

            // Met à jour le formulaire à l'aide des infos reçues de l'utilisateur
            $form->handleRequest($request);

            // Si le formulaire d'ajout d'image est soumis
            if ($form->isSubmitted() && $form->isValid()) {
                // Récupère la nouvelle image
                $pictureFile = $form->get('file')->getData();

                // Si il n'y a pas d'image
                if (empty($pictureFile)) {
                    // Message d'erreur
                    $this->addFlash(
                        'danger',
                        "Aucune image trouvée !"
                    );

                    // Redirection vers la page d'ajout d'image
                    return $this->redirectToRoute('trick_picture_add', [
                        'slug' => $slug
                    ]);
                }

                // Chemin de destination de l'image
                $destination = $this->getParameter('trick_picture_directory');

                // Défini son nom et la déplace dans le dossier cible
                $fileName = $fileUploader->upload($pictureFile, $destination);

                // Crée une instance de Picture
                $picture = new Picture();

                // Attribution des valeurs
                $picture->setName($fileName);
                $picture->setPath('uploads/trick');
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

                // Redirection vers la page d'édition la figure
                return $this->redirectToRoute('trick_edit', [
                    'slug' => $slug
                ]);
            }
            // Affiche par la page d'ajout d'une image
            return $this->render('trick/addPicture.html.twig', [
                'form' => $form->createView()
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
     * @Route("/figure/supprimer/image/{picture}/{trick}", name="trick_picture_delete")
     * @IsGranted("ROLE_USER")
     * @param $pictureId
     * @param $trickId
     * @return RedirectResponse
     */
    public function deletePictureTrick(
        $pictureId,
        $trickId
    ) {
        // Si $picture et $trick ne sont pas vident et sont numérique
        if ((!empty($pictureId)) && (is_numeric($pictureId)) && (!empty($trickId)) && (is_numeric($trickId))) {
            // Récupère l'image
            $picture = $this->getDoctrine()->getRepository(Picture::class)->find($pictureId);

            // Récupère la figure
            $trick = $this->getDoctrine()->getRepository(Trick::class)->find($trickId);

            // Si la figure éxiste
            if ($trick != null) {
                // Si l'image est trouvée
                if ($picture != null) {
                    // Si il y'a une image principale et que l'image a supprimer est l'image principale de la figure
                    if (($trick->getMainPicture() != null) &&
                        ($trick->getMainPicture()->getId() == $picture->getId())) {
                        // Supprime l'image en tant qu'image principale
                        $trick->setMainPicture(null);
                    }

                    // Récupère l'image
                    $filePicture = $this->getParameter('trick_picture_directory') . '/' . $picture->getName();

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

                    // Redirection vers la page d'édition la figure
                    return $this->redirectToRoute('trick_edit', [
                        'slug' => $trick->getSlug()
                    ]);
                } else // Si l'image n'est pas trouvé
                {
                    // Message d'erreur
                    $this->addFlash(
                        'danger',
                        "L'image n'a pas été trouvé."
                    );

                    // Redirection vers la page d'édition la figure
                    return $this->redirectToRoute('trick_edit', [
                        'slug' => $trick->getSlug()
                    ]);
                }
            } else // Si la figure n'existe pas
            {
                // Message d'erreur
                $this->addFlash(
                    'danger',
                    "La figure n'existe pas."
                );

                // Redirection vers la page d'accueil
                return $this->redirectToRoute('home');
            }
        } else // Si $picture et $trick sont vident ou non numérique
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
     * @Route("/figure/modifier/image-principale/{slug}", name="trick_main_picture_edit")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param $slug
     * @return RedirectResponse
     */
    public function editMainPicture(
        Request $request,
        $slug
    ) {
        if ($request->isMethod('post')) {
            // Récupère l'id de l'image
            $mainPictureId = $request->request->get("editMainPicture");

            // Si une image a bien été sélectionné
            if ($mainPictureId != null) {
                // Récupère la figure
                $trick = $this->getDoctrine()->getRepository(Trick::class)->findOneBy(['slug' => $slug]);

                // Si la figure est trouvé
                if ($trick != null) {
                    // Récupère l'image
                    $picture = $this->getDoctrine()->getRepository(Picture::class)->find($mainPictureId);

                    // Défini l'image en tant qu'image principale
                    $trick->setMainPicture($picture);

                    // Récupère le gestionnaire d'entités
                    $entityManager = $this->getDoctrine()->getManager();

                    // Persiste les données dans la BDD
                    $entityManager->flush();

                    // Message de confirmation
                    $this->addFlash(
                        'success',
                        "L'image principale a bien été modifié"
                    );

                    // Redirection vers la page d'edition de la figure
                    return $this->redirectToRoute('trick_edit', [
                        'slug' => $slug
                    ]);
                }
                // Message d'erreur
                $this->addFlash(
                    'danger',
                    "Veuiller ne pas modifier les valeurs du formulaire."
                );

                // Redirection vers la page d'accueil
                return $this->redirectToRoute('home');
            } else // Aucune image n'a été sélectionné
            {
                // Message d'erreur
                $this->addFlash(
                    'danger',
                    "Aucune image sélectionnée"
                );
            }

            // Redirection vers la page d'édition de la figure
            return $this->redirectToRoute('trick_edit', [
                'slug' => $slug
            ]);
        }
        // Message d'erreur
        $this->addFlash(
            'danger',
            "Veuiller ne pas modifier les valeurs du formulaire."
        );

        // Redirection vers la page d'edition de la figure
        return $this->redirectToRoute('trick_edit', [
            'slug' => $slug
        ]);
    }
}
