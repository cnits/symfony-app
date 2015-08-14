<?php
 /** 										*
 * Modified date: 11/05/2014				*
 * Modified by	: PHONG LAM 				*
 */ 

	namespace CnitLdapBundle\Lib;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\HttpFoundation\Request;
	
	class LdapManager{
		protected $ldap;
		protected $ldapRoleEntity;
		protected $ldapUserEntity;
		function __construct()
		{
			$this->ldap = new adLdap();
			$this->ldapRoleEntity = "LdapRole";
			$this->ldapUserEntity = "LdapUser";
		}
		public function authenticate($username, $password, $preventRebind = false)
		{
			return $this->ldap->authenticate($username, $password, $preventRebind);
		}
		public function formatUserInfoStructure($ldapUser)
		{
			$userinfo = array();
			if(count($ldapUser) > 0)
			{
				foreach ($ldapUser as $key => $value) 
				{
					switch ($key) {
						case 'displayname':
							$userinfo['DisplayName'] = utf8_encode($value[0]);
							break;
						case 'memberof':
							$userinfo['MemberOf'] = array();
							if ($value['count'] > 0) {
								$memofarray = array();
								for ($i=0; $i < $value['count']; $i++) { 
									$memofarray[$i] = utf8_encode($value[$i]);
								}
								$userinfo['MemberOf'] = $memofarray;
							}
							break;
						case 'primarygroupid':
							$userinfo['PrimaryGroupId'] = utf8_encode($value[0]);
							break;
						case 'objectsid':
							$userinfo['ObjectsId'] = utf8_encode($value[0]);
							break;
						case 'samaccountname':
							$userinfo['SAMAccountName'] = utf8_encode($value[0]);
							break;
						case 'mail':
							$userinfo['Mail'] = utf8_encode($value[0]);
							break;
						case 'dn':
							$userinfo['DN'] = utf8_encode($value);
							break;
						case 'userprincipalname':
							$userinfo['LogonName'] = utf8_encode($value[0]);
							break;	
						case 'unicodepwd':
							$userinfo['UnicodePassword'] = utf8_encode($value[0]);
							break;	
						case 'givenname':
							$userinfo['GivenName'] = utf8_encode($value[0]);
							break;	
						case 'sn':
							$userinfo['SurName'] = utf8_encode($value[0]);
							break;	
						case 'title':
							$userinfo['Title'] = utf8_encode($value[0]);
							break;
						case 'telephonenumber':
							$userinfo['TelephoneNumber'] = utf8_encode($value[0]);
							break;
						case 'mobile':
							$userinfo['Mobile'] = utf8_encode($value[0]);
							break;
						case 'ipphone':
							$userinfo['IPPhone'] = utf8_encode($value[0]);
							break;
						case 'homephone':
							$userinfo['HomePhone'] = utf8_encode($value[0]);
							break;
						case 'l':
							$userinfo['City'] = utf8_encode($value[0]);
							break;
						case 'postalcode':
							$userinfo['PostalCode'] = utf8_encode($value[0]);
							break;
						case 'c':
							$userinfo['Country'] = utf8_encode($value[0]);
							break;
						case 'postofficebox':
							$userinfo['PostOfficeBox'] = utf8_encode($value[0]);
							break;
						case 'st':
							$userinfo['StateAddress'] = utf8_encode($value[0]);
							break;
						case 'streetaddress':
							$userinfo['StreetAddress'] = utf8_encode($value[0]);
							break;
						case 'company':
							$userinfo['Company'] = utf8_encode($value[0]);
							break;
						case 'department':
							$userinfo['Department'] = utf8_encode($value[0]);
							break;
						case 'accountexpires':
							$userinfo['AccountExpires'] = utf8_encode($value[0]);
							break;
						case 'description':
							$userinfo['Description'] = utf8_encode($value[0]);
							break;
						case 'homedirectory':
							$userinfo['HomeDirectory'] = utf8_encode($value[0]);
							break;
						case 'homedrive':
							$userinfo['HomeDrive'] = utf8_encode($value[0]);
							break;
						case 'initials':
							$userinfo['Initials'] = utf8_encode($value[0]);
							break;
						case 'manager':
							$userinfo['Manager'] = utf8_encode($value[0]);
							break;
						case 'physicaldeliveryofficename':
							$userinfo['Office'] = utf8_encode($value[0]);
							break;
						case 'useraccountcontrol':
							$userinfo['Enabled'] = utf8_encode($value[0]);
							break;
						case 'facsimiletelephonenumber':
							$userinfo['Fax'] = utf8_encode($value[0]);
							break;
						case 'dlmemsubmitperms':
							$userinfo['Group_SendPermission'] = utf8_encode($value[0]);
							break;
						case 'dlmemrejectperms':
							$userinfo['Group_RejectPermission'] = utf8_encode($value[0]);
							break;
						case 'directreports';
							if(isset($value['count']))
								unset($value['count']);
							$userinfo['DirectReports'] = $value;
							break;
						case 'cn':
							$userinfo['FullName'] = $value[0];
							break;
						default:
							break;
					}
				}
			}
			return $userinfo;
		}
		public function getUserInfo($username)
		{
			$userinfo = array();
			$info = $this->ldap->user()->info($username);
			$userinfo = $this->formatUserInfoStructure($info[0]);//var_dump($userinfo); exit;
			return $userinfo;
		}
		public function getUsers($includeDescription = false, $search = "*", $sorted = true)
		{
			$users = $this->ldap->user()->all($includeDescription, $search, $sorted);
			return $users;
		}
		
		public function find($includeDescription = false, $searchField = false, $searchFilter = false, $fields = array(),$sorted = true)
		{
			//return $this->ldap->user();
			if (!$this->ldap->getLdapBind()){ return false; }
          
	        // Perform the search and grab all their details
	        $searchParams = "";
	        if ($searchField) {
	            $searchParams = "(" . $searchField . "=" . $searchFilter . ")";
	        }                           
	        $filter = "(&(objectClass=user)(samaccounttype=" . adLDAP::ADLDAP_NORMAL_ACCOUNT .")(objectCategory=person)" . $searchParams . ")";
	        if(count($fields) <= 0){
	        	$fields = array("samaccountname","userprincipalname","displayname",
            				"pwdlastset","unicodepwd","givenname","sn","title","mail","memberof",
            				"telephonenumber","mobile","ipphone","homephone",
            				"l","postalcode","c","postofficebox","st","streetaddress",
            				"department","company",
            				"accountexpires","description",
            				"homedirectory","homedrive","initials",
            				"manager","physicaldeliveryofficename",
            				"scriptPath","profilepath","primarygroupid","objectsid",
            				"pager","wwwhomepage","facsimiletelephonenumber",
            				"useraccountcontrol","dlmemsubmitperms","dlmemrejectperms", "enabled",
							"directreports"); 
	        }
	        $cookie = null;
			$pageSize = 50;
			$usersArray = array();
			do {
		         ldap_control_paged_result($this->ldap->getLdapConnection(), $pageSize, true, $cookie);
				 
		         $result  = ldap_search($this->ldap->getLdapConnection(), $this->ldap->getTopBaseDn(), $filter, $fields);
		         $entries = ldap_get_entries($this->ldap->getLdapConnection(), $result);
		         foreach ($entries as $e) {
					 array_push($usersArray, $e);
		         }
		         ldap_control_paged_result_response($this->ldap->getLdapConnection(), $result, $cookie);
		       
		     } while($cookie !== null && $cookie != '');
			
	        if ($sorted) { 
	            asort($usersArray); 
	        }
	        return $usersArray;
		}
		
		public function synchronizeLdapServerUserToLdapClientUser(){
			$users = $this->getUsers();
			if(count($users) > 0)
			{
				\Saba\MongoBundle\Biz\BizBase::dropEntity($this->ldapUserEntity);
				foreach ($users as $user) {
					if(is_array($user) && count($user) > 0)
					{
						$userInfo = $this->formatUserInfoStructure($user);
						if(isset($userInfo["SAMAccountName"]) && trim($userInfo["SAMAccountName"]) != "")
						{
							$userInfo["_id"] = trim($userInfo["SAMAccountName"]); 
							\Saba\MongoBundle\Biz\BizBase::updateEntity($this->ldapUserEntity, $userInfo["_id"], $userInfo, false, true);	
						}
					}
				}
				return true;
			}
			return false;
		}
		public function insertLdapUserIntoSabaUserMySQL($dataLdapUser, $roleid, $timezoneid)
		{
			$data = new \stdClass();
			$data->Id = 0;
			$data->Accountname = $dataLdapUser->SAMAccountName;
			$data->Hashedpassword = '123456789';
			$data->Isdeleted = 0;
			$data->Roleid = $roleid;
			$data->Email = $dataLdapUser->Mail;
			$data->Fullname = $dataLdapUser->FullName;
			$data->Timezoneid = $timezoneid;
			$result = UsersBiz::save($data);
			return $result;
		}
		public function getLdapUserList($searchParams)
		{
			$resp = new ServerResponse();
			if(isset($searchParams['samaccountname']) && $searchParams['samaccountname'] !== "")
			{
				$searchParams['search']['SAMAccountName'] = new \MongoRegex("/.*{$searchParams['samaccountname']}.*/i");
			}
			if(isset($searchParams['logonname']) && $searchParams['logonname'] !== "")
			{
				$searchParams['search']['LogonName'] = new \MongoRegex("/.*{$searchParams['logonname']}.*/i");
			}
			
			if (!isset($searchParams['selectFields']))
				$searchParams['selectFields'] = array('_id'=>true,'Description'=>true,'DisplayName'=>true, 'GivenName'=>true,
												'MemberOf'=>true, 'Enabled'=>true, 'PrimaryGroupId'=>true, 'ObjectsId'=>true,
												'AccountExpires'=>true, 'SAMAccountName'=>true, 'LogonName'=>true,
												'Mail'=>true, 'DN'=>true, 'FullName'=>true);
			
			$resp = \Saba\MongoBundle\Lib\MongoDBManager::getListEntity($this->ldapUserEntity,$searchParams);
			
			$datarows = $resp->Data["datarows"];
			$total = $resp->Data["total"];
			
			$resp->setResponseData(array("datarows"=>$datarows, "total"=>$total));
			return $resp;
		}
		public function getListOfUserNameInGroup($groupName, $recursive = NULL)
		{
			$userNameArray = $this->ldap->group()->members($groupName, $recursive);
			return $userNameArray;
		}
		public function getGroupListOfUser($username, $recursive = NULL, $isGUID = false)
		{
			$groupNameArray = $this->ldap->user()->groups($username, $recursive, $isGUID);
			return $groupNameArray;
		}
		public function test($groupName)
		{
			$test = $this->ldap->group()->recursiveGroups($groupName);
			return $test;
		}
		public function getGroups($includeDescription = false, $search = "*", $sorted = true)
		{
			$groupsinfo = array();
			$groups = $this->ldap->group()->all($includeDescription, $search, $sorted);
			if(count($groups) > 0)
			{
				foreach ($groups as $key => $value) {
					$obj = array();
					$obj["Name"] = $value[0];
					$obj["DistinguishedName"] = $value[1];
					$obj["Members"] = $value[2];
					$obj["MemberOf"] = $value[3];
					//var_dump($obj);exit;
					$groupsinfo[] = $obj;
				}
			}
			return $groupsinfo;
		}
		public function synchronizeLdapGroupToLdapRole(){
			$groups = $this->getGroups();
			$groupNames = array();
			if(count($groups) > 0)
			{
				
				\Saba\MongoBundle\Biz\BizBase::dropEntity($this->ldapRoleEntity);
				foreach ($groups as $key => $value) {
					$groupNames[] = $value["Name"];
					$groupData = array('Name'=>$value["Name"], 'DistinguishedName'=>$value["DistinguishedName"],
									'Members'=>$value["Members"], 'MemberOf'=>$value["MemberOf"]);
					$groupData["_id"] = trim($groupData["Name"]); 
					\Saba\MongoBundle\Biz\BizBase::updateEntity($this->ldapRoleEntity, $groupData["_id"], $groupData, false, true);
				}
			}
			return $groupNames;
		}
		public function getLdapRole($field, $value)
		{
			return \Saba\MongoBundle\Biz\BizBase::getOneEnityWithField($this->ldapRoleEntity, $field, $value);
		}
		
		public function authenticateToLDAP($groupName, $roleArray, $ldapUser, $step, $browserTimezoneOffset = null, $localUser = null, $response = true)
		{
			$roles = array();
			for ($i=0; $i<count($roleArray); $i++)
			{
				$roles[$roleArray[$i]["Rolename"]] = $roleArray[$i];
			}
			$this->checkRecursiveLdapRole($groupName, $roles, $ldapUser, $step, $arrResult, $browserTimezoneOffset, $localUser, $response);

			for ($i=0; $i<count($arrResult); $i++)
			{
				if ($arrResult[$i]!=false) return $arrResult[$i];
			}
			return false;
		}
		
		public function checkRecursiveLdapRole($roleName, $roles, $ldapUser, $step, &$arrResult, $browserTimezoneOffset = null, $localUser = null, $response = true)
		{
			$resp = new ServerResponse();
			if ($step>20) return false;
			if (isset($roles[$roleName]))
			{
				$dataResponse = array();
				$clientInfo = new ClientInfo();
				$clientInfo -> setId(1);
				
				if($localUser != null){
					$clientInfo->setId($localUser->getId());
					$clientInfo->setIsAdminUser($localUser->getIsadmin());
					//$clientInfo->setExecutiveview($localUser->getExecutiveview() == 1?true:false);
				}
				
				$clientInfo->setInternalUser(true);
				
                $clientInfo -> setAccountname($ldapUser["SAMAccountName"]);
				if(isset($ldapUser["FullName"]) && !empty($ldapUser["FullName"]))
				{
					$clientInfo -> setFullname($ldapUser["FullName"]);
				}
				else
				{
					$clientInfo -> setFullname($ldapUser["SAMAccountName"]);
				}
				if(isset($ldapUser["TelephoneNumber"])){
					$clientInfo -> setPhone1($ldapUser["TelephoneNumber"]);
				}
				if(isset($ldapUser["Mobile"])){
					$clientInfo -> setPhone2($ldapUser["Mobile"]);
				}
				if(isset($ldapUser["Mail"])){
					$clientInfo -> setEmail($ldapUser["Mail"]);
				}
				
                $clientInfo -> setRoleid($clientInfo->getIsAdminUser()?0:$roles[$roleName]['Id']);
				
				if ($browserTimezoneOffset != null) {
					$tzOffset = substr_replace($browserTimezoneOffset, ':00', -2);
					$param = array ("tzvalue" => $tzOffset, 'isdeleted' => 0);
        			$tzObj = EntityManager::getBy("\Saba\UserBundle\Lib\TimeZonesEntity", $param);
					if ($tzObj != null && !empty($tzObj) && is_array($tzObj) && count($tzObj) > 0) {
						$clientInfo -> setTimeClientZoneid($tzObj[0] -> getId());
	                	$clientInfo -> setTimeClientZone($tzObj[0] -> getTzname());
	                	$clientInfo -> setTimeClientZoneValue($tzObj[0] -> getTzvalue());
					} else {
						$clientInfo -> setTimeClientZoneid(9);
	                	$clientInfo -> setTimeClientZone("America/Los_Angeles");
	                	$clientInfo -> setTimeClientZoneValue("-08:00");
					}
				} else {
					$clientInfo -> setTimeClientZoneid(9);
                	$clientInfo -> setTimeClientZone("America/Los_Angeles");
                	$clientInfo -> setTimeClientZoneValue("-08:00");
				}
				
                $clientInfo -> setTimelogin(time());
                $username = $ldapUser["SAMAccountName"];
				$clientInfo -> setNonLDAP(false); 
				
				if($localUser != null && !$this->checkManager($localUser, $ldapUser)){
					\Saba\UserBundle\Biz\UsersBiz::updateManager($localUser->getId());
				}
				if(!$clientInfo->getIsAdminUser()){
					//account support
					$grandAccounts = \Saba\AuthenticationBundle\Biz\AuthenticationBiz::getGrantAccount($clientInfo, false);
					if(count($grandAccounts) <= 0){ 
						throw new \Exception("msg.notauthorizedtoview", 1);
					}
				
					$clientInfo->setUserMembers(\Saba\UserBundle\Biz\UsersBiz::getManagerList($ldapUser["SAMAccountName"]));
				}else{
					$dataResponse['isSuperadminUser'] = true;
				}
				
                //check if user logged
                $api = SecurityManager::checkUserLogged($username);
				$rolefuncs = \Saba\AuthenticationBundle\Biz\AuthenticationBiz::getFuncsOfRole($clientInfo -> getRoleid());
				$turnElasticsearch = \Saba\CoreBundle\Lib\GeneralConfig::$configs["saba.elasticsearch.turn"];
				$clientInfo -> setCacheFuncs($rolefuncs);
                if(is_null($api)){
                    //save Login Data
                    $api = SecurityManager::saveLoginData($clientInfo);
                } else {
                      //renew Login Data
                    $api =  SecurityManager::renewLoginData($api,$clientInfo);
                }
				$uiPermissions = \Saba\UtilityBundle\Biz\UipermissionBiz::getUiPermissions($clientInfo -> getRoleid());
				$dt =  new \DateTime();

				$data = array("apikey" => $api,
					'roleId' => $roles[$roleName]['Id'],
					"uipermissions" => $uiPermissions,
					"funcgroups" => $rolefuncs[0],
					"funcs" => $rolefuncs[1],
					"isProduction" => \Saba\CoreBundle\Lib\GeneralConfig::$configs["system.runas.sandbox"] != "true",
					"passLDAP" => \Saba\CoreBundle\Lib\GeneralConfig::$configs["system.auth.to.ldap"] == "true",
					"superUser" => \Saba\CoreBundle\Lib\GeneralConfig::$configs["system.mainuser"],
					"timeServer" => $dt->format("Y-m-d h:m:s"),
					'nonLDAP' => $clientInfo->getNonLDAP(),
					'turnElasticsearch' => $turnElasticsearch,
					'username' => $clientInfo->getAccountname()
				);

				if($response) {
					$resp->setResponseData($data);
					$arrResult[] = new Response(json_encode($resp));
					return new Response(json_encode($resp));
				}else{
					$arrResult[] = $data;
					return $data;
				}
			}
			else 
			{
				$group = $this->getLdapRole('Name', $roleName);
				if($group->Data != null){
					if($group->Data['MemberOf'] != null){
						$step++;
						foreach ($group->Data['MemberOf'] as $key2 => $value2) {
							$groupPart1 = explode("=", stristr($value2, ',', true));
							$groupName1 = $groupPart1[1];
							$rst = $this->checkRecursiveLdapRole($groupName1, $roles, $ldapUser, $step, $arrResult, $browserTimezoneOffset, $localUser);
							$arrResult[] = $rst;
							if ($rst!=false) return $rst;
						}
					}
					else return false;
				}
				else return false;
			}
		}
		
		public function loginSabaViaLdapServer($username, $password)
		{
			$user = array();
			$auth = $this->authenticate($username, $password);
			if ($auth) {
				$user = $this->getUserInfo($username);
			} else {
				$user = $this->getUserInfo($username);
				if(count($user) > 0 && isset($user['Enabled']) ){
					//if($user['Enabled'] == "512"){
					//	throw new \Exception("Account is expired.", 1);
					if($user['Enabled'] == '66050' || $user['Enabled'] == "514"){//var_dump('ok'); exit;
						$userLocal = EntityManager::getBy("\Saba\UserBundle\Lib\UsersEntity", array('accountname'=>$username,'isldapuser'=>1, 'isdeleted'=>0));
						if(count($userLocal) > 0){
							$userLocal = new \Saba\UserBundle\Lib\UsersEntity($userLocal[0]);
							$userLocal ->setIsdeleted(1);
							$userLocal ->save();
						}
						throw new \Exception("mess.login.user.disabled", 1);
					}
				}
				throw new \Exception("Account authentication failed. Please check and use your Saba domain account to login.", 1);
				
			}
			return $user;
		}
		public function createUser($username, $password, $firstname, $surname, $email, $container = array())
		{
			$attributes = array();
			$attributes["username"] = $username;
			$attributes["password"] = $password;
			$attributes["firstname"] = $firstname;
			$attributes["surname"] = $surname;
			$attributes["email"] = $email;
			$attributes["container"] = $container;
			$result = $this->ldap->user()->create($attributes);
			return $result;
		}
		public function deleteUser($username)
		{
			$result = true;
			if($username != "")
			{
				$result = $this->ldap->user()->delete($username);
			}
			else {
				$result = false;
			}
			return $result;
		}
		function __destruct ()
		{
			$this->ldap = null;
		}
		
		public function getBaseDN()
		{
			return $this->ldap->getAccountSuffix();
		}

		public function getAccountSuffix()
		{
			return $this->ldap->getAccountSuffix();
		}

		public function checkManager($localUser, $ldapUser)
		{
			$manager = '';
			$displayName = '';
			if(isset($ldapUser['Manager']))
				$manager = preg_replace('/(CN=)|(,.*)/', '', $ldapUser['Manager']);
			if(isset($ldapUser['FullName']))
				$displayName = trim($ldapUser['FullName']);
			return trim($localUser->getManager()) == trim($manager) && trim($localUser->getFullname() == $displayName);
		}
	}
?>