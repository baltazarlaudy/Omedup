<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\ParticipantRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pusher\Pusher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * @Route("/private/message", name="private_message_")
 */
class MessageController extends AbstractController
{
    const DATA_TO_SERIELIZE = ['id', 'content', 'createdAt', 'mine'];
    /**
     * @var ConversationRepository
     */
    private $conversationRepository;
    /**
     * @var MessageRepository
     */
    private $messageRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ParticipantRepository
     */
    private $participantRepository;
    /**
     * @var PublisherInterface
     */
    private $publisher;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var MessageBusInterface
     */
    private MessageBusInterface $bus;
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    public function __construct(ConversationRepository $conversationRepository,
                                MessageRepository $messageRepository,
                                EntityManagerInterface $entityManager,
                                ParticipantRepository $participantRepository,
                                PublisherInterface $publisher,
                                SerializerInterface $serializer,
                                MessageBusInterface $bus,
                                UserRepository $userRepository)
    {
        $this->conversationRepository = $conversationRepository;
        $this->messageRepository = $messageRepository;
        $this->entityManager = $entityManager;
        $this->participantRepository = $participantRepository;
        $this->publisher = $publisher;
        $this->serializer = $serializer;
        $this->bus = $bus;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/json/{id}", name="getMessageJson", methods={"GET"})
     * @param Conversation $conversation
     * @return Response
     */
    public function jsonData(Conversation $conversation): Response
    {
        $this->denyAccessUnlessGranted('view', $conversation);

        $messages = $this->messageRepository->getMessageByConversation(
            $conversation->getId()
        );

        array_map(function ($message) {
            $message->setMine(
                $message->getUser()->getId() == $this->getUser()->getId()
            );
        }, $messages);

        $data = $this->serializer->serialize($messages, 'json', [
            'attributes' => self::DATA_TO_SERIELIZE
        ]);

        $user = $this->userRepository->findUserById(
            $this->getUser()->getId()
        );

        return $this->json([$data], Response::HTTP_OK, [], [
            'attributes' => self::DATA_TO_SERIELIZE
        ]);
    }

    /**
     * @Route("/{id}", name="getMessage", methods={"GET"})
     * @param Conversation $conversation
     * @return Response
     */
    public function index(Conversation $conversation): Response
    {
        $this->denyAccessUnlessGranted('view', $conversation);

        $messages = $this->messageRepository->getMessageByConversation(
            $conversation->getId()
        );

        array_map(function ($message) {
            $message->setMine(
                $message->getUser()->getId() == $this->getUser()->getId()
            );
        }, $messages);

        $data = json_encode($messages);

        $user = $this->userRepository->findUserById(
            $this->getUser()->getId()
        );

        $this->json([$data], Response::HTTP_OK, [], []);

        return $this->render('user/private_message/postMessage.html.twig', [
            'data' => $messages,
            'convId' => $conversation,
            'user' => $user

        ]);
    }

    /**
     * @Route("/{id}", name="postMessage", methods={"POST"})
     * @param Conversation $conversation
     * @param Request $request
     * @param PublisherInterface $publisher
     * @return JsonResponse
     */
    public function postMessage(Conversation $conversation,
                                Request $request,
                                PublisherInterface $publisher
    )
    {
        $recipient = $this->participantRepository->findParticipantByConversationIdandUserId(
            $conversation->getId(),
            $this->getUser()->getId()
        );


        /*$otherUser = $this->participantRepository->findOtherUser(
            $conversation->getId(),
            $this->getUser()->getId()
        );*/

        $content = $request->get('content', null);

        //target for update mercure

        $message = new Message();
        $message->setUser($this->getUser());
        $message->setContent($content);
        $message->setMine(true);

        $conversation->setLastmessage($message);
        $conversation->addMessage($message);
        $entityManager = $this->entityManager;
        $entityManager->getConnection()->beginTransaction();
        try {
            $entityManager->persist($message);
            $entityManager->persist($conversation);

            $entityManager->flush();

            $entityManager->commit();

        } catch (\Exception $e) {
            $this->entityManager->rollback();

            $e->getMessage();
        }

        $message->setMine(false);

        $messageSerialize = $this->serializer->serialize($message, 'json', [
            'attributes' => [...self::DATA_TO_SERIELIZE, 'conversation' => ['id'], 'user' => ['username']]
        ]);

        $update = new Update(
            [sprintf("/message/%s", $conversation->getId())],
            $messageSerialize
        );
        $this->bus->dispatch($update);

        $message->setMine(true);

        return $this->json([$message], Response::HTTP_CREATED, [], [
            'attributes' => self::DATA_TO_SERIELIZE
        ]);
    }

}
