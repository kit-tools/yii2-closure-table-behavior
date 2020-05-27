<?php

namespace tests\functional\components\behaviors;


use tests\app\models\Menu;
use tests\app\models\MenuTreePath;
use tests\FunctionalTester;
use yii\helpers\ArrayHelper;

class ClosureTableQueryBehaviorCest
{
    /**
     * Test get parents.
     *
     * @param FunctionalTester $I
     */
    public function getParentsTest(FunctionalTester $I): void
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

        // withChild = false, depth = null
        $I->assertCount(0, Menu::find()->parents($menu1->id)->all());
        $I->assertCount(1, Menu::find()->parents($menu11->id)->all());
        $I->assertCount(2, Menu::find()->parents($menu111->id)->all());
        $I->assertCount(3, Menu::find()->parents($menu1111->id)->all());
        $I->assertCount(1, Menu::find()->parents($menu12->id)->all());

        // withChild = true, depth = null
        $I->assertCount(1, Menu::find()->parents($menu1->id, true)->all());
        $I->assertCount(2, Menu::find()->parents($menu11->id, true)->all());
        $I->assertCount(3, Menu::find()->parents($menu111->id, true)->all());
        $I->assertCount(4, Menu::find()->parents($menu1111->id, true)->all());
        $I->assertCount(2, Menu::find()->parents($menu12->id, true)->all());

        // withChild = true, depth = 1
        $parents = Menu::find()->parents($menu111->id, true, 1)->all();
        codecept_debug(Menu::find()->parents($menu111->id, true, 1)->createCommand()->getRawSql());
        codecept_debug(ArrayHelper::map($parents, 'id', 'title'));
        $I->assertCount(2, $parents);
        $I->assertEquals($menu11->id, $parents[0]->id);
        $I->assertEquals($menu111->id, $parents[1]->id);

