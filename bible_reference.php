<?php
require_once('includes/nusoap/nusoap.php');
require_once('includes/haml/HamlParser.class.php');
require_once('bible_presentation.php');

class BibleReference {
  // Mobile CN's Bible soap endpoint.
  const server_url = 'http://integracao.cancaonovamobile.com/ws/biblia/server.php?wsdl';

  // Plugin path.
  static function plugin_path(){
    return ABSPATH . 'wp-content/plugins/wp-biblecn/' ;
  }

  // Returns a SOAP client.
  static function client(){
    return new SoapClient(self::server_url); 
  }
  
  // Returns a nuSOAP client.
  static function nusoap_client(){
    return new nusoap_client(self::server_url);
  }

  // == integer static function get_book_id($abreviation)
  // Returns an book id given its abreviation.
  /* Expects: a standard Brazilian Bible book reference.
   * Returns: the book's ID on the server. MAY not correspond with the Bible's book order.
   * On failure: ??
   * On error: uncatched
   */
  static function get_book_id($abbr){
    $response = self::nusoap_client()->call( 'listarLivros', array('', '', $abbr)) ; 
    return $response['item']['liv_cod']  ;
  }
  
  // == array static function list_books
  // Returns an array of information on all books.
  /* Expects: nothing
   * Returns: an array of book objects.
   * On failure: not possible.
   * On error: uncatched
   */
  static function list_books(){
    return self::client()->listarLivros();
  }

  // == array static function get_verse_or_verse_range($array_with_organized_references, $reference)
  // Returns the text for a corresponding verse or range of verses.
  /* Expects: an array with the organized reference, as provided by parse_reference().
   *          the original's reference text, also provided by parse_reference().
   * Returns: an array of verse arrays whose keys are 'text' and 'number'.
   * On failure: ??
   * On error: uncatched
   */
  static function get_verse_or_verse_range($things, $ref){
    $client = self::client();
    $num_hiphens = substr_count($things[2], '-') ; 
    $quotes = array();

    // If there's only one verse, fetches it and stuffs its text into the array.
    if( $num_hiphens == 0 ){
      $response = call_user_func_array(array($client, "listarVersiculos" ), $things) ;
      $quotes[] = array( 'text'=> $response[0]->ver_conteudo, 'number' => $things[2] ) ; 

    // If there's a verse range, gets everything between them and stuffs them into the array.
    } else if ( $num_hiphens == 1) {
      $verses = preg_split( '/-/', $things[2]);
      $params = array( $things[0], $things[1], $verses[0], $verses[1]);
      $response = call_user_func_array( array( $client, "listarVersiculosIntervalo"), $params)  ;
      $text = "";
      for ($i = 0; $i < count($response); $i++) {
        $quotes[] = array( 'text' => $response[$i]->ver_conteudo , 'number' => $i + $verses[0] ) ;
      }

    // Something weird happened otherwise.
    } else {
      return false ;
    }
    return $quotes ;
  }

  // == array static function parse_reference($ref)
  // Breaks down a standard Bible reference into its elements.
  /* Expects: a string with a Bible reference.
   * Returns: an array in the following format:
   *          ( book_number , chapter_number, (verses), original_reference  )
   *   - the book number is a server-side value that may not be the correct order of books in the Bible.
   *   - 'verses' is an array with the desided verses or verse ranges, even if it's only one.
   *   - original_reference is the reference passed as parameter.
   * On failure: returns false if the reference is invalid.
   * On error: uncatched.
   */
  static function parse_reference($ref){
    $result = preg_split( '/,/' , trim($ref) ) ;
    $result2 = preg_split( '/ /', trim($result[0]) ) ;

    // Tries to catch some badly formated references.
    if (isset($result[1]) == false or isset($result2[0]) == false or isset($result2[1]) == false) {
      return false ;
    }

    $book = self::get_book_id($result2[0]) ; 
    $chapter = $result2[1];
    // If there's more than one verse, split then and stuff them into the array
    if (substr_count($result[1], ';' ) > 0) {
      $verses = preg_split('/;/', $result[1]) ;
    } else {
      $verses = array($result[1] )  ;
    }

    return array($book, $chapter, $verses, $ref);
  }


  // == string static function get_reference($ref)
  // Returns an HTML with the quotations corresponding to a reference.
  // parses it with parse_reference, them feeds each ref to get_verse_or_verse_range
  /* Expects: a string with the Bible reference.
   * Returns: an array of processed quotes.
   * On failure: same as parse_reference/get_verse_or_verse_range
   * On error: uncatched.
   */
  static function get_reference($ref){
    $ref = self::parse_reference($ref) ;
    $quotes = array();
    // Fetches verse(s) for each verse/verse range parsed.
    for ($i = 0; $i < count($ref[2]); $i++) {
      $params = array( $ref[0], $ref[1], $ref[2][$i]);
      $temps = self::get_verse_or_verse_range( $params , $ref[3]) ;
      foreach ($temps as $temp){
        $quotes[]= $temp;
      }
    }
    return $quotes ; 
  }
}
?>
