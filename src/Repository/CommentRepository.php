<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    // Récupère tous les commentaires d'une figure avec une pagination
    public function getAllCommentsWithPaging($page, $nbMaxPerPage, $trick)
    {
        // Vérifie que $page correspond à un nombre
        if (!is_numeric($page)) {
            throw new InvalidArgumentException(
                'La valeur de l\'argument est incorrecte (valeur : ' . $page . ').'
            );
        }

        // Si $page est inférieur à 1
        if ($page < 1) {
            throw new NotFoundHttpException('La page demandée n\'existe pas');
        }

        // Vérifie que $nbMaxPerPage correspond à un nombre
        if (!is_numeric($nbMaxPerPage)) {
            throw new InvalidArgumentException(
                'La valeur de l\'argument est incorrecte (valeur : ' . $nbMaxPerPage . ').'
            );
        }

        // Construction de la requête
        $qb = $this->createQueryBuilder('c')
            // Sélectionne la table comment
            ->select('c')
            ->where('c.trick = :trick')
            ->setParameter('trick', $trick)
            // Définit l'ordre d'affichage du plus récent ou plus ancien
            ->orderBy('c.date', 'DESC');

        // Requête
        $query = $qb->getQuery();

        // Calcul les commentaires a afficher
        $firstResult = ($page - 1) * $nbMaxPerPage;

        // Retourne le premier résultat avec setFirstResult()
        // Et retourne le maximum de résultat avec setMaxResults()
        $query->setFirstResult($firstResult)->setMaxResults($nbMaxPerPage);

        // Instancie un objet Paginator qui va contenir uniquement les commentaires souhaités
        $paginator = new Paginator($query);

        // Si la page demandé ne correspond pas au compte
        if (($paginator->count() <= $firstResult) && $page != 1) {
            // Page 404, sauf pour la première page
            throw new NotFoundHttpException('La page demandée n\'existe pas.');
        }

        return $paginator;
    }

    // /**
    //  * @return Comment[] Returns an array of Comment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
