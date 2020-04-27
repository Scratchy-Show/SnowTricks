<?php


namespace App\Tests\Repository;

use App\DataFixtures\AppFixtures;
use App\Repository\CategoryRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoryRepositoryTest extends kernelTestCase // Récupère le repository
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
        // Récupère un repository configuré et renvoie le nombre de catégories en BDD
        $categories = self::$container->get(CategoryRepository::class)->count([]);

        // On s'attend à récupérer 7 catégories
        $this->assertEquals(7, $categories);
    }
}
