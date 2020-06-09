<?php

namespace tests\app\models;

use kittools\closuretable\components\behaviors\ClosureTableBehavior;
use tests\app\models\query\MenuQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $title
 * @property int $parent_id
 *
 * @property MenuTreePath $treePathOwner
 * @property MenuTreePath $treePathsChild
 * @property MenuTreePath $treePathsNearestParent
 * @property MenuTreePath $treePathsParent
 *
 * @mixin ClosureTableBehavior
 *
 * @package tests\models
 */
class Menu extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'menu';
    }

    /**
     * {@inheritdoc}
     * @return MenuQuery the active query used by this AR class.
     */
    public static function find(): MenuQuery
    {
        return new MenuQuery(get_called_class());
    }

    public function behaviors(): array
    {
        return [
            'treePath' => [
                'class' => ClosureTableBehavior::class,
                'treePathModelClass' => MenuTreePath::class,
                'ownerParentIdAttribute' => 'parent_id',
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['title'], 'required'],
            [['title'], 'string', 'max' => 30],
            [['parent_id'], 'integer'],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getTreePathOwner(): ActiveQuery
    {
        return $this->hasOne(MenuTreePath::class, ['parent_id' => 'id', 'child_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTreePathsChild(): ActiveQuery
    {
        return $this->hasMany(MenuTreePath::class, ['child_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTreePathsNearestParent(): ActiveQuery
    {
        return $this->hasMany(MenuTreePath::class, ['nearest_parent_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTreePathsParent(): ActiveQuery
    {
        return $this->hasMany(MenuTreePath::class, ['parent_id' => 'id']);
    }
}