<?php

//db credentials are in php\Engine.php line 19

spl_autoload_register(
    function ($class_name)
    {
        $exploded = explode('\\', $class_name);
        require 'php/'.end($exploded).'.php';
    }
);

date_default_timezone_set('Europe/Berlin');

\Game\Router::route('/', "Game\App@getIndex");
\Game\Router::route('/get_data', "Game\App@getData");
\Game\Router::route('/get_data/(\w+)/(\w+)', "Game\App@getData");
\Game\Router::route('/tests', "Game\Test@index");
\Game\Router::execute($_SERVER['REQUEST_URI']);