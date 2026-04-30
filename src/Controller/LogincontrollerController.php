<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LogincontrollerController extends AbstractController
{
    #[Route('/logincontroller', name: 'app_logincontroller')]
    public function index(): Response
    {
        return $this->render('logincontroller/login.html.twig', [
            'controller_name' => 'LogincontrollerController',
        ]);
    }
}
