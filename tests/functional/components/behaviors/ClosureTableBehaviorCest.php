<?php

namespace tests\functional\components\behaviors;

use Exception;
use tests\app\models\Menu;
use tests\app\models\MenuTreePath;
use tests\FunctionalTester;
use Throwable;
use yii\db\StaleObjectException;

class ClosureTableBehaviorCest
{
    /**
     * Root Creation Testing.
     *
     * @param FunctionalTester $I
     */
    public function makeAsRootTest(FunctionalTester $I): void
    {
        $menu = new Menu();
        $menu->title = 'Make as root';
        $menu->save();

        $I->seeRecord(Menu::class, ['id' => $menu->id, 'parent_id' => null]);
        $I->seeRecord(
            MenuTreePath::class,
            ['parent_id' => $menu->id, 'child_id' => $menu->id, 'parent_level' => 1, 'child_level' => 1]
        );
        $I->seeNumRecords(1, MenuTreePath::tableName(), ['parent_id' => $menu->id]);
        $I->seeNumRecords(1, MenuTreePath::tableName(), ['child_id' => $menu->id]);
    }

    /**
     * The simple addition of a child.
     *
     * @param FunctionalTester $I
     */
    public function simpleSaveTest(FunctionalTester $I): void
    {
        $menu = new Menu();
        $menu->title = 'simple root';
        $menu->save();

        $I->seeNumRecords(1, MenuTreePath::tableName(), ['parent_id' => $menu->id]);
        $I->seeRecord(
            MenuTreePath::class,
            ['parent_id' => $menu->id, 'child_id' => $menu->id, 'parent_level' => 1, 'child_level' => 1]
        );

        $menuSub1 = new Menu();
        $menuSub1->title = 'simple sub 1';
        $menuSub1->parent_id = $menu->id;
        $menuSub1->save();

        $I->seeNumRecords(2, MenuTreePath::tableName(), ['parent_id' => $menu->id]);
        $I->seeRecord(
            MenuTreePath::class,
            ['parent_id' => $menu->id, 'child_id' => $menu->id, 'parent_level' => 1, 'child_level' => 1]
        );
        $I->seeRecord(
            MenuTreePath::class,
            ['parent_id' => $menu->id, 'child_id' => $menuSub1->id, 'parent_level' => 1, 'child_level' => 2]
        );
        $I->seeRecord(
            MenuTreePath::class,
            [
                'parent_id' => $menuSub1->id,
                'child_id' => $menuSub1->id,
                'parent_level' => 2,
                'child_level' => 2,
            ]
        );

        $menuSub2 = new Menu();
        $menuSub2->title = 'simple sub 2';
        $menuSub2->parent_id = $menu->id;
        $menuSub2->save();

        $I->seeNumRecords(3, MenuTreePath::tableName(), ['parent_id' => $menu->id]);
        $I->seeRecord(
            MenuTreePath::class,
            ['parent_id' => $menu->id, 'child_id' => $menu->id, 'parent_level' => 1, 'child_level' => 1]
        );
        $I->seeRecord(
            MenuTreePath::class,
            ['parent_id' => $menu->id, 'child_id' => $menuSub2->id, 'parent_level' => 1, 'child_level' => 2]
        );
        $I->seeRecord(
            MenuTreePath::class,
            [
                'parent_id' => $menuSub1->id,
                'child_id' => $menuSub1->id,
                'parent_level' => 2,
                'child_level' => 2,
            ]
        );
        $I->seeRecord(
            MenuTreePath::class,
            [
                'parent_id' => $menuSub2->id,
                'child_id' => $menuSub2->id,
                'parent_level' => 2,
                'child_level' => 2,
            ]
        );

        $menuSub2->parent_id = $menuSub1->id;
        $menuSub2->save();
        $I->seeNumRecords(3, MenuTreePath::tableName(), ['parent_id' => $menu->id]);
        $I->seeNumRecords(2, MenuTreePath::tableName(), ['parent_id' => $menuSub1->id]);
        $I->seeNumRecords(1, MenuTreePath::tableName(), ['parent_id' => $menuSub2->id]);
        $I->seeRecord(
            MenuTreePath::class,
            ['parent_id' => $menu->id, 'child_id' => $menu->id, 'parent_level' => 1, 'child_level' => 1]
        );
        $I->seeRecord(
            MenuTreePath::class,
            ['parent_id' => $menu->id, 'child_id' => $menuSub1->id, 'parent_level' => 1, 'child_level' => 2]
        );
        $I->seeRecord(
            MenuTreePath::class,
            ['parent_id' => $menu->id, 'child_id' => $menuSub2->id, 'parent_level' => 1, 'child_level' => 3]
        );
        $I->seeRecord(
            MenuTreePath::class,
            [
                'parent_id' => $menuSub1->id,
                'child_id' => $menuSub1->id,
                'parent_level' => 2,
                'child_level' => 2,
            ]
        );
        $I->seeRecord(
            MenuTreePath::class,
            [
                'parent_id' => $menuSub1->id,
                'child_id' => $menuSub2->id,
                'parent_level' => 2,
                'child_level' => 3,
            ]
        );
        $I->seeRecord(
            MenuTreePath::class,
            [
                'parent_id' => $menuSub2->id,
                'child_id' => $menuSub2->id,
                'parent_level' => 3,
                'child_level' => 3,
            ]
        );
    }

