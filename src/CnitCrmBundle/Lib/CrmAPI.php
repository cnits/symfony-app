<?php
 /** 										*
 * Modified date: 11/05/2014				*
 * Modified by	: PHONG LAM 				*
 */ 

namespace CnitCrmBundle\Lib;

use CnitCrmBundle\Lib\Utility\EntityUtils;
use CnitCrmBundle\Lib\Utility\OnlineFederationManager;
use \DOMDocument;
class CrmAPI {
	/*
	 const OFUsername = "";
	 const OFPassword = "";
	 const organizationServiceURL = "https://cnit.api.crm.dynamics.com/XRMServices/2011/Organization.svc";*/

	private static $OFUsername;
	private static $OFPassword;
	private static $organizationServiceURL;

	function __construct($OFUsername, $OFPassword, $organizationServiceURL) {
		self::$OFUsername = $OFUsername;
		self::$OFPassword = $OFPassword;
		self::$organizationServiceURL = $organizationServiceURL;
	}
	
	public function checkLoginCRM() {
		$OFManager = new OnlineFederationManager();
		$securityData = $OFManager -> authenticateWithOnlineFederation(self::$organizationServiceURL, self::$OFUsername, self::$OFPassword);
		if ($securityData != null && !empty($securityData)) {
			return true;
		} else {
			return false;
		}
	}

	private function getSecurityData() {
		$OFManager = new OnlineFederationManager();
		$securityData = $OFManager -> authenticateWithOnlineFederation(self::$organizationServiceURL, self::$OFUsername, self::$OFPassword);
		if ($securityData != null && !empty($securityData)) {
			return $securityData;
		} else {
			throw new \Exception("Your account and password can't authenticate via dynamic crm", 1);
		}
	}

	public function getRowsCount($entityName, $entityfield) {
		$CRMURL = self::$organizationServiceURL;
		$securityData = self::getSecurityData();
		$domainname = self::getCrmDomainName($CRMURL);
		$entityRequest = EntityUtils::getCRMSoapHeader($CRMURL, $securityData) . '
		  	<s:Body>
				  <Execute xmlns="http://schemas.microsoft.com/xrm/2011/Contracts/Services">
						<request i:type="b:RetrieveMultipleRequest" xmlns:b="http://schemas.microsoft.com/xrm/2011/Contracts" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
							<b:Parameters xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic">
								<b:KeyValuePairOfstringanyType>
									<c:key>Query</c:key>
									<c:value i:type="b:FetchExpression">
										<b:Query>&lt;fetch mapping="logical" aggregate="true" distinct="false" version="1.0"&gt;&#xD;
											&lt;entity name="' . $entityName . '"&gt;&#xD;
											&lt;attribute name="' . $entityfield . '" alias="entityRowsCount" aggregate="count" /&gt;&#xD;
											&lt;/entity&gt;&#xD;
											&lt;/fetch&gt;
										</b:Query>
									</c:value>
								</b:KeyValuePairOfstringanyType>
							</b:Parameters>
							<b:RequestId i:nil="true"/><b:RequestName>RetrieveMultiple</b:RequestName>
						</request>
				  </Execute>
		    </s:Body>
		</s:Envelope>';
		$response = OnlineFederationManager::GetSOAPResponse("/Organization.svc", $domainname, $CRMURL, $entityRequest);
		$entitysArray = array();
		if ($response != null && $response != "") {
			$responsedom = new DomDocument();
			$responsedom -> loadXML($response);
			$entities = $responsedom -> getElementsbyTagName("Entity");
			foreach ($entities as $entity) {
				$item = array();
				$kvptypes = $entity -> getElementsbyTagName("KeyValuePairOfstringanyType");
				foreach ($kvptypes as $kvp) {
					$key = $kvp -> getElementsbyTagName("key") -> item(0) -> textContent;
					$value = $kvp -> getElementsbyTagName("Value") -> item(0) -> textContent;
					if ($key == 'entityRowsCount') { $item['entityRowsCount'] = $value;
					}
				}
				$entitysArray[] = $item;
			}
		}
		return $entitysArray;
	}

	private function getCrmDomainName($url) {
		$domainname = substr($url, 8, -1);
		$pos = strpos($domainname, "/");
		$domainname = substr($domainname, 0, $pos);
		return $domainname;
	}

