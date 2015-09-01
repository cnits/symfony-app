<?php
/*
 * Code Owner: Cnit*
 * Modified Date: 8/21/2015
 * Modified By: Phong Lam
 */

namespace CnitLoggerBundle\Lib;


final class CUtility {
    public static function getContainer(){
        $container = null;
        $kernel = $GLOBALS['kernel'];
        if($kernel instanceof \AppCache){
            $kernel = $kernel -> getKernel();
        }
        if($kernel instanceof \AppKernel){
            $container = $kernel -> getContainer();
        }
        return $container;
    }

    public static function getException($ex, $logType = "LogType"){
        $mLogger = new \Monolog\Logger($logType);
        $mLogger -> pushHandler(new \Monolog\Handler\StreamHandler("app/logs/".$logType.".log", \Monolog\Logger::ERROR));
        if($ex instanceof \Exception){
            $mLogger -> addError(json_encode($ex));
        }else{
            $mLogger -> addError($ex);
        }
    }
}