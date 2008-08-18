<?php
	require_once('wsrpcustomer.class.php');
	
	$myconsumer=new wsrpconsumer('http://wsrp.netunitysoftware.com/WSRPTestService/WSRPTestService.asmx?Operation=WSDL','wsrpproxy.php');
	$myconsumer->request=$myconsumer->decodeRequest($_GET["request"]);
	if (isset($_GET["portletHandle"]))
	{
		//use a new portletHandle
		$portletHandle=$_GET["portletHandle"];
	}
	else
	{
		//use the already defined portlethandle
		$portletHandle=$myconsumer->request["portletContext"]["portletHandle"];
	}
	
	switch ($myconsumer->request["doRequest"])
	{
		case "blockingAction":
			$htmlfragment=$myconsumer->performBlockingInteraction($portletHandle,$request["markupParams"]["mode"],$_POST);
			break;

		case "render":
		default:
			
			switch ($_GET["mode"])
			{
				case "wsrp:edit":
				case "edit":	
					$mode="wsrp:edit";
					break;
				case "wsrp:view":
				case "view":
					$mode="wsrp:view";
					break;
				case "wsrp:help":
				case "help":
					$mode="wsrp:help";
					break;
				default:
					$mode=$request["markupParams"]["mode"];
					break;
			}
			$myconsumer->request["markupParams"]["mode"]=$mode;
			$htmlfragment=$myconsumer->render($portletHandle,$mode,'div');			
			break;
	}
?><html>
<head>
	<style>
		body { font-family:arial, helvetica; margin:0; padding:0; }
		.portlet		{}
		.portlet h1		{font-size:100%; margin:0; padding:0; width:80%; margin-right:100px; overflow:hidden; float:left;}
		.portletControls {position:absolute; top:2px; right:2px;  text-align:right; list-style:none; width:150px; margin:0; }
		.portletControls li { display:inline; font-size:90%;}
		.portletControls .title {font-weight:bold; float:left; width:70%; }
		.portletMarkup  {padding:4px; clear:both; }
	</style>
</head>
<body>
<?php 	
	//print $portletHandle;
	print $htmlfragment; 
?>
</body>
</html>
