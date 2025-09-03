<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Form\OfferType;
use App\Form\SearchOfferType;
use App\Repository\OfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OfferController extends AbstractController
{
    #[Route('/offres', name: 'offer_index')]
    public function index(Request $request, OfferRepository $offerRepository): Response
    {
        // Formulaire de recherche en GET
        $form = $this->createForm(SearchOfferType::class, null, [
            'method' => 'GET',
        ]);
        $form->handleRequest($request);

        $page  = max(1, (int) $request->query->get('page', 1));
        $limit = 10;

        $skillOffered   = $form->get('skillOffered')->getData();
        $skillRequested = $form->get('skillRequested')->getData();
        $q              = $form->get('q')->getData();

        // Utilisation de la bonne méthode du repository
        $result = $offerRepository->findByFiltersPaginated(
            $skillOffered,
            $skillRequested,
            $q,
            $page,
            $limit
        );

        $offers = $result['items'];
        $total  = $result['total'];
        $pages  = $result['pages'];

        // Requête AJAX ⇒ renvoyer uniquement la liste (fragment)
        if ($request->isXmlHttpRequest()) {
            return $this->render('offer/_offers_list.html.twig', [
                'offers' => $offers,
                'page'   => $page,
                'pages'  => $pages,
                'total'  => $total,
            ]);
        }

        // Requête classique ⇒ page complète
        return $this->render('offer/index.html.twig', [
            'offers' => $offers,
            'form'       => $form->createView(), 
            'searchForm' => $form->createView(),
            'page'   => $page,
            'pages'  => $pages,
            'total'  => $total,
        ]);
    }

    #[Route('/offres/nouvelle', name: 'offer_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $offer = new Offer();
        $offer->setOwner($this->getUser());
        // adapte si tu utilises un enum Status
        $offer->setStatus('active');

        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($offer);
            $em->flush();

            $this->addFlash('success', 'Offre créée avec succès !');
            return $this->redirectToRoute('offer_index');
        }

        return $this->render('offer/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/offres/{id}/supprimer', name: 'offer_delete', methods: ['POST'])]
    public function delete(Offer $offer, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($offer->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres offres.');
        }

        if ($this->isCsrfTokenValid('delete_offer_' . $offer->getId(), (string) $request->request->get('_token'))) {
            $em->remove($offer);
            $em->flush();
            $this->addFlash('success', 'Offre supprimée avec succès.');
        }

        return $this->redirectToRoute('offer_index');
    }
}
