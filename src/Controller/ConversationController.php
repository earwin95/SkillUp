<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

class ConversationController extends AbstractController
{
    private const EDIT_WINDOW_SECONDS = 120; // 2 minutes

    #[Route('/conversations/{id}/messages', name: 'conversation_messages', methods: ['GET'])]
    public function messages(Conversation $conversation, Request $request, MessageRepository $messageRepo): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        if (!$conversation->isParticipant($user)) {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        $afterId = $request->query->getInt('after', 0);
        $messages = $messageRepo->findAfterId($conversation, $afterId);

        $now = new \DateTimeImmutable();

        $data = array_map(function (Message $m) use ($user, $now) {
            $mine = $m->getAuthor()->getId() === $user->getId();
            $deleted = $m->isDeleted();
            $created = $m->getCreatedAt();
            $editableUntil = $created->modify('+' . self::EDIT_WINDOW_SECONDS . ' seconds');
            $canEdit = $mine && !$deleted && $editableUntil > $now;
            $canDelete = $canEdit;

            return [
                'id'            => $m->getId(),
                'authorId'      => $m->getAuthor()->getId(),
                'authorName'    => method_exists($m->getAuthor(), 'getDisplayName') && $m->getAuthor()->getDisplayName()
                                    ? $m->getAuthor()->getDisplayName()
                                    : ($m->getAuthor()->getEmail() ?? 'Utilisateur'),
                'mine'          => $mine,
                'content'       => $deleted ? 'Message supprimé' : $m->getContent(),
                'createdAt'     => $m->getCreatedAt()->format(DATE_ATOM),
                'edited'        => null !== $m->getEditedAt(),
                'deleted'       => $deleted,
                'editableUntil' => $editableUntil->format(DATE_ATOM),
                'canEdit'       => $canEdit,
                'canDelete'     => $canDelete,
            ];
        }, $messages);

        return $this->json(['messages' => $data]);
    }

    #[Route('/conversations/{id}/send', name: 'conversation_send', methods: ['POST'])]
    public function send(
        Conversation $conversation,
        Request $request,
        EntityManagerInterface $em,
        CsrfTokenManagerInterface $csrf
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        if (!$conversation->isParticipant($user)) {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        $payload = json_decode($request->getContent(), true) ?? [];
        $content = trim((string)($payload['content'] ?? ''));
        $token = (string)($payload['_token'] ?? '');

        if (!$csrf->isTokenValid(new CsrfToken('send_message_'.$conversation->getId(), $token))) {
            return $this->json(['error' => 'Invalid CSRF token'], 400);
        }

        if ($content === '') {
            return $this->json(['error' => 'Message vide'], 422);
        }

        $message = (new Message())
            ->setConversation($conversation)
            ->setAuthor($user)
            ->setContent($content);

        $em->persist($message);
        $conversation->touch();
        $em->flush();

        $editableUntil = $message->getCreatedAt()->modify('+'.self::EDIT_WINDOW_SECONDS.' seconds');

        return $this->json([
            'ok' => true,
            'message' => [
                'id'            => $message->getId(),
                'authorId'      => $user->getId(),
                'authorName'    => method_exists($user, 'getDisplayName') && $user->getDisplayName() ? $user->getDisplayName() : ($user->getEmail() ?? 'Moi'),
                'mine'          => true,
                'content'       => $message->getContent(),
                'createdAt'     => $message->getCreatedAt()->format(DATE_ATOM),
                'edited'        => false,
                'deleted'       => false,
                'editableUntil' => $editableUntil->format(DATE_ATOM),
                'canEdit'       => true,
                'canDelete'     => true,
            ]
        ], 201);
    }

    #[Route('/conversations/{id}/seen', name: 'conversation_seen', methods: ['POST'])]
    public function seen(Conversation $conversation, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        if (!$conversation->isParticipant($user)) {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        $conversation->markSeenBy($user);
        $em->flush();

        return $this->json(['ok' => true]);
    }

    #[Route('/conversations', name: 'conversation_index', methods: ['GET'])]
    public function index(ConversationRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $convs = $repo->findByUserOrdered($this->getUser());

        return $this->render('conversation/index.html.twig', [
            'conversations' => $convs,
            'me' => $this->getUser(),
        ]);
    }

    // --------- ÉDITION ----------
    #[Route('/conversations/{id}/edit', name: 'conversation_edit', methods: ['POST'])]
    public function edit(
        Conversation $conversation,
        Request $request,
        EntityManagerInterface $em,
        CsrfTokenManagerInterface $csrf,
        MessageRepository $messages
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        if (!$conversation->isParticipant($user)) {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        $payload   = json_decode($request->getContent(), true) ?? [];
        $token     = (string)($payload['_token'] ?? '');
        $messageId = (int)($payload['messageId'] ?? 0);
        $content   = trim((string)($payload['content'] ?? ''));

        if (!$csrf->isTokenValid(new CsrfToken('edit_message_'.$conversation->getId(), $token))) {
            return $this->json(['error' => 'Invalid CSRF token'], 400);
        }
        if ($content === '') {
            return $this->json(['error' => 'Message vide'], 422);
        }

        /** @var Message|null $m */
        $m = $messages->find($messageId);
        if (!$m || $m->getConversation()->getId() !== $conversation->getId()) {
            return $this->json(['error' => 'Message introuvable'], 404);
        }
        if ($m->getAuthor()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Forbidden'], 403);
        }
        if ($m->isDeleted()) {
            return $this->json(['error' => 'Message supprimé'], 422);
        }

        $deadline = $m->getCreatedAt()->modify('+'.self::EDIT_WINDOW_SECONDS.' seconds');
        if ($deadline <= new \DateTimeImmutable()) {
            return $this->json(['error' => 'Délai dépassé'], 422);
        }

        $m->setContent($content);
        $m->setEditedAt(new \DateTimeImmutable());
        $conversation->touch();
        $em->flush();

        return $this->json(['ok' => true, 'editedAt' => $m->getEditedAt()->format(DATE_ATOM)]);
    }

    // --------- SUPPRESSION ----------
    #[Route('/conversations/{id}/delete', name: 'conversation_delete', methods: ['POST'])]
    public function deleteMsg(
        Conversation $conversation,
        Request $request,
        EntityManagerInterface $em,
        CsrfTokenManagerInterface $csrf,
        MessageRepository $messages
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        if (!$conversation->isParticipant($user)) {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        $payload   = json_decode($request->getContent(), true) ?? [];
        $token     = (string)($payload['_token'] ?? '');
        $messageId = (int)($payload['messageId'] ?? 0);

        if (!$csrf->isTokenValid(new CsrfToken('delete_message_'.$conversation->getId(), $token))) {
            return $this->json(['error' => 'Invalid CSRF token'], 400);
        }

        /** @var Message|null $m */
        $m = $messages->find($messageId);
        if (!$m || $m->getConversation()->getId() !== $conversation->getId()) {
            return $this->json(['error' => 'Message introuvable'], 404);
        }
        if ($m->getAuthor()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        $deadline = $m->getCreatedAt()->modify('+'.self::EDIT_WINDOW_SECONDS.' seconds');
        if ($deadline <= new \DateTimeImmutable()) {
            return $this->json(['error' => 'Délai dépassé'], 422);
        }

        $m->setDeletedAt(new \DateTimeImmutable());
        $conversation->touch();
        $em->flush();

        return $this->json(['ok' => true]);
    }
}
