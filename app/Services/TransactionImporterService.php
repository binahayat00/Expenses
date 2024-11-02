<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Transaction;
use App\Entity\User;
use App\DataObjects\TransactionData;
use Clockwork\Clockwork;
use Clockwork\Request\LogLevel;

class TransactionImporterService
{

    public function __construct(
        private readonly CategoryService $categoryService,
        private readonly TransactionService $transactionService,
        private readonly EntityManagerService $entityManagerService,
        private readonly Clockwork $clockwork,
    )
    {

    }
    public function importFromFile(string $file, User $user)
    {
        $resource = fopen($file, 'r');
        $categories = $this->categoryService->getAllKeyedByName();

        fgetcsv($resource);

        $count = 1;
        $batchSize = 250;
        while(($row = fgetcsv($resource)) !== false && $row !== '')
        {            
            [$date, $description, $category, $amount] = $row;
            if(
                strlen($date) === 0 && 
                strlen($description) === 0 &&
                strlen($category) === 0 &&
                strlen($amount) === 0
            )
            {
                break;
            }

        
            $date = new \DateTime($date);
            $category = $categories[strtolower($category)] ?? null;
            $amount = str_replace(['$',','], '', $amount);

            $transactionData = new TransactionData($description, (float) $amount, $date, $category);

            $this->transactionService->create($transactionData, $user);

            if($count % $batchSize === 0){
                $this->entityManagerService->flush();
                $this->entityManagerService->clear(Transaction::class);
                
                $count = 1;
            } else{
                $count++;   
            }
        }

        if($count > 1)
        {
            $this->entityManagerService->flush();
            $this->entityManagerService->clear();
        }

    }
}
