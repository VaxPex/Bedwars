<?php

declare(strict_types=1);

namespace VaxPex\arena;

use pocketmine\utils\Config;
use VaxPex\Bedwars;

class ArenaFakeData
{

	public array $data = [];

	public Config $fakeConfig;

	public function __construct(string $arenaName)
	{
		$this->fakeConfig = new Config(Bedwars::getInstance()->getDataFolder() . "arenas/" . $arenaName . ".yml", Config::YAML);
		$this->data = $this->fakeConfig->getAll();
	}
}