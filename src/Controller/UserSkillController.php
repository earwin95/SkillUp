<?php

namespace App\Controller;

use App\Entity\UserSkill;
use App\Form\UserSkillType;
use App\Repository\UserSkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserSkillController extends AbstractController
{
    #[Route('/user-skill/new', name: 'user_skill_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $userSkill = new UserSkill();

        $form = $this->createForm(UserSkillType::class, $userSkill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userSkill->setUser($this->getUser()); // Si ton entité a un champ User

            $em->persist($userSkill);
            $em->flush();

            $this->addFlash('success', 'Compétence ajoutée avec succès !');
            return $this->redirectToRoute('user_skill_new'); // ou vers 'user_skill_index' si tu préfères revenir à la liste
        }

        return $this->render('user_skill/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user-skill', name: 'user_skill_index')]
    public function index(UserSkillRepository $userSkillRepository): Response
    {
        $userSkills = $userSkillRepository->findAll();

        return $this->render('user_skill/index.html.twig', [
            'user_skills' => $userSkills,
        ]);
    }
}
