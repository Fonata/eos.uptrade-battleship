<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class GameClientController extends AbstractController
{
    /**
     * @Route("/game/jquery", name="game_jquery")
     * @param Security $security
     * @return Response
     */
    public function jquery(Security $security)
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('login_form');
        }
        return $this->render('game_client/jquery.html.twig');
    }

    /**
     * @Route("/game/vue", name="game_vue")
     * @param Security $security
     * @return Response
     */
    public function vue(Security $security)
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('login_form');
        }
        return $this->render('game_client/vue.html.twig');
    }
}
