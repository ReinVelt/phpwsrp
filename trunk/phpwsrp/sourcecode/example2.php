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

	$myconsumer=new wsrpconsumer('http://portalstandards.oracle.com/wsrp/jaxrpc?WSDL','wsrpproxy.php');
	$registration=$myconsumer->register();
	$serv=$myconsumer->getAvailableServices();
	
	print_r($serv);
	

	
	

?>
</body>
</html>
