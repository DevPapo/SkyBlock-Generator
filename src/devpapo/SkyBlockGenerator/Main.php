<?php

namespace devpapo\SkyBlockGenerator;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\Config;

class Main extends PluginBase {

    private BlockGenerator $generator;
    private ?TaskHandler $taskHandler = null; // Cambiado a TaskHandler

    protected function onEnable(): void {
        $this->saveResource("config.yml");
        $this->generator = new BlockGenerator($this);

        $interval = (float)max(0.05, $this->getConfig()->get("generation_interval", 1.5));
        $ticks = (int)($interval * 20);
        
        // Programar tarea y guardar el handler
        $this->taskHandler = $this->getScheduler()->scheduleRepeatingTask(
            new ClosureTask(function(): void {
                try {
                    $this->generator->processGeneration();
                } catch (\Throwable $e) {
                    $this->getLogger()->error("Error en generación: ".$e->getMessage());
                }
            }),
            $ticks
        );

        $this->getLogger()->info("§aSkyBlock-Generator activado! Intervalo: ".$interval."s");
    }

    protected function onDisable(): void {
        if($this->taskHandler !== null) {
            $this->taskHandler->cancel();
            $this->taskHandler = null;
        }
        $this->getLogger()->info("§cGenerador detenido");
    }

    public function getBlockGenerator(): BlockGenerator {
        return $this->generator;
    }
}