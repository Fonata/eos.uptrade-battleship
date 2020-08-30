<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * This controller handles just one page.
 */
class FrontPageController extends AbstractController
{
    /**
     * @Route("/", name="front_page")
     * @param Security $security
     * @return Response
     */
    public function index(Security $security): Response
    {
        $user = $security->getUser();
        $readme = file_get_contents(__DIR__ . '/../../../README.md');
        $readme_no_heading = preg_replace('/^.+\n/', '', $readme);
        return $this->render('front_page/index.html.twig', [
            'controller_name' => 'FrontPageController',
            'username' => $user ? $user->getUsername() : '',
            'title' => 'eos.uptrade coding challenge submission',
            'readme' => $readme_no_heading,
        ]);
    }
}
