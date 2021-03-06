<?php

namespace Yokai\SecurityTokenBundle\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Yokai\SecurityTokenBundle\Entity\Token;
use Yokai\SecurityTokenBundle\Exception\TokenExpiredException;
use Yokai\SecurityTokenBundle\Exception\TokenNotFoundException;
use Yokai\SecurityTokenBundle\Exception\TokenConsumedException;

/**
 * Doctrine ORM token repository;
 *
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class DoctrineORMTokenRepository implements TokenRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @param EntityManager    $manager    The token entity manager
     * @param EntityRepository $repository The token entity repository
     */
    public function __construct(EntityManager $manager, EntityRepository $repository)
    {
        $this->manager = $manager;
        $this->repository = $repository;
    }

    /**
     * @inheritdoc
     */
    public function get($value, $purpose)
    {
        $token = $this->repository->findOneBy(
            [
                'value' => $value,
                'purpose' => $purpose,
            ]
        );

        if (!$token instanceof Token) {
            throw TokenNotFoundException::create($value, $purpose);
        }
        if ($token->isExpired()) {
            throw TokenExpiredException::create($value, $purpose, $token->getExpiresAt());
        }
        if ($token->isConsumed()) {
            throw TokenConsumedException::create($value, $purpose, $token->getCountUsages());
        }

        return $token;
    }

    /**
     * @inheritDoc
     */
    public function findExisting($userClass, $userId, $purpose)
    {
        $token = $this->repository->findOneBy(
            [
                'userClass' => $userClass,
                'userId' => $userId,
                'purpose' => $purpose,
            ]
        );
        if (!$token instanceof Token) {
            return null;
        }
        if ($token->isConsumed() || $token->isExpired()) {
            return null;
        }

        return $token;
    }

    /**
     * @inheritdoc
     */
    public function create(Token $token)
    {
        $this->manager->persist($token);
        $this->manager->flush($token);
    }

    /**
     * @inheritdoc
     */
    public function update(Token $token)
    {
        $this->manager->persist($token);
        $this->manager->flush($token);
    }

    /**
     * @inheritdoc
     */
    public function exists($value, $purpose)
    {
        $builder = $this->repository->createQueryBuilder('token');
        $builder
            ->select('COUNT(token.id)')
            ->where('token.value = :value')
            ->andWhere('token.purpose = :purpose')
            ->setParameters(
                [
                    'value' => $value,
                    'purpose' => $purpose,
                ]
            )
        ;

        return intval($builder->getQuery()->getSingleScalarResult()) > 0;
    }
}
