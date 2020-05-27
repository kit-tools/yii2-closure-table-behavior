<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%menu}}`.
 */
class m200515_100903_create_menu_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(
            '{{%menu}}',
            [
                'id' => $this->primaryKey(11)->unsigned(),
                'parent_id' => $this->integer(11)->null()->unsigned()->comment('ID parent menu'),
                'title' => $this->string(30)->notNull()->comment('Menu title'),
            ]
        );

        $this->addForeignKey(
            'fk_menu_parent_id',
            '{{%menu}}',
            'parent_id',
            '{{%menu}}',
            'id',
            'RESTRICT'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_menu_parent_id', '{{%menu}}');
        $this->dropTable('{{%menu}}');
    }
}
