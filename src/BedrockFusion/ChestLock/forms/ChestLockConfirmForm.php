<?php

declare(strict_types=1);

namespace BedrockFusion\ChestLock\forms;

use BedrockFusion\ChestLock\data\DataManager;
use BedrockFusion\ChestLock\Loader;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\player\Player;

class ChestLockConfirmForm extends MenuForm{
	/** @var Block[] */
	private array $blocks;

	public function __construct(array $blocks){
		$this->blocks = $blocks;
		parent::__construct(Loader::getInstance()->FormTitle(), Loader::getInstance()->FormDesc(), [
			new MenuOption(Loader::getInstance()->FormButton1()),
			new MenuOption(Loader::getInstance()->FormButton2())
		], function(Player $player, int $data): void{
			if($data === 0){
				foreach($this->blocks as $block){
					if($player->getWorld()->getBlock($block->getPosition()->asVector3())->getTypeId() !== BlockTypeIds::CHEST)
						continue;

					DataManager::getInstance()->lockChest($id = uniqid("chest-"),$player->getName(), $block->getPosition()->asPosition());
					$key = Loader::getInstance()->generateKey($id);
					if($player->getInventory()->canAddItem($key))
						$player->getInventory()->addItem($key);
					else $player->getWorld()->dropItem($player->getPosition()->asVector3(), $key);

					$player->sendMessage(Loader::getInstance()->LockingChestMessage());
				}
			}
		});
	}
}
