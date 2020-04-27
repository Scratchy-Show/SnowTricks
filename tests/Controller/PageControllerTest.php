<?php


namespace App\tests\Controller;

use App\Tests\NeedLogin;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PageControllerTest extends WebTestCase // Permet de créer des tests avec des requêtes et des réponses
{
    use FixturesTrait;
    use NeedLogin;

    // Test l'affichage de la page d'accueil
    public function testHomePage()
    {
        // Récupère un client
        $client = static::createClient();

        // Requête qui analyse le contenu de la page
        $client->request('GET', '/');

        // Statut de la réponse attendu : type 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    // Test le titre de la page d'accueil
    public function testH1HomePage()
    {
        // Récupère un client
        $client = static::createClient();

        // Requête qui analyse le contenu de la page
        $client->request('GET', '/');

        // Sélectionne le H1 et vérifie son contenu
        $this->assertSelectorTextContains('h1', 'LE SNOWBOARD PAR DES PASSIONNES, POUR DES PASSIONNES');
    }

    // Test la redirection vers la page de connexion si non autorisé à accéder à la page profil
    public function testRedirectToLogin()
    {
        // Récupère un client
        $client = static::createClient();

        // Requête qui analyse le contenu de la page
        $client->request('GET', '/profil');

        // Redirection vers la page de connexion
        $this->assertResponseRedirects('/connexion');
    }

    // Test l'accès a la page profil si autorisé
    public function testUserAccessProfile()
    {
        // Récupère un client
        $client = static::createClient();

        // Charge un fichier avec des données pour User
        $users = $this->loadFixtureFiles([__DIR__ . '/Users.yaml']);

        // Connecte l'utilisateur au client
        $this->login($client, $users['user_user1']);

        // Requête qui analyse le contenu de la page
        $client->request('GET', '/profil');

        // Statut de la réponse attendu : type 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
