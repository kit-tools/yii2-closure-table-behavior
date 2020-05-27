<?php

namespace tests\unit\models;

use Codeception\Test\Unit;
use kittools\closuretable\models\AbstractTreePath;
use tests\UnitTester;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;

class AbstractTreePathTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /** @var AbstractTreePath */
    private $abstractTreePathMock;

    /**
     * Check that we get an exception if inheritance from an abstract class AbstractTreePath and no table is specified.
     *
     * @throws InvalidConfigException
     */
    public function testInvalidConfigExceptionInTableNameMethod(): void
    {
        $this->expectException(InvalidConfigException::class);

        $this->abstractTreePathMock::tableName();
    }

    public function testConfigTimestampBehavior(): void
    {
        /** @var TimestampBehavior $timestampBehavior */
        $timestampBehavior = null;
        foreach ($this->abstractTreePathMock->getBehaviors() as $behavior) {
            if ($behavior instanceof TimestampBehavior) {
                $timestampBehavior = $behavior;
            }
        }
        $this->assertNotNull($timestampBehavior);
        $this->assertEquals('created_at', $timestampBehavior->createdAtAttribute);
        $this->assertNull($timestampBehavior->updatedAtAttribute);
    }

    public function testPrimaryKey(): void
    {
        $this->assertEquals(['parent_id', 'child_id'], $this->abstractTreePathMock::primaryKey());
    }

    protected function _before(): void
    {
        $this->abstractTreePathMock = $this->getMockForAbstractClass(AbstractTreePath::class);
    }
}