	private function getValueDOMNode($tagItem, $attribute = '', $filter = "Id") {
		$value = "";
		if (isset($tagItem)) {
			if ($attribute == '') {
				$value = $tagItem -> textContent;
			} else {
				switch ($tagItem->getAttribute('i:'.$attribute)) {
					case 'b:EntityReference' :
						$value = $tagItem -> getElementsbyTagName($filter) -> item(0) -> textContent;
						break;
					case 'b:AliasedValue' :
						$value = $tagItem -> getElementsbyTagName('Value') -> item(0) -> textContent;
						break;
					default :
						$value = $tagItem -> textContent;
						break;
				}
			}
		}
		return $value;
	}

	private function getEntityCollectionFromSOAPResponse($response) {
		$responsedom = new \DomDocument();
		$responsedom -> loadXML($response);
		$entities = $responsedom -> getElementsbyTagName("Entity");
		$entityArray = array();
		foreach ($entities as $entity) {
			$entityObject = array();
			$kvptypes = $entity -> getElementsbyTagName("KeyValuePairOfstringanyType");
			foreach ($kvptypes as $kvp) {
				$key = $kvp -> getElementsbyTagName("key") -> item(0) -> textContent;
				$valueTagItem = $kvp -> getElementsbyTagName("value") -> item(0);
				$entityObject[$key] = self::getValueDOMNode($valueTagItem, 'type', 'Name');
			}
			$kvpformats = $entity -> getElementsbyTagName("KeyValuePairOfstringstring");
			foreach ($kvpformats as $fkvp) {
				$fkey = $fkvp -> getElementsbyTagName("key") -> item(0) -> textContent;
				$fvalueTagItem = $fkvp -> getElementsbyTagName("value") -> item(0);
				if ($fkey != 'revenue') {
					$entityObject[$fkey] = $fvalueTagItem -> textContent;
				}
			}
			$entityArray[] = $entityObject;
		}
		return $entityArray;
	}
	private function getErrorMessageFromSOAPResponse($response) {
		$msgText = '';
		$responsedom = new \DomDocument();
		$responsedom -> loadXML($response);
		$faults = $responsedom -> getElementsbyTagName("OrganizationServiceFault");
		if($faults -> length > 0){
			foreach ($faults as $fault) {
				$msgText = $msgText . ' - ' . $fault -> getElementsbyTagName("Message") -> item(0) -> textContent;
			}
		}else{
			$faults = $responsedom -> getElementsbyTagName("Reason");
			if($faults -> length > 0){
				foreach ($faults as $fault) {
					$msgText = $msgText . ' - ' . $fault -> getElementsbyTagName("Text") -> item(0) -> nodeValue;
				}
			}
		}
		return $msgText;
	}

	private function setupLinkEntity($entityname, $fromfieldname, $tofieldname, $linktype, $alias, $attributes = null, $filters = null, $visible = "false", $intersect = "true", $childlinkentity = "") {
		$linkEntityBuilder = '';
		$beginTag = '&lt;link-entity name="' . $entityname . '" 
				from="' . $fromfieldname . '" to="' . $tofieldname . '" 
				link-type="' . $linktype . '" alias="' . $alias . '" 
				visible="' . $visible . '" intersect="' . $intersect . '"&gt;&#xD;';

		$attributeTag = '';
		if ($attributes != null && is_array($attributes)) {
			foreach ($attributes as $key => $value) {
				$attributeTag = $attributeTag . '&lt;attribute name="' . $value . '" /&gt;&#xD;';
			}
		}

		$filterTag = '';
		if (is_array($filters) && count($filters) > 0) {
			$btFilter = '&lt;filter type="and"&gt;&#xD;';
			$condition = '';
			foreach ($filters as $key => $value) {
				if ($key == 'and' || $key == 'or') {
					$condition .= $this -> setupSimpleFilter($key, $value);
				} else {
					$condition .= $this -> setupSimpleFilter("", $value);
				}
			}
			$etFilter = '&lt;/filter&gt;&#xD;';
			$filterTag = $btFilter . $condition . $etFilter;
		} else {
			$filterTag = '';
		}

		$endTag = '&lt;/link-entity&gt;&#xD;';

		$linkEntityBuilder = $beginTag . $attributeTag . $filterTag . $childlinkentity . $endTag;
		return $linkEntityBuilder;
	}

