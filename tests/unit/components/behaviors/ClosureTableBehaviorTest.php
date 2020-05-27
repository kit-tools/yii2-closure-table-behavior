<?php

namespace tests\unit\components\behaviors;

use Codeception\Test\Unit;
use kittools\closuretable\components\behaviors\ClosureTableBehavior;
use tests\app\models\Menu;
use tests\app\models\MenuTreePath;
use tests\UnitTester;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

class ClosureTableBehaviorTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * Tests available events and methods.
     */
    public function testEvents(): void
    {
        $events = (new ClosureTableBehavior())->events();

        $this->assertCount(4, $events);

        $this->assertArrayHasKey(ActiveRecord::EVENT_AFTER_INSERT, $events);
        $this->assertEquals('afterInsert', $events[ActiveRecord::EVENT_AFTER_INSERT]);

        $this->assertArrayHasKey(ActiveRecord::EVENT_BEFORE_UPDATE, $events);
        $this->assertEquals('beforeUpdate', $events[ActiveRecord::EVENT_BEFORE_UPDATE]);

        $this->assertArrayHasKey(ActiveRecord::EVENT_AFTER_UPDATE, $events);
        $this->assertEquals('afterUpdate', $events[ActiveRecord::EVENT_AFTER_UPDATE]);

        $this->assertArrayHasKey(ActiveRecord::EVENT_BEFORE_DELETE, $events);
        $this->assertEquals('beforeDelete', $events[ActiveRecord::EVENT_BEFORE_DELETE]);
    }

    /**
     * Test invalid config exception, if empty treePathModelClass.
     */
    public function testInvalidConfigExceptionIfEmptyTreePathModelClass(): void
    {
        $this->expectException(InvalidConfigException::class);

        $menu = new Menu();
        $menu->detachBehaviors();
        $menu->attachBehaviors(
            [
                [
                    'class' => ClosureTableBehavior::class,
                    'ownerParentIdAttribute' => 'parent_id',
                ],
            ]
        );
    }

    /**
     * Test invalid config exception, if not a string treePathModelClass.
     */
    public function testInvalidConfigExceptionIfNotAStringTreePathModelClass(): void
    {
        $this->expectException(InvalidConfigException::class);

        $menu = new Menu();
        $menu->detachBehaviors();
        $menu->attachBehaviors(
            [
                [
                    'class' => ClosureTableBehavior::class,
                    'treePathModelClass' => new MenuTreePath(),
                    'ownerParentIdAttribute' => 'parent_id',
                ],
            ]
        );
    }
}