<?php

namespace alvin0319\CustomItemLoader\item;

use alvin0319\CustomItemLoader\item\properties\CustomItemProperties;
use pocketmine\item\Armor;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\ItemIdentifier;

class CustomArmorItem extends Armor{
	use CustomItemTrait;

	public function __construct(string $name, CustomItemProperties $properties){
		$this->properties = $properties;
		parent::__construct(new ItemIdentifier($this->properties->getId(), $this->properties->getMeta()), $this->properties->getName(), new ArmorTypeInfo(
			$this->properties->getDefencePoints(),
			$this->properties->getMaxDurability(),
			$this->properties->getArmorSlot()
		));
	}
}