<?php

namespace App\Controller;

use App\Form\SearchOfferType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        // Formulaire minimal : 2 selects (offerte/demandée) + champ mot-clé
        // On laisse l'action du form côté Twig => offer_index
        $form = $this->createForm(SearchOfferType::class, null, [
            'method' => 'GET',
        ]);
        // Pas indispensable ici, mais permet de pré-remplir si besoin
        $form->handleRequest($request);

        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
