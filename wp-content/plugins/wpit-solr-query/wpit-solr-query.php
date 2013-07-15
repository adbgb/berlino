<?php
/*
plugin name: wpit solr query
plugin uri: http://www.wpitaly.it http://www.goodpress.it
version: 1.0beta
author: Stefano Aglietti for goodpress & wpitaly
description: plugin per gestie vari tipi di interrogazioni su SOLR

copyright 2012 goodpress (email: info@goodpress.it )

    this program is free software; you can redistribute it and/or modify
    it under the terms of the gnu general public license as published by
    the free software foundation; either version 2 of the license, or
    (at your option) any later version.

    this program is distributed in the hope that it will be useful,
    but without any warranty; without even the implied warranty of
    merchantability or fitness for a particular purpose.  see the
    gnu general public license for more details.

    you should have received a copy of the gnu general public license
    along with this program; if not, write to the free software
    foundation, inc., 59 temple place, suite 330, boston, ma  02111-1307  usa

*/

define ('wpit_solrquery_dir_path', plugin_dir_path( __file__ ));
define ('wpit_solrquery_dir_url', plugin_dir_url( __file__ ));

require_once wpit_solrquery_dir_path . 'wpit-solr-blog-classification.php';
require_once wpit_solrquery_dir_path . 'libraries/autoload.php';
require_once wpit_solrquery_dir_path . 'wpit-solr-config.php';

/**
 * WPIT_SOLAR_QUERIES
 *
 * @package
 * @author
 * @copyright SteveAgl
 * @version 2013
 * @access public
 */
class WPIT_SOLAR_QUERIES {

	/**
	 * A static member variable representing the class instance
	 *
	 * @var object|array
	*/
	private static $_instance = null;

	/**
	 * List of alla blog of the network and their channel classification
	 *
	 * @var array
	*/
	private $blog_classification;

	/**
	 * Holds the instance of Solarium class
	 *
	 * @var object
	*/
	private $solarium;

	/**
	 * Holdes the configuration of the SOLR connetion pameters
	 *
	 * @var array
	*/
	private $config;

	/**
	 * Holds the returned array of post from a query
	 *
	 * @var array
	*/
	private $solr_posts;

	/**
	 * Holds the number of post to fetch by a query
	 *
	 * @var int
	*/
	private $solr_post2fetch;

	/**
	 * Holds the number of post returned by a query
	 *
	 * @var int
	*/
	private $solr_posts_count;

	/**
	 * Holds the number of post matching the qeury
	 *
	 * @var int
	*/
	private $solr_total_posts_count;

	/**
	 * Holds the current page extracted
	 *
	 * @var int
	*/
	private $solr_page_number;

	/**
	 * Holds the total number of pages
	 *
	 * @var int
	*/
	private $solr_total_pages;

	/**
	 * Holds statistical data
	 *
	 * @var array
	*/
	private $solr_stats_data;

	/**
	 * Holds the curren post during iterations
	 *
	 * @var array
	*/
	private $solr_post;

	/**
	 * Holds the current array element during iterations
	 *
	 * @var int
	*/
	private $solr_post_index;

	/**
	 * Holds the current array element during iterations
	 *
	 * @var int
	*/
	private $solr_query_string;

	/**
	 * WPIT_SOLAR_QUERIES::__construct()
	 *
	 * Locked down the constructor, therefore the class cannot be externally instantiated
	 *
	 * @return
	 */
	private function __construct() {

        $this->blog_classification = $GLOBALS['blogs_type'];
        $this->config = $GLOBALS['solr_config'];
        $this->solarium = new Solarium\Client($this->config);
        $this->solr_post_index = -1;
        $this->solr_posts_count = 0;
        $this->solr_total_posts_count = 0;
        $this->solr_page_number = 1;
        $this->solr_stats_data = array();
        $this->solr_query_string = '';
	}

	/**
	 * WPIT_SOLAR_QUERIES::__clone()
	 *
	 * Prevent any object or instance of that class to be cloned
	 *
	 * @return
	 */
	public function __clone() {
		trigger_error( "Cannot clone instance of Singleton pattern ...", E_USER_ERROR );
	}

	/**
	 * WPIT_SOLAR_QUERIES::__wakeup()
	 *
	 * Prevent any object or instance to be deserialized
	 *
	 * @return
	 */
	public function __wakeup() {
		trigger_error('Cannot deserialize instance of Singleton pattern ...', E_USER_ERROR );
	}

