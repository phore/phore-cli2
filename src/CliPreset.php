<?php

namespace Phore\Cli;

class CliPreset
{
    public $presets = null;
    public function __construct($iniFile=null)
    {
        if ($iniFile !== null) {
            $this->loadPresets($iniFile);
        }
    }

    public function loadPresets($iniFile) : void
    {
        $this->presets = parse_ini_file($iniFile, true);
        if ($this->presets === false)
            throw new \InvalidArgumentException("Failed to load presets from '$iniFile': " . error_get_last()["message"]);
    }




    public function getPreset($presetName, &$arguments) : array
    {
        if ($this->presets === null)
            throw new \InvalidArgumentException("No presets loaded.");
        if ( ! isset ($this->presets[$presetName]))
            throw new \InvalidArgumentException("Preset '$presetName' not found.");
        $preset = $this->presets[$presetName];
        $ret = null;
        foreach ($preset as $key => $value) {
            if ($key === "CMD") {
                $ret = explode(" ", $value);
                continue;
            }
            if ( ! isset ($arguments[$key]))
                $arguments[$key] = $value; // Only set if not already set

        }
        return $ret;
    }

    public function getHelp() : string {
        if ($this->presets === null)
            return "";
        $help = "\nPresets: (call by prepending :presetName to command)";
        foreach ($this->presets as $presetName => $preset) {
            $help .= "\n\n" . $presetName . "";
            foreach ($preset as $key => $value) {
                $help .= "\n\t" . $key . " = " . $value;
            }
        }
        return $help;
    }

}
