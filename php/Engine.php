<?php
/**
 * Created by PhpStorm.
 * User: n1
 * Date: 20.10.2016
 * Time: 18:54
 */

namespace Game;

class Engine
{
    public    $scales;
    protected $db_credentials;
    public    $db;

    public function __construct()
    {
        $this->db_credentials = [
            'host'     => 'localhost',
            'db_name'  => 'game',
            'username' => 'root',
            'password' => '',
        ];

        $this->db     = new Db($this->db_credentials);
        $this->scales = [];
    }


    /**
     * returns list of balls with initial weight
     * @param int $selected
     * @return array
     */
    public function getInitialData($selected = 0)
    {
        $data = [];
        for ($i = 1; $i <= 9; $i++)
        {
            $data[$i] = $selected == $i ? 1 : 0;
        }

        return $data;
    }

    public function calculate($selected)
    {
        if ($selected < 1 || $selected > 9)
        {
            $selected = 1;
        }

        $items = $this->getInitialData($selected);
        $this->shuffle_assoc($items);

        $scale_one = $this->getHeaviestGroup($items, 3);
        $items     = $scale_one['heaviest'];

        $scale_two = $this->getHeaviestGroup($items, 1);
        $items     = $scale_two['heaviest'];

        $result = 0;
        foreach ($items as $id => $weight)
        {
            $result = $id;
        }

        $save_data = [
            'selected'  => $selected,
            'scale_one' => $scale_one,
            'scale_two' => $scale_two,
            'result'    => $result,
        ];

        return $save_data;
    }

    /**
     * get the heaviest group
     * @param $items
     * @param $num_in_group
     * @return mixed
     */
    public function getHeaviestGroup($items, $num_in_group)
    {
        $this->clearScales();

        $groups = [
            'group_1' => [
                'items' => $this->putOnScales($items, $num_in_group),
            ],
            'group_2' => [
                'items' => $this->putOnScales($items, $num_in_group),
            ],
            'group_3' => [
                'items' => $this->putOnScales($items, $num_in_group),
            ],
        ];

        $this->weightGroup($groups);

        if ($groups['group_1']['weight'] > $groups['group_2']['weight'])
        {
            $heaviest_group   = $groups['group_1']['items'];
            $groups['result'] = 'Left scale is heavier';
        }
        elseif ($groups['group_2']['weight'] > $groups['group_1']['weight'])
        {
            $heaviest_group   = $groups['group_2']['items'];
            $groups['result'] = 'Right scale is heavier';
        }
        else
        {
            $heaviest_group   = $groups['group_3']['items'];
            $groups['result'] = 'Scale balance. Taking unweighted ball(s)';
        }

        return ['groups' => $groups, 'heaviest' => $heaviest_group];
    }

    public function weightGroup(&$step)
    {
        foreach ($step as &$group)
        {
            $group['weight'] = $this->getWeight($group['items']);
        }
    }

    public function getWeight($group)
    {
        $total = 0;
        foreach ($group as $weight)
        {
            $total += $weight;
        }

        return $total;
    }

    public function putOnScales($items, $count)
    {
        $group = [];
        foreach ($items as $item => $weight)
        {
            if (!isset($group[$item]) && !isset($this->scales[$item]))
            {
                $group[$item]        = $weight;
                $this->scales[$item] = $weight;
                if (!--$count)
                {
                    break;
                }
            }
        }

        return $group;
    }

    /**
     * load saved game by game_id
     * @param $id
     * @return array|null
     */
    public function loadSavedGame($id)
    {
        $params = [
            ':game_id' => (int)$id,
        ];

        $sql = "
          SELECT game_data
          FROM data 
          WHERE game_id = :game_id
        ";

        $result = $this->db->exec($sql, $params);

        if ($result)
        {
            return json_decode($result[0]['game_data'], true);
        }

        return $result;
    }

    /**
     * returns last 20 replays.
     * all older replays will be removed
     * @return array
     */
    public function getReplays()
    {
        $sql = "
          SELECT *
          FROM games 
          ORDER BY id DESC LIMIT 20
        ";

        $data = $this->db->exec($sql);

        //remove all previous replays
        if ($data && count($data) == 20)
        {
            $last = end($data);
            $sql  = "
              DELETE 
              FROM games 
              WHERE id < {$last['id']}
            ";
            $this->db->exec($sql);
        }

        return $data;
    }

    public function saveGame($id, $data)
    {
        //store game
        $time      = date('d/m/y H:i:s');
        $game_name = "Ball {$id} at {$time}";

        $sql = "INSERT INTO games SET name='{$game_name}'";

        $this->db->exec($sql);

        $id = $this->db->lastId;

        //store data
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $sql  = "INSERT INTO data (game_id, game_data) VALUES ($id, '{$data}')";
        $this->db->exec($sql);

        return ['id' => $id, 'name' => $game_name];
    }

    protected function shuffle_assoc(&$array)
    {
        $keys = array_keys($array);
        $new  = [];
        shuffle($keys);

        foreach ($keys as $key)
        {
            $new[$key] = $array[$key];
        }

        $array = $new;

        return true;
    }

    public function clearScales()
    {
        $this->scales = [];
    }
}