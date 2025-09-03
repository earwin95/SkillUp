<?php

namespace App\Controller;

use App\Repository\ConversationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NavbarController extends AbstractController
{
    #[Route('/_partials/nav/inbox', name: 'nav_inbox_badge', methods: ['GET'])]
    public function inbox(ConversationRepository $repo): Response
    {
        if (!$this->getUser()) {
            return new Response(''); // rien si pas connecté
        }

        $count = $repo->countUnreadForUser($this->getUser());

        return $this->render('_partials/_inbox_badge.html.twig', [
            'count' => $count,
        ]);
    }
}
