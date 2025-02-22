<?php

declare(strict_types=1);

namespace App\Services;

use DateTime;
use App\Entity\User;
use App\Entity\Income;
use App\DataObjects\IncomeData;
use App\DataObjects\DataTableQueryParams;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Contracts\EntityManagerServiceInterface;

class IncomeService
{
    public function __construct(
        private readonly EntityManagerServiceInterface $entityManager,
        private readonly Income $income,
        )
    {
    }

    public function getAll(): array
    {
        return $this->entityManager->getRepository(Income::class)->findAll();
    }

    public function create($data, User $user): Income
    {
        $income = new Income();
        $income->setAmount($data['amount']);
        $income->setSource($data['source']);
        $income->setDate(new DateTime($data['date']));
        $income->setUser($user);

        return $income;
    }

    public function update(Income $income, IncomeData $incomeData): Income
    {
        $income->setAmount($incomeData->amount);
        $income->setSource($incomeData->source);
        $income->setDate($incomeData->date);

        return $income;
    }

    public function getById(int $id): ?Income
    {
        return $this->entityManager->find(Income::class , $id);
    }

    public function getPaginatedTransactions(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Income::class)
            ->createQueryBuilder('i')
            ->select('i')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy = in_array($params->orderBy, ['source', 'amount', 'date'])
            ? $params->orderBy
            : 'date';
        $orderDir = strtolower($params->orderDir) === 'asc' ? 'asc' : 'desc';

        if (!empty($params->searchTerm)) {
            $query->where('i.source LIKE :source')
                ->setParameter('source', '%' . addcslashes($params->searchTerm, '%_') . '%');
        }

        $query->orderBy('i.' . $orderBy, $orderDir);

        error_log($query->getQuery()->getSQL());

        return new Paginator($query);
    }

    public function getTotals(DateTime $startDate, DateTime $endDate): int
    {
        $query = $this->entityManager->getRepository(Income::class)
            ->createQueryBuilder('i')
            ->select('SUM(i.amount) as total')
            ->where('i.date >= :startDate')
            ->andWhere('i.date <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $query ?? 0;
    }

    public function getMonthlySummary(int $year, Datetime $startOfYear, DateTime $endOfYear): array
    {
        $query = $this->entityManager->getRepository(Income::class)
            ->createQueryBuilder('i')
            ->select('MONTH(i.date) as month, SUM(i.amount) as total')
            ->where('i.date BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startOfYear)
            ->setParameter('endDate', $endOfYear)
            ->groupBy('month')
            ->getQuery();

        $results = $query->getArrayResult();
        
        return array_map(fn($row) => [$row['month'] => $row['total']], $results);
    }
}
