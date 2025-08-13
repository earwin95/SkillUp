<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExchangeRequestController extends AbstractController
{
    #[Route('/exchange/request', name: 'app_exchange_request')]
    public function index(): Response
    {
        return $this->render('exchange_request/index.html.twig', [
            'controller_name' => 'ExchangeRequestController',
        ]);
    }
}
