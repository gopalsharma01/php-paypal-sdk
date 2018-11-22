<?php
namespace CodeMark\PaypalSdk;

/**
 * Handle frontend forms
 *
 * @class 		Paypal_Express
 * @version		1.0.0
 * @package		paypal/
 * @category	Class
 * @author 		WebplanetSoft
 */
class Paypal_Express extends Base_Paypal_Adapter {
	
    protected $_API = [];
	/**
     * Constructor
     *
     * @access  public
     * @param   mixed[] $aData Array structure providing config data
     * @return  void
     */
	public function __construct($aData) {
        $this->_API = $aData['API'];
		parent::__construct($aData);
	}
    /**
     * Initiate an Express Checkout transaction.
     * 
     * Used to generate a unique TOKEN for use with the checkout.
     *
     * @access  public
     * @param   string $Token  string token which we get from paypal.
     * @return  string Returns an string HTTP url.
     */
    function getRedirectUrl($Token){
        $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $Token;
        if(!$this->_SandBox){
            //$NVPResponseArray['REDIRECTURLDIGITALGOODS'] = 'https://www.sandbox.paypal.com/incontext?'.$SkipDetailsOption.'&token='.$NVPResponseArray['TOKEN'];
            $url = str_replace('sandbox.', '', $url);//'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $Token;
        }
        return $url;
    }
    /**
     * Initiate an Express Checkout transaction.
     * 
     * Used to generate a unique TOKEN for use with the checkout.
     *
     * @access  public
     * @param   mixed[] $DataArray  Array structure of request data.
     * @return  mixed[] Returns an array structure of the PayPal HTTP response params as well as parsed errors and the raw request/response.
     */
    function SetExpressCheckout($DataArray)
    {   
        $aRequest = $this->_API;
        $aRequest['METHOD'] = 'SetExpressCheckout';
        $aRequest['VERSION'] = self::$_version;
        $aRequest['CANCELURL'] = $DataArray['cancel'];
        $aRequest['RETURNURL'] = $DataArray['return'];

        $extraData = [];
        if(isset($DataArray['recurring'])){
            $extraData = ['L_BILLINGTYPE0'=>$DataArray['recurring'],
                          'L_BILLINGAGREEMENTDESCRIPTION0'=>$DataArray['recurringDesc']];
        }else{
            $extraData = ['PAYMENTREQUEST_0_AMT' => $DataArray['amount'],
                          'PAYMENTREQUEST_0_CURRENCYCODE' => $DataArray['currency'],
                          'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
                          /*'PAYMENTREQUEST_0_ITEMAMT' => 5,
                          'L_PAYMENTREQUEST_0_NAME0' => 'new item 123 new',
                          'L_PAYMENTREQUEST_0_DESC0' => 'new item 123 descriptions new',
                          'L_PAYMENTREQUEST_0_QTY0' => 1,
                          'L_PAYMENTREQUEST_0_AMT0' => 5,*/
                         ];
        }

        $aRequest = array_merge($aRequest,$extraData);
        
        $NVPResponse = $this->CURLRequest($aRequest);
        $NVPResponseArray = $this->NVPToArray($NVPResponse);
        
        $Errors = $this->GetErrors($NVPResponseArray);
        //$this->Logger($this->LogPath, __FUNCTION__.'Request', $this->MaskAPIResult($NVPRequest));
        //$this->Logger($this->LogPath, __FUNCTION__.'Response', $NVPResponse);
        
        if(isset($NVPResponseArray['TOKEN']) && $NVPResponseArray['TOKEN'] != ''){
            $NVPResponseArray['REDIRECTURL'] = $this->getRedirectUrl($NVPResponseArray['TOKEN']);
        }else{
            $NVPResponseArray['ERRORS'] = true;
        }
        //$NVPResponseArray['ERRORS'] = $Errors;
        //$NVPResponseArray['REQUESTDATA'] = $aRequest;
        //$NVPResponseArray['RAWRESPONSE'] = $NVPResponseArray;
                
        return $NVPResponseArray;
    
    }  // End function SetExpressCheckout()
    
