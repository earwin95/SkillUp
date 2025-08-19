<?php

namespace App\Controller;

use App\Entity\UserSkill;
use App\Form\UserSkillType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mes-competences')]
class UserSkillController extends AbstractController
{
    #[Route('', name: 'app_user_skills_index', methods: ['GET'])]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        return $this->render('user_skill/index.html.twig', [
            'mySkills' => $user->getUserSkills(), // Collection
        ]);
    }

    #[Route('/ajouter', name: 'app_user_skills_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $userSkill = new UserSkill();
        $userSkill->setUser($user); // sécurité: user fixé côté serveur

        $form = $this->createForm(UserSkillType::class, $userSkill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($userSkill);
                $em->flush();
                $this->addFlash('success', 'Compétence ajoutée.');
                return $this->redirectToRoute('app_user_skills_index');
            } catch (\Throwable $e) {
                // ex: contrainte d’unicité (user, skill)
                $this->addFlash('warning', 'Cette compétence existe déjà dans votre profil.');
            }
        }

        return $this->render('user_skill/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_skills_edit', methods: ['GET','POST'])]
    public function edit(UserSkill $userSkill, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($userSkill->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // ✅ on recrée le formulaire mais en désactivant le champ "skill"
        $form = $this->createForm(UserSkillType::class, $userSkill, [
            'disable_skill' => true, // option custom définie dans UserSkillType
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Compétence mise à jour.');
            return $this->redirectToRoute('app_user_skills_index');
        }

        return $this->render('user_skill/edit.html.twig', [
            'form' => $form->createView(),
            'userSkill' => $userSkill,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_user_skills_delete', methods: ['POST'])]
    public function delete(UserSkill $userSkill, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($userSkill->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete_user_skill_'.$userSkill->getId(), $request->request->get('_token'))) {
            $em->remove($userSkill);
            $em->flush();
            $this->addFlash('success', 'Compétence supprimée.');
        }

        return $this->redirectToRoute('app_user_skills_index');
    }
}
