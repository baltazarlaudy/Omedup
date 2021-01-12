<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Participant;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\ParticipantRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\WebLink\Link;

/**
 * @Route("/private", name="private_")
 */
class PrivateMessageController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;
    /**
     * @var ConversationRepository
     */
    private ConversationRepository $conversationRepository;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var MessageRepository
     */
    private MessageRepository $messageRepository;
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;
    /**
     * @var ParticipantRepository
     */
    private ParticipantRepository $participantRepository;


    public function __construct(UserRepository $userRepository,
                                ConversationRepository $conversationRepository,
                                EntityManagerInterface $entityManager,
                                MessageRepository $messageRepository,
                                SerializerInterface $serializer,
                                ParticipantRepository $participantRepository)
    {
        $this->userRepository = $userRepository;
        $this->conversationRepository = $conversationRepository;
        $this->entityManager = $entityManager;
        $this->messageRepository = $messageRepository;
        $this->serializer = $serializer;
        $this->participantRepository = $participantRepository;
    }

    /**
     * @Route("/conversations", name="conversation_list")
     * @return Response
     */
    public function conversationList()
    {

        $conversation = $this->conversationRepository->getConversationByUser($this->getUser()->getId());

        return $this->render('user/private_message/index.html.twig', [
            'conversationList' => $conversation,
            'user' => $this->userRepository->findUserById($this->getUser()->getId())
        ]);

    }

    /**
     * @Route("/", name="get", methods={"GET"})
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function index(Request $request): Response
    {
        $otherUser = (int)$request->get('otherUser', null);
        $me = $this->getUser()->getId();

        $hubUrl = $this->getParameter('mercure.default_hub');

        $this->addLink($request, new Link('mercure',$hubUrl));

        if (is_null($otherUser)) {
            throw new \Exception('vous devez choisir quelqu\'un pour envoyer votre message');
        }
        if ($me === $otherUser) {
            throw new \Exception('vous ne pouvez pas creer une conversation avec toi meme');
        }
        //check if conversation already existe
        $conversation = $this->conversationRepository->findConversationByParticipant(
            $me,
            $otherUser
        );
        $other = $this->userRepository->findOneBy(['id' => $otherUser]);
        $users = $this->userRepository->findAllOtherUser(
            $this->getUser()->getId(),
            3
        );

        $hubUrl = $this->getParameter('mercure.default_hub');

        $this->addLink($request, new Link('mercure',$hubUrl));

        if ($conversation) {
            return $this->render('user/private_message/postMessage.html.twig', [
                'conversationList' => $this->conversationRepository->findConversationByParticipant($me, $otherUser),
                'user' => $this->userRepository->findUserById($me),
                'convId' => $this->conversationRepository->getConvId($this->getUser()->getId(), $otherUser),
            ]);
        }

        $conversation = new Conversation();
        $participant = new Participant();
        $participant->setUser($this->getUser());
        $participant->setConversation($conversation);

        //other participant
        $otherParticipant = new Participant();
        $otherParticipant->setUser($other);
        $otherParticipant->setConversation($conversation);

        $this->entityManager->getConnection()->beginTransaction();
        try {
            $this->entityManager->persist($conversation);
            $this->entityManager->persist($participant);
            $this->entityManager->persist($otherParticipant);

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            echo $e->getMessage();
        }

        return $this->render('user/private_message/postMessage.html.twig', [
            'conversation' => $this->conversationRepository->findConversationByParticipant($me, $otherUser),
            'user' => $this->userRepository->findUserById($me),
            'convId' => $this->conversationRepository->getConvId($this->getUser()->getId(), $otherUser),
        ]);
    }

}