	/**
	 * WPIT_SOLAR_QUERIES::getInstance()
	 *
	 * Have a single globally accessible static method
	 *
	 * @return
	 */
	public static function getInstance() {
		if( !is_object(self::$_instance) )
			self::$_instance = new self();
		return self::$_instance;
	}

	/**
	 * WPIT_SOLAR_QUERIES::get_last_posts()
	 *
	 * Get the latest posts from SOLR based on some query optional parameters.
	 *
	 * The list of arguments is below:
	 * 		'channel' (string) - The channel to match if the channel doesn't
	 *  exists the return data will be empty
	 * 		'blog' (string) - The blog from where to select data, URL or already
	 *  normalized
	 * 		'days' (int) - the number of day from today to consider for
	 *  selecting posts, 30 is the default value
	 * 		'post_num' (int) - The number of post to retrive from the query
	 *  if none or less than 1 default to 25
	 * 		'fq' (string) - the string to use for filtering the query
	 *
	 * @param string|array $args Optional. Override default arguments.
	 * @return
	 */
	public function get_last_posts( $args = array() ) {

		$this->solr_posts = array();
		$this->solr_posts = array();
		$this->solr_post_index = -1;

		$defaults = array(
			'type'			=> 'blog',
			'channel'		=> 'all',
			'blog'			=> 'all',
			'days'			=> '99999',
			'query'			=> array(),
			'query_extra'	=> array(),
			'post_num'		=> -1,
			'fq'			=> null,
			'page'			=> 1,
			'sort'			=> ''
		);

		$args = wp_parse_args( $args, $defaults );

		// get a select query instance
		$query = $this->solarium->createSelect();

		$query_fields = array();

		switch ( $args['type'] ) {
			case 'image':
				$query_fields[] = 'pictures:[* TO *]';
			break;
			case 'video':
				$query_fields[] = 'video:[* TO *]';
			break;
			default:
		}

		// create the query fields:
		if ( 'all' != $args['channel']) {
			$query_fields[] = 'channel:' . $args['channel'];
		}

		if ( 'all' != $args['blog']) {
			$query_fields[] = 'blogid:' . $this->blog_serialize ($args['blog']);
		}


		if ( -1 != $args['days']) {
			$days = abs($args['days']);
			if ( 0 < $days ) {
				$query_fields[] = $this->date_limit( $days );
			}
		}

		if ( ! empty($args['query']) ) {
			foreach ( $args['query'] as $qstring ) {
   				$query_fields[] = $qstring;
   			}
		}

		if ( ! empty($args['query_extra']) ) {
			foreach ( $args['query_extra'] as $qstring ) {
   				$query_fields[] = $qstring;
   			}
		}

		// set a query
		if ( ! empty ($query_fields) ) {
			$query->setQuery( implode (' AND ', $query_fields ));
		} else {
			$query->setQuery('*:*');
		}

		// create a filterquery
		if ( null !== $args['fq'] ) {
			$query->createFilterQuery('queryfilter')->setQuery($args['fq']);
		}

		// sort the results by price ascending
		if ( '' !== $args['sort'] ) {
			foreach ( $args['sort'] as $field=>$order ) {
   				$query->addSort($field, $order);
   			}
		}


		// set start and rows param (comparable to SQL limit) using fluent interface
		$start = ( $args['page'] * $args['post_num'] ) - $args['post_num'];
		$start = ( 0 < $start ) ? $start : 0;
		$this->solr_page_number = $args['page'];
		if ( -1 != $args['post_num'] && 0 < $args['post_num'] ) {
			$query->setStart($start)->setRows( (int) $args['post_num']);
		} else {
			$query->setStart($start)->setRows(25);
		}
		$this->solr_post2fetch = $args['post_num'];


		// set fields to fetch (this overrides the default setting 'all fields')
		//$query->setFields('*,score');
		$query->setFields('channel,blogid,permalink,title,content,author,displaydate,categories,tags,pictures,video,score');

		// sort the results by price ascending
		$query->addSort('date', $query::SORT_DESC);

		// this executes the query and returns the result
		$resultset = $this->solarium->select($query);

		$c = 0;
		foreach ($resultset as $document) {
			foreach($document AS $field => $value) {
				$this->solr_posts[$c][$field] = $value;
    		}
  			$c++;
		}

		$this->solr_posts_count = count($this->solr_posts);
		$this->solr_total_posts_count = $resultset->getNumFound();
		$this->solr_total_pages = intval ( $this->solr_total_posts_count / $args['post_num'] );

	}

