<?php

namespace alvin0319\CustomItemLoader\item;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\item\Durable;

class CustomDurableItem extends Durable{
	use CustomItemTrait;

	public function getMaxDurability() : int{
		return $this->getProperties()->getMaxDurability();
	}

	public function onDestroyBlock(Block $block) : bool{
		return $this->applyDamage(1);
	}

	public function onAttackEntity(Entity $victim) : bool{
		return $this->applyDamage(1);
	}
}