<?php


namespace App\Tests\Repository;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends kernelTestCase // Récupère le repository
{
    use FixturesTrait;

    public function testCount()
    {
        // Démarre le kernel
        self::bootKernel();

        // Met en place les nom de classes des fixtures
        // Permet de remplir la BDD avec les données de test
        $this->loadFixtures([AppFixtures::class]);

        // La propriété static $container permet d'accéder au container et au service privé
        // Récupère un repository configuré et renvoie le nombre d'utilisateurs en BDD
        $users = self::$container->get(UserRepository::class)->count([]);

        // On s'attend à récupérer 10 utilisateurs
        $this->assertEquals(10, $users);
    }
}
