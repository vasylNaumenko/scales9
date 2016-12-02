<?php
namespace Game;

class App
{
    protected $engine;

    /**
     * App constructor.
     */
    public function __construct()
    {
        $this->engine = new Engine();
    }

    /**
     * show the main page
     */
    public function getIndex()
    {
        $view = new View();

        ob_start();
        echo $view->render('main');
        ob_end_flush();
    }

    public function getData($command = null, $id = null)
    {
        $data = [];
        if (!$command)
        {
            $data = [
                'init'    => $this->engine->getInitialData(),
                'replays' => $this->engine->getReplays(),
            ];
        }
        else
        {
            switch ($command)
            {
                case 'selected':
                    $data           = $this->engine->calculate($id);
                    $data['replay'] = $this->engine->saveGame($id, $data);

                    break;

                case 'load':
                    $data = $this->engine->loadSavedGame($id);
                    break;
            }
        }

        echo json_encode($data);
    }
}