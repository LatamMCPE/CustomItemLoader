<?php

/*
 *    ____          _                  ___ _                 _                    _
 *   / ___|   _ ___| |_ ___  _ __ ___ |_ _| |_ ___ _ __ ___ | |    ___   __ _  __| | ___ _ __
 *  | |  | | | / __| __/ _ \| '_ ` _ \ | || __/ _ \ '_ ` _ \| |   / _ \ / _` |/ _` |/ _ \ '__|
 *  | |__| |_| \__ \ || (_) | | | | | || || ||  __/ | | | | | |__| (_) | (_| | (_| |  __/ |
 *   \____\__,_|___/\__\___/|_| |_| |_|___|\__\___|_| |_| |_|_____\___/ \__,_|\__,_|\___|_|
 *
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace alvin0319\CustomItemLoader\item;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\item\Durable;

class CustomDurableItem extends Durable{
	use CustomItemTrait;

	public function getMaxStackSize() : int{
		return $this->getProperties()->getMaxStackSize();
	}

	public function getMaxDurability() : int{
		return $this->getProperties()->getMaxDurability();
	}

	public function onDestroyBlock(Block $block) : bool{
		return $this->applyDamage(1);
	}

	public function onAttackEntity(Entity $victim) : bool{
		return $this->applyDamage(1);
	}

	public function getMiningEfficiency(Block $block) : float{
		return $this->getProperties()->getMiningSpeed();
	}
}