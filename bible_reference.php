<?php
class BibleReference {
  const server_url = 'http://integracao.cancaonovamobile.com/ws/biblia/server.php?wsdl';
  
  static function client(){
    return new SoapClient(self::server_url); 
  }

  static function get_book_id($abbr){
    $response = self::client()->listarLivros(array('liv_abrev' => $abbr)) ; 
    return $response[0]->liv_cod  ;
  }

  static function build_quotes_for_haml($text, $number){
    return array( 'number' => $number , 'text' => $text) ;
  }

  static function get_verse_or_verse_range($things, $ref){
    $client = self::client();
    $num_hiphens = substr_count($things[2], '-') ; 
    $quotes = array();
    if( $num_hiphens == 0 ){
      $response = call_user_func_array(array($client, "listarVersiculos" ), $things) ;
      $quotes[] = self::build_quotes_for_haml( $response[0]->ver_conteudo, $things[2] ) ; 
    } else if ( $num_hiphens == 1) {
      $verses = preg_split( '/-/', $things[2]);
      $params = array( $things[0], $things[1], $verses[0], $verses[1]);
      $response = call_user_func_array( array( $client, "listarVersiculosIntervalo"), $params)  ;
      $text = "";
      for ($i = 0; $i < count($response); $i++) {
        $quotes[] = self::build_quotes_for_haml( $response[$i]->ver_conteudo , $i + $verses[0] ) ;
      }
    } else {
      return false ;
    }
    $parser = new HamlParser ;
    $parser->append( array( 'reference' => $ref, 'quotes' => $quotes ));
    $path_to_template = ABSPATH . 'wp-content/plugins/wp-biblecn/layout.haml' ;
    return $parser->fetch($path_to_template);
  }

  static function parse_reference($ref){
    $result = preg_split( '/,/' , trim($ref) ) ;
    $result2 = preg_split( '/ /', trim($result[0]) ) ;
    if (isset($result[1]) == false or isset($result2[0]) == false or isset($result2[1]) == false) {
      return false ;
    }
    $book = self::get_book_id($result2[0]) ; 
    $chapter = $result2[1];
    if (count(';',$result[1]) > 0) {
      $verses = preg_split('/;/', $result[1]) ;
    } else {
      $verses = array($result[1] )  ;
    }

    return array($book, $chapter, $verses, $ref);
  }

  static function get_reference($ref){
    $ref = self::parse_reference($ref) ;
    $text = "" ;
    for ($i = 0; $i < count($ref[2]); $i++) {
      $params = array( $ref[0], $ref[1], $ref[2][$i]);
      $text .= self::get_verse_or_verse_range( $params , $ref[3]) ;
    }
    return $text ; 
  }
}
?>
