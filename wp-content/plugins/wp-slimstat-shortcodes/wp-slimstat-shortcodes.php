<?php
/*
Plugin Name: WP SlimStat ShortCodes
Plugin URI: http://wordpress.org/extend/plugins/wp-slimstat-shortcodes/
Description: Adds support for shortcodes to WP SlimStat
Version: 2.1
Author: Camu
Author URI: http://www.duechiacchiere.it/
*/

class wp_slimstat_shortcodes{

	/**
	 * Attaches functions to hooks
	 */
	public static function init() {
		if (class_exists('wp_slimstat')){
			// These filters replace the metatag with the actual HTML code
			add_shortcode('slimstat', array(__CLASS__, 'slimstat_shortcode'), 15);
			
			// TODO: display chart on site
			// add_shortcode('slimstat-chart', array(__CLASS__, 'slimstat_chart'), 15);
		}
	}
	// end init

	/**
	 * Retrieves the information from the database
	 */
	protected function _get_results($_attr = array()){
		// Optional fields and other variables are defined to avoid PHP warnings
		$join_tables = '';
		$table_identifier = wp_slimstat_db::get_table_identifier($_attr['w']);
		if ($table_identifier != 't1.'){
			$join_tables = $table_identifier.'*,';
		}
		if (!isset($_attr['lf'])) $_attr['lf'] = '';
		if (!isset($_attr['lc'])){
			$_attr['lc'] = array($_attr['w']);
		}
		elseif ($_attr['lc'] != '*'){
			$_attr['lc'] = explode(',', $_attr['lc']);
			foreach($_attr['lc'] as $a_column){
				$table_identifier = wp_slimstat_db::get_table_identifier($a_column);
				if ($table_identifier != 't1.' && strpos($join_tables, $table_identifier.'*') === false){
					$join_tables .= $table_identifier.'*,';
				}
			}
		}
		$join_tables = substr($join_tables, 0, -1);

		if (!isset($_attr['s'])) $_attr['s'] = ', ';

		// Load locales
		load_plugin_textdomain('countries-languages', WP_PLUGIN_DIR .'/wp-slimstat/admin/lang', '/wp-slimstat/lang');

		// If a local translation for countries and languages does not exist, use English
		if (!isset($l10n['countries-languages'])){
			load_textdomain('countries-languages', WP_PLUGIN_DIR .'/wp-slimstat/admin/lang/countries-languages-en_US.mo');
		}

		$content = '';
		switch($_attr['f']){
			// Custom SQL: use the lf param to retrieve the data; no syntax check is done!
			case 'custom':
				if (!empty($_attr['lf']))
					return $GLOBALS['wpdb']->query($_attr['lf']);
				break;
			case 'recent':
			case 'popular':
			case 'count':
			case 'count-all':
				// Avoid PHP warnings in strict mode
				$custom_where = '';

				if (strpos($_attr['lf'], 'WHERE:') !== false){
					$custom_where = html_entity_decode(substr($_attr['lf'], 6), ENT_QUOTES, 'UTF-8');
					wp_slimstat_db::init();
				}
				else{
					wp_slimstat_db::init($_attr['lf']);
				}	

				if ($_attr['f'] == 'count')
					return wp_slimstat_db::count_records($custom_where, $_attr['w'], true, $join_tables);
					
				if ($_attr['f'] == 'count-all')
					return wp_slimstat_db::count_records($custom_where, $_attr['w'], true, $join_tables, false);

				$_attr['f'] = 'get_'.$_attr['f'];
				$results = wp_slimstat_db::$_attr['f'](wp_slimstat_db::get_table_identifier($_attr['w']).$_attr['w'], $custom_where, $join_tables);

				// Format results
				if (empty($results)) return $content;

				// What columns to include?
				if ($_attr['lc'] == '*')
					$_attr['lc'] = array_keys($results[0]);

				$home_url = get_home_url();
				foreach($results as $a_result){
					$content .= '<li>';
					foreach($_attr['lc'] as $id_column => $a_column){
						$content .= "<span class='col-$id_column'>";
						switch($a_column){
							case 'post_link':
								$post_id = url_to_postid(strtok($a_result['resource'], '?'));
								if ($post_id > 0)
									$content .= "<a href='{$a_result['resource']}'>".get_the_title($post_id).'</a>';
								else 
									$content .= strtok($a_result['resource'], '?');
								break;
							case 'dt':
								$content .= date_i18n(wp_slimstat_db::$date_time_format, $a_result['dt']);
								break;
							case 'hostname':
								$content .= gethostbyaddr($a_result['ip']);
								break;
							case 'ip':
								$content .= long2ip($a_result['ip']);
								break;
							default:
								$content .= $a_result[$a_column];
								break;
						}
						$content .= $_attr['s'];
					}
					$content = substr($content, 0, strrpos($content, $_attr['s'])).'</li>';
				}
				return "<ul class='slimstat-shortcode {$_attr['f']}-{$_attr['w']}'>$content</ul>";
				break;
			default:
		}
	}
	// end _get_results

