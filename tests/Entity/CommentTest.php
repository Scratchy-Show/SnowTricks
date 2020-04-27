<?php


namespace App\Tests\Entity;

use App\Entity\Comment;
use DateTime;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;

class CommentTest extends KernelTestCase // Permet de récupérer le validateur avec des logiques plus complexes
{
    use FixturesTrait;

    // Récupère l'entité
    public function getEntity(): Comment
    {
        return (new Comment())
            ->setContent('Contenu du commentaire test.')
            ->setDate(new DateTime())
            ;
    }

    // Assertion personnalisée qui attend aucun erreur
    public function assertHasErrors(Comment $comment, int $number = 0)
    {
        self::bootKernel();

        // Récupère le Validateur depuis le container pour valider l'entité et récupère une liste d'erreur
        $errors = self::$container->get('validator')->validate($comment);

        // Sauvegarde l'ensemblde des messages dans un tableau
        $messages = [];

        // Chaque erreur est un objet ConstraintViolation, c'est à dire qu'il y a un problème de contrainte
        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            // Pour chaque message: la clé du problème . => . message du problème
            $messages[] = $error->getPropertyPath() . ' => ' . $error->getMessage();
        }
        // Indique le nombre d'erreur, l'erreur et les messages d'erreurs
        $this->assertCount($number, $errors, implode(', ', $messages));
    }

    // Permet de vérifier qu'une entité valide reste valide
    public function testValidEntity()
    {
        // Initialise l'entité avec aucune erreur
        $this->assertHasErrors($this->getEntity(), 0);
    }

    public function testBlankEntity()
    {
        // Contenu vide
        $this->assertHasErrors($this->getEntity()->setContent(''), 1);
    }
}
