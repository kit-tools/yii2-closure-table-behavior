<?php

namespace kittools\closuretable\components\behaviors;

use kittools\closuretable\exceptions\LogicException;
use kittools\closuretable\models\AbstractTreePath;
use Yii;
use yii\base\Behavior;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * @package kittools\closuretable\components\behaviors
 *
 * @property ActiveRecord $owner
 */
class ClosureTableBehavior extends Behavior
{
    /** @var int the default deletion type does not delete the owner if there are children */
    public const DELETION_TYPE_0 = 0;

    /** @var int removes the owner by first moving the children under the owner’s parent */
    public const DELETION_TYPE_1 = 1;

    /** @var string name of the class that is responsible for the nesting tree */
    public $treePathModelClass;

    /** @var string field name to save the parent */
    public $ownerParentIdAttribute = 'parent_id';

    /** @var int owner deletion type. It is set from constants self::DELETION_TYPE_* */
    public $deletionType = self::DELETION_TYPE_0;

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
        $model = $this->addTreePathModelObject();
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
    protected function addTreePathModelObject(): AbstractTreePath
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
        $this->oldParentId = $this->owner->oldAttributes[$this->ownerParentIdAttribute] ?? null;

        if ($this->owner->getAttribute($this->ownerParentIdAttribute) !== null) {
            if ($this->hasChilds()) {
                throw new LogicException('You cannot move a parent under a child');
            }
        }
    }

    /**
     * Verifies that the parent has a child.
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
        switch ($this->deletionType) {
            case self::DELETION_TYPE_1:
                $this->deletionType1();
                break;
            case self::DELETION_TYPE_0:
            default:
                $this->deletionType0();
                break;
        }
    }

    /**
     * The default deletion type does not delete the owner if there are children.
     */
    protected function deletionType0(): void
    {
        if ($this->childs()->count()) {
            throw new LogicException('You can’t delete the owner, he has childs.');
        }
        $this->removeTreePathByIds($this->owner->id);
    }

    /**
     * Removes the owner by first moving the children under the owner’s parent.
     */
    protected function deletionType1(): void
    {
        foreach ($this->childs(1)->all() as $child) {
            $child->setAttribute(
                $this->ownerParentIdAttribute,
                $this->owner->getAttribute($this->ownerParentIdAttribute)
            );
            $child->save();
        }
        $this->removeTreePathByIds($this->owner->id);
    }

    /**
     * Updates the tree path after updating the parent.
     */
    public function afterUpdate(): void
    {
        if ($this->oldParentId != $this->owner->getAttribute($this->ownerParentIdAttribute)) {
            $this->rebuildTrePath();
        }
    }

    /**
     * Get all owner children.
     *
     * @param bool|false $withParent
     * @param int|null $depth
     * @param bool|false $eagerLoading
     * @return ActiveQuery
     */
    public function childs(?int $depth = null, bool $withParent = false, bool $eagerLoading = false): ActiveQuery
    {
        return $this->owner::find()
            ->childs($this->owner->id, $withParent, $depth, $eagerLoading)
            ->orderBy(['treePathsChild.child_level' => SORT_ASC]);
    }

    /**
     * Add tree path.
     *
     * @throws InvalidConfigException
     */
    public function addTreePath(): void
    {
        $this->addTreePathOwnerToParents();
        $this->addTreePathOwnerToOwner();
    }

    /**
     * Rebuild tree path.
     *
     * @throws InvalidConfigException
     */
    public function rebuildTrePath(): void
    {
        $childs = $this->childs()->all();
        $this->removeTreePathByIds($this->owner->id);
        $this->addTreePath();

        if ($childs) {
            $this->removeTreePathByIds(ArrayHelper::map($childs, 'id', 'id'));
            foreach ($childs as $child) {
                $child->addTreePath();
            }
        }
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