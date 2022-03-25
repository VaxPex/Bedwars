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

namespace VaxPex;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Bedwars extends PluginBase {

	public const PREFIX = TextFormat::DARK_GRAY . "[" . TextFormat::AQUA . "Bedwars" . TextFormat::DARK_GRAY . "] " . TextFormat::RESET . TextFormat::WHITE;

	protected function onEnable(): void {
		$FormAPI = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		if(!$FormAPI){
			error:
			$this->getServer()->getLogger()->info(self::PREFIX . "Install jojoe77777 FormAPI to run this plugin");
			$this->onEnableStateChange(false);
		}elseif($FormAPI->getDescription()->getAuthors()[0] !== "jojoe77777"){
			goto error;
		}
	}
}