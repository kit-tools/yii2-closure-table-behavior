<?php

namespace tests\functional\components\behaviors;

use tests\app\models\Menu;
use tests\app\models\MenuTreePath;
use tests\FunctionalTester;

class ClosureTableTreePathQueryBehaviorCest
{
    /**
     * Test filter byParentId.
     *
     * @param FunctionalTester $I
     */
    public function findByParentIdTest(FunctionalTester $I): void
    {
        $menu1 = new Menu();
        $menu1->title = 'Menu 1';
        $menu1->save();

        $I->assertCount(1, MenuTreePath::find()->byParentId($menu1->id)->all());
    }

    /**
     * Test filter byChildId.
     *
     * @param FunctionalTester $I
     */
    public function findByChildIdTest(FunctionalTester $I): void
    {
        $menu = new Menu();
        $menu->title = 'findByChild';
        $menu->save();

        $I->assertCount(1, MenuTreePath::find()->byChildId($menu->id)->all());
    }

    /**
     * Test filter byChildId.
     *
     * @param FunctionalTester $I
     */
    public function findByNotChildIdTest(FunctionalTester $I): void
    {
        $menu1 = new Menu();
        $menu1->title = 'Menu 1';
        $menu1->save();

        $I->assertCount(MenuTreePath::find()->count() - 1, MenuTreePath::find()->byNotChildId($menu1->id)->all());
    }

    /**
     * Tests find by parent level.
     *
     * @param FunctionalTester $I
     */
    public function findByParentLevelTest(FunctionalTester $I): void
    {
        $menu1 = new Menu();
        $menu1->title = 'Menu 1';
        $menu1->save();

        $I->seeNumRecords(
            MenuTreePath::find()->byParentLevel(1)->count(),
            MenuTreePath::tableName(),
            ['parent_level' => 1]
        );
    }

    /**
     * Tests find by child level.
     *
     * @param FunctionalTester $I
     */
    public function findByChildLevelTest(FunctionalTester $I): void
    {
        $menu1 = new Menu();
        $menu1->title = 'Menu 1';
        $menu1->save();

        $I->seeNumRecords(
            MenuTreePath::find()->byChildLevel(1)->count(),
            MenuTreePath::tableName(),
            ['child_level' => 1]
        );
    }
}
