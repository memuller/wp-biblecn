<?php
require_once('bible_reference.php');
require_once('includes/haml/HamlParser.class.php');
$response = BibleReference::client()->listarLivros(   )  ;
print_r($response);

?>
