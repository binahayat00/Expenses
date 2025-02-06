<?php

declare(strict_types=1);

namespace App\Services;
use DateTime;
use App\Entity\User;
use App\Entity\Transaction;
use App\DataObjects\TransactionData;
use App\DataObjects\DataTableQueryParams;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Contracts\EntityManagerServiceInterface;

class TransactionService
{

    public function __construct(private readonly EntityManagerServiceInterface $entityManager)
    {
    }

    public function create(TransactionData $transactionData, User $user): Transaction
    {
        $transaction = new Transaction();

        $transaction->setUser($user);

        return $this->update($transaction, $transactionData);
    }

    public function getPaginatedTransactions(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->select('t', 'c', 'r')
            ->leftJoin('t.category', 'c')
            ->leftJoin('t.receipts', 'r')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy = in_array($params->orderBy, ['description', 'amount', 'date', 'category'])
            ? $params->orderBy
            : 'date';
        $orderDir = strtolower($params->orderDir) === 'asc' ? 'asc' : 'desc';

        if (!empty($params->searchTerm)) {
            $query->where('t.description LIKE :description')
                ->setParameter('description', '%' . addcslashes($params->searchTerm, '%_') . '%');
        }

        if ($orderBy === 'category') {
            $query->orderBy('c.name', $orderDir);
        } else {
            $query->orderBy('t.' . $orderBy, $orderDir);
        }

        error_log($query->getQuery()->getSQL());

        return new Paginator($query);
    }

    public function getById(int $id): ?Transaction
    {
        return $this->entityManager->find(Transaction::class, $id);
    }

    public function update(Transaction $transaction, TransactionData $transactionData)
    {
        $transaction->setDescription(($transactionData->description));
        $transaction->setAmount($transactionData->amount);
        $transaction->setDate($transactionData->date);
        $transaction->setCategory($transactionData->category);

        return $transaction;
    }

    public function toggleReviewed(Transaction $transaction): void
    {
        $transaction->setWasReviewed(!$transaction->getWasReviewed());
    }

    public function getTotals(DateTime $startDate, DateTime $endDate): array
    {
        $query = $this->entityManager->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->select('SUM(t.amount) as total')
            ->where('t.date > :startDate')
            ->andWhere('t.date < :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
        
        $total = $query ?? 0;

        return ['net' => $total * 0.3, 'income' => $total * 1.3, 'expense' => $total];
    }

    public function getRecentTransactions(int $limit): array
    {
        $query = $this->entityManager->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->orderBy('t.date', 'desc')
            ->setMaxResults($limit);

        $result = $query->getQuery()->getArrayResult();

        return $result;
    }

    public function getMonthlySummary(int $year): array
    {
        $startOfYear = new DateTime("$year-01-01 00:00:00");
        $endOfYear = new DateTime("$year-12-31 23:59:59");

        $query = $this->entityManager->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->select('MONTH(t.date) as month, SUM(t.amount) as total')
            ->where('t.date BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startOfYear)
            ->setParameter('endDate', $endOfYear)
            ->groupBy('month')
            ->getQuery();

        $results = $query->getArrayResult();

        return array_map(fn($row) => [
            'income' => (float) $row['total'] * 1.3,
            'expense' => (float) $row['total'],
            'm' => (string) $row['month']
        ], $results);
    }


    public function getTopSpendingCategories(int $limit): array
    {
        $result = $this->entityManager->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->select('COUNT(t.id) as transaction_count', 'c.name','SUM(t.amount) as total')
            ->leftJoin('t.category', 'c')
            ->groupBy('c.id')
            ->orderBy('total','DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return $result;
    }
}
