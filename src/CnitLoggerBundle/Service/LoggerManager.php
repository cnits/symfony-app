<?php
/*
 * Code Owner: Cnit
 * Modified Date: 8/21/2015
 * Modified By: Phong Lam
 */
namespace CnitLoggerBundle\Service;
use CnitLoggerBundle\Lib\CUtility;

class LoggerManager {
    public static function executeGrayLogService($level, $message, $context = array(), $facility = ""){
        try{
            $container = CUtility::getContainer();
            if(!is_null($container)){
                if(!empty($facility)){
                    $container -> get("cnit_logger.gl") -> setFacility($facility);
                }
                $container -> get("cnit_logger.gl") -> sendMessage($message, $level, $context);
            }else{
                throw new \Exception("Container is null. System raises critical error!");
            }
        }catch (\Exception $ex){
            CUtility::getException($ex, "GrayLog2");
        }
    }
}