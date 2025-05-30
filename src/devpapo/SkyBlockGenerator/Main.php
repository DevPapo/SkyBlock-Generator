<?php

namespace DevPapo\SkyBlockGenerator;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase {

    private BlockGenerator $generator;

    protected function onEnable(): void {
        $this->saveDefaultConfig();
        $this->generator = new BlockGenerator($this);
        
        $interval = (float)$this->getConfig()->get("generation_interval", 1.5);
        $this->getScheduler()->scheduleRepeatingTask(
            new \pocketmine\scheduler\ClosureTask(fn() => $this->generator->processGeneration()),
            (int)($interval * 20)
        );
        
        $this->getLogger()->info("§aSkyBlock-Generator activado correctamente");
    }
}