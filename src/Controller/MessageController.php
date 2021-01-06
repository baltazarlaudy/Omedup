<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
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
 * @Route("/dashboard/message", name="message_")
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

    public function __construct(ConversationRepository $conversationRepository,
                                MessageRepository $messageRepository,
                                EntityManagerInterface $entityManager,
                                ParticipantRepository $participantRepository,
                                PublisherInterface $publisher,
                                SerializerInterface $serializer,
                                MessageBusInterface $bus)
    {
        $this->conversationRepository = $conversationRepository;
        $this->messageRepository = $messageRepository;
        $this->entityManager = $entityManager;
        $this->participantRepository = $participantRepository;
        $this->publisher = $publisher;
        $this->serializer = $serializer;
        $this->bus = $bus;
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     * @return Response
     */
    public function messageJson()
    {
        $id = $this->getUser()->getId();
        $conversation = $this->conversationRepository->findOtherUserInConversation(
            $this->getUser()->getId()
        );


        return $this->render('user/message/message_index.html.twig',
            [
                'conversation' => $conversation,
                'userid' => $id
            ]);
    }

    /**
     * @Route("/{id}", name="get", methods={"GET"})
     * @param Conversation $conversation
     * @return Response
     */
    public function index(Conversation $conversation): Response
    {
        $this->denyAccessUnlessGranted('view', $conversation);

        $messages = $this->messageRepository->getMessageByConversation(
            $conversation->getId()
        );
        $otherUser = $this->participantRepository->findOtherUser(
            $conversation->getId(),
            $this->getUser()->getId()
        );
        array_map(function ($message) {
            $message->setMine(
                $message->getUser()->getId() == $this->getUser()->getId()
            );
        }, $messages);

        $data = $this->serializer->serialize($messages, 'json', [
            'attributes' => self::DATA_TO_SERIELIZE
        ]);

        /*return $this->json([$data], Response::HTTP_OK, [], [
            'attributes' => self::DATA_TO_SERIELIZE
        ]);*/

        return $this->render('user/message/message_user.html.twig', [
            'data' => $messages,
            'conversation' => $conversation,
            'username' => $this->getUser()->getId()
        ]);
    }

    /**
     * @Route("/{id}", name="post", methods={"POST"})
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
        $otherUser = $this->participantRepository->findOtherUser(
            $conversation->getId(),
            $this->getUser()->getId()
        );

        $content = $request->get('content', null);

        //target for update mercure


        $message = new Message();
        $message->setUser($this->getUser());
        $message->setContent($content);

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

        $messageSerialize = $this->serializer->serialize($message, 'json', [
            'attributes' => [...self::DATA_TO_SERIELIZE, 'conversation' => ['id']]
        ]);
        $update = new Update(
            '/conversation/',
            $messageSerialize
        /*[
            sprintf("/conversation/%s", $conversation->getId()),
            sprintf('/message/%s', $otherUser->getUser()->getId())
        ],
        $messageSerialize,
        true*/
        //[sprintf("/%s", $otherUser->getUser()->getId())]
        );
        $publisher($update);
        return $this->json([$message], Response::HTTP_CREATED, [], [
            'attributes' => self::DATA_TO_SERIELIZE
        ]);
    }

}
