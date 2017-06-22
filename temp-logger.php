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

        date_default_timezone_set('America/Los_Angeles');

        $uri        = $this->grav['uri'];
        $path_new   = $this->config->get('plugins.temp-logger.path_new');

        if( $uri->path() == $path_new ) {
            $local_temp   = $this->get_local_temp();
            $current_date = date("m-d-y H:i");

            $this->record_temp($_GET['temp'], $local_temp, $current_date);
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

    public function get_local_temp()
    {
        $str = 'http://api.wunderground.com/api/%s/conditions/q/%s/%s.json';
        
        $key   = $this->config->get('plugins.temp-logger.api_key');
        $state = $this->config->get('plugins.temp-logger.state');
        $city  = $this->config->get('plugins.temp-logger.city');
        
        $url   = sprintf($str, $key, $state, $city);
        
        $r = file_get_contents($url);
        $r = json_decode($r);

        return $r->current_observation->temp_f;
    }

    public function record_temp($measured, $local, $current_date)
    {
        $query = 'insert into temp(office_temp, local_temp, logged) values(?,?,?)';
        $stmt  = $this->db->prepare($query);

        $stmt->bindParam(1, $measured);
        $stmt->bindParam(2, $local);
        $stmt->bindParam(3, $current_date);

        $stmt->execute();
    }
}
