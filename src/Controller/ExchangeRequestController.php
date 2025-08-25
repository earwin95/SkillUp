<?php

// src/Controller/ExchangeRequestController.php

namespace App\Controller;

use App\Entity\Offer;
use App\Repository\OfferRepository;
use App\Repository\ExchangeRequestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExchangeRequestController extends AbstractController
{
    #[Route('/mes-demandes-reçues', name: 'app_exchange_requests')]
    public function received(
        OfferRepository $offerRepository,
        ExchangeRequestRepository $exchangeRequestRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $offers = $offerRepository->findBy(['owner' => $user]);

        $pendingRequestsByOffer = [];

        foreach ($offers as $offer) {
            $pendingRequests = $exchangeRequestRepository->findPendingForOffer($offer);

            $pendingRequestsByOffer[$offer->getId()] = [
                'offer' => $offer,
                'requests' => $pendingRequests,
            ];
        }

        return $this->render('exchange_request/index.html.twig', [
            'pendingRequestsByOffer' => $pendingRequestsByOffer,
        ]);
    }
}
