<?php
 /** 										*
 * Modified date: 11/05/2014				*
 * Modified by	: PHONG LAM 				*
 */ 

	namespace CnitCrmBundle\Biz;
	use CnitCrmBundle\Lib\CrmAPI;
	
	class CrmBaseBiz {
		
		private $crmAPI;
		private $entity = "";
		function __construct($entity) {
			$dbm = MongoDBManager::getDBManager();
			$config = $dbm -> findOne(EnumUtility::enumMongoDBCollection() -> API_CONFIG, 'Key', "Crm");
			if($config != null && isset($config['Value'])){
				$url = (isset($config['Value']['Url']) ? $config['Value']['Url']: "");
				$acc = (isset($config['Value']['Account']) ? $config['Value']['Account']: "");
				$pas = (isset($config['Value']['Password']) ? $config['Value']['Password']: "");
				
				if (isset($config['Value']['Tables']) && $config['Value']['Tables'] != NULL) {
					foreach ($config['Value']['Tables'] as $key => $value) {
						if ($value['Name'] == $entity) {
							$this -> entity = $value['Suffix'];
							break;
						}
					}
				}
				$this -> crmAPI = new CrmAPI($acc, $pas, $url);
			}else{
				$this -> crmAPI = null;
			}
		}
		
		public function dcrmGetDataList($filters = array(), $fields = array(), $orderFields = "", $linkEntities = null) {
			$dataArray = array();
			try {
				$criteria = array();
				$page = 0;
				$count = 500;
				if (isset($filters['page'])) {
					$page = $filters['page'];
				}
				if (isset($filters['count'])) {
					$count = $filters['count'];
				}
				if (isset($filters['criteria'])) {
					$criteria = $filters['criteria'];
				}
				$dataArray = $this -> crmAPI -> dcrmGetDataList(
					$this ->entity, $fields, $criteria, $orderFields, $linkEntities, $page, $count
				);
			} catch(\Exception $ex) {
				echo 'Caught exception: ', $ex -> getMessage(), "\n";
			}
			return $dataArray;
		}
		public function dcrmCreateData($data = array()){
			try{
				return $this -> crmAPI -> dcrmCreateData($this -> entity, $data);
			}catch(\Exception $ex){
				echo 'Caught exception: ', $ex -> getMessage(), "\n";
			}
		}
		public function dcrmUpdateData($entityId, $data = array()){
			try{
				return $this -> crmAPI -> dcrmUpdateData($entityId, $this -> entity, $data);
			}catch(\Exception $ex){
				echo 'Caught exception: ', $ex -> getMessage(), "\n";
			}
		}
		public function dcrmDeleteData($entityId){
			try{
				return $this -> crmAPI -> dcrmDeleteData($entityId, $this -> entity);
			}catch(\Exception $ex){
				echo 'Caught exception: ', $ex -> getMessage(), "\n";
			}
		}
		public function periodDateParameter($datefieldname = "modifiedon",$fromdate = null, $todate){
			$result = array();
			if(strtotime($fromdate) > 0 && strtotime($todate) > 0){
				$f = $fromdate;
				$t = $todate;
				if(strtotime($fromdate) > strtotime($todate)){
					$f = $todate;
					$t = $fromdate;
				}
				$result = array($datefieldname, 'between', array(
					$f, $t
				));
			}
			return $result;
		}
	}
?>