    /**
     * Obtain details about an Express Checkout transaction.
     *
     * This is used after PayPal redirects the buyer back to your 
     * ReturnURL supplied in the SetExpressCheckout request.  Data 
     * returned includes the buyer's name, shipping address, phone number, 
     * and general transaction details.
     *
     * @access  public
     * @param   string  $Token  The token returned from a previous SetExpressCheckout request.
     * @return  mixed[] Returns an array structure of the PayPal HTTP response params as well as parsed errors and the raw request/response.
     */
    function GetExpressCheckoutDetails($Token)
    {
        $NVPRequest             =   $this->_API;
        $NVPRequest['METHOD']   =   'GetExpressCheckoutDetails';
        $NVPRequest['VERSION']  =   self::$_version;
        $NVPRequest['TOKEN']    =   $Token;
        
        $NVPResponse = $this->CURLRequest($NVPRequest);
        $NVPResponseArray = $this->NVPToArray($NVPResponse);
        
        $Errors = $this->GetErrors($NVPResponseArray);
        
        //$this->Logger($this->LogPath, __FUNCTION__.'Request', $this->MaskAPIResult($NVPRequest));
        //$this->Logger($this->LogPath, __FUNCTION__.'Response', $NVPResponse);
        if(!empty($Errors))
        {
            $NVPResponseArray['ERRORS'] = true;    
        }
        
        //$NVPResponseArray['REQUESTDATA'] = $NVPRequest;
        //$NVPResponseArray['RAWRESPONSE'] = $NVPResponse;

        return $NVPResponseArray;
        
    
    }  // End function GetExpressCheckoutDetails()
        
    
    /**
     * Complete an Express Checkout transaction.
     *
     * If you set up a billing agreement in your SetExpressCheckout API call, 
     * the billing agreement is created when you call the DoExpressCheckoutPayment 
     * API operation.
     *
     * @access  public
     * @param   mixed[] $DataArray  Array structure of request data.
     * @return  mixed[] Returns an array structure of the PayPal HTTP response params as well as parsed errors and the raw request/response.
     */
    function DoExpressCheckoutPayment($DataArray)
    {
        $NVPRequest             =   $this->_API;
        $NVPRequest['METHOD']   =   'DoExpressCheckoutPayment';
        $NVPRequest['VERSION']  =   self::$_version;
        
        $NVPResponse = $this->CURLRequest($NVPRequest);
        $NVPResponseArray = $this->NVPToArray($NVPResponse);
        
        $Errors = $this->GetErrors($NVPResponseArray);

        //$this->Logger($this->LogPath, __FUNCTION__.'Request', $this->MaskAPIResult($NVPRequest));
        //$this->Logger($this->LogPath, __FUNCTION__.'Response', $NVPResponse);
                
        if(!empty($Errors))
        {
            $NVPResponseArray['ERRORS'] = true;    
        }
        
        return $NVPResponseArray;
    }

    /**
     * Create a recurring payments profile.
     *
     * You must invoke the CreateRecurringPaymentsProfile API operation for each 
     * profile you want to create. The API operation creates a profile and an 
     * associated billing agreement.
     * 
     * There is a one-to-one correspondence between billing agreements and 
     * recurring payments profiles. To associate a recurring payments profile 
     * with its billing agreement, you must ensure that the description in the 
     * recurring payments profile matches the description of a billing agreement. 
     * For version 54.0 and later, use SetExpressCheckout to initiate creation of 
     * a billing agreement.
     *
     * @access  public
     * @param   mixed[] $DataArray  Array structure of request data.
     * @return  mixed[] Returns an array structure of the PayPal HTTP response params as well as parsed errors and the raw request/response.
     */
    function CreateRecurringPaymentsProfile($DataArray)
    {
        $NVPRequest                      =   $this->_API;
        $NVPRequest['METHOD']            =   'CreateRecurringPaymentsProfile';
        $NVPRequest['VERSION']           =   self::$_version;
        $NVPRequest['TOKEN']             =   $DataArray['token'];
        $NVPRequest['PayerID']           =   $DataArray['payer_id'];
        $NVPRequest['AMT']               =   $DataArray['amount'];
        $NVPRequest['DESC']              =   $DataArray['descriptions'];

        $NVPRequest['BILLINGPERIOD']     =   (isset($DataArray['period']) && !empty($DataArray['period'])?$DataArray['period']:'Month');
        $NVPRequest['BILLINGFREQUENCY']  =   (isset($DataArray['frequency']) && !empty($DataArray['frequency'])?$DataArray['frequency']:1);
        $NVPRequest['CURRENCYCODE']      =   (isset($DataArray['currency']) && !empty($DataArray['currency'])?$DataArray['currency']:'USD');
        
        $NVPRequest['MAXFAILEDPAYMENTS'] =   (isset($DataArray['reattempt']) && !empty($DataArray['reattempt'])?$DataArray['reattempt']:3);
        $NVPRequest['PROFILESTARTDATE']  =   (isset($DataArray['profile_start']) && !empty($DataArray['profile_start'])?$DataArray['profile_start']:gmdate( 'Y-m-d\TH:i:s\Z'));
        if(isset($DataArray['total_cycle']) && !empty($DataArray['total_cycle'])){
            $NVPRequest['TOTALBILLINGCYCLES']  = $DataArray['total_cycle'];
        }
        if(isset($DataArray['country']) && !empty($DataArray['country'])){
            $NVPRequest['COUNTRYCODE']  = $DataArray['country'];
        }

        $NVPResponse = $this->CURLRequest($NVPRequest);
        $NVPResponseArray = $this->NVPToArray($NVPResponse);
        
        $Errors = $this->GetErrors($NVPResponseArray);

        //$this->Logger($this->LogPath, __FUNCTION__.'Request', $this->MaskAPIResult($NVPRequest));
        //$this->Logger($this->LogPath, __FUNCTION__.'Response', $NVPResponse);
        
        if(!empty($Errors))
        {
            $NVPResponseArray['ERRORS'] = true;    
        }
        //$NVPResponseArray['ERRORS'] = $Errors;

                                
        return $NVPResponseArray;   
    }   

