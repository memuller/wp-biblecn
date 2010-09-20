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
require_once('bible_reference.php'); 
require_once('includes/haml/HamlParser.class.php');

function biblecn_filter($content) {
  $content = preg_replace("'\[Bible:(.*?)\]'e", "BibleReference::get_reference('\\1')", $content);
  return $content;	

}

function biblecn_install(){
    return true ; 
}

if( function_exists('add_filter') && function_exists('add_action') ) {
  if ( isset($_GET['activate']) && $_GET['activate'] == 'true' ){
    add_action( 'init', 'biblecn_install' );
  }
	
  add_filter( 'the_content', 'biblecn_filter' );
	
}
?>

