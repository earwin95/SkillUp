<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Form\OfferType;
use App\Form\SearchOfferType;
use App\Repository\OfferRepository;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route; 
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/offres')]
class OfferController extends AbstractController
{
    #[Route('', name: 'offer_index', methods: ['GET'])]
    public function index(Request $request, OfferRepository $offerRepository): Response
    {
        // Formulaire de recherche (GET)
        $form = $this->createForm(SearchOfferType::class, null, [
            'method' => 'GET',
        ]);
        $form->handleRequest($request);

        $page  = max(1, (int) $request->query->get('page', 1));
        $limit = 10;

        $skillOffered   = $form->get('skillOffered')->getData();
        $skillRequested = $form->get('skillRequested')->getData();
        $q              = $form->get('q')->getData();

        $result = $offerRepository->findByFiltersPaginated(
            $skillOffered,
            $skillRequested,
            $q,
            $page,
            $limit
        );

        $offers = $result['items'] ?? [];
        $total  = $result['total'] ?? 0;
        $pages  = $result['pages'] ?? 1;

        if ($request->isXmlHttpRequest()) {
            return $this->render('offer/_offers_list.html.twig', [
                'offers' => $offers,
                'page'   => $page,
                'pages'  => $pages,
                'total'  => $total,
            ]);
        }

        return $this->render('offer/index.html.twig', [
            'offers'     => $offers,
            'form'       => $form->createView(),
            'searchForm' => $form->createView(), 
            'page'       => $page,
            'pages'      => $pages,
            'total'      => $total,
        ]);
    }

    #[Route('/nouvelle', name: 'offer_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $offer = new Offer();

        // Propriétaire = utilisateur connecté (si méthode dispo sur l’entité)
        if (method_exists($offer, 'setOwner') && $this->getUser()) {
            $offer->setOwner($this->getUser());
        }

        // Si tu as un champ status string et que tu veux une valeur par défaut
        if (method_exists($offer, 'setStatus') && null === $offer->getStatus()) {
            $offer->setStatus('active');
        }

        // Timestamps si présents
        if (method_exists($offer, 'setCreatedAt') && null === $offer->getCreatedAt()) {
            $offer->setCreatedAt(new \DateTimeImmutable());
        }
        if (method_exists($offer, 'setUpdatedAt')) {
            $offer->setUpdatedAt(new \DateTimeImmutable());
        }

        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // (re)mettre à jour updatedAt si présent
            if (method_exists($offer, 'setUpdatedAt')) {
                $offer->setUpdatedAt(new \DateTimeImmutable());
            }

            $em->persist($offer);
            $em->flush();

            $this->addFlash('success', '✅ Offre créée avec succès !');
            return $this->redirectToRoute('offer_index');
        }

        return $this->render('offer/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/supprimer', name: 'offer_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Offer $offer, Request $request, EntityManagerInterface $em): Response
    {
        // Vérifie propriétaire OU admin
        $user = $this->getUser();
        $isOwner = method_exists($offer, 'getOwner') && $offer->getOwner() === $user;

        if (!$isOwner && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', '❌ Action non autorisée.');
            return $this->redirectToRoute('offer_index');
        }

        // CSRF
        $submittedToken = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_offer_' . $offer->getId(), $submittedToken)) {
            $this->addFlash('error', '❌ Jeton CSRF invalide.');
            return $this->redirectToRoute('offer_index');
        }

        $em->remove($offer);
        $em->flush();

        $this->addFlash('success', '🗑️ Offre supprimée avec succès.');
        return $this->redirectToRoute('offer_index');
    }

    // Afficher une offre + préparer la conversation (find-or-create)
    #[Route('/{id}', name: 'offer_show', methods: ['GET'])]
    public function show(
        Offer $offer,
        ConversationRepository $conversationRepository,
        EntityManagerInterface $em
    ): Response {
        $conversation = null;
        $user = $this->getUser();

        // Uniquement si connecté ET que l'utilisateur n'est pas le propriétaire
        if ($user && method_exists($offer, 'getOwner') && $user !== $offer->getOwner()) {
            // Méthode custom à prévoir dans le repo : findOneByOfferAndParticipant(Offer $offer, User $user)
            $conversation = $conversationRepository->findOneByOfferAndParticipant($offer, $user);

            if (!$conversation) {
                // Création "find-or-create" de la conversation
                $conversation = (new \App\Entity\Conversation())
                    ->setOffer($offer)
                    ->setOwner($offer->getOwner())
                    ->setParticipant($user);

                $em->persist($conversation);
                $em->flush();
            }
        }

        return $this->render('offer/show.html.twig', [
            'offer'        => $offer,
            'conversation' => $conversation, // peut rester null si non connecté ou si owner
        ]);
    }
}
