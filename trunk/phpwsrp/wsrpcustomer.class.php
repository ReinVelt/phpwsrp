<?php
	/**
	* WSRP Consumer for PHP - PROOF OF CONCEPT
	*
	* @description	This class provides all required methods to implement a wsrp porlet customer
	*
	* This source file is subject to the new BSD license that is bundled
	* with this package in the file LICENSE.txt. It is also available through
	* the world-wide-web at this URL: http://framework.zend.com/license/new-bsd
	* If you did not receive a copy of the license and are unable to obtain
	* it through the world-wide-web, please send an email to rein@velt.org so
	* we can email you a copy immediately.
	*
	* To understand this class please read the the wsrp portlet primer from oasis-open.org
	* http://www.oasis-open.org/committees/download.php/10539/wsrp-primer-1.0.html#scen_discover_portlets
	*
	* @package		PHP_Portlet_Consumer
	* @author		Rein Velt
	* @copyright	Copyright (c) 2008 Rein Velt (http://velt.org)
	* @version 		Release: @package_version@
	* @link			http://velt.org/research/phpwsrp
	**/



	// @TODO: fix bug: the portles looses its porletHandle after it is cloned and a form is submitted.
	// it will cause an crash when submitting the cloned form the second time. This is fixed by 
	// preventing the ported to be submitted for the second time.
	
	

	class wsrpconsumer
	{
		
		private $wsdl;
		private $proxyUrl;
		private $location;
		private $options;
		public $request;
		private $availableServices;
		
		/**
		*  constructor - initialize the wsrp consumer object 
		*  @param	string		location - location of the webservice
		*  @param	string		proxyUrl - url of the consumer proxy
		*  @return	object		server response to the register request
		**/
		function __construct($location,$proxyUrl)
		{
			//initialize class variables and default request structure
			$this->location=$location;
			$this->proxyUrl=$proxyUrl;
			$this->options=array("trace"=>1);
			$this->wsdl = new SoapClient($this->location,$this->options);
			$this->request=array();
			$this->request["consumerName"]="test";
			$this->request["consumerAgent"]="PhpWsrpCustomer1.0";
			$this->request["methodGetSupported"]=true;       
			$this->request["consumerModes"] = array('wsrp:view','wsrp:edit','wsrp:help');  
			$this->request["consumerWindowStates"] = array('wsrp:normal');
			$this->request["consumerUserScopes"] = array();
			$this->request["customUserProfileData"] = array();
			$this->request["registrationProperties"] = array();
			$this->request["extensions"] = array();
		}



		/**
		*  Register a the portal and receive a registrationHandle
		*  @return	object	server response to the register request
		**/		
		public function register()
		{
			//DO REQUEST AND GET A REGISTRATIONHANDLE/ID BACK
			$request=$this->request;
			$response=$this->wsdl->register($request);
			$this->request["registrationHandle"]=$response->registrationHandle;
			return $response;
		}



		/**
		*  get the registrationHandle from the portal
		*  @return	string	registrationHandle
		**/	
		public function getRegistrationHandle()
		{
			return $this->request["registrationHandle"];
		}



		/**
		*  getAvailableServices - get a list of available services with metainfo from the current portal
		*  @return	object	server response to the getAvailableServices request
		**/	
		public function getAvailableServices()
		{
			//get available services
			$request=$this->request;
			$response=$this->wsdl->getServiceDescription($request);
			$this->availableServices=$response;
			return $response;
		}


	
		/**
		*  getMarkup - Get markup from a specified portlet
		*  @param	string	portletHandle (e.g. 22DCEB09-25E0-4d24-9BC9-8ACAF3108567)
		*  @param	string  mode (view|edit)
		*  @return	object	server response to the getMarkup request
		**/	
		public function getMarkup($portletHandle,$mode='view')
		{
			//print $portletHandle;
			//get markup from portlet
			$request=$this->request;
			$request["registrationContext"]=array("registrationHandle"=>$this->request["registrationHandle"]);
			$request["portletContext"]=array("portletHandle" => $portletHandle);
			$request["runtimeContext"]=array("userAuthentication" => '');
			$request["markupParams"]=array();
			$request["markupParams"]["secureClientCommunication"]=false;
			$request["markupParams"]["locales"]=array(0 => "en-US");
			$request["markupParams"]["mimeTypes"]=array(0 => "text/HTML");
			$request["markupParams"]["mode"]=$this->validateMode($mode);
			$request["markupParams"]["windowState"]="wsrp:normal";
			$response=$this->wsdl->getMarkup($request);
			$this->request=$request;
			return $response;
		}


		/**
		*  validateMode - make node value fully qualified 
		*  @param	string	unvalidated mode string (e.g. edit or view)
		*  @return	string	validated mode (e.g. wsrp:edit or wstp:view)
		**/
		private function validateMode($mode)
		{
			switch ($mode)
			{
				case "wsrp:edit":
				case "edit":      $mode="wsrp:edit"; break;
				case "wsrp:view":
				case "view":      $mode="wsrp:view"; break;
				case "wsrp:help":
				case "help":      $mode="wsrp:help"; break;
				default:          $mode="wsrp:view"; break;
			}
			return $mode;
		}


		/**
		*  performBlockingInteraction - Send form fields to the portlet
		*  @param	string	portletHandle (e.g. 22DCEB09-25E0-4d24-9BC9-8ACAF3108567)
		*  @param	array	GET or POST array containing fields
		*  @return	object	server response to the getMarkup request
		**/
		public function performBlockingInteraction($portletHandle,$mode,$parameterArray)
		{
			//send data to portlet (post)
			$request=$this->request;
			$request["registrationContext"]=array("registrationHandle"=>$this->request["registrationHandle"]);
			$request["portletContext"]=array("portletHandle" => $portletHandle);
			$request["runtimeContext"]=array("userAuthentication" => '','sessionID'=>'12345');
			$request["markupParams"]=array();
			$request["markupParams"]["secureClientCommunication"]=false;
			$request["markupParams"]["locales"]=array(0 => "en-US");
			$request["markupParams"]["mimeTypes"]=array(0 => "text/HTML");
			$request["markupParams"]["mode"]=$this->validateMode($mode);
			$request["markupParams"]["windowState"]="wsrp:normal";
			$request["interactionParams"]=array();
			if ($this->request["interactionParams"]["portletStateChange"]=="cloneBeforeWrite")
			{
				//an already cloned portlet is writabled and cannot be cloned again
				$request["interactionParams"]["portletStateChange"]="readWrite";
			}	
			else
			{
				//the portlet should be cloned when portletId is original and parameters/fields are changed
				$request["interactionParams"]["portletStateChange"]="cloneBeforeWrite";
			} 	

			//parse form field parameters	
			$request["interactionParams"]["formParameters"]=array();
			while (list($paramName,$paramValue)=each($parameterArray))
			{
				$request["interactionParams"]["formParameters"][]=array("name"=> $paramName, "value"=>$paramValue);
			}		

			//send the form to the server and get the response
			$response=$this->wsdl->performBlockingInteraction($request);

			//parse the response and update the request parameters if they are updated
			if (isset($response->updateResponse->portletContext->portletHandle))
			{
				$portletHandle=$response->updateResponse->portletContext->portletHandle;
				$this->request["portletContext"]=array("portletHandle"=> $portletHandle);
				$this->request["markupParams"]["mode"]=$response->newMode;
			}
		
			//do a render request and return the xhtml fragment of the result
			$response=$this->render($portletHandle,$this->request["markupParams"]["mode"],'div');
			return $response;
		}



		/**
		*  render - do getMarkup request, rewrite the urls and put it in a object or div-container
		*  the div-container has the problem that user will navigate out of the portlet when
		*  the portlet uses external links. The object container does not have this problem and you
		*  can also use non text/html portlets because the content-type will be passed to the
		*  object container.
		*  @param	string	portletHandle (e.g. 22DCEB09-25E0-4d24-9BC9-8ACAF3108567)
		*  @param	string  mode (view|edit|help)
		*  @param	string  containerType (object|div)
		*  @return	object	server response to the getMarkup request
		**/
		public function render($portletHandle,$mode,$containerType='div')
		{
			$mode=$this->validateMode($mode);
			$response=$this->getMarkup($portletHandle,$mode);
			$markup=$response->markupContext->markupString;
			$windowState=$this->request["markupParams"]["windowState"];
			$this->request["markupParams"]["mode"]=$mode;;
			$this->request["portletContext"]["portletHandle"]=$portletHandle;
			$htmlFragmentBody=$this->urlRewrite($markup);	
			if ($containerType=='div')
			{
				//put the portlet in a div-tag
				$htmlFragment='<div id="portlet'.$portletHandle.'" class="portlet" title="'.$response->markupContext->preferredTitle.'">';
				$htmlFragment.=$this->renderControls($response->markupContext->preferredTitle,$mode,$windowState);
				$htmlFragment.='<div class="portletMarkup">';
				$htmlFragment.=$htmlFragmentBody;
				$htmlFragment.='</div>';
				$htmlFragment.='</div>';
			}
			else
			{
				//put the portlet in an object tag and run it via the wsrp customer proxy
				$contentType=$response->markupContext->mimeType;
				$htmlFragment='<object id="portlet'.$portletHandle.'" class="portlet portletMarkup" title="'.$response->markupContext->preferredTitle.'"';
				$htmlFragment.=' data="'.$this->proxyUrl.'?mode='.$mode.'&portletHandle='.$portletHandle.'&request='.$this->encodeRequest($this->request).'" type="'.$contentType.'">';
				$htmlFragment.=$htmlFragmentBody; //insert markup for disabled webbrowsers
				$htmlFragment.='</object>';
			}			
			return $htmlFragment;
		}


		
		/**
		*  renderControls - create xhtml markup for the edit/view/minimize/maximize/help/close indicators
		*  @param	string	current mode
		*  @param	string	current windowState
		*  @return	string	xhtml fragment containing markup for indicators
		**/
		private function renderControls($title,$mode,$windowState)
		{
			$htmlFragment='<div class="portlet">';
			$htmlFragment.='<h1 class="title">'.$title.'</h1>';
			$htmlFragment.='<ul class="portletControls" title="portlet controls">';
			switch($mode)
			{
				case "wsrp:view":
				case "view"		: $newmode="wsrp:edit"; break;
				case "wsrp:edit":
				case "edit"		: $newmode="wsrp:view"; break;
				case "wsrp:help":
				case "help"		: $newmode="wsrp:help"; break;
				default			: break;
			}
			$encodedRequest=$this->encodeRequest($this->request);
			$htmlFragment.='<li><a href="'.$this->proxyUrl.'?mode=view&request='.$encodedRequest.'" title="click to view">[view]</a></li>';
			$htmlFragment.='<li><a href="'.$this->proxyUrl.'?mode=edit&request='.$encodedRequest.'" title="click to edit">[edit]</a></li>';
			$htmlFragment.='<li><a href="'.$this->proxyUrl.'?mode=help&request='.$encodedRequest.'" title="click to help">[help]</a></li>';
			//$htmlFragment.='<li><a href="'.$this->proxyUrl.'?request='.$encodedRequest.'" title="windowState">'.$windowState.'</a></li>';
			//$htmlFragment.='<li><a href="'.$this->proxyUrl.'?unregister=1&request='.$encodedRequest.'" title="unregister">X</a></li>';
			$htmlFragment.='</ul>';
			$htmlFragment.='</div>';
			return $htmlFragment;		
		}

		

		/**
		*  encodeRequest - encode request object to use with a link
		*  @param	string	markup with url placeholders
		*  @return	string	markup with rewritten urls
		**/
		public function encodeRequest($request)
		{
			return base64_encode(serialize($request));
		}

		

		/**
		*  decodeRequest - decode string and convert it to a requestobject
		*  @param	string	markup with url placeholders
		*  @return	string	markup with rewritten urls
		**/
		public function decodeRequest($encodedRequest)
		{
			return unserialize(base64_decode($encodedRequest));
		}
	
			
		
		/**
		*  urlRewrite - rewrite urls
		*  @param	string	markup with url placeholders
		*  @return	string	markup with rewritten urls
		**/
		function urlRewrite($markup)
		{
			$replace=array();
			preg_match_all('"wsrp_rewrite\?(.*)/wsrp_rewrite"',$markup,$matches);
			while (list($index,$data)=each($matches[1]))
			{
				$replace['wsrp_rewrite?'.$data.'/wsrp_rewrite']=$this->translateResource($data);
			}
			return strtr($markup,$replace);
		}


		/**
		*  translateResource - translate url placeholders to real urls
		*  @param	string	placeholder url
		*  @return	string	rewritten url
		**/
		private function translateResource($resourceUri)
		{
			//translate encoded wsrp-url to a valid url
			$request=$this->request;
			$paramList=explode("&amp;",$resourceUri);
			while (list($index,$data)=each($paramList))
			{
				$keyvalue=explode("=",$data);
				$parameters[$keyvalue[0]]=$keyvalue[1];
			}
			switch ($parameters["wsrp-urlType"])
			{
				case "resource": 
					$resourceUrl=urldecode(rawurldecode($parameters["wsrp-url"]));
					break;
				
				case "render":
					$request["doRequest"]="render";
					$request["markupParams"]["navigationalState"]="add";
					$resourceUrl=$this->proxyUrl.'?request='.$this->encodeRequest($request);
					break;
				case "blockingAction":
					$request["doRequest"]="blockingAction";
					$request["markupParams"]["mode"]=$parameters["wsrp-mode"];
					$request["markupParams"]["windowState"]=$parameters["wsrp-windowState"];
					$resourceUrl=$this->proxyUrl.'?request='.$this->encodeRequest($request);
					break;
				default:
					$resourceUrl=$resourceUri;
					break;
			}
			return $resourceUrl;
		}

	}


	
	

	
?>
