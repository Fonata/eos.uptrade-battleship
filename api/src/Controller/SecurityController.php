<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * This class provides the routes for the login.
 * The actual logic for the symfony guard is in the class ApiAuthenticator.
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login", methods={"POST"})
     * @param Request $request
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function loginPost(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        return new JsonResponse(['status' => $error ? 'error' : 'ok']);
    }

    /**
     * @Route("/login", methods={"GET"})
     * @param Request $request
     * @param Security $security
     * @return Response
     */
    public function loginGet(Request $request, Security $security): Response
    {
        /** @var User $user */
        $user = $security->getUser();
        if ($user) {
            return new JsonResponse(['status' => 'ok', 'current_user' => '/api/user/' . $user->getId()]);
        }
        return new JsonResponse([
            'status' => 'login needed',
            'details' => "You need to send a POST request to this endpoint to log in.
                Here is a JS snippet that you can paste in the console:
                xhr = new XMLHttpRequest(); xhr.open('POST', 'https://battleship.blaeul.de/login', true); xhr.send(JSON.stringify({email: 'admin@eos-uptrade.de', password: 'demo'}));"
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        // This function is just a placeholder for the Route annotation.
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
