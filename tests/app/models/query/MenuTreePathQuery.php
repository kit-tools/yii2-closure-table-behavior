<?php

namespace tests\app\models\query;

use kittools\closuretable\components\behaviors\ClosureTableTreePathQueryBehavior;
use yii\db\ActiveQuery;

/**
 * Class TreePathQuery
 * @package models\query
 *
 * @mixin ClosureTableTreePathQueryBehavior
 */
class MenuTreePathQuery extends ActiveQuery
{
    public function behaviors(): array
    {
        return [
            [
                'class' => ClosureTableTreePathQueryBehavior::class,
            ],
        ];
    }
}