    /**
     * Moving within the first level parent
     *
     * @param FunctionalTester $I
     */
    public function movingWithinTheFirstLevelParentTest(FunctionalTester $I): void
    {
        $menu1 = new Menu();
        $menu1->title = 'menu 1';
        $menu1->save();

        $menu11 = new Menu();
        $menu11->title = 'menu 1.1';
        $menu11->parent_id = $menu1->id;
        $menu11->save();

        $menu111 = new Menu();
        $menu111->title = 'menu 1.1.1';
        $menu111->parent_id = $menu11->id;
        $menu111->save();

        $menu1111 = new Menu();
        $menu1111->title = 'menu 1.1.1.1';
        $menu1111->parent_id = $menu111->id;
        $menu1111->save();

        $menu12 = new Menu();
        $menu12->title = 'menu 1.2';
        $menu12->parent_id = $menu1->id;
        $menu12->save();

        $menu11->parent_id = $menu12->id;
        $menu11->save();

        $I->seeNumRecords(5, MenuTreePath::tableName(), ['parent_id' => $menu1->id]);
        $I->seeNumRecords(4, MenuTreePath::tableName(), ['parent_id' => $menu12->id]);
        $I->seeNumRecords(3, MenuTreePath::tableName(), ['parent_id' => $menu11->id]);
        $I->seeNumRecords(2, MenuTreePath::tableName(), ['parent_id' => $menu111->id]);
        $I->seeNumRecords(1, MenuTreePath::tableName(), ['parent_id' => $menu1111->id]);
        $I->seeRecord(
            MenuTreePath::class,
            [
                'parent_id' => $menu1->id,
                'child_id' => $menu1->id,
                'nearest_parent_id' => null,
                'parent_level' => 1,
                'child_level' => 1,
            ]
        );
        $I->seeRecord(
            MenuTreePath::class,
            [
                'parent_id' => $menu12->id,
                'child_id' => $menu12->id,
                'nearest_parent_id' => $menu1->id,
                'parent_level' => 2,
                'child_level' => 2,
            ]
        );
        $I->seeRecord(
            MenuTreePath::class,
            [
                'parent_id' => $menu12->id,
                'child_id' => $menu11->id,
                'nearest_parent_id' => $menu12->id,
                'parent_level' => 2,
                'child_level' => 3,
            ]
        );
        $I->seeRecord(
            MenuTreePath::class,
            [
                'parent_id' => $menu12->id,
                'child_id' => $menu111->id,
                'nearest_parent_id' => $menu11->id,
                'parent_level' => 2,
                'child_level' => 4,
            ]
        );
        $I->seeRecord(
            MenuTreePath::class,
            [
                'parent_id' => $menu12->id,
                'child_id' => $menu1111->id,
                'nearest_parent_id' => $menu111->id,
                'parent_level' => 2,
                'child_level' => 5,
            ]
        );

        $I->seeRecord(
            MenuTreePath::class,
            [
                'parent_id' => $menu11->id,
                'child_id' => $menu11->id,
                'nearest_parent_id' => $menu12->id,
                'parent_level' => 3,
                'child_level' => 3,
            ]
        );
        $I->seeRecord(
            MenuTreePath::class,
            [
                'parent_id' => $menu11->id,
                'child_id' => $menu111->id,
                'nearest_parent_id' => $menu11->id,
                'parent_level' => 3,
                'child_level' => 4,
            ]
        );
        $I->seeRecord(
            MenuTreePath::class,
            [
                'parent_id' => $menu11->id,
                'child_id' => $menu1111->id,
                'nearest_parent_id' => $menu111->id,
                'parent_level' => 3,
                'child_level' => 5,
            ]
        );

        $I->seeRecord(
            MenuTreePath::class,
            [
                'parent_id' => $menu111->id,
                'child_id' => $menu111->id,
                'nearest_parent_id' => $menu11->id,
                'parent_level' => 4,
                'child_level' => 4,
            ]
        );
        $I->seeRecord(
            MenuTreePath::class,
            [
                'parent_id' => $menu111->id,
                'child_id' => $menu1111->id,
                'nearest_parent_id' => $menu111->id,
                'parent_level' => 4,
                'child_level' => 5,
            ]
        );

        $I->seeRecord(
            MenuTreePath::class,
            [
                'parent_id' => $menu1111->id,
                'child_id' => $menu1111->id,
                'nearest_parent_id' => $menu111->id,
                'parent_level' => 5,
                'child_level' => 5,
            ]
        );
    }

