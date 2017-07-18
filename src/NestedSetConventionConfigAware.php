<?php declare(strict_types=1);

namespace Shopware\DbalNestedSet;

use Doctrine\DBAL\Connection;

trait NestedSetConventionConfigAware
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

    /**
     * @param NestedSetConventionsConfig $conventionsConfig
     * @param Connection $connection
     */
    private function setUpWithConnection(NestedSetConventionsConfig $conventionsConfig, Connection $connection)
    {
        $this->pkCol = $connection->quoteIdentifier($conventionsConfig->getPrimaryKeyColumnName());
        $this->leftCol = $connection->quoteIdentifier($conventionsConfig->getLeftColumnName());
        $this->rightCol = $connection->quoteIdentifier($conventionsConfig->getRightColumnName());
        $this->levelCol = $connection->quoteIdentifier($conventionsConfig->getLevelColumnName());
    }

    /**
     * @param string $rootColumnName
     * @param Connection $connection
     */
    private function setRootColName(string $rootColumnName, Connection $connection)
    {
        $this->rootCol = $connection->quoteIdentifier($rootColumnName);
    }
}
