<?php

namespace kittools\closuretable\components\behaviors;

use kittools\closuretable\exceptions\LogicException;
use yii\base\Behavior;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * ActiveQuery for owner tree path.
 *
 * @package kittools\closuretable\components\behaviors
 *
 * @property ActiveQuery $owner
 */
class ClosureTableQueryBehavior extends Behavior
{
    /**
     * Depth check.
     *
     * @param int|null $depth
     * @throws LogicException
     */
    protected function depthCheck(?int $depth = null): void
    {
        if ($depth !== null && $depth < 0) {
            throw new LogicException('Depth must be a positive number');
        }
    }

    /**
     * All childs to owner.
     *
     * @param int $ownerId
     * @param bool|false $withParent
     * @param int|null $depth
     * @param bool|false $eagerLoading
     * @return ActiveQuery
     */
    public function childs(
        int $ownerId,
        bool $withParent = false,
        ?int $depth = null,
        bool $eagerLoading = false
    ): ActiveQuery {
        $this->owner->innerJoinWith('treePathsChild treePathsChild', $eagerLoading)
            ->andWhere(['treePathsChild.parent_id' => $ownerId]);

        if (!$withParent) {
            $this->owner
                ->andWhere(['!=', 'treePathsChild.child_id', $ownerId]);
        }

        if ($depth !== null) {
            $this->depthCheck($depth);
            /** @var self $subQuery */
            $subQuery = ($this->owner->modelClass::find())
                ->select(new Expression('treePathOwner.child_level + :depth', [':depth' => $depth]))
                ->owner($ownerId);

            $this->owner
                ->andWhere(['<=', 'treePathsChild.child_level', $subQuery]);
        }

        return $this->owner;
    }

    /**
     * All parents to owner.
     *
     * @param int $ownerId
     * @param bool|false $withChild
     * @param int|null $depth
     * @param bool|false $eagerLoading
     * @return ActiveQuery
     * @throws LogicException
     */
    public function parents(
        int $ownerId,
        bool $withChild = false,
        ?int $depth = null,
        bool $eagerLoading = false
    ): ActiveQuery {
        $this->owner
            ->innerJoinWith('treePathsParent treePathsParent', $eagerLoading)
            ->andWhere(['treePathsParent.child_id' => $ownerId]);

        if (!$withChild) {
            $this->owner
                ->andWhere(['!=', 'treePathsParent.parent_id', $ownerId]);
        }

        if ($depth !== null) {
            $this->depthCheck($depth);
            /** @var self $subQuery */
            $subQuery = ($this->owner->modelClass::find())
                ->select(
                    new Expression(
                        'IF(treePathOwner.child_level <= :depth, 1, treePathOwner.child_level - :depth)',
                        [':depth' => $depth]
                    )
                )
                ->owner($ownerId);

            $this->owner
                ->andWhere(['>=', 'treePathsParent.parent_level', $subQuery]);
        }

        return $this->owner;
    }

    /**
     * Owner parent.
     *
     * @param int $ownerId
     * @param bool $eagerLoading
     * @return ActiveQuery
     */
    public function parent(int $ownerId, bool $eagerLoading = false): ActiveQuery
    {
        return $this->owner
            ->innerJoinWith('treePathsNearestParent treePathsNearestParent', $eagerLoading)
            ->andWhere(['treePathsNearestParent.parent_id' => $ownerId])
            ->andWhere(['treePathsNearestParent.child_id' => $ownerId]);
    }

    /**
     * All roots.
     *
     * @param bool $eagerLoading
     * @return ActiveQuery
     */
    public function roots(bool $eagerLoading = false): ActiveQuery
    {
        return $this->owner
            ->innerJoinWith('treePathsParent treePathsParent', $eagerLoading)
            ->andWhere(['IS', 'treePathsParent.nearest_parent_id', null]);
    }

    /**
     * Immediate children whose owner is the parent.
     *
     * @param int $ownerId
     * @param bool $eagerLoading
     * @return ActiveQuery
     */
    public function nearestChilds(int $ownerId, bool $eagerLoading = false): ActiveQuery
    {
        return $this->owner
            ->innerJoinWith('treePathsChild treePathsChild', $eagerLoading)
            ->andWhere(['treePathsChild.nearest_parent_id' => $ownerId])
            ->andWhere(['treePathsChild.parent_id' => $ownerId]);
    }

    /**
     * Query for owner.
     *
     * @param int $ownerId
     * @param bool $eagerLoading
     * @return ActiveQuery
     */
    public function owner(int $ownerId, bool $eagerLoading = false): ActiveQuery
    {
        return $this->owner
            ->innerJoinWith('treePathOwner treePathOwner', $eagerLoading)
            ->andWhere(['treePathOwner.parent_id' => $ownerId])
            ->andWhere(['treePathOwner.child_id' => $ownerId]);
    }
}