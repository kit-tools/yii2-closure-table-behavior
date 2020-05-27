<?php

namespace tests\app\models\query;

use kittools\closuretable\components\behaviors\ClosureTableQueryBehavior;
use yii\db\ActiveQuery;

/**
 * @mixin ClosureTableQueryBehavior
 */
class MenuQuery extends ActiveQuery
{
    public function behaviors(): array
    {
        return [
            [
                'class' => ClosureTableQueryBehavior::class,
            ],
        ];
    }
}