    /**
     * Obtain information about a recurring payments profile.
     *
     * @access  public
     * @param   mixed[] $DataArray  Array structure of request data.
     * @return  mixed[] Returns an array structure of the PayPal HTTP response params as well as parsed errors and the raw request/response.
     */
    function GetRecurringPaymentsProfileDetails($DataArray)
    {
        $NVPRequest                      =   $this->_API;
        $NVPRequest['METHOD']            =   'GetRecurringPaymentsProfileDetails';
        $NVPRequest['VERSION']           =   self::$_version;

        $NVPResponse = $this->CURLRequest($NVPRequest);
        //$NVPRequestArray = $this->NVPToArray($NVPRequest);
        $NVPResponseArray = $this->NVPToArray($NVPResponse);
        
        /*$Errors = $this->GetErrors($NVPResponseArray);

        $this->Logger($this->LogPath, __FUNCTION__.'Request', $this->MaskAPIResult($NVPRequest));
        $this->Logger($this->LogPath, __FUNCTION__.'Response', $NVPResponse);
        
        $NVPResponseArray['ERRORS'] = $Errors;
        $NVPResponseArray['REQUESTDATA'] = $NVPRequestArray;
        $NVPResponseArray['RAWREQUEST'] = $NVPRequest;
        $NVPResponseArray['RAWRESPONSE'] = $NVPResponse;*/

                                
        return $NVPResponseArray;
    }

    /**
     * Cancel, suspend, or reactivate a recurring payments profile.
     *
     * @access  public
     * @param   mixed[] $DataArray  Array structure of request data.
     * @return  mixed[] Returns an array structure of the PayPal HTTP response params as well as parsed errors and the raw request/response.
     */
    function ManageRecurringPaymentsProfileStatus($DataArray)
    {
        $NVPRequest                      =   $this->_API;
        $NVPRequest['METHOD']            =   'ManageRecurringPaymentsProfileStatus';
        $NVPRequest['VERSION']           =   self::$_version;

        $NVPRequest['PROFILEID']         =   $DataArray['subscr_id'];
        $NVPRequest['ACTION']            =   urlencode( (isset($DataArray['action']) && !empty($DataArray['action'])?ucfirst(strtolower($DataArray['action'])):'Cancel') );
        $NVPRequest['NOTE']              =   urlencode( (isset($DataArray['note']) && !empty($DataArray['note'])?$DataArray['note']:'Profile cancelled at store') );
        

        //$NVPRequest = $this->NVPCredentials . $MRPPSFieldsNVP;
        $NVPResponse = $this->CURLRequest($NVPRequest);
        //$NVPRequestArray = $this->NVPToArray($NVPRequest);
        $NVPResponseArray = $this->NVPToArray($NVPResponse);
        
        $Errors = $this->GetErrors($NVPResponseArray);

        /*$this->Logger($this->LogPath, __FUNCTION__.'Request', $this->MaskAPIResult($NVPRequest));
        $this->Logger($this->LogPath, __FUNCTION__.'Response', $NVPResponse);*/
        if(!empty($Errors))
        {
            $NVPResponseArray['ERRORS'] = true;    
        }

        return $NVPResponseArray;
    }
}