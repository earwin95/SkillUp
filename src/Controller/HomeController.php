<?php

namespace App\Controller;

use App\Form\SearchOfferHomeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        // Formulaire d’accueil SANS mot-clé, qui envoie vers /offres
        $form = $this->createForm(SearchOfferHomeType::class, null, [
            'method' => 'GET',
            'action' => $this->generateUrl('offer_index'),
        ]);

        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
