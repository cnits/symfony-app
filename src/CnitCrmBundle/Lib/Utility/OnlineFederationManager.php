<?php
 /** 										*
 * Code owner	: Cnit						*
 * Modified date: 11/05/2014				*
 * Modified by	: PHONG LAM 				*
 */ 

	namespace CnitCrmBundle\Lib\Utility;

	use CnitCrmBundle\Lib\Utility\SecurityData;
	use \DomDocument;
	class OnlineFederationManager {
	
	    public function authenticateWithOnlineFederation($CRMUrl, $OFUsername, $OFPassword) {
	
			//OnlineFederation Authentication
			//https://login.microsoftonline.com/extSTS.srf 
			//https://login.microsoftonline.com/RST2.srf; 
			//https://dynamicscrmna.accesscontrol.windows.net
			
	        // Register Device Credentials and get binaryDAToken
	        
	        //$liveEndpoint = "https://login.microsoftonline.com/extSTS.srf";
			$loginEndpoint = "https://login.microsoftonline.com/RST2.srf";
			//$liveEndpoint = "https://login.live.com/extSTS.srf";
			//$liveEndpoint = "https://login.live.com/liveidSTS.srf";
			$securityTokenSoapTemplate = '<s:Envelope xmlns:s="http://www.w3.org/2003/05/soap-envelope"
	                 xmlns:a="http://www.w3.org/2005/08/addressing"
	                 xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
	                <s:Header>
	                    <a:Action s:mustUnderstand="1">
	                    http://schemas.xmlsoap.org/ws/2005/02/trust/RST/Issue</a:Action>
	                    <a:MessageID>urn:uuid:%s</a:MessageID>
	                    <a:ReplyTo>
	                      <a:Address>http://www.w3.org/2005/08/addressing/anonymous</a:Address>
	                    </a:ReplyTo>
	                    <VsDebuggerCausalityData xmlns="http://schemas.microsoft.com/vstudio/diagnostics/servicemodelsink">
	                    uIDPo4TBVw9fIMZFmc7ZFxBXIcYAAAAAbd1LF/fnfUOzaja8sGev0GKsBdINtR5Jt13WPsZ9dPgACQAA</VsDebuggerCausalityData>
	                    <a:To s:mustUnderstand="1">
	                    '.$loginEndpoint.'</a:To>
	                    <o:Security s:mustUnderstand="1"
	                    xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
	                      <u:Timestamp u:Id="_0">
	                       <u:Created>%s</u:Created>
	                       <u:Expires>%s</u:Expires>
	                      </u:Timestamp>
	                      <o:UsernameToken u:Id="uuid-14bed392-2320-44ae-859d-fa4ec83df57a-1">
	                        <o:Username>%s</o:Username>
	                        <o:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">%s</o:Password>
	                      </o:UsernameToken>
	                    </o:Security>
	                 </s:Header>
	                  <s:Body>
	                    <t:RequestSecurityToken xmlns:t="http://schemas.xmlsoap.org/ws/2005/02/trust">
	                      <wsp:AppliesTo xmlns:wsp="http://schemas.xmlsoap.org/ws/2004/09/policy">
	                        <a:EndpointReference>
	                          <a:Address>%s</a:Address>
	                        </a:EndpointReference>
	                      </wsp:AppliesTo>
	                     <wsp:PolicyReference URI="MBI_FED_SSL"
	                      xmlns:wsp="http://schemas.xmlsoap.org/ws/2004/09/policy" />
	                      <t:RequestType>http://schemas.xmlsoap.org/ws/2005/02/trust/Issue</t:RequestType>
	                    </t:RequestSecurityToken>
	                  </s:Body>
	                 </s:Envelope>';
	
	        // Create the URN address of the format urn:crm:dynamics.com.
	        // Replace crm with crm4 for Europe & crm5 for Asia.
			$URNAddress = "urn:crmna:dynamics.com";
	        if (strpos($CRMUrl,"crmemea:dynamics.com")) {
	            $URNAddress = "urn:crmemea:dynamics.com";
	        }
	        if (strpos($CRMUrl,"crmapac:dynamics.com")) {
	            $URNAddress = "urn:crmapac:dynamics.com";
	        }
	        $securityTemplate = sprintf(
	                        $securityTokenSoapTemplate, OnlineFederationManager::gen_uuid(), OnlineFederationManager::getCurrentTime(), OnlineFederationManager::getNextDayTime(), $OFUsername, $OFPassword, $URNAddress);
			//$securityTokenXML = LiveIDManager::GetSOAPResponse("/liveidSTS.srf" , "login.live.com" , "https://login.live.com/liveidSTS.srf", $securityTemplate);
			//$securityTokenXML = LiveIDManager::GetSOAPResponse("/extSTS.srf" , "login.live.com" , "https://login.live.com/extSTS.srf", $securityTemplate);
			//$securityTokenXML = LiveIDManager::GetSOAPResponse("/extSTS.srf" , "login.microsoftonline.com" , "https://login.microsoftonline.com/extSTS.srf", $securityTemplate);
			$securityTokenXML = OnlineFederationManager::GetSOAPResponse("/RST2.srf" , "login.microsoftonline.com" , "https://login.microsoftonline.com/RST2.srf", $securityTemplate);
			
	        $responsedom = new \DomDocument();
	        $responsedom->loadXML($securityTokenXML);
			
	        $cipherValues = $responsedom->getElementsbyTagName("CipherValue");
	        if(isset ($cipherValues) && $cipherValues->length>0){
	            $securityToken0 =  $cipherValues->item(0)->textContent;
	            $securityToken1 =  $cipherValues->item(1)->textContent;
	            $keyIdentifier = $responsedom->getElementsbyTagName("KeyIdentifier")->item(0)->textContent;
	        }else{
	            return null;
	        }
	        $objSecurityData = new SecurityData();
			$newSecurityData = $objSecurityData->getSecurityData($keyIdentifier, $securityToken0, $securityToken1);
			
	        return $newSecurityData;
	    }
	    public static function getCurrentTime() {
	        return substr(date('c'), 0, -6) . ".00";
	    }
	    public static function getNextDayTime() {
	        return substr(date('c', strtotime('+1 day')), 0, -6) . ".00";
	    }
	    public static function gen_uuid() {
	        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
	                // 32 bits for "time_low"
	                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
	                // 16 bits for "time_mid"
	                mt_rand(0, 0xffff),
	                // 16 bits for "time_hi_and_version",
	                // four most significant bits holds version number 4
	                mt_rand(0, 0x0fff) | 0x4000,
	                // 16 bits, 8 bits for "clk_seq_hi_res",
	                // 8 bits for "clk_seq_low",
	                // two most significant bits holds zero and one for variant DCE1.1
	                mt_rand(0, 0x3fff) | 0x8000,
	                // 48 bits for "node"
	                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
	        );
	    }
		
	    public static function GetSOAPResponse($postUrl, $hostname, $soapUrl, $content) {
	        $headers = array(
	            "POST " . $postUrl . " HTTP/1.1",
	            "Host: " . $hostname,
	            'Connection: Keep-Alive',
	            "Content-type: application/soap+xml; charset=UTF-8",
	            "Content-length: " . strlen($content),
	        );
			
	        $cURLHandle = curl_init();
	        curl_setopt($cURLHandle, CURLOPT_URL, $soapUrl);
	        curl_setopt($cURLHandle, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($cURLHandle, CURLOPT_TIMEOUT, 180);
	        curl_setopt($cURLHandle, CURLOPT_SSL_VERIFYPEER, false);
	        curl_setopt($cURLHandle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	        curl_setopt($cURLHandle, CURLOPT_HTTPHEADER, $headers);
	        curl_setopt($cURLHandle, CURLOPT_POST, 1);
	        curl_setopt($cURLHandle, CURLOPT_POSTFIELDS, $content);
			curl_setopt($cURLHandle, CURLOPT_SSLVERSION, 4);
			$response = curl_exec($cURLHandle);
			if($response === false){
				throw new \Exception(curl_error($cURLHandle), 1);
			}
	        curl_close($cURLHandle);
	        return $response;
	  }
}	
?>