	/**
	 * Handles the shortcode to get recent and popular data
	 */
	public static function slimstat_shortcode($_content = ''){
		// Include the library to retrieve the information from the database
		if (file_exists(WP_PLUGIN_DIR."/wp-slimstat/admin/view/wp-slimstat-db.php"))
			include_once(WP_PLUGIN_DIR."/wp-slimstat/admin/view/wp-slimstat-db.php");

		// This function can be associated to both the new shortcode syntax with square brackets, or the old one using HTML comments
		if (is_array($_content)){
			// Look for required fields
			if (empty($_content['f']) || empty($_content['w'])){
				return '<!-- slimstat shortcode error: missing parameter -->';
			}
			else{
				// Get the data and replace the placeholder
				return self::_get_results($_content);
			}
		}

		// Find the shortcodes and process them
		preg_match_all('/<!--slimstat (.+)-->/U', $_content, $matches);

		foreach($matches[1] as $a_idx => $a_shortcode){
			$attr = shortcode_parse_atts($a_shortcode);

			// Look for required fields
			if (empty($attr['f']) || empty($attr['w'])){
				$_content = str_replace($matches[0][$a_idx], '<!-- slimstat shortcode error: missing parameter -->', $_content);
			}
			else{
				// Get the data and replace the placeholder
				$_content = str_replace($matches[0][$a_idx], self::_get_results($attr), $_content);
			}
		}
		return $_content;
	}
	// end slimstat_shortcode
	
	/**
	 * Handles the shortcode to embed the chart
	 *
	public static function slimstat_chart($_content = ''){
		if (!file_exists(WP_PLUGIN_DIR."/wp-slimstat/admin/view/wp-slimstat-db.php")) return $_content;

		// Include the library to retrieve the information from the database
		include_once(WP_PLUGIN_DIR."/wp-slimstat/admin/view/wp-slimstat-db.php");
		include_once(WP_PLUGIN_DIR."/wp-slimstat/admin/view/wp-slimstat-boxes.php");
		
		wp_enqueue_script('slimstat_flot', plugins_url('/wp-slimstat/admin/js/jquery.flot.min.js', dirname(__FILE__)), array('jquery'), '0.7', false);
		wp_enqueue_script('slimstat_flot_navigate', plugins_url('/wp-slimstat/admin/js/jquery.flot.navigate.min.js', dirname(__FILE__)), array('jquery','slimstat_flot'), '0.7', false);
		wp_enqueue_script('slimstat_admin', plugins_url('/wp-slimstat/admin/js/slimstat.admin.js', dirname(__FILE__)), array('jquery-ui-dialog'), '1.0', false);

		if (!is_array($_content)) return $_content;
		
		wp_slimstat_db::init($_content['lf']);	

		ob_start();
		wp_slimstat_boxes::show_chart('slim_p1_01', wp_slimstat_db::extract_data_for_chart('COUNT(t1.ip)', 'COUNT(DISTINCT(t1.ip))'), array(__('Pageviews','wp-slimstat-view'), __('Unique IPs','wp-slimstat-view')));
		$chart_html = ob_get_contents();
		ob_end_clean();

		return str_replace('<div id="chart-placeholder">', '<div id="chart-placeholder" style="width:300px;height:300px">', $chart_html);
	}
	// end slimstat_shortcode */
}
// end of class declaration

// Bootstrap
if (function_exists('add_action')) add_action('init', array('wp_slimstat_shortcodes', 'init'), 5);