<?php

/**
 * WPIT_SOLREXP_POSTEXTRACTOR_CLASS
 *
 * Description
 *
 * @package
 * @author SteveAgl
 * @copyright 2012
 * @version 1.0
 * @access public
 */
class WPIT_SOLREXP_POSTEXTRACTOR_CLASS {

	//A static member variable representing the class instance
	private static $_instance = null;

	protected $options;
    protected $nr_2_extract;
    protected $limit;
    protected $extracted_posts;

    const OPTION_NAME = 'wpit_slrexp_options';

	//Locked down the constructor, therefore the class cannot be externally instantiated
	private function __construct( $extrct = null ) {

        // Leggo la option se esite
        if ( ! $this->options = get_option( self::OPTION_NAME ) ) {
            $this->nr_2_extract = 10;
            $this->limit = 1;
            self::update_option();

        } else {
            $this->nr_2_extract = $this->options['nr_2_extract'];
            $this->limit = $this->options['limit'];
        }

        if ( null != $extrct )
        	$this->nr_2_extract = $extrct;

	}

	//Prevent any object or instance of that class to be cloned
	public function __clone() {
		trigger_error( "Cannot clone instance of Singleton pattern ...", E_USER_ERROR );
	}

	//Prevent any object or instance to be deserialized
	public function __wakeup() {
		trigger_error('Cannot deserialize instance of Singleton pattern ...', E_USER_ERROR );
	}

	//Have a single globally accessible static method
	public static function get_instance( $extrct = null ) {
		if( !is_object(self::$_instance) )
			self::$_instance = new self($extrct);

		return self::$_instance;
	}


	private function update_option() {

        $new_options = array();
        $new_options['nr_2_extract'] = $this->nr_2_extract;
        $new_options['limit'] = $this->limit;
        update_option( self::OPTION_NAME, $new_options );

        return;
	}

	public function reset_option() {

        $new_options = array();
        $new_options['nr_2_extract'] = 25;
        $new_options['limit'] = 1;
        update_option( self::OPTION_NAME, $new_options );

        return;
	}

	public function rewind_page( $num_pages ) {

		$new_limit = $this->limit - (int) $num_pages;
		$new_limit = ( 1 > $new_limit )? 1 : $new_limit;
        $new_options = array();
        $new_options['nr_2_extract'] = $this->nr_2_extract;;
        $new_options['limit'] = $new_limit;
        update_option( self::OPTION_NAME, $new_options );

        return;
	}

	public function option_status() {

        $status_options = get_option( self::OPTION_NAME);
        echo 'Nr. record da estrarre: ' . $status_options['nr_2_extract'] . '<br />';
        echo 'Paginazione: ' . $status_options['limit'] . '<br />';

        return;
	}

	public function get_posts() {

        global $wpdb;

        echo 'Estraggo ' . $this->nr_2_extract . ' da pagina ' . $this->limit . '<br />';

        $args = array (
            'post_type' => 'post',
            'posts_per_page'    => $this->nr_2_extract,
            'paged' => $this->limit,
            'orderby'   => 'ID',
            'order' => 'ASC',
        );

        $query = new WP_Query( $args );

        $this->extracted_posts = $query->posts;

        return $this->extracted_posts;

    }

    public function next_block() {
        $this->limit = $this->limit + 1;
        $this->update_option();
    }

}