	/**
	 * WPIT_SOLAR_QUERIES::get_solr_stats()
	 *
	 * Get the latest posts from SOLR based on some query optional parameters.
	 *
	 * The list of arguments is below:
	 * 		'type' (string) - The channel to match if the channel doesn't
	 *  exists the return data will be empty
	 *
	 * @param string|array $args Optional. Override default arguments.
	 * @return
	 */
	public function get_solr_stats( $args = array() ) {

		$this->solr_posts = array();

		$defaults = array(
			'type'	=> 'total',
			'days'	=> '99999',
		);

		$args = wp_parse_args( $args, $defaults );

		// get a select query instance
		$query = $this->solarium->createSelect();

		$query_fields = array();

		switch ( $args['type'] ) {
			case 'total':
			default:
				$query_fields[] = '*:*';
					if ( -1 != $args['days']) {
					$days = abs($args['days']);
					if ( 0 < $days ) {
						$query_fields[] = $this->date_limit( $days );
					}
				}
				// set start and rows param (comparable to SQL limit) using fluent interface
				$query->setStart(0)->setRows(1);
				$query->setFields('channel,blogid');

				// this executes the query and returns the result
				$resultset = $this->solarium->select($query);

				$this->solr_total_posts_count = $resultset->getNumFound();
			break;

			case 'channel':
				$query_fields[] = '*:*';
					if ( -1 != $args['days']) {
					$days = abs($args['days']);
					if ( 0 < $days ) {
						$query_fields[] = $this->date_limit( $days );
					}
				}
				// set start and rows param (comparable to SQL limit) using fluent interface
				$query->setStart(0)->setRows(1);

				// get the facetset component
				$facetSet = $query->getFacetSet();

				// create a facet field instance and set options
				$facetSet->createFacetField('channel')->setField('channel');

				$query->setStart(0)->setRows(1);
				$query->setFields('channel,blogid');

				// this executes the query and returns the result
				$resultset = $this->solarium->select($query);

				$facet = $resultset->getFacetSet()->getFacet('channel');

				foreach($facet as $value => $count) {
				    $this->solr_stats_data[$value] = $count;
				}
			break;

			case 'blog':
				$query_fields[] = '*:*';
					if ( -1 != $args['days']) {
					$days = abs($args['days']);
					if ( 0 < $days ) {
						$query_fields[] = $this->date_limit( $days );
					}
				}
				// set start and rows param (comparable to SQL limit) using fluent interface
				$query->setStart(0)->setRows(1);

				// get the facetset component
				$facetSet = $query->getFacetSet();

				// create a facet field instance and set options
				$facetSet->createFacetField('blogs')->setField('blogid');

				$query->setStart(0)->setRows(1);
				$query->setFields('channel,blogid');

				// this executes the query and returns the result
				$resultset = $this->solarium->select($query);

				$facet = $resultset->getFacetSet()->getFacet('blogs');

				foreach($facet as $value => $count) {
				    $this->solr_stats_data[str_replace('-', '.', $value)] = $count;
				}
			break;
		}

	}

	/**
	 * WPIT_SOLAR_QUERIES::blog_serialize()
	 *
	 * Serialize a blog URL, if empty return *
	 *
	 * @param string $name
	 * @return string $serialize_name
	 */
	function blog_serialize ( $name = '' ) {

		$serialize_name = '*';

		if ( '' === $name )
			return $serialize_name;

		$name = str_replace('http://', '', $name);

		if ( key_exists($name, $this->blog_classification ) ) {
			$serialize_name = sanitize_title($name);
		}

		return $serialize_name;
	}

