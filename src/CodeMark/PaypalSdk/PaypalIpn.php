<?php
namespace CodeMark\PaypalSdk;

/**
 * Handle frontend forms
 *
 * @class 		Paypal_Ipn
 * @version		1.0.0
 * @package		paypal/
 * @category	     Class
 * @author 		WebplanetSoft
 */
class Paypal_Ipn extends Base_Paypal_Adapter {
	
	/**
     * Constructor
     *
     * @access  public
     * @param   mixed[] $aData Array structure providing config data
     * @return  void
     */
	public function __construct($aData) {
		parent::__construct($aData);
          $this->_EndPointURL = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
          if(!$this->_SandBox){
               $this->_EndPointURL = str_replace('sandbox.', '', $this->_EndPointURL);
          }
	}
     /**
     * Check notify of IPN response.
     * 
     * Used to get all fields.
     *
     * @access  public
     * @param   mixed[] $aRequest  Array structure of request data.
     * @return  mixed[] Returns an array structure of the PayPal HTTP response params as well as parsed errors and the raw request/response.
     */
     public function checkIpnNotify($aRequest){

          $aRequest['cmd'] = '_notify-validate';
          $aRequest['VERSION'] = '108';

          $NVPResponse = $this->CURLRequest($aRequest);

          //$NVPResponseArray['ERRORS'] = $this->GetErrors($NVPResponseArray);

          return $NVPResponse;
     }
}