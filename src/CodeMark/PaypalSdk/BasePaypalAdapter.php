<?php
namespace CodeMark\PaypalSdk;
/**
 * Handle frontend forms
 *
 * @class 		PaypalSDK
 * @version		1.0.0
 * @package		CodeMark\PaypalSdk
 * @category	Class
 * @author 		WebplanetSoft
 */
abstract class Base_Paypal_Adapter {
	
	protected $_SandBox = false;
	protected $_EndPointURL = 'https://api-3t.sandbox.paypal.com/nvp';
	protected static $_Method = 'POST';
	protected static $_version = '108';
	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	mixed[]	$aDetail	Array structure providing config data
	 * @return	void
	 */
	public function __construct($aDetail){
		if(isset($aDetail['sandbox'])){
			$this->_SandBox = $aDetail['sandbox'];
		}
		if(isset($aDetail['method'])){
			$this->_Method = $aDetail['method'];
		}
		if(isset($aDetail['version'])){
			$this->_version = $aDetail['version'];
		}
		if(!$this->_SandBox){
			$this->_EndPointURL = str_replace('sandbox.', '', $this->_EndPointURL);
		}
	}

	/**
	 * Send the API request to PayPal using CURL.
	 *
	 * @access	public
	 * @param	string	$Request		Raw API request string.
	 * @return	string	$Response		Returns the raw HTTP response from PayPal.
	 */
	public function CURLRequest($Request) //: string
	{
		/*$Request = array_merge(array(
    'USER' => 'gopalB_api1.webplanet.com',
    'PWD' => 'N7PQ2D9RQJ3UMPU8',
    'SIGNATURE' => 'AyMtdPR7Swna5x6JBALLeZ31xTQ6A1buUFwvTgxirfxp3i33ppJWu1d4'),$Request);
		*/

		$curl = curl_init();

		$options = array();

		$options[CURLOPT_URL] 				= $this->_EndPointURL;
		$options[CURLOPT_CUSTOMREQUEST] 	= self::$_Method;
		$options[CURLOPT_RETURNTRANSFER]	= true;
		
		if(self::$_Method=='POST'){
			$options[CURLOPT_POST] 				= true;
		}
		
		$options[CURLOPT_SSL_VERIFYPEER] 	= false;
		$options[CURLOPT_FAILONERROR] 		= false;
		$options[CURLOPT_POSTFIELDS] 		= http_build_query($Request);

		curl_setopt_array($curl, $options);

		$response = curl_exec($curl);
		$errno = curl_errno($curl);
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if ($response == false || $errno != CURLE_OK) {	
			return $response;
		}

		/*$curl = curl_init();

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_URL, $this->_EndPointURL);
		
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($Request));
		 
		$response =    curl_exec($curl);
		 
		curl_close($curl);*/
		return $response;
		
		/*$args = array('method' => self::$_Method, 'sslverify' => false);
		if($Request)
		{
			$args['body'] = $Request;
		}
		$aResponse = wp_remote_request($this->_EndPointURL,$args);
		$cData = wp_remote_retrieve_body( $aResponse );
		return $cData;*/
	}

