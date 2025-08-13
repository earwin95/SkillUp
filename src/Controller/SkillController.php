<?php

namespace App\Controller;

use App\Entity\Skill;
use App\Form\SkillType;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SkillController extends AbstractController
{
    #[Route('/skill', name: 'app_skill')]
    public function index(SkillRepository $skillRepository): Response
    {
        $skills = $skillRepository->findAll();

        return $this->render('skill/index.html.twig', [
            'skills' => $skills,
        ]);
    }

    #[Route('/skill/{id}', name: 'skill_show', requirements: ['id' => '\d+'])]
    public function show(Skill $skill): Response
    {
        return $this->render('skill/show.html.twig', [
            'skill' => $skill,
        ]);
    }

    #[Route('/skill/new', name: 'skill_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $skill = new Skill();
        $form = $this->createForm(SkillType::class, $skill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($skill);
            $em->flush();

            $this->addFlash('success', 'Compétence ajoutée avec succès.');
            return $this->redirectToRoute('app_skill');
        }

        return $this->render('skill/new.html.twig', [
            'form' => $form->createView(),
        ]);
        }

    #[Route('/skill/{id}/edit', name: 'skill_edit', requirements: ['id' => '\d+'])]
    public function edit(Skill $skill, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SkillType::class, $skill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Compétence mise à jour.');
            return $this->redirectToRoute('skill_show', ['id' => $skill->getId()]);
        }

        return $this->render('skill/edit.html.twig', [
            'form' => $form->createView(),
            'skill' => $skill,
        ]);
    }

    #[Route('/skill/{id}/delete', name: 'skill_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Skill $skill, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_skill_'.$skill->getId(), $request->request->get('_token'))) {
            $em->remove($skill);
            $em->flush();
            $this->addFlash('success', 'Compétence supprimée.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_skill');
    }
}
