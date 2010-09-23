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
    static function build_quotes_for_haml($text, $number){
      return array( 'number' => $number , 'text' => $text) ;
    }

    static function render_to_string($view, $scope){
      $path = BibleReference::plugin_path() . 'views/'; 
      $parser = new HamlParser($path, $path);
      $parser->append($scope) ;
      return $parser->fetch($view . '.haml') ;
    }

    static function render($view, $scope){
      echo self::render_to_string($view, $scope) ;
    }

    static function get_reference($ref){
      $quotes = BibleReference::get_reference($ref);
      return self::render_to_string('quotes', array('quotes' => $quotes, 'reference' => $ref)  );
    }

    static function book_list(){
      $results = BibleReference::list_books();
      $books = array();
      foreach ($results as $result){
        $books[]= $result->liv_nome . ' -> ' . $result->liv_abrev  ;
      }
      return self::render('books_list', array( 'books' => $books, 'title' => $title  )  );
    }
  }
?>
