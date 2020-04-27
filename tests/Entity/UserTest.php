<?php


namespace App\Tests\Entity;

use App\Entity\User;
use DateTime;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;

class UserTest extends KernelTestCase // Permet de récupérer le validateur avec des logiques plus complexes
{
    use FixturesTrait;

    // Récupère l'entité
    public function getEntity(): User
    {
        return (new User())
            ->setUsername('Test1')
            ->setEmail('nom1@exemple.fr')
            ->setDate(new DateTime())
            ->setPassword('password')
            ->setPictureName('Test' . '-' . uniqid() . '.' . 'jpg')
            ->setProfilPicturePath('uploads/profil')
            ->setToken(bin2hex(random_bytes(64)))
            ->setActivated(false)
            ->setRoles(['ROLE_USER'])
            ;
    }

    // Assertion personnalisée qui attend aucun erreur
    public function assertHasErrors(User $user, int $number = 0)
    {
        self::bootKernel();

        // Récupère le Validateur depuis le container pour valider l'entité et récupère une liste d'erreur
        $errors = self::$container->get('validator')->validate($user);

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
        $this->loadFixtureFiles([dirname(__DIR__) . '/Fixtures/User.yaml']);
        // Pseudo déjà existant
        $this->assertHasErrors($this->getEntity()->setUsername('Test2'), 1);
        // Email déjà existant
        $this->assertHasErrors($this->getEntity()->setEmail('nom2@exemple.fr'), 1);
    }

    public function testInvalidUsernameEntity()
    {
        // Pseudo trop court
        $this->assertHasErrors($this->getEntity()->setUsername('T'), 1);
        // Pseudo trop long
        $this->assertHasErrors($this->getEntity()->setUsername('Tesssssssssssssssssssssssst'), 1);
    }

    public function testInvalidBlankUsernameEntity()
    {
        // Pseudo vide et trop court
        $this->assertHasErrors($this->getEntity()->setUsername(''), 2);
    }

    public function testInvalidEmailEntity()
    {
        // Email invalide
        $this->assertHasErrors($this->getEntity()->setEmail('nomexemple.fr'), 1);
    }

    public function testInvalidBlankEmailEntity()
    {
        // Email vide
        $this->assertHasErrors($this->getEntity()->setEmail(''), 1);
    }

    public function testInvalidPasswordEntity()
    {
        // Mot de passe trop court
        $this->assertHasErrors($this->getEntity()->setPassword('1234567'), 1);
    }

    public function testInvalidBlankPasswordEntity()
    {
        // Mot de passe vide et trop court
        $this->assertHasErrors($this->getEntity()->setPassword(''), 2);
    }
}
