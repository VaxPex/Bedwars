<?php

/**
 *  ____           ___        __
 * | __ )  ___  __| \ \      / /_ _ _ __ ___
 * |  _ \ / _ \/ _` |\ \ /\ / / _` | '__/ __|
 * | |_) |  __/ (_| | \ V  V / (_| | |  \__ \
 * |____/ \___|\__,_|  \_/\_/ \__,_|_|  |___/
 *
 * This file is under the GNU-3.0 read it before you do anything
 *
 * @copyright VaxPex 2018/2022
 */

declare(strict_types=1);

namespace VaxPex\arena;

use pocketmine\utils\Config;
use pocketmine\world\World;
use VaxPex\Bedwars;

final class Arena
{

	public array $data = [];
	public string $mode = "NoMode";
	public ?World $world = null;
	public Bedwars $plugin;

	public function __construct(World $world, array $data, string $mode)
	{
		$this->data = $data;
		$this->mode = $mode;
		$this->world = $world;
		$this->plugin = Bedwars::getInstance();
	}

	public function saveData()
	{
		$config = new Config($this->plugin->getDataFolder() . "arenas/" . $this->world->getFolderName() . ".yml", Config::YAML);
		$config->setAll($this->data);
		$config->save();
		$config->reload();
	}
}