        // withChild = false, depth = 1
        /*$parents = Menu::find()->parents($menu111->id, false, 1)->all();
        $I->assertCount(1, $parents);;
        $I->assertEquals($menu111->id, $parents[0]->id);

        // withChild = true, depth = 4
        $parents = Menu::find()->parents($menu111->id, true, 4)->all();
        $I->assertCount(3, $parents);;
        $I->assertEquals($menu1->id, $parents[0]->id);
        $I->assertEquals($menu11->id, $parents[1]->id);
        $I->assertEquals($menu111->id, $parents[2]->id);*/
    }

    /**
     * Test get parent.
     *
     * @param FunctionalTester $I
     */
    public function getParentTest(FunctionalTester $I): void
    {
        $menu1 = new Menu();
        $menu1->title = 'menu 1';
        $menu1->save();

        $menu11 = new Menu();
        $menu11->title = 'menu 1.1';
        $menu11->parent_id = $menu1->id;
        $menu11->save();

        $I->seeRecord(MenuTreePath::class, ['parent_id' => $menu1->id, 'child_id' => $menu11->id, 'child_level' => 2]);
        $parent = Menu::find()->parent($menu11->id)->one();
        $I->assertEquals($menu1->id, $parent->id);
    }

    /**
     * Test get childs.
     *
     * @param FunctionalTester $I
     */
    public function getChildsTest(FunctionalTester $I): void
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

        // $withParent = false, $depth = null
        $childs = Menu::find()->childs($menu1->id)->all();
        $I->assertCount(4, $childs);
        $I->assertEquals($menu11->id, $childs[0]->id);
        $I->assertEquals($menu111->id, $childs[1]->id);
        $I->assertEquals($menu1111->id, $childs[2]->id);
        $I->assertEquals($menu12->id, $childs[3]->id);
        $I->assertCount(2, Menu::find()->childs($menu11->id)->all());
        $I->assertCount(1, Menu::find()->childs($menu111->id)->all());
        $I->assertCount(0, Menu::find()->childs($menu1111->id)->all());
        $I->assertCount(0, Menu::find()->childs($menu12->id)->all());

        // $withParent = true, $depth = null
        $childs = Menu::find()->childs($menu1->id, true)->all();
        $I->assertCount(5, $childs);
        $I->assertEquals($menu1->id, $childs[0]->id);
        $I->assertEquals($menu11->id, $childs[1]->id);
        $I->assertEquals($menu111->id, $childs[2]->id);
        $I->assertEquals($menu1111->id, $childs[3]->id);
        $I->assertEquals($menu12->id, $childs[4]->id);

        // $withParent = false, $depth = 0
        $I->assertCount(0, Menu::find()->childs($menu1->id, false, 0)->all());

        // $withParent = false, $depth = 1
        $childs = Menu::find()->childs($menu1->id, false, 1)->all();
        $I->assertCount(2, $childs);
        $I->assertEquals($menu11->id, $childs[0]->id);
        $I->assertEquals($menu12->id, $childs[1]->id);
        $childs = Menu::find()->childs($menu11->id, false, 1)->all();
        $I->assertCount(1, $childs);
        $I->assertEquals($menu111->id, $childs[0]->id);

        // $withParent = true, $depth = 1
        $childs = Menu::find()->childs($menu1->id, true, 1)->all();
        $I->assertCount(3, $childs);
        $I->assertEquals($menu1->id, $childs[0]->id);
        $I->assertEquals($menu11->id, $childs[1]->id);
        $I->assertEquals($menu12->id, $childs[2]->id);
        $childs = Menu::find()->childs($menu11->id, true, 1)->all();
        $I->assertCount(2, $childs);
        $I->assertEquals($menu11->id, $childs[0]->id);
        $I->assertEquals($menu111->id, $childs[1]->id);

        // $withParent = false, $depth = 2
        $childs = Menu::find()->childs($menu11->id, false, 2)->all();
        $I->assertCount(2, $childs);
        $I->assertEquals($menu111->id, $childs[0]->id);
        $I->assertEquals($menu1111->id, $childs[1]->id);

        // $withParent = true, $depth = 2
        $childs = Menu::find()->childs($menu11->id, true, 2)->all();
        $I->assertCount(3, $childs);
        $I->assertEquals($menu11->id, $childs[0]->id);
        $I->assertEquals($menu111->id, $childs[1]->id);
        $I->assertEquals($menu1111->id, $childs[2]->id);

        // $withParent = true, $depth = 10
        $childs = Menu::find()->childs($menu11->id, true, 10)->all();
        $I->assertCount(3, $childs);
        $I->assertEquals($menu11->id, $childs[0]->id);
        $I->assertEquals($menu111->id, $childs[1]->id);
        $I->assertEquals($menu1111->id, $childs[2]->id);
    }

    /**
     * Test get roots.
     *
     * @param FunctionalTester $I
     */
    public function getRootsTest(FunctionalTester $I): void
    {
        $menu1 = new Menu();
        $menu1->title = 'menu root 1';
        $menu1->save();

        $menu11 = new Menu();
        $menu11->title = 'menu 1.1';
        $menu11->parent_id = $menu1->id;
        $menu11->save();

        $menu2 = new Menu();
        $menu2->title = 'menu root 2';
        $menu2->save();

        $menu21 = new Menu();
        $menu21->title = 'menu 2.1';
        $menu21->parent_id = $menu2->id;
        $menu21->save();

        $menu3 = new Menu();
        $menu3->title = 'menu root 3';
        $menu3->save();

        $roots = Menu::find()
            ->roots()
            ->andWhere(
                [
                    'title' => [
                        'menu root 1',
                        'menu root 2',
                        'menu root 3',
                    ],
                ]
            )
            ->all();
        $I->assertCount(3, $roots);
    }

    /**
     * Test get nearest childs.
     *
     * @param FunctionalTester $I
     */
    public function getNearestChildsTest(FunctionalTester $I): void
    {
        $menu1 = new Menu();
        $menu1->title = 'menu 1';
        $menu1->save();

        $menu11 = new Menu();
        $menu11->title = 'menu 1.1';
        $menu11->parent_id = $menu1->id;
        $menu11->save();

        $menu12 = new Menu();
        $menu12->title = 'menu 1.2';
        $menu12->parent_id = $menu1->id;
        $menu12->save();

        $menu111 = new Menu();
        $menu111->title = 'menu 1.1.1';
        $menu111->parent_id = $menu11->id;
        $menu111->save();

        $nearestChilds = Menu::find()->nearestChilds($menu1->id)->orderBy('child_id')->all();
        $I->assertCount(2, $nearestChilds);
        $I->assertEquals($menu11->id, $nearestChilds[0]->id);
        $I->assertEquals($menu12->id, $nearestChilds[1]->id);
    }
}
