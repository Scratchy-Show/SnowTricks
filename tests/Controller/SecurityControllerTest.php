<?php


namespace App\tests\Controller;

use Doctrine\ORM\EntityManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase // Permet de créer des tests avec des requêtes et des réponses
{
    use FixturesTrait;

    /**
     * @var EntityManager
     */
    private $entityManager;

    // Test l'affichage de la page de Connexion
    public function testDisplayLogin()
    {
        // Récupère un client
        $client = static::createClient();

        // Requête qui analyse le contenu de la page
        $client->request('GET', '/connexion');

        // Statut de la réponse attendu : type 200
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Sélectionne le H1 et vérifie son contenu
        $this->assertSelectorTextContains('h1', 'Connexion');

        // Attend aucun selecteur particulier
        $this->assertSelectorNotExists('.alert.alert-danger');
    }

    // Test la connexion avec un mauvais login
    public function testLoginWithBadCredentials()
    {
        // Récupère un client
        $client = static::createClient();

        // Requête qui renvoie un crawler qui permet d'analyser le contenu de la page et stock la réponse en mémoire
        $crawler = $client->request('GET', '/connexion');

        // Récupère le bouton et le formulaire associé
        $form = $crawler->selectButton('Connexion')->form([
            'username' => 'test',
            'password' => 'fakepassword'
        ]);

        // Soumet le formulaire
        $client->submit($form);

        // Redirection vers la page de connexion si mauvais login
        $this->assertResponseRedirects('/connexion');

        // Suit la redirection et charge la page suivante
        $client->followRedirect();

        // Attend un selecteur particulier
        $this->assertSelectorExists('.alert.alert-danger');
    }

    // Test la connexion avec un bon login
    public function testSuccessfullLogin()
    {
        // Charge un fichier avec des données pour User
        $this->loadFixtureFiles([__DIR__ . '/Users.yaml']);

        // Arrête le noyau avant de créer le client
        self::ensureKernelShutdown();

        // Récupère un client
        $client = static::createClient();

        // Génère un token pour la clé 'authenticate'
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');

        $client->request('POST', '/connexion', [
            '_csrf_token' => $csrfToken,
            'username' => 'Test',
            'password' => 'password'
        ]);

        // Redirection vers la page d'accueil
        $this->assertResponseRedirects('/');
    }

    // Test la connexion avec un compte non validé
    public function testInactiveAccount()
    {
        // Charge un fichier avec des données pour User
        $this->loadFixtureFiles([__DIR__ . '/Users.yaml']);

        // Arrête le noyau avant de créer le client
        self::ensureKernelShutdown();

        // Récupère un client
        $client = static::createClient();

        // Requête qui renvoie un crawler qui permet d'analyser le contenu de la page et stock la réponse en mémoire
        $crawler = $client->request('GET', '/connexion');

        // Récupère le bouton et le formulaire associé
        $form = $crawler->selectButton('Connexion')->form([
            'username' => 'Test2',
            'password' => 'password'
        ]);

        // Soumet le formulaire
        $client->submit($form);

        // Redirection vers la page d'accueil
        $this->assertResponseRedirects('/connexion');

        // Suit la redirection et charge la page suivante
        $client->followRedirect();

        // Attend un selecteur particulier
        $this->assertSelectorExists('.alert.alert-danger');
    }
}
