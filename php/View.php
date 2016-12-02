<?php

namespace Game;

class View
{
    protected $dir;

    public function __construct()
    {
        $this->dir = 'tpl/';
    }

    /**
     * Loads the file contents
     * and loops through the $values replacing every key for its value.
     *
     * @param $file
     * @param array $values
     * @return mixed|string
     */
    public function render($file, $values=[])
    {
        $file = $this->dir.$file.'.tpl';

        if (!file_exists($file))
        {
            return "Error loading file ($file).<br />";
        }

        $output = file_get_contents($file);

        foreach ($values as $key => $value)
        {
            $tagToReplace = "[@$key]";
            $output       = str_replace($tagToReplace, $value, $output);
        }

        return $output;
    }
}