<?php declare(strict_types=1);

namespace Shopware\DbalNestedSet\Tool;

use Doctrine\DBAL\Connection;
use Shopware\DbalNestedSet\NestedSetConfig;

trait NestedSetConfigAware
{
    /**
     * @var string
     */
    private $pkCol;

    /**
     * @var string
     */
    private $leftCol;

    /**
     * @var string
     */
    private $rightCol;

    /**
     * @var string
     */
    private $levelCol;

    /**
     * @var string
     */
    private $rootCol;

    private function setUpWithConnection(NestedSetConfig $conventionsConfig, Connection $connection): void
    {
        $this->pkCol = $connection->quoteIdentifier($conventionsConfig->getPrimaryKeyColumnName());
        $this->leftCol = $connection->quoteIdentifier($conventionsConfig->getLeftColumnName());
        $this->rightCol = $connection->quoteIdentifier($conventionsConfig->getRightColumnName());
        $this->levelCol = $connection->quoteIdentifier($conventionsConfig->getLevelColumnName());
    }

    private function setRootColName(string $rootColumnName, Connection $connection): void
    {
        $this->rootCol = $connection->quoteIdentifier($rootColumnName);
    }
}
