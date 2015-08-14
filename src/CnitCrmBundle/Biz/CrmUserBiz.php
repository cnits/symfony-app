<?php
 /** 										*
 * Modified date: 11/05/2014				*
 * Modified by	: PHONG LAM 				*
 */ 

	namespace CnitCrmBundle\Biz;
	
	class CrmUserBiz extends CrmBaseBiz {
		
		public function getList($fromdate = null, $todate = null, $name = "", $page = 0, $count = 0, $datefieldname = "modifiedon") {
			$dataArray = array();
			try {
				$fields = array('systemuserid', 'fullname', 'lastname', 'middlename', 'firstname', 'jobtitle', 'title'
					, 'internalemailaddress', 'territoryid', 'accessmode', 'mobilephone'
				);
				$textSearch = array();
				if(!empty($name)){
					$textSearch = array('fullname', 'like', $name.'%');
				}
				$criteria = array(
					array(
						$textSearch,
						$this -> periodDateParameter($datefieldname, $fromdate, $todate)
					)
					/*'or' => array(
						 array('lastname', 'not-null'),
						 array('firstname', 'not-null'),
						 array('fullname', 'not-null')
					 ),
					 'and' => array(
						 array('internalemailaddress', 'not-null'),
						 array('nickname', 'not-null')
					 )*/
				);
				$orderFields = $datefieldname;
				$linkEntities = null/*array(
					array("account", "owninguser", "systemuserid", "inner", "account_owning_user", 
						array("name", "accountid", "accountnumber")
						, null
					)
				)*/;
				$filters = array(
					'page' => $page,
					'count' => $count,
					'criteria' => $criteria
				);
				$dataArray = $this -> dcrmGetDataList(
					$filters, $fields, $orderFields, $linkEntities
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
		
	}
?>