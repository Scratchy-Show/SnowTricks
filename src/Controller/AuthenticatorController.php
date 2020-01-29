<?php


namespace App\Controller;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;



class AuthenticatorController extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $entityManager;
    private $router;
    private $csrfTokenManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    // Définit les conditions d'appelles de la classe
    public function supports(Request $request)
    {
        return 'login' === $request->attributes->get('_route') && $request->isMethod('POST');
    }

    // Retourne les éléments d'information d'authentification
    public function getCredentials(Request $request)
    {
        $credentials = [
            'username' => $request->request->get('username'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];

        $request->getSession()->set(Security::LAST_USERNAME, $credentials['username']);

        return $credentials;
    }

    // Retourne un utilisateur de l'instance UserInterface
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);

        // Si le jeton ne correspond pas
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            // Echec de l'authentification avec une erreur personnalisée
            throw new CustomUserMessageAuthenticationException('Erreur d\'authentification, veuillez réessayer.');
        }

        // Récupère l'utilisateur avec le pseudo et si son compte est activé
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'username' => $credentials['username'],
            'activated' => true
        ]);

        // Si le pseudo n'a aucune correspondance ou le compte n'est pas activé
        if (!$user) {
            // Echec de l'authentification avec une erreur personnalisée
            throw new CustomUserMessageAuthenticationException('Login incorrect ou compte non activé');
        }

        return $user;
    }

    // Vérifie que les informations d'authentification sont valides.
    public function checkCredentials($credentials, UserInterface $user)
    {
        $passwordEncoder = $this->passwordEncoder->isPasswordValid($user, $credentials['password']);

        // Si le mot de passe ne correspond pas
        if (!$passwordEncoder) {
            // Echec de l'authentification avec une erreur personnalisée
            throw new CustomUserMessageAuthenticationException('Login incorrect');
        }

        return $passwordEncoder;
    }

    // Si l'utilisateur est bien authentifié, redirection vers l'accueil.
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->router->generate('home'));
    }

    // Définit l’URL du formulaire de connexion
    protected function getLoginUrl()
    {
        return $this->router->generate('login');
    }
}