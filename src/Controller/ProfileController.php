<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        // Bloque l'accès si l'utilisateur n'est pas connecté ou n'a pas le bon rôle
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('profile/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}
