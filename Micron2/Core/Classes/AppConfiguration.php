<?php

final class AppConfiguration {
    /**
     * @var array<string, mixed>
     */
    public array $configuration = [];

    public function __construct(string $configPath){
        $configurations = array_filter(scandir($configPath), function ($item) {
            return $item != '' && $item != '.' && $item != '..' && pathinfo($item, PATHINFO_EXTENSION) == 'json'; 
        }); 

        foreach ($configurations as $key => $appFile) {
            $fileString = file_get_contents($appFile);
            $fileDecoded = json_decode($fileString, true, 512, JSON_THROW_ON_ERROR);
            $this->configuration = array_merge($this->configuration, $fileDecoded);
        }
    }
}