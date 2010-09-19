<?php
/*
Plugin Name: WP-BibleCN
Plugin URI: http://retrospectiva.deploy.cancaonova.com/projects/por-trás-das-palavras
Description: This plugin allows the insertion of Bible verses quoted in articles. The verses are extracted from Canção Nova's Online Bible service, and are on Brazilian Portuguese.
Author: Matheus E. Muller, for Canção Nova
Version: 0.1
Author URI: http://memuller.com
*/

/*  Copyright 2010  Matheus E. Muller  (email : hello at memuller dot com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

********************************************************************************
*/

class BibleReference {
  const server_url = 'http://integracao.cancaonovamobile.com/ws/biblia/server.php?wsdl';
  
  static function client(){
    return new SoapClient(self::server_url); 
  }

  static function get_book_id($abbr){
    $response = self::client()->listarLivros(array('liv_abrev' => $abbr)) ; 
    return $response[0]->liv_cod  ;
  }

  static function get_verse_or_verse_range($things){
    $client = self::client();
    $num_hiphens = substr_count($things[2], '-') ; 
    if( $num_hiphens == 0 ){
      $response = call_user_func_array(array($client, "listarVersiculos" ), $things) ;
      $text = $response[0]->ver_conteudo ; 
    } else if ( $num_hiphens == 1) {
      $verses = preg_split( '/-/', $things[2]);
      $params = array( $things[0], $things[1], $verses[0], $verses[1]);
      $response = call_user_func_array( array( $client, "listarVersiculosIntervalo"), $params)  ;
      $text = "";
      for ($i = 0; $i < count($response); $i++) {
        $text .= $response[$i]->ver_conteudo ;
      }
    } else {
      return false ;
    }
    return $text ;
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

    return array($book, $chapter, $verses);
  }

  static function get_reference($ref){
    $ref = self::parse_reference($ref) ;
    $text = "" ;
    for ($i = 0; $i < count($ref[2]); $i++) {
      $params = array( $ref[0], $ref[1], $ref[2][$i] );
      $text .= self::get_verse_or_verse_range( $params ) ;
    }
    return $text ; 
  }
}

function biblecn_filter($content) {
  $content = preg_replace("'\[Bible:(.*?)\]'e", "BibleReference::get_reference('\\1')", $content);
  return $content;	

}

if( function_exists('add_filter') && function_exists('add_action') ) {
  if ( isset($_GET['activate']) && $_GET['activate'] == 'true' ){
    add_action( 'init', 'biblecn_install' );
  }
	
	add_filter( 'the_content', 'biblecn_filter' );
	
}
?>
