<html>
<head>
<style>
	body {font-family:verdana, arial;}
	.portlet { margin:10px; border:solid 1px; width:500px; height:300px; font-size:8px; float:left;}
</style>
</head>
<body>
<?php
	require_once('wsrpcustomer.class.php');

	$myconsumer=new wsrpconsumer('http://wsrp.netunitysoftware.com/WSRPTestService/WSRPTestService.asmx?Operation=WSDL','wsrpproxy.php');
	$registration=$myconsumer->register();
	$registrationHandle=$myconsumer->getRegistrationHandle();

	//RSS FEED
	$portletHandle="22DCEB09-25E0-4d24-9BC9-8ACAF3108567"; 
	$htmlfragment=$myconsumer->render($portletHandle,'view','object');
	print $htmlfragment;

	//STOCK QUOTE
	$portletHandle="22DCEB09-25E0-4d24-9BC9-8ACAF3108567";
	$htmlfragment=$myconsumer->render($portletHandle,'edit','object');
	print $htmlfragment;

	print '<br clear="all"/>';	
	//$response=$myconsumer->getMarkup($portletHandle,'edit');
	//print_r($response);

	//STOCK QUOTE
	$portletHandle="781F3EE5-22DF-4ef9-9664-F5FC759065DB";
	$htmlfragment=$myconsumer->render($portletHandle,'edit','object');
	print $htmlfragment;

	//print '<pre>'; 
	//print_r( $myconsumer->getAvailableServices());	
	//print '</pre>';

	


?>
</body>
</html>
