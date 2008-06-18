<html>
<head>
<style>
	body {margin:2em; font-family:verdana, arial;}
	.portlet { margin:10px; border:solid 1px; width:500px; height:350px; font-size:8px; float:left;}
</style>
</head>
<body>
<h1>WSRP Customer example</h1>

<?php
	
	require_once('wsrpcustomer.class.php');

	$myconsumer=new wsrpconsumer('http://wsrp.netunitysoftware.com/WSRPTestService/WSRPTestService.asmx?Operation=WSDL','wsrpproxy.php');
	$registration=$myconsumer->register();
	$registrationHandle=$myconsumer->getRegistrationHandle();

	//RSS FEED
	print $myconsumer->render("22DCEB09-25E0-4d24-9BC9-8ACAF3108567",'view','object');
	
	
	//STOCK QUOTE
	print $myconsumer->render("22DCEB09-25E0-4d24-9BC9-8ACAF3108567",'edit','object');
	

	


?>
</body>
</html>
