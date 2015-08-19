<?php
/*
 * Code Owner: Saba*
 * Modified Date: 6/25/2015
 * Modified By: Phong Lam
 */

namespace CnitLoggerBundle\Lib;

use Gelf\Logger;
use Gelf\Publisher;
use Gelf\Transport\UdpTransport;

class CLogger extends Logger{
    /**
     * @var: string
     */
    protected $owner;

    /**
     * @return mixed
     */
    public function getOwner()
    {
        if(isset($_SERVER['USERNAME'])){
            $this -> owner = $_SERVER['USERNAME'];
        }
        if(empty($this -> owner)){
            if(isset($_SERVER['LOGNAME'])){
                $this -> owner = $_SERVER['LOGNAME'];
            }
        }
        return $this->owner;
    }

    /**
     * @var: string
     */
    protected $location;

    /**
     * @return mixed
     */
    public function getLocation()
    {
        if(empty($this -> location)){
            $objIP = $this -> getObjectIP();
            if(!empty($objIP) && is_object($objIP)){
                if(isset($objIP -> city)){
                    $this -> location = $objIP -> city;
                }
                if(isset($objIP -> region)){
                    $this -> location .= ", " . $objIP -> region;
                }
                if(isset($objIP -> country)){
                    $this -> location .= ", " . $objIP -> country;
                }
            }
        }
        return $this->location;
    }

    /**
     * @var: string
     */
    protected $timeZone;

    /**
     * @return mixed
     */
    public function getTimeZone()
    {
        $this -> timeZone = date_default_timezone_get();
        return $this->timeZone;
    }

    /**
     * @var: string
     */
    protected $server;

    /**
     * @return mixed
     */
    public function getServer()
    {
        $host = gethostbyname(gethostname());
        $this -> server = $host . "-" . gethostbyaddr($host);
        return $this->server;
    }

    /**
     * @param $facility: string
     */
    public function __constructor($facility, $host = "127.0.0.1", $port = 12021){
        $transport = new UdpTransport($host, $port);
        $publisher = new Publisher($transport);
        parent::__construct($publisher, $facility);
    }

    /**
     * @param mixed $level
     * @param mixed $rawMessage
     * @param array $context
     */
    public function log($level, $rawMessage, array $context = array())
    {
        $localContext = array(
            "Owner" => $this -> getOwner(),
            "Location" => $this -> getLocation(),
            "Server" => $this -> getServer(),
            "TimeZone" => $this -> getTimeZone()
        );
        //TODO: Change the auto generated stub
        parent::log($level, self::formatMessage($rawMessage), array_merge($context, $localContext));
    }

    protected function getObjectIP(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://ipinfo.io");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result);
    }

    private function formatMessage($message){
        $fMessage = "";
        if(!empty($message)){
            if(is_string($message)){
                $fMessage = $message;
            }elseif(is_array($message) || is_object($message)){
                $fMessage = json_encode($message);
            }else{
                //TODO: check other data types
            }
        }
        return $fMessage;
    }
}