	private function setupSimpleFilter($type, $conditions) {
		$filerBuilder = '';
		$beginTag = '&lt;filter type="' . $type . '"&gt;';
		$conditionTag = '';
		if (is_array($conditions) && count($conditions) > 0) {
			foreach ($conditions as $key => $value) {
				if (isset($value[2])) {
					if (is_array($value[2])) {
						$mcon = '&lt;condition attribute="' . $value[0] . '" operator="' . $value[1] . '"&gt;&#xD;';
						foreach ($value[2] as $key1 => $value1) {
							$mcon = $mcon . '&lt;value&gt;' . $value1 . '&lt;/value&gt;&#xD;';
						}
						$mcon = $mcon . '&lt;/condition&gt;';
						$conditionTag = $conditionTag . $mcon;
					} else {
						$conditionTag = $conditionTag . '&lt;condition attribute="' . $value[0] . '" operator="' . $value[1] . '" value="' . $value[2] . '" /&gt;&#xD;';
					}
				} else {
					if(count($value) > 0){
						$conditionTag = $conditionTag . '&lt;condition attribute="' . $value[0] . '" operator="' . $value[1] . '"/&gt;&#xD;';
					}
				}
			}
		} else {
			return $filerBuilder;
		}
		$endTag = '&lt;/filter&gt;';
		if ($type !== "") {
			$filerBuilder = $beginTag . $conditionTag . $endTag;
		} else {
			$filerBuilder = $conditionTag;
		}
		return $filerBuilder;
	}

	private function setupDataStructureForSubmit($data = array()){
		$dataString = '';
		foreach ($data as $key => $value) {
			if(isset($value["property"]) && isset($value["value"]) && $value["property"] != ""){
				if(isset($value["type"]) && !empty($value["type"])){
					if(strtolower($value["type"]) == 'lookup'){
						$dataString = $dataString.
							'<b:KeyValuePairOfstringanyType>
								<c:key>'. $value["property"] .'</c:key>
								<c:value i:type="b:EntityReference">
									<b:Id>'. $value["value"] .'</b:Id>
									<b:LogicalName>'. $value["entity"] .'</b:LogicalName>
								</c:value>
							</b:KeyValuePairOfstringanyType>';
					}elseif(strtolower($value["type"]) == 'option'){
						$dataString = $dataString.
							'<b:KeyValuePairOfstringanyType>
								<c:key>'. $value["property"] .'</c:key>
								<c:value i:type="b:OptionSetValue">
									<b:Value>'. $value["value"] .'</b:Value>
								</c:value>
							</b:KeyValuePairOfstringanyType>';
					}else{
						$dataString = $dataString.
							'<b:KeyValuePairOfstringanyType>
								<c:key>'. $value["property"] .'</c:key>
								<c:value i:type="d:'. $value["type"] .'" xmlns:d="http://www.w3.org/2001/XMLSchema">'. $value["value"] .'</c:value>
							</b:KeyValuePairOfstringanyType>';
					}
				}else{
					$dataString = $dataString.
						'<b:KeyValuePairOfstringanyType>
							<c:key>'. $value["property"] .'</c:key>
							<c:value i:type="d:string" xmlns:d="http://www.w3.org/2001/XMLSchema">'. $value["value"] .'</c:value>
						</b:KeyValuePairOfstringanyType>';
				}
			}
		}
		return $dataString;
	}

