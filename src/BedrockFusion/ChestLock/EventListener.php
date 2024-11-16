<?php

declare(strict_types = 1);

namespace BedrockFusion\ChestLock;

use BedrockFusion\ChestLock\data\DataManager;
use BedrockFusion\ChestLock\forms\ChestLockConfirmForm;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\tile\Chest as TileChest;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\ChestPairEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\ItemTypeIds;

class EventListener implements Listener{
	private Loader $plugin;

	public function __construct(Loader $plugin){
		$this->plugin = $plugin;
	}

	public function onInteract(PlayerInteractEvent $event): void{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$tile = $block->getPosition()->getWorld()->getTile($block->getPosition()->asVector3());

		if(
			$event->isCancelled() ||
			$event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK ||
			$block->getTypeId() !== BlockTypeIds::CHEST ||
			!$tile instanceof TileChest
		){
			return;
		}

		$chestPosition = $block->getPosition()->asPosition();
		if(DataManager::getInstance()->isChestLocked($chestPosition)){
			$chestData = DataManager::getInstance()->getChestData($chestPosition);
			if(!$player->getServer()->isOp($player->getName()) && $chestData->getOwner() !== $player->getName()){
				$event->cancel();
				$player->sendMessage($this->getPlugin()->ChestLockedMessage());
				return;
			}

			$itemInHand = $player->getInventory()->getItemInHand();
			if(
				$itemInHand->getTypeId() === ItemTypeIds::BLAZE_ROD &&
				$itemInHand->getCustomName() === $this->getPlugin()->getKeyName($chestData->getIdentifier())
			){
				DataManager::getInstance()->unlockChest($chestPosition);
				$player->getInventory()->removeItem($itemInHand);
				$player->sendMessage($this->getPlugin()->UnlockingChestMessage());
			}
			$event->cancel();
			return;
		}

		if($player->isSneaking()){
			$blocks = [$block];
			if($tile->isPaired()){
				$blocks[] = $tile->getPair();
			}
			$player->sendForm(new ChestLockConfirmForm($blocks));
			$event->cancel();
		}
	}

	public function onBreak(BlockBreakEvent $event): void{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		if(
			$event->isCancelled() ||
			$block->getTypeId() !== BlockTypeIds::CHEST ||
			!DataManager::getInstance()->isChestLocked($block->getPosition())
		){
			return;
		}

		$chestData = DataManager::getInstance()->getChestData($block->getPosition());
		if($chestData->getOwner() === $player->getName() || $player->getServer()->isOp($player->getName())){
			DataManager::getInstance()->unlockChest($block->getPosition());
			$player->sendMessage($this->getPlugin()->BreakLockedChestMessage());
			return;
		}

		$event->cancel();
		$player->sendMessage($this->getPlugin()->ChestLockedMessage());
	}

	public function onPair(ChestPairEvent $event): void {
		$rightChest = $event->getRight();
		$leftChest = $event->getLeft();

		$world = $rightChest->getPosition()->getWorld();
		$rightTile = $world->getTile($rightChest->getPosition());
		$leftTile = $world->getTile($leftChest->getPosition());

		if($rightTile instanceof TileChest && $leftTile instanceof TileChest){
			$isRightLocked = DataManager::getInstance()->isChestLocked($rightTile->getPosition());
			$isLeftLocked = DataManager::getInstance()->isChestLocked($leftTile->getPosition());

			if($isRightLocked || $isLeftLocked){
				if($rightTile->isPaired()){
					$rightTile->unpair();
				}
				if($leftTile->isPaired()){
					$leftTile->unpair();
				}
				$event->cancel();
			}else{
				$event->uncancel();
				$rightTile->pairWith($leftTile);
				$leftTile->pairWith($rightTile);
			}
		}else{
			$event->cancel();
		}
	}

	public function onExplode(EntityExplodeEvent $event): void{
		$blocks = $event->getBlockList();

		foreach($blocks as $num => $block){
			if($block->getTypeId() !== BlockTypeIds::CHEST)
				continue;

			if(!DataManager::getInstance()->isChestLocked($block->getPosition()))
				continue;

			unset($blocks[$num]);
		}

		$event->setBlockList($blocks);
	}

	public function getPlugin(): Loader{
		return $this->plugin;
	}

	public function getDataManager(): DataManager{
		return DataManager::getInstance();
	}
}
