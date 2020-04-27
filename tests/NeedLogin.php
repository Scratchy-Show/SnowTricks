<?php


namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

trait NeedLogin
{
    public function login(KernelBrowser $client, User $user)
    {
        // Crée une session
        $session = $client->getContainer()->get('session');

        // Génère un token
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());

        // Clé de sécurité qu'attend le firewall
        $session->set('_security_main', serialize($token));

        // Sauvegarde la session
        $session->save();

        // Cookie qui lie la session à l'utilisateur
        $cookie = new Cookie($session->getName(), $session->getId());

        // Stock le cookie
        $client->getCookieJar()->set($cookie);
    }
}
