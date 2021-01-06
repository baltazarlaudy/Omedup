<?php

namespace App\Repository;

use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;
use function Symfony\Component\DependencyInjection\Loader\Configurator\expr;

/**
 * @method Conversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conversation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conversation[]    findAll()
 * @method Conversation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    // /**
    //  * @return Conversation[] Returns an array of Conversation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findConversationByParticipant(int $myId, int $otherUser)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select($qb->expr()->count('p.conversation'))
        ->innerJoin('c.participants', 'p')
        ->where($qb->expr()->orX(
            $qb->expr()->eq('p.user', ':otherUser'),
            $qb->expr()->eq('p.user', ':myId')
        ))
        ->groupBy('p.conversation')
        ->having($qb->expr()->eq(
            $qb->expr()->count('p.conversation'), 2
        ))
        ->setParameters([
            'otherUser' => $otherUser,
            'myId' => $myId
        ]);

       return $qb->getQuery()->getResult();
    }

    public function getConversationByUser(int $myId)
    {
        $qb = $this->createQueryBuilder('c');
        $qb ->select('otherUser.slug', 'lm.content', 'c.id as conversationId','p.createdAt')
            ->innerJoin('c.participants', 'p', Join::WITH, $qb->expr()->neq('p.user', ':user'))
            ->innerJoin('c.participants', 'me', Join::WITH, $qb->expr()->eq('me.user', ':user'))
            ->leftJoin('c.lastmessage', 'lm')
            ->innerJoin('p.user', 'otherUser')
            ->innerJoin('me.user', 'meUser')
            ->where('meUser.id = :user')
            ->setParameter(':user', $myId);

        return $qb->getQuery()->getResult();
    }

    public function checIfUserIsParticipant(int $userId, int $conversationId)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->innerJoin('c.participants', 'p')
            ->where($qb->expr()->andX(
            $qb->expr()->eq('c.id', ':conversationId'),
            $qb->expr()->eq('p.user', ':userId')

        ))
        ->setParameters([
            'conversationId' => $conversationId,
            'userId' => $userId
        ]);

       return $qb->getQuery()->getOneOrNullResult();
    }

    public function findOtherUserByConversation(int $conversationId, int $myId)
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->innerJoin('c.participants', 'p')
             ->where($qb->expr()->neq('p.user', ':myId'))
            ->andWhere($qb->expr()->eq('p.conversation', ':conversationId'))
             ->setParameters([
            'conversationId' => $conversationId,
            'myId' => $myId
        ]);
       return $qb->getQuery()->getResult();
    }

    public function findConversationByUser(int $myId)

    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('c.id', 'lm.content', 'lm.createdAt')
            ->innerJoin('c.participants', 'p')
            ->innerJoin('p.user', 'u')
            ->leftJoin('c.lastmessage', 'lm')
            ->where($qb->expr()->eq(
            'p.user', ':myId'
        ))
        ->setParameter('myId', $myId);
       return $qb->getQuery()->getResult();
    }

    public function findOtherUserInConversation(int $myId)
    {
        $qb = $this->createQueryBuilder('c');
        $qb ->select('u.id', 'lm.createdAt', 'lm.content', 'c.id as convId')
            ->innerJoin('c.participants', 'p')
            ->innerJoin('p.user', 'u')
            ->leftJoin('c.lastmessage', 'lm')
            ->where(
                $qb->expr()->neq('p.user', ':myId')
            )
            ->setParameter('myId', $myId);
        return $qb->getQuery()->getResult();
    }
}
