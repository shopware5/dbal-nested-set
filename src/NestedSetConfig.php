<?php declare(strict_types=1);

namespace Shopware\DbalNestedSet;

class NestedSetConfig
{
    /**
     * @var string
     */
    private $leftColumnName;

    /**
     * @var string
     */
    private $rightColumnName;

    /**
     * @var string
     */
    private $levelColumnName;

    /**
     * @var string
     */
    private $primaryKeyColumnName;

    public function __construct(
        string $primaryKeyColumnName,
        string $leftColumnName,
        string $rightColumnName,
        string $levelColumnName
    ) {
        $this->primaryKeyColumnName = $primaryKeyColumnName;
        $this->leftColumnName = $leftColumnName;
        $this->rightColumnName = $rightColumnName;
        $this->levelColumnName = $levelColumnName;
    }

    public function getPrimaryKeyColumnName(): string
    {
        return $this->primaryKeyColumnName;
    }

    public function getLeftColumnName(): string
    {
        return $this->leftColumnName;
    }

    public function getRightColumnName(): string
    {
        return $this->rightColumnName;
    }

    public function getLevelColumnName(): string
    {
        return $this->levelColumnName;
    }
}