    /**
     * Moving to root.
     *
     * @param FunctionalTester $I
     */
    public function movingToRootTest(FunctionalTester $I): void
    {
        $menu1 = new Menu();
        $menu1->title = 'menu 1';
        $menu1->save();

        $menu11 = new Menu();
        $menu11->title = 'menu 1.1';
        $menu11->parent_id = $menu1->id;
        $menu11->save();

        $menu111 = new Menu();
        $menu111->title = 'menu 1.1.1';
        $menu111->parent_id = $menu11->id;
        $menu111->save();

        $menu12 = new Menu();
        $menu12->title = 'menu 1.2';
        $menu12->parent_id = $menu1->id;
        $menu12->save();

        $I->seeNumRecords(4, MenuTreePath::tableName(), ['parent_id' => $menu1->id]);
        $I->seeNumRecords(2, MenuTreePath::tableName(), ['parent_id' => $menu11->id]);

        $menu11->parent_id = null;
        $menu11->save();

        $I->seeNumRecords(2, MenuTreePath::tableName(), ['parent_id' => $menu1->id]);
        $I->seeNumRecords(2, MenuTreePath::tableName(), ['parent_id' => $menu11->id]);

        $I->seeRecord(
            MenuTreePath::class,
            ['parent_id' => $menu1->id, 'child_id' => $menu1->id, 'parent_level' => 1, 'child_level' => 1]
        );
        $I->seeRecord(
            MenuTreePath::class,
            ['parent_id' => $menu1->id, 'child_id' => $menu12->id, 'parent_level' => 1, 'child_level' => 2]
        );

        $I->seeRecord(
            MenuTreePath::class,
            [
                'parent_id' => $menu11->id,
                'child_id' => $menu11->id,
                'nearest_parent_id' => null,
                'parent_level' => 1,
                'child_level' => 1,
            ]
        );
        $I->seeRecord(
            MenuTreePath::class,
            [
                'parent_id' => $menu11->id,
                'child_id' => $menu111->id,
                'parent_level' => 1,
                'nearest_parent_id' => $menu11->id,
                'child_level' => 2,
            ]
        );
    }

