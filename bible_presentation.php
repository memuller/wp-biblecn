<?php
  class BiblePresenter {
    // == array static function build_quotes_for_haml($verse_text, $verse_number)
    // Returns a pre-formated array of verses texts and numbers for use on HAML templates.
    /* Expects: $text => the verse's text
     *          $number => the verse's number
     * Returns: an associative array on the form 'number' => $number and 'text' => $text
     * On failure: failure not possible
     * On error: uncatched
     */

    static function render_to_string($view, $scope=array()){
      $path = BibleReference::plugin_path() . 'views/'; 
      $parser = new HamlParser($path, $path);
      if ( ! empty($scope)) {
        $parser->append($scope);
      }
      return $parser->fetch($view . '.haml') ;
    }

    static function render($view, $scope=array()){
      echo self::render_to_string($view, $scope) ;
    }

    static function make_full_reference($ref, $book_name){
      $space_position = strpos($ref, ' ',1);
      $book_abrev = substr($ref, 0, $space_position);
      $full_reference = preg_replace("/$book_abrev/", $book_name, $ref);
      return $full_reference ;
    }

    static function error(){
      return self::render_to_string('error');
    }

    static function get_reference($ref){
        $result = BibleReference::get_reference($ref);
        if ($result == false) {
          return  self::error();
        }
        $quotes = $result[0];
        $full_reference = self::make_full_reference($ref, $result[1]) ;
        return self::render_to_string('quotes', array('quotes' => $quotes, 'reference' => $ref, 'full_reference' => $full_reference)  );
    }

    static function book_list(){
      $results = BibleReference::list_books();
      $books = array();
      foreach ($results as $result){
        $books[]= array( 'name' => $result->liv_nome, 'abbreviation' => $result->liv_abrev );
      }
      return self::render('books_list', array( 'books' => $books, 'title' => $title  )  );
    }
  }
?>
