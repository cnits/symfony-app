<?php
/*
 * Code Owner: Cnit
 * Modified Date: 8/21/2015
 * Modified By: Phong Lam
 */
namespace CnitLoggerBundle\Service;
use CnitLoggerBundle\Lib\CUtility;
use CnitLoggerBundle\Lib\GrayLogger;

class GrayLogService {
    protected $facility;

    /**
     * @return mixed
     */
    public function getFacility()
    {
        return $this->facility;
    }

    /**
     * @param mixed $facility
     */
    public function setFacility($facility)
    {
        $this->facility = $facility;
    }

    protected $host;

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    protected $port;

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    private $gLogger;

    function __construct($facility){
        $container = CUtility::getContainer();
        if($container -> hasParameter("cnit.gl.host")){
            $this -> host = $container -> getParameter("cnit.gl.host");
        }
        if($container -> hasParameter("cnit.gl.port")){
            $this -> port = $container -> getParameter("cnit.gl.port");
        }
        $this -> setFacility($facility);

        $this -> gLogger = new GrayLogger($facility, $this -> getHost(), $this -> getPort());
    }

    public function sendMessage($message, $level, $context = array()){
        try {
            $this -> gLogger -> setFacility($this -> getFacility());
            $this -> gLogger -> log($level, $message, $context);
        } catch (\Exception $ex){
            throw new \Exception($ex -> getMessage(), $ex -> getCode());
        }
    }
}