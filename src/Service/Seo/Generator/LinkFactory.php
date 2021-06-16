<?php

namespace App\Service\Seo\Generator;

use Doctrine\Common\Persistence\ManagerRegistry;

class LinkFactory
{
    private ManagerRegistry $connection;

    public function __construct(ManagerRegistry $connection)
    {
        $this->connection = $connection;
    }

    public function create(string $entityClassName, string $columnName): Link
    {
        $repository = $this->connection->getRepository($entityClassName);

        return new Link($repository, $columnName);
    }
}
