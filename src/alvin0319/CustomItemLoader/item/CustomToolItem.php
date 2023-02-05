<?php

namespace alvin0319\CustomItemLoader\item;

use pocketmine\item\Tool;

final class CustomToolItem extends Tool{
	use CustomItemTrait;

	public function getMaxDurability() : int{
		return $this->properties->getMaxDurability();
	}
}