	public function dcrmGetDataList($entityName, $fields = null, $criteria = null, $orderFields = null, $linkEntities = null, $page = 0, $count = 0) {
		$arrayData = array();
		try {
			if (!is_string($entityName) || is_null($entityName) || $entityName == "") {
				throw new \Exception("Error: Entity is invalid or an empty instance!", 1);
			} else {
				$securityData = $this -> getSecurityData();
				if ($securityData != null && isset($securityData)) {
					$domainname = $this -> getCrmDomainName(self::$organizationServiceURL);
					$strRequest = '';
					$soapHeader = EntityUtils::getCRMSoapHeader(self::$organizationServiceURL, $securityData) . '<s:Body>
					            <Execute xmlns="http://schemas.microsoft.com/xrm/2011/Contracts/Services">
					                <request i:type="b:RetrieveMultipleRequest" xmlns:b="http://schemas.microsoft.com/xrm/2011/Contracts" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
					                    <b:Parameters xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic">
					                        <b:KeyValuePairOfstringanyType>
					                            <c:key>Query</c:key>
					                            <c:value i:type="b:FetchExpression">';

					$btFetchQuery = '<b:Query>&lt;fetch mapping="logical" output-format="xml-platform" version="1.0"&gt;&#xD;';
					if ($page != 0 && $count != 0) {
						$btFetchQuery = '<b:Query>&lt;fetch mapping="logical" output-format="xml-platform" page="' . $page . '" count="' . $count . '" version="1.0"&gt;&#xD;';
					}
					$btEntity = '&lt;entity name="' . $entityName . '"&gt;&#xD;';
					$selectedAttributes = '';
					if (is_string($fields) && $fields != "") {
						$selectedAttributes = '&lt;attribute name="' . $fields . '" /&gt;&#xD;';
					} else {
						if (is_array($fields) && count($fields) > 0) {
							foreach ($fields as $key => $value) {
								if (is_string($value) && $value != "") {
									$selectedAttributes .= '&lt;attribute name="' . $value . '" /&gt;&#xD;';
								}
							}
						} else {
							$selectedAttributes = '&lt;all-attributes /&gt;&#xD;';
						}
					}
					$strSorting = '';
					if (is_string($orderFields) && $orderFields != "") {
						$strSorting = '&lt;order attribute="' . $orderFields . '" descending="true" /&gt;&#xD;';
					} else {
						if (is_array($orderFields) && count($orderFields) > 0) {
							foreach ($orderFields as $key => $value) {
								if (is_string($value) && $value != "") {
									$strSorting .= '&lt;order attribute="' . $value . '" descending="true" /&gt;&#xD;';
								} else {
									if (isset($value[0]) && isset($value[1])) {
										if (is_string($value[0]) && $value[0] != "" && is_bool($value[1])) {
											$strSorting .= '&lt;order attribute="' . $value[0] . '" descending="' . $value[1] . '" /&gt;&#xD;';
										} else {
											$strSorting .= '';
										}
									} else {
										$strSorting .= '';
									}
								}
							}
						} else {
							$strSorting = '';
						}
					}
					$strFilter = '';
					if (is_array($criteria) && count($criteria) > 0) {
						$btFilter = '&lt;filter type="and"&gt;&#xD;';
						$condition = '';
						foreach ($criteria as $key => $value) {
							if ($key === 'and' || $key === 'or') {
								$condition .= $this -> setupSimpleFilter($key, $value);
							} else {
								$condition .= $this -> setupSimpleFilter("", $value);
							}
						}
						$etFilter = '&lt;/filter&gt;&#xD;';
						$strFilter = $btFilter . $condition . $etFilter;
					} else {
						$strFilter = '';
					}
					$strLinkEntity = '';
					if (is_array($linkEntities) && count($linkEntities) > 0) {
						foreach ($linkEntities as $key => $value) {
							$strLinkEntity .= $this -> setupLinkEntity($value[0], $value[1], $value[2], $value[3], $value[4], $value[5]);
						}
					} else {
						$strLinkEntity = '';
					}
					$etEntity = '&lt;/entity&gt;&#xD;';
					$etFetchQuery = '&lt;/fetch&gt;&#xD;</b:Query>';
					$soapFooter = '</c:value>
			                        </b:KeyValuePairOfstringanyType>
			                    </b:Parameters>
			                    <b:RequestId i:nil="true"/><b:RequestName>RetrieveMultiple</b:RequestName>
			                </request>
			            </Execute>
			            </s:Body>
			        </s:Envelope>';
					$strRequest = $soapHeader . $btFetchQuery . $btEntity . $selectedAttributes . $strSorting . $strFilter . $strLinkEntity . $etEntity . $etFetchQuery . $soapFooter;
					$response = OnlineFederationManager::GetSOAPResponse("/Organization.svc", $domainname, self::$organizationServiceURL, $strRequest);
					$msgText = $this -> getErrorMessageFromSOAPResponse($response);
					if($msgText != "") {
						throw new \Exception($msgText, 1);
					} else {
						$arrayData = self::getEntityCollectionFromSOAPResponse($response);
					}
				}
			}
		} catch(\Exception $ex) {
			echo 'Caught exception: ', $ex -> getMessage(), "\n";
		}
		return $arrayData;
	}

