<?php

namespace tests\app\models;

use kittools\closuretable\models\AbstractTreePath;
use tests\app\models\query\MenuTreePathQuery;

/**
 * @package tests\models
 *
 * @property int $parent_id
 * @property int $child_id
 * @property int $nearest_parent_id
 * @property int $parent_level
 * @property int $child_level
 * @property string $created_at
 */
class MenuTreePath extends AbstractTreePath
{
    /**
     * @inheritDoc
     */
    public static function tableName(): string
    {
        return 'menu_tree_path';
    }

    /**
     * @inheritdoc
     * @return MenuTreePathQuery the active query used by this AR class.
     */
    public static function find(): MenuTreePathQuery
    {
        return new MenuTreePathQuery(get_called_class());
    }
}