	/**
	 * WPIT_SOLAR_QUERIES::date_limit()
	 *
	 * Set the date limit query
	 *
	 * @param int $days
	 * @return string
	 */
	function date_limit ($days) {
		$days = abs( intval($days));
		if ( 0 != $days ) {
			return 'date:[NOW-' . $days . 'DAY TO NOW]';
		} else {
			return 'date:[NOW]';
		}

	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_have_posts()
	 *
	 * Check if there is post to show in the current solr loop
	 *
	 * @return boolean
	 */
	function solr_have_posts() {

		if ( $this->solr_post_index + 1 < (int) $this->solr_posts_count ) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_the_post()
	 *
	 * 	Set the solr_post array to the current post of the solr loop
	 *
	 * @return boolean
	 */
	function solr_the_post() {
		$this->solr_post_index++;
		$this->solr_post = $this->solr_posts[$this->solr_post_index];
		return true;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_the_post()
	 *
	 * return the current post as a simple array
	 *
	 * @return array
	 */
	function solr_get_the_post() {
		return $this->solr_post;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_total_posts_count()
	 *
	 * return the current post as a simple array
	 *
	 * @return array
	 */
	function solr_get_total_posts_count() {
		return $this->solr_total_posts_count;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_current_page()
	 *
	 * return the current page shown
	 *
	 * @return array
	 */
	function solr_get_current_page() {
		return $this->solr_page_number;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_set_query_string()
	 *
	 * return the current query string
	 *
	 * @return array
	 */
	function solr_set_query_string($query) {
		$this->solr_query_string = $query;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_query_string()
	 *
	 * return the current query string
	 *
	 * @return array
	 */
	function solr_get_query_string() {
		return $this->solr_query_string;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_post2fetch()
	 *
	 * return the current page shown
	 *
	 * @return array
	 */
	function solr_get_post2fetch() {
		return $this->solr_post2fetch;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_total_pages()
	 *
	 * return the current page shown
	 *
	 * @return array
	 */
	function solr_get_total_pages() {
		return $this->solr_total_pages;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_stats()
	 *
	 * return the array of stats
	 *
	 * @return array
	 */
	function solr_get_stats() {
		return $this->solr_stats_data;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_the_title()
	 *
	 * Get the current post title
	 *
	 * @return string
	 */
	function solr_get_the_title() {
		return $this->solr_post['title'];
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_the_title()
	 *
	 * Return or print the title of the curret post inside the before and after
	 * strings if they are set
	 *
	 * @param string $before
	 * @param string $after
	 * @param bool $echo
	 * @return string $title
	 */
	function solr_the_title($before = '', $after = '', $echo = true) {

		$title = $this->solr_get_the_title();

        if ( strlen($title) == 0 )
			return;

        $title = $before . $title . $after;

        if ( $echo )
			echo $title;
        else
			return $title;
	}


	/**
	 * WPIT_SOLAR_QUERIES::solr_get_the_score()
	 *
	 * Get the current post score
	 *
	 * @return string
	 */
	function solr_get_the_score() {
		return $this->solr_post['score'];
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_the_score()
	 *
	 * Return or print the score of the curret post inside the before and after
	 * strings if they are set
	 *
	 * @param string $before
	 * @param string $after
	 * @param bool $echo
	 * @return string $title
	 */
	function solr_the_score($before = '', $after = '', $echo = true) {

		$score = $this->solr_get_the_score();

        $score = $before . $score . $after;

        if ( $echo )
			echo $score;
        else
			return $score;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_permalink()
	 *
	 * Get the current post permalink
	 *
	 * @return string
	 */
	function solr_get_permalink() {
		return $this->solr_post['permalink'];
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_permalink()
	 *
	 * Return or print the permalink of the curret post inside the before and
	 * after strings if they are set

	 * @param bool $echo
	 * @return
	 */
	function solr_permalink($echo = true) {

		$permalink = $this->solr_get_permalink();

        if ( strlen($permalink) == 0 )
        	return;

        if ( $echo )
			echo $permalink;
        else
            return $permalink;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_the_author()
	 *
	 * Get the current post author name
	 *
	 * @return string
	 */
	function solr_get_the_author() {
		return $this->solr_post['author'];
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_the_author()
	 *
	 * Return or print the author name of the curret post inside the before and
	 * after strings ifthey are set

	 * @param string $before
	 * @param string $after
	 * @param bool $echo
	 * @return sting $author
	 */
	function solr_the_author($before = '', $after = '', $echo = true) {

		$author = $this->solr_get_the_author();

        if ( strlen($author) == 0 )
			return;

        $author = $before . $author . $after;

        if ( $echo )
			echo $author;
        else
            return $author;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_the_channel()
	 *
	 * Get the current post channel name
	 *
	 * @return string
	 */
	function solr_get_the_channel() {
		return $this->solr_post['channel'];
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_the_channel()
	 *
	 * Return or print the channel of the curret post inside the before and
	 * after strings if they are set

	 * @param string $before
	 * @param string $after
	 * @param bool $echo
	 * @return string $channel
	 */
	function solr_the_channel($before = '', $after = '', $echo = true) {

		$channel = $this->solr_get_the_channel();

        if ( strlen($channel) == 0 )
			return;

        $channel = $before . $channel . $after;

        if ( $echo )
			echo $channel;
        else
            return $channel;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_the_blogid()
	 *
	 * Get the current post blog id
	 *
	 * @return string
	 */
	function solr_get_the_blogid() {
		return $this->solr_post['blogid'];
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_the_blogid()
	 *
	 * Return or print the blog id of the curret post $type could be
	 * 'raw' (default)that return the simple value or 'url' that return
	 * the full URL of the blog
	 *
	 * @param string $type
	 * @param bool $echo
	 * @return string $blogid
	 */
	function solr_the_blogid($type = 'raw', $echo = true) {

		$blogid = $this->solr_get_the_blogid();

        if ( strlen($blogid) == 0 )
			return;

        if ( 'url' == $type ) {
        	$blogid = 'http://' . str_replace('-', '.', $blogid);
        }

        if ( $echo )
			echo $blogid;
        else
            return $blogid;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_the_date()
	 *
	 * Get the date of the current post in the specific format or based on blog
	 * default
	 *
	 * @param string $d
	 * @return string $the_date
	 */
	function solr_get_the_date($d) {

		$the_date = '';

		if ( '' == $d ){
			$the_date .= mysql2date(get_option('date_format'), $this->solr_post['displaydate']);
		} else {
			$the_date .= mysql2date($d, $this->solr_post['displaydate']);
		}

		return $the_date;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_the_date()
	 *
 	 * Return or print date of the curret post inside the before and after
 	 * strings if they are set, using the defined format $d
	 *
	 * @param string $d
	 * @param string $before
	 * @param string $after
	 * @param bool $echo
	 * @return
	 */
	function solr_the_date($d = '', $before = '', $after = '', $echo = true) {

		$the_date = $this->solr_get_the_date($d);

		$the_date = $before . $the_date . $after;

        if ( $echo )
			echo $the_date;
        else
            return $the_date;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_the_time()
	 *
	 * Get the time of the current in the specific format or based on blog
	 * default
	 *
	 * @param mixed $d
	 * @return
	 */
	function solr_get_the_time($d) {

		$the_time = '';

		if ( '' == $d ){
			$the_time .= mysql2date(get_option('time_format'), $this->solr_post['displaydate']);
		} else {
			$the_time .= mysql2date($d, $this->solr_post['displaydate']);
		}

		return $the_time;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_the_time()
	 *
	 * Return or print time of the curret post inside the before and after
 	 * strings if they are set, using the defined format $d
	 *
	 * @param string $d
	 * @param string $before
	 * @param string $after
	 * @param bool $echo
	 * @return
	 */
	function solr_the_time($d = '', $before = '', $after = '', $echo = true) {

		$the_time = $this->solr_get_the_time($d);

		$the_time = $before . $the_time . $after;

        if ( $echo )
			echo $the_time;
        else
            return $the_time;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_the_category()
	 *
	 * Return the array of the categories for the current post or false if there
	 * is no categories
	 *
	 * @return boolean|array
	 */
	function solr_get_the_category() {
		if ( isset($this->solr_post['categories']) ) {
			return $this->solr_post['categories'];
		} else {
			return false;
		}
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_the_category()
	 *
	 * Return or print the categories for the curret post
	 *
	 * The list of arguments is below:
	 * 		'type' (string) - the format to use to return the categories, could
	 * be html (default) that return a HTML string with an unordered list of
	 * categories or using the $separator if set. php value will return the raw
	 * array of categories
	 * 		'saparator' (string) - the string to use to separe the categories
	 * html string, if none or empty an unordered list will be generated
	 * 		'echo' (boolean) -show the result or return it
	 *
	 * @param string $type
	 * @param string $separator
	 * @param bool $echo
	 * @return boolean|string|none
	 */
	function solr_the_category($type = 'html', $separator = '', $echo = true) {

		$raw_cats = $this->solr_get_the_category();

		if ( ! $raw_cats)
			return false;


		$categories = '';

		if ( 'php' == $type )
			return $raw_cats;

		if ( 'html' != $type )
			return;

		if ( '' != $separator ) {
			$categories = implode($separator, $raw_cats);
		} else {
			$categories .= '<ul><li>';
			$categories .= implode('</li><li>', $raw_cats);
			$categories .= '</li></ul>';
		}

        if ( $echo )
			echo $categories;
        else
            return $categories;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_the_tag()
	 *
	 * Return the array of the tags for the current post or false if there
	 * is no categories
	 *
	 * @return boolean|array
	 */
	function solr_get_the_tag() {
		if ( isset($this->solr_post['tags']) ) {
			return $this->solr_post['tags'];
		} else {
			return false;
		}
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_the_tag()
	 *
	 *
	 * Return or print the tags for the curret post
	 *
	 * The list of arguments is below:
	 * 		'type' (string) - the format to use to return the tags, could be
	 * html (default) that return a HTML string with an unodered list of tags
	 * or using the $separator if set. php value will return the raw
	 * array of tags
	 * 		'saparator' (string) - the string to use to separe the tags html
	 * string, if none  or empty an unordered list will be generated
	 * 		'echo' (boolean) -show the result or return it
	 *
	 * @param string $type
	 * @param string $separator
	 * @param bool $echo
	 * @return boolean|string|none
	 */
	function solr_the_tag($type = 'html', $separator = '', $echo = true) {

		$raw_tags = $this->solr_get_the_tag();

		if ( ! $raw_tags)
			return false;

		$tags = '';

		if ( 'php' == $type )
			return $raw_cats;

		if ( 'html' != $type )
			return;

		if ( '' != $separator ) {
			$tags = implode($separator, $raw_tags);
		} else {
			$tags .= '<ul><li>';
			$tags .= implode('</li><li>', $raw_tags);
			$tags .= '</li></ul>';
		}

        if ( $echo )
			echo $tags;
        else
            return $tags;
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_the_image()
	 *
	 * Return the image for the current post or false if there
	 * is no image
	 *
	 * @return boolean|array
	 */
	function solr_get_the_image() {
		if ( isset($this->solr_post['pictures']) ) {
			return $this->solr_post['pictures'];
		} else {
			return false;
		}
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_the_image()
	 *
	 * Return or print the image for the curret post $type could be
	 * 'html' (default) that return url string of the image or 'php' that
	 * returns the array of the images
	 *
	 * Note: the image is an array for future improvement when in solr there
	 * will be all the images of a post
	 *
	 * @param string $type
	 * @param bool $echo
	 * @return boolean|string|array|none
	 */
	function solr_the_image($type = 'html', $echo = true) {

		$raw_images = $this->solr_get_the_image();
		$image = '';

		if ( ! $raw_images)
			return false;

		if ( 'php' == $type )
			return $raw_images;

		$image = $raw_images[0];

		if ( $echo )
			echo $image;
        else
            return $image;

	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_the_video()
	 *
	 * Return the video for the current post or false if there
	 * is no video
	 *
	 * @return boolean|array
	 */
	function solr_get_the_video() {
		if ( isset($this->solr_post['video']) ) {
			return $this->solr_post['video'];
		} else {
			return false;
		}
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_the_video()
	 *
	 * Return or print the video for the curret post. $type could be
	 * 'html' (default) that return url string of the video, 'img' that returns
	 * the large image preview or 'php' that return the array of the video
	 *
	 * Note: the video is an array for future improvement when in solr there
	 * will be all the videos of a post
	 *
	 * note: at the moment only youtube video are supported and present in solr
	 *
	 * @param string $type
	 * @param bool $echo
	 * @return boolean|string|array|none
	 */
	function solr_the_video($type = 'html', $echo = true) {

		$raw_video = $this->solr_get_the_video();
		$video = '';

		if ( ! $raw_video)
			return false;

		if ( 'php' == $type )
			return $raw_video;

		$video = $raw_video[0];

		if ( 'img' == $type ) {
			if (preg_match('%(\.be/|/embed/|/v/|/watch\?v=)([A-Za-z0-9_-]{5,11})%', $video, $regs)) {
				$video = 'http://i.ytimg.com/vi/' . $regs[2] . '/0.jpg';
				# Successful match
			} else {
				return '';
			}
		}

		if ( $echo )
			echo $video;
        else
            return $video;

	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_blog_name()
	 *
	 * Return the blog name for the current post or false if there
	 * is no name (impossible!!)
	 *
	 * @return boolean|array
	 */
	function solr_get_blog_name() {
		$blog_nicename = $this->solr_the_blogid('raw', false);
		if ( $blog_nicename ) {
			return $blog_nicename;
		} else {
			return false;
		}
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_get_the_content()
	 *
	 * Return the content for the current post or false if there
	 * is no content
	 *
	 * @return boolean|array
	 */
	function solr_get_the_content() {
		if ( isset($this->solr_post['content']) ) {
			return $this->solr_post['content'];
		} else {
			return false;
		}
	}

	/**
	 * WPIT_SOLAR_QUERIES::solr_the_content()
	 *
	 * Return or print the content for the curret post with some optional
	 * parameters.
	 *
	 * The list of arguments is below:
	 * 		'type' (string) - the return data could be 'html' the cleaned
	 * content or 'raw' the content as is in the solr db
	 * 		'words' (string) - The number of words to truncate the content
	 * 		'chars' (string) - The number of characters to truncate the content
	 * 		'end' (string) - a optional string to append at the end of the
	 * content
	 * 		'echo' (boolean) - echo the result or return it
	 *
	 * @param string|array $args
	 * @return boolean|string
	 */
	function solr_the_content ($args = array()) {

		$defaults = array(
			'type'	=> 'html',
			'chars'	=> 0,
			'words'	=> 0,
			'end'	=> '',
			'echo'	=> true,
		);

		$args = wp_parse_args( $args, $defaults );

		$raw_content = $this->solr_get_the_content();

		$content = '';

		if ( ! $raw_content)
			return false;

		if ( 'raw' == $args['type'] )
			return $raw_content;

		$content = $raw_content;



		//clean content
		$content = strip_shortcodes( $content );

		//if words cut to the words number
		if ( 0 < $args['words']) {
			$content = wp_trim_words( $content, $num_words = $args['words'], $more = '' );
		} elseif ( 0 < $args['chars']) {
			//if chars cut to chars lenght
			$content = $content . ' ';
			$content = substr($content,0,$args['chars']);
			$content = substr($content,0,strrpos($content,' '));
		}
		//if there is end add it
		if ( '' != $args['end'])
			$content .= $args['end'];


		if ( $args['echo'] )
			echo $content;
        else
            return $content;

	}

}

add_action( 'template_redirect', 'wpit_solr_query_template_redirect', 1 );

function wpit_solr_query_template_redirect() {
    wp_enqueue_script('suggest');

    // not a search page; don't do anything and return
    // thanks to the Better Search plugin for the idea:  http://wordpress.org/extend/plugins/better-search/
    $search = stripos($_SERVER['REQUEST_URI'], '?s=');
    $autocomplete = stripos($_SERVER['REQUEST_URI'], '?method=autocomplete');

    if ( ($search || $autocomplete) == FALSE ) {
        return;
    }

    if ($autocomplete) {
        $q = stripslashes($_GET['q']);
        $limit = $_GET['limit'];

        s4w_autocomplete($q, $limit);
        exit;
    }

    // If there is a template file then we use it
    if (file_exists(TEMPLATEPATH . '/solr_search.php')) {
        // use theme file
        include_once(TEMPLATEPATH . '/solr_search.php');
    } else if (file_exists( wpit_solrquery_dir_path . 'templates/solr_search.php')) {
        // use plugin supplied file
        include_once( wpit_solrquery_dir_path . 'templates/solr_search.php');
    } else {
    	echo wpit_solrquery_dir_path . 'passo qua';
        // no template files found, just continue on like normal
        // this should get to the normal WordPress search results
        return;
    }

    exit;
}

function wpit_solr_query_search_results() {

	$query = stripslashes($_GET['s']);

	$solr = WPIT_SOLAR_QUERIES::getInstance();

	$solr->solr_set_query_string($query);

	$solr->get_last_posts(array(
		'post_num' => 25,
		'query'	=> array ( 'spell:' . $query ),
		'sort'	=> array(
			'score'	=> 'desc',
			'date'	=> 'desc',
		),
	)
	);

	return $solr;

}

//$pippo = WPIT_SOLAR_QUERIES::getInstance();