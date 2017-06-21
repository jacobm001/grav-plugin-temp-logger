<?php
namespace Grav\Plugin;

use \PDO;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class TempLoggerPlugin
 * @package Grav\Plugin
 */
class TempLoggerPlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        $this->init_db();

        $uri        = $this->grav['uri'];
        $path_new   = $this->config->get('plugins.temp-logger.path_new');
        $local_temp = $this->get_local_temp('Corvallis', 'OR');

        if( $uri->path() == $path_new ) {
            date_default_timezone_set('America/Los_Angeles');

            $f = fopen(DATA_DIR . "temper.csv" , "a");
            $str = date("m-d-y H:i") . "," . $_GET['temp'] . "\n";
            fwrite($f, $str);
            fclose($f);
        }
    }

    public function init_db()
    {
        $f_lnk = DATA_DIR . "/temper.db";

        if( !file_exists($f_lnk) ) {
            return $this->build_db($f_lnk);
        }

        try {
            $this->db = new PDO('sqlite:' . $f_lnk);
        } 
        catch(Exception $e) {
            $this->grav['debugger']->addMessage($e);
            die("Cannot deal with database");
        }
        
        return true;
    }

    public function build_db($f_lnk) 
    {
        try {
            $this->db = new PDO('sqlite:' . $f_lnk);
            $this->db->exec(file_get_contents(__DIR__ . "/build_db.sql"));
        } 
        catch(Exception $e) {
            $this->grav['debugger']->addMessage($e);

            die("Cannot build database");
        }

        return;
    }

    public function get_local_temp($city, $state)
    {
        
    }
}
