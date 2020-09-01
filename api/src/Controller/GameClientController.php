<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class GameClientController extends AbstractController
{
    /**
     * @Route("/game", name="game_client")
     * @param Security $security
     * @return Response
     */
    public function index(Security $security)
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('login_form');
        }
        return $this->render('game_client/index.html.twig');
    }
}
