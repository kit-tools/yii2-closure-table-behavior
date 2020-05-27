<?php

namespace kittools\closuretable\models;

use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Abstract class for *TreePath model.
 *
 * @property int $parent_id
 * @property int $child_id
 * @property int $nearest_parent_id
 * @property int $parent_level
 * @property int $child_level
 * @property string $created_at
 *
 * @package kittools\closuretable\src
 */
abstract class AbstractTreePath extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName(): string
    {
        throw new InvalidConfigException('Specify the name of the table for storing parent-child relations');
    }

    /**
     * @inheritDoc
     */
    public static function primaryKey()
    {
        return ['parent_id', 'child_id'];
    }

    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => null,
                'value' => new Expression('NOW()'),
            ],
        ];
    }
}