<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Participant;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\WebLink\Link;

/**
 * @Route("/conversation", name="conversation_")
 */
class ConversationController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ConversationRepository
     */
    private $conversationRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * ConversationController constructor.
     * @param UserRepository $userRepository
     * @param ConversationRepository $conversationRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(UserRepository $userRepository,
                                ConversationRepository $conversationRepository,
                                EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->conversationRepository = $conversationRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/create/{id}", name="create")
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws \Exception
     */
    public function index(Request $request, int $id): Response
    {
        //$otherUser = $request->get('otherUser', 0);
        $otherUser = $this->userRepository->find($id);


        // verify is user Exist

        if (is_null($otherUser)) {
            throw new \Exception('user not foud');
        }

        //verify if user is me
        if ($otherUser->getId() === $this->getUser()->getId()) {
            throw new \Exception("can't send message to myself");
        }

        //find conversation

        $conversation = $this->conversationRepository->findConversationByParticipant(
            $this->getUser()->getId(),
            $otherUser->getId()
        );

        if ($conversation) {

        }
        $conversation = new Conversation();

        $participant = new Participant();
        $participant->setConversation($conversation);
        $participant->setUser($this->getUser());

        $otherParticipant = new Participant();
        $otherParticipant->setUser($otherUser);
        $otherParticipant->setConversation($conversation);

        $this->entityManager->getConnection()->beginTransaction();
        try {
            $this->entityManager->persist($conversation);
            $this->entityManager->persist($participant);
            $this->entityManager->persist($otherParticipant);

            $this->entityManager->flush();

            $this->entityManager->commit();
        }catch (\Exception $e){
            $this->entityManager->rollback();
            $e->getMessage();
        }
        return $this->redirectToRoute('message_get', array('id' => $conversation));
        /*return $this->render('conversation/user_home.html.twig', [
            'controller_name' => 'ConversationController',
        ]);*/
    }

    /**
     * @Route("/", name="post", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getConversation(Request $request){
        $conversation = $this->conversationRepository->getConversationByUser(
            $this->getUser()->getId()
        );

        $hubUrl = $this->getParameter('mercure.default_hub');

        $this->addLink($request, new Link('mercure',$hubUrl));
        return $this->json([$conversation], Response::HTTP_OK, [], []);
    }

}
