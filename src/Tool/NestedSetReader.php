<?php declare(strict_types=1);

namespace Shopware\DbalNestedSet\Tool;

use Doctrine\DBAL\Connection;
use PDO;
use Shopware\DbalNestedSet\NestedSetConfig;
use Shopware\DbalNestedSet\NestedSetExceptionNodeNotFound;
use function array_map;

class NestedSetReader
{
    use NestedSetConfigAware;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection, NestedSetConfig $conventionsConfig)
    {
        $this->connection = $connection;
        $this->setUpWithConnection($conventionsConfig, $connection);
    }

    /**
     * @throws NestedSetExceptionNodeNotFound
     */
    public function fetchNodeData(string $tableExpression, string $rootColumnName, int $nodeId): array
    {
        $quotedRootColumnName = $this->connection->quoteIdentifier($rootColumnName);

        $data = $this->connection
            ->createQueryBuilder()
            ->select([
                $this->pkCol . ' AS ' . $this->connection->quoteIdentifier('id'),
                $this->leftCol . ' AS ' . $this->connection->quoteIdentifier('left'),
                $this->rightCol . ' AS ' . $this->connection->quoteIdentifier('right'),
                $this->levelCol . ' AS ' . $this->connection->quoteIdentifier('level'),
                $quotedRootColumnName . ' AS ' . $this->connection->quoteIdentifier('root_id'),
            ])
            ->from($tableExpression)
            ->where($this->pkCol . '= :nodeId')
            ->setParameter('nodeId', $nodeId)
            ->execute()
            ->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw new NestedSetExceptionNodeNotFound("No node found with id $nodeId");
        }

        $data = array_map('intval', $data);

        $data['isRoot'] = $data['left'] === 1;
        $data['isLeaf'] = ($data['left'] + 1) === $data['right'];

        return $data;
    }
}
