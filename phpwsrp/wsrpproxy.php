<?php
	require_once('wsrpcustomer.class.php');
	
	$myconsumer=new wsrpconsumer('http://wsrp.netunitysoftware.com/WSRPTestService/WSRPTestService.asmx?Operation=WSDL','wsrpproxy.php');
	$myconsumer->request=$myconsumer->decodeRequest($_GET["request"]);
	if (isset($_GET["portletHandle"]))
	{
		$portletHandle=$_GET["portletHandle"];

	}
	else
	{
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
		.portletControls {background-color:blue; color: white; list-style:none; height:20px; width:100%; margin:0; margin-right:20px;}
		.portletControls li {background-color:silver; border:solid 1px; width:50px; display:inline; }
		.portletMarkup  {margin:4px;}
		.portletMarkup:hover {border:solid 1px;}
	</style>
</head>
<body>
<?php 	
	//print $portletHandle;
	print $htmlfragment; 
?>
</body>
</html>
