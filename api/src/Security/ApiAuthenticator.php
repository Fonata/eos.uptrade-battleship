<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;

/**
 * This class checks the submitted login. Internally, Symfony sets the Set-Cookie header in the response.
 *
 * It supports two "Content-Type" values:
 *   - application/json
 *   - application/x-www-form-urlencoded
 */
class ApiAuthenticator extends AbstractGuardAuthenticator implements PasswordAuthenticatedInterface
{
    public const LOGIN_ROUTE = 'app_login';

    private $entityManager;
    private $urlGenerator;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        /** @noinspection SuspiciousBinaryOperationInspection */
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $form_credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
        ];
        $json_credentials = strpos($request->headers->get('Content-Type'), 'json') > 0 ?
            json_decode($request->getContent(), true, 2, JSON_THROW_ON_ERROR) :
            [];
        return $json_credentials + $form_credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);

        if (!$user) {
            if ($credentials['email'] === 'admin@eos-uptrade.de') {
                // This makes testing with an empty DB easy.
                return $this->user = $this->createFirstUser($credentials['email']);
            }
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Email could not be found.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     * @param mixed $credentials The user credentials
     * @return mixed
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new JsonResponse(['status' => 'ok']);
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse(
            ['status' => 'error', 'reason' => "Not logged in."],
            JsonResponse::HTTP_FORBIDDEN);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(
            ['status' => 'error', 'reason' => $exception->getMessage()],
            JsonResponse::HTTP_FORBIDDEN);
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    public function supportsRememberMe()
    {
        return false;
    }

    private function createFirstUser($email): User
    {
        $user = new User();
        $user->setName('Adam');
        $user->setSurname('Admin');
        $user->setEmail($email);
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $user->setPlainPassword('demo');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }
}
