<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\ExchangeRequest;
use App\Enum\ExchangeRequestStatus;
use App\Repository\OfferRepository;
use App\Repository\ExchangeRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    #[Route('/demande/{id}/accepter', name: 'exchange_request_accept', methods: ['POST'])]
    public function accept(
        ExchangeRequest $exchangeRequest,
        EntityManagerInterface $em,
        Request $request
    ): RedirectResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($exchangeRequest->getOffer()->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Action non autorisée.");
        }

        if ($this->isCsrfTokenValid('accept_exchange_' . $exchangeRequest->getId(), $request->request->get('_token'))) {
            $exchangeRequest->setStatus(ExchangeRequestStatus::ACCEPTED);
            $em->flush();
            $this->addFlash('success', 'Demande acceptée avec succès.');
        }

        return $this->redirectToRoute('app_exchange_requests');
    }

    #[Route('/demande/{id}/refuser', name: 'exchange_request_decline', methods: ['POST'])]
    public function decline(
        ExchangeRequest $exchangeRequest,
        EntityManagerInterface $em,
        Request $request
    ): RedirectResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($exchangeRequest->getOffer()->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Action non autorisée.");
        }

        if ($this->isCsrfTokenValid('decline_exchange_' . $exchangeRequest->getId(), $request->request->get('_token'))) {
            $exchangeRequest->setStatus(ExchangeRequestStatus::DECLINED);
            $em->flush();
            $this->addFlash('info', 'Demande refusée.');
        }

        return $this->redirectToRoute('app_exchange_requests');
    }

    #[Route('/test', name: 'app_test')]
    public function test(
        OfferRepository $offerRepository,
        ExchangeRequestRepository $exchangeRequestRepository
    ): Response {
        $offer = $offerRepository->findOneBy([]);

        if (!$offer) {
            return new Response("❌ Aucune offre trouvée.");
        }

        $pendingRequests = $exchangeRequestRepository->findPendingForOffer($offer);

        return $this->json([
            'offer_id' => $offer->getId(),
            'pending_count' => count($pendingRequests),
            'requests' => array_map(function ($req) {
                return [
                    'id' => $req->getId(),
                    'requester_id' => $req->getRequester()?->getId(),
                    'status' => $req->getStatus()->value,
                    'createdAt' => $req->getCreatedAt()->format('Y-m-d H:i'),
                ];
            }, $pendingRequests),
        ]);
    }
}
