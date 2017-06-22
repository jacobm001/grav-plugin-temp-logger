<?php

namespace Grav\Plugin;
use \PDO;

class TemperTwigExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'TemperTwigExtension';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('getOfficeTemperature', [$this, 'getOfficeTemperature']),
            new \Twig_SimpleFunction('getLocationTemperature', [$this, 'getLocationTemperature'])
        ];
    }
    
    public function getOfficeTemperature()
    {  
        $db    = new PDO("sqlite:" . DATA_DIR . "/temper.db"); 
        $query = "select office_temp from temp where logged = (select max(logged) from temp);";
        $stmt  = $db->prepare($query);
        $stmt->execute();

        $ret = $stmt->fetch(PDO::FETCH_ASSOC);

        return $ret['office_temp'];
    }

    public function getLocationTemperature()
    {
        $db    = new PDO("sqlite:" . DATA_DIR . "/temper.db"); 
        $query = "select location_temp from temp where logged = (select max(logged) from temp);";
        $stmt  = $db->prepare($query);
        $stmt->execute();

        $ret = $stmt->fetch(PDO::FETCH_ASSOC);

        return $ret['location_temp'];
    }
}