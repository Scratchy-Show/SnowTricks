<?php


namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Trick;
use DateTime;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;

class TrickTest extends KernelTestCase // Permet de récupérer le validateur avec des logiques plus complexes
{
    use FixturesTrait;

    // Récupère l'entité
    public function getEntity(): Trick
    {
        return (new Trick())
            ->setName('Test Figure Test')
            ->setDescription('Test de la figure avec les fixtures')
            ->setDate(new DateTime())
            ->setUpdateDate(null)
            ->setCategory(new Category())
            ;
    }

    // Assertion personnalisée qui attend aucun erreur
    public function assertHasErrors(Trick $trick, int $number = 0)
    {
        self::bootKernel();

        // Récupère le Validateur depuis le container pour valider l'entité et récupère une liste d'erreur
        $errors = self::$container->get('validator')->validate($trick);

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

    public function testInvalidUniqueEntity()
    {
        // Charge un fichier avec des données pour User
        $this->loadFixtureFiles([dirname(__DIR__) . '/Fixtures/Trick.yaml']);
        // Nom déjà existant
        $this->assertHasErrors($this->getEntity()->setName('Test figure'), 1);
    }

    public function testInvalidNameEntity()
    {
        // Nom trop court
        $this->assertHasErrors($this->getEntity()->setName('T'), 1);
        // Nom trop long
        $this->assertHasErrors($this->getEntity()->setName('Tesssssssssssssssssssssssst'), 1);
    }

    public function testInvalidBlankNameEntity()
    {
        // Nom vide et trop court
        $this->assertHasErrors($this->getEntity()->setName(''), 2);
    }

    public function testInvalidDescriptionEntity()
    {
        // Description trop courte
        $this->assertHasErrors($this->getEntity()->setDescription('Tessssssssssssst'), 1);
    }

    public function testInvalidBlankDescriptionEntity()
    {
        // Description vide et trop courte
        $this->assertHasErrors($this->getEntity()->setDescription(''), 2);
    }
}