	public function dcrmCreateData($entityName, $data) {
		$createResult = "";
		try{
			if(!is_string($entityName) || $entityName == "" || $data == null || !is_array($data) || count($data) <= 0){
				throw new \Exception("Entity name or data for submitting is invalid.", 1);
			}else{
				$securityData = $this -> getSecurityData();
				if ($securityData != null) {
					$domainname = $this -> getCrmDomainName(self::$organizationServiceURL);
					$dataString = $this -> setupDataStructureForSubmit($data);
					if(empty($dataString)){
						throw new \Exception("Data is empty", 1);
					}
					$entityRequest = EntityUtils::getCreateCRMSoapHeader(self::$organizationServiceURL, $securityData) . '
		              <s:Body>
		                    <Create xmlns="http://schemas.microsoft.com/xrm/2011/Contracts/Services">
		                    <entity xmlns:b="http://schemas.microsoft.com/xrm/2011/Contracts" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
		                        <b:Attributes xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic">
		                            '. $dataString .'
		                        </b:Attributes>
		                        <b:EntityState i:nil="true"/>
		                        <b:FormattedValues xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic"/>
		                        <b:Id>00000000-0000-0000-0000-000000000000</b:Id>
		                        <b:LogicalName>'. $entityName .'</b:LogicalName>
		                        <b:RelatedEntities xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic"/>
		                    </entity>
		                    </Create>
		                </s:Body>
		            </s:Envelope>
					';
					$response = OnlineFederationManager::GetSOAPResponse("/Organization.svc", $domainname, self::$organizationServiceURL, $entityRequest);
					$msgText = $this -> getErrorMessageFromSOAPResponse($response);
					if($msgText != "") {
						throw new \Exception($msgText, 1);
					} else {
						preg_match('/<CreateResult>(.*)<\/CreateResult>/', $response, $matches);
						$createResult = $matches[1];
					}
				}
			}
		}catch(\Exception $ex){
			echo 'Caught exception: ', $ex -> getMessage(), "\n";
		}
		return $createResult;
	}

	public function dcrmUpdateData($entityId, $entityName, $data) {
		$response = "";
		try{
			if ($entityId == null || $entityId == "" || $data == null || !is_array($data) || count($data) <= 0
				|| $entityName == null || $entityName == "" || !is_string($entityId) || !is_string($entityName)) {
				throw new \Exception("Entity name or id for submitting is invalid.", 1);
			} else {
				$securityData = $this -> getSecurityData();
				if($securityData != null){
					$domainname = $this -> getCrmDomainName(self::$organizationServiceURL);
					$dataString = $this -> setupDataStructureForSubmit($data);
					if(empty($dataString)){
						throw new \Exception("Data is empty", 1);
					}
					$entityRequest = EntityUtils::getUpdateCRMSoapHeader(self::$organizationServiceURL, $securityData).
					'<s:Body><Update xmlns="http://schemas.microsoft.com/xrm/2011/Contracts/Services">
			                <entity xmlns:b="http://schemas.microsoft.com/xrm/2011/Contracts" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
			                    <b:Attributes xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic">
			                        '. $dataString .'
			                    </b:Attributes>
			                    <b:EntityState i:nil="true"/>
			                    <b:FormattedValues xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic"/>
			                    <b:Id>' . $entityId . '</b:Id>
			                    <b:LogicalName>'. $entityName .'</b:LogicalName>
			                    <b:RelatedEntities xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic"/>
			                </entity></Update>
			            </s:Body>
			        </s:Envelope>';
					$response = OnlineFederationManager::GetSOAPResponse("/Organization.svc", $domainname, self::$organizationServiceURL, $entityRequest);
					$msgText = $this -> getErrorMessageFromSOAPResponse($response);
					if($msgText != "") {
						throw new \Exception($msgText, 1);
					} else {
						
					}
				}
			}
		}catch(\Exception $ex){
			Logger::log(Logger::LOG_ERROR, 'Submit data to CRM server', 1, "Some errors for saving: "
								.$ex -> getMessage()." Line number ".__LINE__.".", null);
			echo "Caught Message: ", $ex -> getMessage(), "\n";
		}
		return $response;
	}

	public function dcrmDeleteData($entityId, $entityName) {
		$response = "";
		try{
			if ($entityId == null || $entityId == "" || $entityName == null 
				|| $entityName == "" || !is_string($entityId) || !is_string($entityName)) {
				throw new \Exception("Entity name or id for submitting is invalid.", 1);
			} else {
				$securityData = $this -> getSecurityData();
				if($securityData != null){
					$domainname = $this -> getCrmDomainName(self::$organizationServiceURL);
					$entityRequest = EntityUtils::getDeleteCRMSoapHeader(self::$organizationServiceURL, $securityData).
					'<s:Body>
			                <Delete xmlns="http://schemas.microsoft.com/xrm/2011/Contracts/Services">
			                    <entityName>'. $entityName .'</entityName>
			                    <id>' . $entityId . '</id>
			                </Delete>
			            </s:Body>
			        </s:Envelope>';
					$response = OnlineFederationManager::GetSOAPResponse("/Organization.svc", $domainname, self::$organizationServiceURL, $entityRequest);
					$msgText = $this -> getErrorMessageFromSOAPResponse($response);
					if($msgText != "") {
						throw new \Exception($msgText, 1);
					} else {
						
					}
				}
			}
		}catch(\Exception $ex){
			echo "Caught Message: ", $ex -> getMessage(), "\n";
		}
		return $response;
	}
}
?>
