<?php

namespace App\Repository;

use App\Entity\Answer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Answer>
 */
class AnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Answer::class);
    }

    public function findAllUsers(string $sessionId)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.sessionId = :sessionId')
            ->setParameter('sessionId', $sessionId)
            ->getQuery()
            ->getResult();

    }
}
