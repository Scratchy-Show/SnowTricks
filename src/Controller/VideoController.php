<?php


namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Video;
use App\Form\VideoType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VideoController extends AbstractController // Permet d'utiliser la méthode render
{
    /**
     * @Route("/figure/ajouter/video/{slug}", name="trick_video_add")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param $slug
     * @return RedirectResponse|Response
     */
    public function addVideoTrick(
        Request $request,
        $slug
    ) {
        // Récupère le gestionnaire d'entités
        $entityManager = $this->getDoctrine()->getManager();

        // Récupère la figure
        $trick = $entityManager->getRepository(Trick::class)->findOneBy(['slug' => $slug]);

        // Si une figure correspond au slug
        if ($trick != null) {
            // Création du formulaire des videos
            $form = $this->createForm(VideoType::class);

            // Met à jour le formulaire à l'aide des infos reçues de l'utilisateur
            $form->handleRequest($request);

            // Si le formulaire d'ajout d'image est soumis
            if ($form->isSubmitted() && $form->isValid()) {
                // Récupère l'Url
                $url = $form->get('url')->getData();

                // Si il n'y a pas d'url
                if (empty($url)) {
                    // Message d'erreur
                    $this->addFlash(
                        'danger',
                        "Aucune URL trouvée !"
                    );

                    // Redirection vers la page d'ajout de vidéo
                    return $this->redirectToRoute('trick_video_add', [
                        'slug' => $slug
                    ]);
                }

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

                // Redirection vers la page d'édition la figure
                return $this->redirectToRoute('trick_edit', [
                    'slug' => $slug
                ]);
            }
            // Affiche par la page d'ajout d'une vidéo
            return $this->render('trick/addVideo.html.twig', [
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
     * @Route("/figure/supprimer/video/{videoId}/{trickIid}", name="trick_video_delete")
     * @IsGranted("ROLE_USER")
     * @param $videoId
     * @param $trickId
     * @return RedirectResponse
     */
    public function deleteVideoTrick(
        $videoId,
        $trickId
    ) {

        // Si $videoId et $trick ne sont pas vident et $trick est bien numérique
        if ((!empty($videoId)) && (!empty($trickId)) && (is_numeric($trickId))) {
            // Récupère la vidéo
            $url = $this->getDoctrine()->getRepository(Video::class)->find($videoId);

            // Récupère la figure
            $trick = $this->getDoctrine()->getRepository(Trick::class)->find($trickId);

            // Si la figure éxiste
            if ($trick != null) {
                // Si l'Url est trouvée
                if ($url != null) {
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

                    // Redirection vers la page d'édition la figure
                    return $this->redirectToRoute('trick_edit', [
                        'slug' => $trick->getSlug()
                    ]);
                } else // Si l'Url n'est pas trouvé
                {
                    // Message d'erreur
                    $this->addFlash(
                        'danger',
                        "La vidéo n'a pas été trouvé."
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
}
