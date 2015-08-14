<?php
 /** 										*
 * Modified date: 11/05/2014				*
 * Modified by	: PHONG LAM 				*
 */ 

	namespace CnitCrmBundle\Lib\Utility;

	class SecurityData {
		private $keyIdentifier;
	    private $securityToken0;
	    private $securityToken1;
		
	    public function getSecurityData($keyIdentifier, $securityToken0, $securityToken1)
	    {
	        $this->keyIdentifier = $keyIdentifier;
	        $this->securityToken0 = $securityToken0;
	        $this->securityToken1 = $securityToken1;
			return $this;
	    }	
	    public function getKeyIdentifier() {
	        return $this->keyIdentifier;
	    }
	    public function getSecurityToken0() {
	        return $this->securityToken0;
	    }
	    public function getSecurityToken1() {
	        return $this->securityToken1;
	    }   
	}
?>
