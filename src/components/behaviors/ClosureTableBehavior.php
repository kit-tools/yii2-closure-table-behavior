<?php

namespace kittools\closuretable\components\behaviors;

use kittools\closuretable\exceptions\LogicException;
use kittools\closuretable\models\AbstractTreePath;
use Yii;
use yii\base\Behavior;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * @package kittools\closuretable\components\behaviors
 *
 * @property ActiveRecord $owner
 */
class ClosureTableBehavior extends Behavior
{
    /** @var string name of the class that is responsible for the nesting tree */
    public $treePathModelClass;

    /** @var string field name to save the parent */
    public $ownerParentIdAttribute = 'parent_id';

    /** @var int|null old parent id */
    protected $oldParentId;

    /**
     * @inheritDoc
     *
     * @param Component $owner
     * @throws InvalidConfigException
     */
    public function attach($owner): void
    {
        if (empty($this->treePathModelClass) || !is_string($this->treePathModelClass)) {
            throw new InvalidConfigException('Tree path table name is not configured in ClosureTableBehavior');
        }

        parent::attach($owner);
    }

    /**
     * @inheritDoc
     */
    public function events(): array
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    /**
     * Creation tree path after insert.
     *
     * @throws InvalidConfigException
     */
    public function afterInsert(): void
    {
        if ($this->owner->getAttribute($this->ownerParentIdAttribute)) {
            $this->addTreePathOwnerToParents();
        }
        $this->addTreePathOwnerToOwner();
    }

    /**
     * Creates parent-child relationships for the owner.
     *
     * @throws InvalidConfigException
     */
    protected function addTreePathOwnerToParents(): void
    {
        foreach ($this->getTreePathParents() as $treePath) {
            $this->saveTreePathModel(
                $treePath->parent_id,
                $this->owner->id,
                $this->owner->getAttribute($this->ownerParentIdAttribute),
                $treePath->parent_level,
                $treePath->child_level + 1
            );
        }
    }

    /**
     * Returns tree paths relationships for owner.
     *
     * @return array
     */
    protected function getTreePathParents(): array
    {
        $primaryKey = $this->owner->getAttribute($this->ownerParentIdAttribute);
        if ($primaryKey === null) {
            return [];
        }

        return $this->treePathModelClass::find()
            ->byChildId($primaryKey)
            ->orderBy('child_level')
            ->all();
    }

    /**
     * Save TreePath Model.
     *
     * @param int $parentId
     * @param int $childId
     * @param int|null $nearestParentId
     * @param int $parentLevel
     * @param int $childLevel
     * @return bool
     * @throws InvalidConfigException
     */
    protected function saveTreePathModel(
        int $parentId,
        int $childId,
        ?int $nearestParentId,
        ?int $parentLevel,
        int $childLevel
    ): bool {
        $model = $this->createTreePathModelObject();
        $model->parent_id = $parentId;
        $model->child_id = $childId;
        $model->nearest_parent_id = $nearestParentId;
        $model->parent_level = $parentLevel;
        $model->child_level = $childLevel;

        return $model->save();
    }

    /**
     * Create TreePath Model Object.
     *
     * @return AbstractTreePath
     * @throws InvalidConfigException
     */
    protected function createTreePathModelObject(): AbstractTreePath
    {
        return Yii::createObject($this->treePathModelClass);
    }

    /**
     * Create owner-owner relationship.
     *
     * @throws InvalidConfigException
     */
    protected function addTreePathOwnerToOwner(): void
    {
        $parentLevel = $this->getParentLevel();
        $this->saveTreePathModel(
            $this->owner->id,
            $this->owner->id,
            $this->owner->getAttribute($this->ownerParentIdAttribute),
            $parentLevel + 1,
            $parentLevel + 1
        );
    }

    /**
     * Returns the parent level.
     *
     * @return int
     */
    protected function getParentLevel(): ?int
    {
        if (empty($this->owner->getAttribute($this->ownerParentIdAttribute))) {
            return 0;
        }

        return $this->treePathModelClass::find()
            ->select('parent_level')
            ->byParentId($this->owner->getAttribute($this->ownerParentIdAttribute))
            ->byChildId($this->owner->getAttribute($this->ownerParentIdAttribute))
            ->scalar();
    }

    /**
     * Verify owner before update.
     *
     * @throws LogicException
     */
    public function beforeUpdate(): void
    {
        $this->oldParentId = $this->owner->oldAttributes['parent_id'] ?? null;

        if ($this->owner->getAttribute($this->ownerParentIdAttribute) !== null) {
            if ($this->hasChilds()) {
                throw new LogicException('You cannot move a parent under a child');
            }
        }
    }

    /**
     * Verifies that the parent has a child
     *
     * @return bool
     */
    private function hasChilds(): bool
    {
        return (bool)$this->treePathModelClass::find()
            ->byParentId($this->owner->id)
            ->byChildId($this->owner->getAttribute($this->ownerParentIdAttribute))
            ->count();
    }

    /**
     * Checking parent before deleting.
     *
     * @throws LogicException
     */
    public function beforeDelete(): void
    {
        if ($this->owner::find()->childs($this->owner->id)->count()) {
            throw new LogicException('You can’t delete the owner, he has childs.');
        }
    }

    /**
     * Updates the tree path after updating the parent.
     */
    public function afterUpdate(): void
    {
        if ($this->oldParentId != $this->owner->getAttribute($this->ownerParentIdAttribute)) {
            $childs = $this->getChilds();
            $this->removeTreePathByIds($this->owner->id);
            $this->rebuildTreePath();

            if ($childs) {
                $this->removeTreePathByIds(ArrayHelper::map($childs, 'id', 'id'));
                foreach ($childs as $child) {
                    $child->rebuildTreePath();
                }
            }
        }
    }

    /**
     * Get all owner children.
     *
     * @return array
     */
    protected function getChilds(): array
    {
        return $this->owner::find()
            ->childs($this->owner->id)
            ->orderBy(['treePathsChild.child_level' => SORT_ASC])
            ->all();
    }

    /**
     * Rebuild tree path.
     *
     * @throws InvalidConfigException
     */
    public function rebuildTreePath(): void
    {
        $this->addTreePathOwnerToParents();
        $this->addTreePathOwnerToOwner();
    }

    /**
     * Remove tree path by ids.
     */
    /**
     * @param array|int $ids
     */
    protected function removeTreePathByIds($ids): void
    {
        $this->treePathModelClass::deleteAll(
            [
                'child_id' => $ids,
            ]
        );
    }
}