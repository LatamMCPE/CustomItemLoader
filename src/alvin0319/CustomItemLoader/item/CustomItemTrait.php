<?php

namespace alvin0319\CustomItemLoader\item;

use alvin0319\CustomItemLoader\item\properties\CustomItemProperties;
use pocketmine\item\ItemIdentifier;

trait CustomItemTrait{
	/** @var CustomItemProperties */
	protected CustomItemProperties $properties;

	public function __construct(string $name, CustomItemProperties $properties){
		$this->properties = $properties;
		parent::__construct(new ItemIdentifier($this->properties->getId(), $this->properties->getMeta()), $this->properties->getName());
	}

	public function getProperties() : CustomItemProperties{
		return $this->properties;
	}

	public function getAttackPoints() : int{
		return $this->properties->getAttackPoints();
	}

	public function getCooldownTicks() : int{
		return $this->properties->getCooldown();
	}

	public function getBlockToolType() : int{
		return $this->properties->getBlockToolType();
	}

	public function getBlockToolHarvestLevel() : int{
		return $this->properties->getBlockToolHarvestLevel();
	}

	public function getMiningEfficiency(bool $isCorrectTool) : float{
		if($isCorrectTool){
			return $this->properties->getMiningSpeed();
		}
		return parent::getMiningEfficiency(false);
	}

	public function getMaxStackSize() : int{
		return $this->getProperties()->getMaxStackSize();
	}
}