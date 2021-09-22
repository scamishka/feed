<?php
/* Created by cetacs on 16.10.2020 */

class DoEcho
{
    private static $instance = null;

    /**
     * @var float|string
     */
    private $start;

    /**
     *
     * @return DoEcho
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function __construct()
    {
        $this->start = microtime(true);
    }

    public function do_echo($msg)
    {
        $end = microtime(true);
        $time = number_format(($end - $this->start), 2);
        fwrite(STDOUT, "{$time}:\t{$msg}");
    }
}

function do_echo($msg)
{
    DoEcho::getInstance()->do_echo($msg);
}

function get_array_default($arr, $id, $def)
{
    if (array_key_exists($id, $arr)) {
        return empty($arr[$id]) ? $def : $arr[$id];
    }

    return $def;
}