<?php
/*
Plugin Name: WP-BibleCN
Plugin URI: http://retrospectiva.deploy.cancaonova.com/projects/por-trás-das-palavras
Description: This plugin allows the insertion of Bible verses quoted in articles. The verses are extracted from Canção Nova's Online Bible service, and are on Brazilian Portuguese.
Author: Matheus E. Muller, for Canção Nova
Version: 0.5
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
// Requires the BibleReference class and the HAML library.
require_once('bible_reference.php'); 

// The filter. Replaces references on the_content with quotations.
function biblecn_filter($content) {
  $content = preg_replace("'\[Bible:(.*?)\]'e", "BiblePresenter::get_reference('\\1')", $content);
  return $content;	

}

// Post-update check for quotes validation.
function check_quotes_are_valid($post_id) {
  $post = get_post($post_id);
}

// Install function, executed when the plugin is enabled.
function biblecn_install(){
    return true ; 
}

// Registers the filter and install functions.
if( function_exists('add_filter') && function_exists('add_action') ) {
  if ( isset($_GET['activate']) && $_GET['activate'] == 'true' ){
    add_action( 'init', 'biblecn_install' );
  }

  function adm_menu(){
    add_submenu_page( 'options-general.php', 'WP-BibleCN Options', 'BibleCN', 'edit_posts', __FILE__  , 'BiblePresenter::book_list'  );
  }

  add_action('admin_menu', 'adm_menu');
  add_filter( 'the_content', 'biblecn_filter' );
  add_action('pre_post_update', 'check_quotes_are_valid');

	
}
?>
