<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%menu_tree_path}}`.
 */
class m200515_103213_create_menu_tree_path_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(
            '{{%menu_tree_path}}',
            [
                'parent_id' => $this->integer(11)->notNull()->unsigned()->comment('ID parent'),
                'child_id' => $this->integer(11)->notNull()->unsigned()->comment('ID child'),
                'nearest_parent_id' => $this->integer(11)->null()->unsigned()->comment('ID nearest parent'),
                'parent_level' => $this->integer(11)->null()->unsigned()->comment('Parent level'),
                'child_level' => $this->integer(11)->notNull()->unsigned()->defaultValue(1)->comment('Child level'),
                'created_at' => $this->dateTime()->notNull()->defaultExpression('NOW()')->comment('Created date'),
            ]
        );

        $this->addPrimaryKey('primary_key', '{{%menu_tree_path}}', ['parent_id', 'child_id']);

        $this->addForeignKey(
            'fk_menu_tree_path_parent',
            '{{%menu_tree_path}}',
            'parent_id',
            '{{%menu}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_menu_tree_path_child',
            '{{%menu_tree_path}}',
            'child_id',
            '{{%menu}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_menu_tree_path_nearest_parent',
            '{{%menu_tree_path}}',
            'nearest_parent_id',
            '{{%menu}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_menu_tree_path_parent', '{{%menu_tree_path}}');
        $this->dropForeignKey('fk_menu_tree_path_child', '{{%menu_tree_path}}');
        $this->dropForeignKey('fk_menu_tree_path_nearest_parent', '{{%menu_tree_path}}');
        $this->dropTable('{{%menu_tree_path}}');
    }
}