    /**
     * Move to level with other childs
     *
     * @param FunctionalTester $I
     */
    public function moveToLevelWithOtherChildsTest(FunctionalTester $I): void
    {
        $menu1 = new Menu();
        $menu1->title = 'menu 1';
        $menu1->save();

        $menu11 = new Menu();
        $menu11->title = 'menu 1.1';
        $menu11->parent_id = $menu1->id;
        $menu11->save();

        $menu111 = new Menu();
        $menu111->title = 'menu 1.1.1';
        $menu111->parent_id = $menu11->id;
        $menu111->save();

        $menu1111 = new Menu();
        $menu1111->title = 'menu 1.1.1.1';
        $menu1111->parent_id = $menu111->id;
        $menu1111->save();

        $menu11111 = new Menu();
        $menu11111->title = 'menu 1.1.1.1.1';
        $menu11111->parent_id = $menu1111->id;
        $menu11111->save();

        $I->seeNumRecords(5, MenuTreePath::tableName(), ['parent_id' => $menu1->id]);
        $I->seeNumRecords(3, MenuTreePath::tableName(), ['parent_id' => $menu111->id]);
        $I->seeNumRecords(2, MenuTreePath::tableName(), ['parent_id' => $menu1111->id]);

        $menu1111->parent_id = $menu1->id;
        $menu1111->save();

        $I->seeNumRecords(5, MenuTreePath::tableName(), ['parent_id' => $menu1->id]);
        $I->seeNumRecords(2, MenuTreePath::tableName(), ['parent_id' => $menu11->id]);
        $I->seeNumRecords(1, MenuTreePath::tableName(), ['parent_id' => $menu111->id]);
        $I->seeNumRecords(2, MenuTreePath::tableName(), ['parent_id' => $menu1111->id]);

        $I->assertCount(2, Menu::find()->childs($menu1->id, false, 1)->all());
    }

    /**
     * Exception if we move under our descendant
     *
     * @param FunctionalTester $I
     */
    public function exceptionIfWeMoveUnderOurChild(FunctionalTester $I): void
    {
        $I->expectThrowable(
            Exception::class,
            function () {
                $menu1 = new Menu();
                $menu1->title = 'menu 1';
                $menu1->save();

                $menu11 = new Menu();
                $menu11->title = 'menu 1.1';
                $menu11->parent_id = $menu1->id;
                $menu11->save();

                $menu111 = new Menu();
                $menu111->title = 'menu 1.1.1';
                $menu111->parent_id = $menu11->id;
                $menu111->save();

                $menu11->parent_id = $menu111->id;
                $menu11->save();
            }
        );
    }

    /**
     * Delete owner.
     *
     * @param FunctionalTester $I
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function deleteOwnerTest(FunctionalTester $I): void
    {
        $menu1 = new Menu();
        $menu1->title = 'menu 1';
        $menu1->save();

        $I->seeRecord(MenuTreePath::class, ['parent_id' => $menu1->id, 'child_id' => $menu1->id]);

        $menu1->delete();
        $I->seeNumRecords(0, MenuTreePath::tableName(), ['parent_id' => $menu1->id, 'child_id' => $menu1->id]);
    }

    /**
     * Removing the owner, if there are childs.
     *
     * @param FunctionalTester $I
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function removingTheOwnerIfThereAreChildsTest(FunctionalTester $I): void
    {
        $I->expectThrowable(
            Exception::class,
            function () {
                $menu1 = new Menu();
                $menu1->title = 'menu 1';
                $menu1->save();

                $menu11 = new Menu();
                $menu11->title = 'menu 1.1';
                $menu11->parent_id = $menu1->id;
                $menu11->save();

                $menu1->delete();
            }
        );
    }
}
