<?php
 /** 										*
 * Modified date: 11/05/2014				*
 * Modified by	: PHONG LAM 				*
 */ 
	namespace CnitCrmBundle\Biz;
	
	class CrmAccountBiz extends CrmBaseBiz {
		
		public function getList($fromdate = null, $todate = null, $name = "", $page = 0, $count = 0, $datefieldname = "modifiedon") {
			$dataArray = array();
			try {
				$fields = array('accountid', 'name', 'accountcategorycode', 'accountnumber');
				$textSearch = array();
				if(!empty($name)){
					$textSearch = array('name', 'like', $name.'%');
				}
				$criteria = array(
					array(
						array('statecode', 'eq', 0),
						array('statuscode', 'eq', 1),
						$textSearch,
						$this -> periodDateParameter($datefieldname, $fromdate, $todate)
					)
				);
				$orderFields = $datefieldname;
				$linkEntities = null;
				$filters = array(
					'page' => $page,
					'count' => $count,
					'criteria' => $criteria
				);
				$dataArray = $this -> dcrmGetDataList(
					$filters, $fields, $criteria, $orderFields, $linkEntities
				);
			} catch(\Exception $ex) {
				echo 'Caught exception: ', $ex -> getMessage(), "\n";
			}
			return $dataArray;
		}
		public function createData($data = array()){
			return $this -> dcrmCreateData($data);
		}
		public function updateData($entityId, $data = array()){
			return $this -> dcrmUpdateData($entityId, $data);
		}
		public function deleteData($entityId){
			return $this -> dcrmDeleteData($entityId);
		}
		public function updateAccountScore($entityId, $accountscore){
			$data = array(
				array('property' => 'saba_accountscore', 'value' => $accountscore)
			);
			return $this -> updateData($entityId, $data);
		}
	}
?>