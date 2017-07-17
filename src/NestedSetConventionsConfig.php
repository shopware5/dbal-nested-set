<?php declare(strict_types=1);

namespace Shopware\DbalNestedSet;

class NestedSetConventionsConfig
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

    /**
     * @param string $primaryKeyColumnName
     * @param string $leftColumnName
     * @param string $rightColumnName
     * @param string $levelColumnName
     */
    public function __construct(
        string $primaryKeyColumnName,
        string $leftColumnName,
        string $rightColumnName,
        string $levelColumnName
    )
    {
        $this->primaryKeyColumnName = $primaryKeyColumnName;
        $this->leftColumnName = $leftColumnName;
        $this->rightColumnName = $rightColumnName;
        $this->levelColumnName = $levelColumnName;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyColumnName(): string
    {
        return $this->primaryKeyColumnName;
    }

    /**
     * @return string
     */
    public function getLeftColumnName(): string
    {
        return $this->leftColumnName;
    }

    /**
     * @return string
     */
    public function getRightColumnName(): string
    {
        return $this->rightColumnName;
    }

    /**
     * @return string
     */
    public function getLevelColumnName(): string
    {
        return $this->levelColumnName;
    }
}