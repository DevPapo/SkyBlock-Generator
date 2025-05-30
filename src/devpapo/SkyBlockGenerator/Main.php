<?php

namespace devpapo\SkyBlockGenerator;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;

class Main extends PluginBase implements Listener {

    private BlockGenerator $generator;
    private Config $config;
    private float $generationInterval;
    
    protected function onEnable(): void {
        $this->saveDefaultConfig();
        $this->config = $this->getConfig();
        
        try {
            $this->generationInterval = (float)$this->config->get("generation_interval", 1.5);
            $this->generator = new BlockGenerator($this, $this->config);
            
            $this->getScheduler()->scheduleRepeatingTask(
                new ClosureTask(fn() => $this->generator->generateAroundPlayers()),
                (int)($this->generationInterval * 20)
            );
            
            $this->getServer()->getPluginManager()->registerEvents($this, $this);
            $this->getLogger()->info("§aSkyBlock-Generator activado correctamente");
        } catch (\Throwable $e) {
            $this->getLogger()->error("Error al iniciar: " . $e->getMessage());
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
    }

    public function onDisable(): void {
        $this->getLogger()->info("§cSkyBlock-Generator desactivado");
    }
}