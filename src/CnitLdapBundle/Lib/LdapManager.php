<?php
 /** 										*
 * Modified date: 11/05/2014				*
 * Modified by	: Phong Lam 				*
 */ 

	namespace CnitLdapBundle\Lib;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\HttpFoundation\Request;
	
	class LdapManager{
		protected $ldap;
		function __construct()
		{
			$this->ldap = new \adLDAP();
		}
		public function authenticate($username, $password, $preventRebind = false)
		{
			return $this->ldap->authenticate($username, $password, $preventRebind);
		}
		public function formatUserInfoStructure($ldapUser)
		{
			$userInfo = array();
			if(count($ldapUser) > 0)
			{
				foreach ($ldapUser as $key => $value) 
				{
					switch ($key) {
						case 'displayname':
							$userInfo['DisplayName'] = utf8_encode($value[0]);
							break;
						case 'memberof':
							$userInfo['MemberOf'] = array();
							if ($value['count'] > 0) {
								$memofarray = array();
								for ($i=0; $i < $value['count']; $i++) { 
									$memofarray[$i] = utf8_encode($value[$i]);
								}
								$userInfo['MemberOf'] = $memofarray;
							}
							break;
						case 'primarygroupid':
							$userInfo['PrimaryGroupId'] = utf8_encode($value[0]);
							break;
						case 'objectsid':
							$userInfo['ObjectsId'] = utf8_encode($value[0]);
							break;
						case 'samaccountname':
							$userInfo['SAMAccountName'] = utf8_encode($value[0]);
							break;
						case 'mail':
							$userInfo['Mail'] = utf8_encode($value[0]);
							break;
						case 'dn':
							$userInfo['DN'] = utf8_encode($value);
							break;
						case 'userprincipalname':
							$userInfo['LogonName'] = utf8_encode($value[0]);
							break;	
						case 'unicodepwd':
							$userInfo['UnicodePassword'] = utf8_encode($value[0]);
							break;	
						case 'givenname':
							$userInfo['GivenName'] = utf8_encode($value[0]);
							break;	
						case 'sn':
							$userInfo['SurName'] = utf8_encode($value[0]);
							break;	
						case 'title':
							$userInfo['Title'] = utf8_encode($value[0]);
							break;
						case 'telephonenumber':
							$userInfo['TelephoneNumber'] = utf8_encode($value[0]);
							break;
						case 'mobile':
							$userInfo['Mobile'] = utf8_encode($value[0]);
							break;
						case 'ipphone':
							$userInfo['IPPhone'] = utf8_encode($value[0]);
							break;
						case 'homephone':
							$userInfo['HomePhone'] = utf8_encode($value[0]);
							break;
						case 'l':
							$userInfo['City'] = utf8_encode($value[0]);
							break;
						case 'postalcode':
							$userInfo['PostalCode'] = utf8_encode($value[0]);
							break;
						case 'c':
							$userInfo['Country'] = utf8_encode($value[0]);
							break;
						case 'postofficebox':
							$userInfo['PostOfficeBox'] = utf8_encode($value[0]);
							break;
						case 'st':
							$userInfo['StateAddress'] = utf8_encode($value[0]);
							break;
						case 'streetaddress':
							$userInfo['StreetAddress'] = utf8_encode($value[0]);
							break;
						case 'company':
							$userInfo['Company'] = utf8_encode($value[0]);
							break;
						case 'department':
							$userInfo['Department'] = utf8_encode($value[0]);
							break;
						case 'accountexpires':
							$userInfo['AccountExpires'] = utf8_encode($value[0]);
							break;
						case 'description':
							$userInfo['Description'] = utf8_encode($value[0]);
							break;
						case 'homedirectory':
							$userInfo['HomeDirectory'] = utf8_encode($value[0]);
							break;
						case 'homedrive':
							$userInfo['HomeDrive'] = utf8_encode($value[0]);
							break;
						case 'initials':
							$userInfo['Initials'] = utf8_encode($value[0]);
							break;
						case 'manager':
							$userInfo['Manager'] = utf8_encode($value[0]);
							break;
						case 'physicaldeliveryofficename':
							$userInfo['Office'] = utf8_encode($value[0]);
							break;
						case 'useraccountcontrol':
							$userInfo['Enabled'] = utf8_encode($value[0]);
							break;
						case 'facsimiletelephonenumber':
							$userInfo['Fax'] = utf8_encode($value[0]);
							break;
						case 'dlmemsubmitperms':
							$userInfo['Group_SendPermission'] = utf8_encode($value[0]);
							break;
						case 'dlmemrejectperms':
							$userInfo['Group_RejectPermission'] = utf8_encode($value[0]);
							break;
						case 'directreports';
							if(isset($value['count']))
								unset($value['count']);
							$userInfo['DirectReports'] = $value;
							break;
						case 'cn':
							$userInfo['FullName'] = $value[0];
							break;
						default:
							break;
					}
				}
			}
			return $userInfo;
		}
		public function getUserInfo($username)
		{
			$info = $this->ldap->user()->info($username);
			$userInfo = $this->formatUserInfoStructure($info[0]);
			return $userInfo;
		}
		public function getUsers($includeDescription = false, $search = "*", $sorted = true)
		{
			$users = $this->ldap->user()->all($includeDescription, $search, $sorted);
			return $users;
		}
		
		public function find($includeDescription = false, $searchField = false, $searchFilter = false, $fields = array(),$sorted = true)
		{
			if (!$this->ldap->getLdapBind()){
				return false;
			}
	        // Perform the search and grab all their details
	        $searchParams = "";
	        if ($searchField) {
	            $searchParams = "(" . $searchField . "=" . $searchFilter . ")";
	        }                           
	        $filter = "(&(objectClass=user)(samaccounttype=" . \adLDAP::ADLDAP_NORMAL_ACCOUNT .")(objectCategory=person)" . $searchParams . ")";
	        if(count($fields) <= 0){
	        	$fields = array(
					"samaccountname","userprincipalname","displayname",
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
					"directreports"
				);
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
			$groups = $this->ldap->group()->all($includeDescription, $search, $sorted);
			return $groups;
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
	}
?>