	/**
	 * Convert an NVP string to an array with URL decoded values.
	 *
	 * @access	public
	 * @param	string	$NVPString	Name-value-pair string that you would like to convert to an array.
	 * @return	mixed[]	Returns the NVP string as an array structure.
	 */
	function NVPToArray($NVPString) //: array
	{
		$proArray = array();
		while(strlen($NVPString))
		{
			// name
			$keypos= strpos($NVPString,'=');
			$keyval = substr($NVPString,0,$keypos);
			// value
			$valuepos = strpos($NVPString,'&') ? strpos($NVPString,'&'): strlen($NVPString);
			$valval = substr($NVPString,$keypos+1,$valuepos-$keypos-1);
			// decoding the respose
			$proArray[$keyval] = urldecode($valval);
			$NVPString = substr($NVPString,$valuepos+1,strlen($NVPString));
		}
		/*
		$proArray = array();
		if (preg_match_all('/(?<name>[^\=]+)\=(?<value>[^&]+)&?/', $NVPString, $matches)) {
		    foreach ($matches['name'] as $offset => $name) {
		        $proArray[$name] = urldecode($matches['value'][$offset]);
		    }
		}
		*/
		
		return $proArray;
		
	}
	/**
     * Save log info to a location on the disk.
     *
     * @param $log_path
     * @param $filename
     * @param $string_data
     * @return bool
     */
    function Logger($log_path, $filename, $string_data){

        if($this->LogResults)
        {
            $timestamp = strtotime('now');
            $timestamp = date('mdY_gi_s_A_',$timestamp);

            $string_data_array = $this->NVPToArray($string_data);

            $file = $log_path.$timestamp.$filename.'.txt';
            $fh = fopen($file, 'w');
            fwrite($fh, $string_data.chr(13).chr(13).print_r($string_data_array, true));
            fclose($fh);
        }
		
		return true;	
	}
	/**
	 * Check whether or not the API call was successful.
	 *
	 * @access	public
	 * @param	string	$ack	The value for ACK returned by a PayPal API response.
	 * @return	boolean	Returns a boolean (true/false) value for whether or not the ACK supplied is successful.
	 */
	function APICallSuccessful($ack)
	{
		if(strtoupper($ack) != 'SUCCESS' && strtoupper($ack) != 'SUCCESSWITHWARNING' && strtoupper($ack) != 'PARTIALSUCCESS')
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/**
	 * Check whether or not warnings were returned.
	 *
	 * @access	public
	 * @param	string	$ack	The value for ACK returned by a PayPal API response.
	 * @return	boolean	Returns a boolean (true/false) value for whether or not the response includes warnings.
	 */
	function WarningsReturned($ack)
	{
        if(strpos(strtoupper($ack),'WARNING') !== false)
        {
            return true;
        }
        else
        {
            false;
        }
	}
	
	/**
	 * Get all errors returned from PayPal.
	 *
	 * @access	public
	 * @param	mixed[]	$DataArray	Array structure of PayPal NVP response.
	 * @return	mixed[]	$Errors		Returns an array structure of all errors / warnings returned in a PayPal HTTP response.
	 */
	function GetErrors($DataArray)
	{
	
		$Errors = array();
		$n = 0;
		while(isset($DataArray['L_ERRORCODE' . $n . '']))
		{
			$LErrorCode = isset($DataArray['L_ERRORCODE' . $n . '']) ? $DataArray['L_ERRORCODE' . $n . ''] : '';
			$LShortMessage = isset($DataArray['L_SHORTMESSAGE' . $n . '']) ? $DataArray['L_SHORTMESSAGE' . $n . ''] : '';
			$LLongMessage = isset($DataArray['L_LONGMESSAGE' . $n . '']) ? $DataArray['L_LONGMESSAGE' . $n . ''] : '';
			$LSeverityCode = isset($DataArray['L_SEVERITYCODE' . $n . '']) ? $DataArray['L_SEVERITYCODE' . $n . ''] : '';
			
			$CurrentItem = array(
								'L_ERRORCODE' => $LErrorCode, 
								'L_SHORTMESSAGE' => $LShortMessage, 
								'L_LONGMESSAGE' => $LLongMessage, 
								'L_SEVERITYCODE' => $LSeverityCode
								);
								
			array_push($Errors, $CurrentItem);
			$n++;
		}
		
		return $Errors;
		
	}
	
	/**
	 * Display errors on screen using line breaks.
	 *
	 * @access	public
	 * @param	mixed[]	$Errors	An array structure of errors returned in a PayPal HTTP response.
	 * @return	output	Returns an HTML string of the errors passed in for basic display purposes.
	 */
	function DisplayErrors($Errors)
	{
		foreach($Errors as $ErrorVar => $ErrorVal)
		{
			$CurrentError = $Errors[$ErrorVar];
			foreach($CurrentError as $CurrentErrorVar => $CurrentErrorVal)
			{
				if($CurrentErrorVar == 'L_ERRORCODE')
				{
					$CurrentVarName = 'Error Code';
				}
				elseif($CurrentErrorVar == 'L_SHORTMESSAGE')
				{
					$CurrentVarName = 'Short Message';
				}
				elseif($CurrentErrorVar == 'L_LONGMESSAGE')
				{
					$CurrentVarName = 'Long Message';
				}
				elseif($CurrentErrorVar == 'L_SEVERITYCODE')
				{
					$CurrentVarName = 'Severity Code';
				}
			
				echo $CurrentVarName . ': ' . $CurrentErrorVal . '<br />';		
			}
			echo '<br />';
		}
	}
}