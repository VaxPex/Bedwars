<?php

declare(strict_types=1);

namespace VaxPex\utils;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\Vine;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\player\Player;
use pocketmine\world\World;

class Utils
{

	/**
	 * Helper function which creates minimal NBT needed to spawn an entity.
	 */
	public static function makeNBT(Vector3 $position, ?Vector3 $motion = null, float $yaw = 0.0, float $pitch = 0.0): CompoundTag
	{
		return CompoundTag::create()
			->setTag("Pos", new ListTag([
				new DoubleTag($position->x),
				new DoubleTag($position->y),
				new DoubleTag($position->z)
			]))
			->setTag("Motion", new ListTag([
				new DoubleTag($motion !== null ? $motion->x : 0),
				new DoubleTag($motion !== null ? $motion->y : 0),
				new DoubleTag($motion !== null ? $motion->z : 0)
			]))
			->setTag("Rotation", new ListTag([
				new FloatTag($yaw),
				new FloatTag($pitch)
			]));
	}

	public static function getGenericFlag(Entity $entity, int $flagId): bool
	{
		return isset($entity->getNetworkProperties()->getAll()[$flagId]) && $entity->getNetworkProperties()->getAll()[$flagId] == true;
	}

	public static function broadcastActorEvent(Entity $entity, int $eventId, ?int $eventData = null, ?array $players = null): void
	{
		self::broadcastPacket($players ?? $entity->getViewers(), ActorEventPacket::create($entity->getId(), $eventId, $eventData ?? 0));
	}

	/**
	 * @param Player[] $players
	 * @param ClientboundPacket $pk
	 * @return void
	 */
	public static function broadcastPacket(array $players, ClientboundPacket $pk)
	{
		foreach ($players as $player) {
			$player->getNetworkSession()->sendDataPacket($pk);
		}
	}

	public static function broadcastWorldSoundEvent(World $world, Vector3 $pos, int $soundId, int $extraData = -1, string $entityTypeId = ':', bool $isBabyMob = false, bool $disableRelativeVolume = false): void
	{
		$pk = new LevelSoundEventPacket();
		$pk->sound = $soundId;
		$pk->extraData = $extraData;
		$pk->entityType = $entityTypeId;
		$pk->isBabyMob = $isBabyMob;
		$pk->disableRelativeVolume = $disableRelativeVolume;
		$pk->position = $pos->asVector3();
		$world->broadcastPacketToViewers($pos, $pk);
	}

	public static function canPassThrough(Block $block): bool
	{
		if ($block instanceof Air) {
			return true;
		}
		if ($block instanceof Vine) {
			return true;
		}
		return false;
	}

	public static function getWoolMetaByName(string $team): int
	{
		$meta = [
			"red" => 14,
			"blue" => 11,
			"yellow" => 4,
			"green" => 5,
			"aqua" => 3,
			"white" => 0,
			"pink" => 2,
			"gray" => 8
		];
		return $meta[strtolower($team)];
	}

	public static function getColorByName(string $team): string
	{
		$color = [
			"red" => "§c",
			"blue" => "§9",
			"yellow" => "§e",
			"green" => "§a",
			"aqua" => "§b",
			"white" => "§f",
			"pink" => "§d",
			"gray" => "§7"
		];
		return $color[strtolower($team)];
	}
}