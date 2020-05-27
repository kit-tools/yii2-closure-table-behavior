<?php

namespace kittools\closuretable\components\behaviors;

use yii\base\Behavior;
use yii\db\ActiveQuery;

/**
 * ActiveQuery for tree path.
 *
 * @package kittools\closuretable\components\behaviors
 *
 * @property ActiveQuery $owner
 */
class ClosureTableTreePathQueryBehavior extends Behavior
{
    /**
     * Filter by parent_id.
     *
     * @param int $childId
     * @return $this
     */
    public function byParentId(int $parentId): ActiveQuery
    {
        return $this->owner->andWhere(['parent_id' => $parentId]);
    }

    /**
     * Filter by child_id.
     *
     * @param int $childId
     * @return $this
     */
    public function byChildId(int $childId): ActiveQuery
    {
        return $this->owner->andWhere(['child_id' => $childId]);
    }

    /**
     * Filter by not child_id.
     *
     * @param int $childId
     * @return $this
     */
    public function byNotChildId(int $childId): ActiveQuery
    {
        return $this->owner->andWhere(['!=', 'child_id', $childId]);
    }

    /**
     * Filter by parent level.
     *
     * @param int $parentLevel
     * @return ActiveQuery
     */
    public function byParentLevel(int $parentLevel): ActiveQuery
    {
        return $this->owner->andWhere(['parent_level' => $parentLevel]);
    }

    /**
     * Filter by child level.
     *
     * @param int $childLevel
     * @return ActiveQuery
     */
    public function byChildLevel(int $childLevel): ActiveQuery
    {
        return $this->owner->andWhere(['child_level' => $childLevel]);
    }
}