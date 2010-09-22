<?php
require_once('bible_reference.php');
require_once('includes/haml/HamlParser.class.php');
require_once('includes/nusoap/nusoap.php');
//isto funciona (c/ nusoap)
$client = new nusoap_client( BibleReference::server_url  );
$response = $client->call( 'listarLivros', array( '', '', 'Ex' ) );
echo ($response['item']['liv_nome']) ;
?> <br/> <?php
// isto não funciona (retorna sempre o gênesis)
$client = new SoapClient (BibleReference::server_url);
$response = $client->listarLivros( array( '', '', 'Ex'  )  )   ;
echo ($response[0]->liv_nome);

?>
