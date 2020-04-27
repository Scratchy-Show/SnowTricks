<?php


namespace App\Tests\Repository;

use App\DataFixtures\AppFixtures;
use App\Repository\TrickRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TrickRepositoryTest extends kernelTestCase // Récupère le repository
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
        // Récupère un repository configuré et renvoie le nombre de figures en BDD
        $tricks = self::$container->get(TrickRepository::class)->count([]);

        // On s'attend à récupérer 16 figures
        $this->assertEquals(16, $tricks);
    }
}
