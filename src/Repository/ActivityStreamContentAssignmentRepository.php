<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\Actor\Actor;
use Mitra\Filtering\Filter;
use Mitra\Filtering\SqlFilterRenderer;

final class ActivityStreamContentAssignmentRepository implements ActivityStreamContentAssignmentRepositoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Actor $actor
     * @param Filter|null $filter
     * @param int $offset
     * @param int $limit
     * @return array<ActivityStreamContentAssignment>
     */
    public function findContentForActor(Actor $actor, ?Filter $filter, ?int $offset, ?int $limit): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('ca', 'c')
            ->from(ActivityStreamContentAssignment::class, 'ca')
            ->join('ca.content', 'c')
            ->where('ca.actor = :actor')
            ->setParameter('actor', $actor->getUser());

        if (null !== $filter) {
            $this->addFilter($qb, $filter);
        }

        $qb->orderBy('c.published', 'DESC');

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function getTotalCountForActor(Actor $actor, ?Filter $filter): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->select($qb->expr()->count('ca'))
            ->from(ActivityStreamContentAssignment::class, 'ca')
            ->where('ca.actor = :actor')
            ->setParameter('actor', $actor->getUser());

        if (null !== $filter) {
            $this->addFilter($qb, $filter);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function addFilter(QueryBuilder $qb, Filter $filter): void
    {
        $allowedProperties = ['attributedTo'];

        $filterRenderer = new SqlFilterRenderer(
            $qb,
            function (string $propertyName) use ($allowedProperties, $qb): string {
                if ('attributedTo' === $propertyName) {
                    $aliases = $qb->getAllAliases();

                    if (!in_array('c', $aliases, true)) {
                        $qb->join('ca.content', 'c');
                    }

                    if (!in_array('a', $aliases, true)) {
                        $qb->join('c.attributedTo', 'a');
                    }

                    return 'a.user';
                }

                throw new \RuntimeException(sprintf(
                    'Filtering for property `%s` is not defined. Available properties are: %s',
                    $propertyName,
                    implode(', ', $allowedProperties)
                ));
            }
        );

        $filterRenderer->apply($filter);
    }

    /**
     * @param Actor $actor
     * @param ActivityStreamContent $content
     * @return ActivityStreamContentAssignment|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findAssignment(Actor $actor, ActivityStreamContent $content): ?ActivityStreamContentAssignment
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('ca', 'c')
            ->from(ActivityStreamContentAssignment::class, 'ca')
            ->join('ca.content', 'c')
            ->where('ca.actor = :actor')
            ->andWhere('ca.content = :content')
            ->setParameter('actor', $actor->getUser())
            ->setParameter('content', $content);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
