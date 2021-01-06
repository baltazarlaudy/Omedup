<?php


namespace App\Security\Voter;


use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;


class conversationVoter extends Voter
{
    /**
     * @var ConversationRepository
     */
    private $conversationRepository;

    public function __construct(ConversationRepository $conversationRepository)
    {
        $this->conversationRepository = $conversationRepository;
    }

    const VIEW = 'view';

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports(string $attribute, $subject)
    {
        return $attribute == self::VIEW and $subject instanceof Conversation;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $result = $this->conversationRepository->checIfUserIsParticipant(
            $token->getUser()->getId(),
            $subject->getId()
        );

        return !!$result;
    }
}