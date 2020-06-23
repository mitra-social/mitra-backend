<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\Actor\Actor;
use Mitra\Entity\User\InternalUser;
use Mitra\Filtering\Filter;
use Mitra\Filtering\SqlFilterRenderer;

final class ActivityStreamContentAssignmentRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
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
        $qb = $this->repository->createQueryBuilder('ca')
            ->select('ca', 'c')
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
        $qb = $this->repository->createQueryBuilder('ca');
        $qb
            ->select($qb->expr()->count('ca'))
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

                    if (!in_array('c', $aliases)) {
                        $qb->join('ca.content', 'c');
                    }

                    if (!in_array('a', $aliases)) {
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
}
