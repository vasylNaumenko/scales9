<?php
namespace Game;

class Router
{
    private static $routes = [];

    /**
     * store route for further processing
     * @param $pattern
     * @param $callback
     */
    public static function route($pattern, $callback)
    {
        $pattern                = '/^'.str_replace('/', '\/', $pattern).'$/';
        self::$routes[$pattern] = $callback;
    }

    /**
     * executes callback at matched route
     * @param $url
     * @return mixed|null
     */
    public static function execute($url)
    {
        foreach (self::$routes as $pattern => $callback)
        {
            if (preg_match($pattern, $url, $params))
            {
                array_shift($params);

                $parts = explode("@", $callback);
                if ($parts && count($parts) == 2 && class_exists($parts[0]))
                {
                    $class = new $parts[0];
                    if (method_exists($class, $parts[1]))
                    {
                        return call_user_func_array([$class, $parts[1]], array_values($params));
                    }
                    echo "Method not found ".$parts[1];
                }
                else
                {
                    echo "Class not found ".$parts[0];
                }
            }
        }

        echo "404. Page is not found";

        return null;
    }
}