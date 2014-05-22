<?php 

if( !class_exists('acf_compatibility') ):

class acf_compatibility {
	
	/*
	*  __construct
	*
	*  description
	*
	*  @type	function
	*  @date	30/04/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function __construct() {
		
		// all field
		add_filter('acf/get_valid_field',					array($this, 'get_valid_field'), 20, 1);
		
		
		// specific fields
		add_filter('acf/get_valid_field/type=image',		array($this, 'get_valid_image_field'), 20, 1);
		add_filter('acf/get_valid_field/type=file',			array($this, 'get_valid_image_field'), 20, 1);
		add_filter('acf/get_valid_field/type=wysiwyg',		array($this, 'get_valid_wysiwyg_field'), 20, 1);
		add_filter('acf/get_valid_field/type=date_picker',	array($this, 'get_valid_date_picker_field'), 20, 1);
		
		
		// all field groups
		add_filter('acf/get_valid_field_group',				array($this, 'get_valid_field_group'), 20, 1);
		
		
		// settings
		add_action('after_setup_theme',						array($this, 'after_setup_theme'), 20);
	}
	
	
	/*
	*  after_setup_theme
	*
	*  description
	*
	*  @type	function
	*  @date	19/05/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function after_setup_theme() {
		
		if( defined('ACF_LITE') && ACF_LITE ) {
			
			acf_update_setting('show_admin', false);
			
		}
			
	}
	
	
	/*
	*  get_valid_field
	*
	*  This function will provide compatibility with ACF4 fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function get_valid_field( $field ) {
		
		// conditional logic has changed
		if( isset($field['conditional_logic']['status']) ) {
			
			// extract logic
			$logic = acf_extract_var( $field, 'conditional_logic' );
			
			
			// disabled
			if( !empty($logic['status']) ) {
				
				// reset
				$field['conditional_logic'] = array();
				
				
				// vars
				$group = 0;
		 		$all_or_any = $logic['allorany'];
		 		
		 		
		 		// loop over rules
		 		if( !empty($logic['rules']) ) {
			 		
			 		foreach( $logic['rules'] as $rule ) {
				 		
					 	// sperate groups?
					 	if( $all_or_any == 'any' ) {
					 	
						 	$group++;
						 	
					 	}
					 	
					 	
					 	// add to group
					 	$field['conditional_logic'][ $group ][] = $rule;
			 	
				 	}
				 	
		 		}
			 	
			 	
			 	// reset keys
				$field['conditional_logic'] = array_values($field['conditional_logic']);
				
				
			} else {
				
				$field['conditional_logic'] = 0;
				
			}
		 	
		}
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  get_valid_image_field
	*
	*  This function will provide compatibility with ACF4 fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function get_valid_image_field( $field ) {
		
		// save_format is now return_format
		if( !empty($field['save_format']) ) {
			
			$field['return_format'] = acf_extract_var( $field, 'save_format' );
			
		}
		
		
		// object is now array
		if( $field['return_format'] == 'object' ) {
			
			$field['return_format'] = 'array';
			
		}
		
		
		// return
		return $field;
	}
	
	
	/*
	*  get_valid_wysiwyg_field
	*
	*  This function will provide compatibility with ACF4 fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function get_valid_wysiwyg_field( $field ) {
		
		// media_upload is now numeric
		if( $field['media_upload'] === 'yes' ) {
			
			$field['media_upload'] = 1;
			
		} elseif( $field['media_upload'] === 'no' ) {
			
			$field['media_upload'] = 0;
			
		}
		
		
		// return
		return $field;
	}
	
	
	/*
	*  get_valid_date_picker_field
	*
	*  This function will provide compatibility with ACF4 fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function get_valid_date_picker_field( $field ) {
		
		// v4 used date_format
		if( !empty($field['date_format']) ) {
			
			// extract vars
			$date_format = acf_extract_var( $field, 'date_format' );
			$display_format = acf_extract_var( $field, 'display_format' );
			
			
			// php_to_js
			$php_to_js = array(
				
				// Year
				'Y'	=> 'yy',	// Numeric, 4 digits 								1999, 2003
				'y'	=> 'y',		// Numeric, 2 digits 								99, 03
				
				
				// Month
				'm'	=> 'mm',	// Numeric, with leading zeros  					01–12
				'n'	=> 'm',		// Numeric, without leading zeros  					1–12
				'F'	=> 'MM',	// Textual full   									January – December
				'M'	=> 'M',		// Textual three letters    						Jan - Dec 
				
				
				// Weekday
				'l'	=> 'DD',	// Full name  (lowercase 'L') 						Sunday – Saturday
				'D'	=> 'D',		// Three letter name 	 							Mon – Sun 
				
				
				// Day of Month
				'd'	=> 'dd',	// Numeric, with leading zeros						01–31
				'j'	=> 'd',		// Numeric, without leading zeros 					1–31
				'S'	=> '',		// The English suffix for the day of the month  	st, nd or th in the 1st, 2nd or 15th. 
			);
			
			foreach( $php_to_js as $php => $js ) {
			
				$date_format = str_replace($js, $php, $date_format);
				$display_format = str_replace($js, $php, $display_format);
				
			}
			
			
			// append settings
			$field['return_format'] = $date_format;
			$field['display_format'] = $display_format;
			
		}
		
		
		// return
		return $field;
	}
	
	
	/*
	*  get_valid_field_group
	*
	*  This function will provide compatibility with ACF4 field groups
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field_group (array)
	*  @return	$field_group
	*/
	
	function get_valid_field_group( $field_group ) {
		
		// bail ealry if field group contains key ( is ACF5 )
		if( ! empty($field_group['key']) ) {
			
			return $field_group;
			
		}
		
		
		// global
		global $wpdb;
		
		
		// add missing key
		$field_group['key'] = empty($field_group['id']) ? uniqid('group_') : 'group_' . $field_group['id'];
		
		
		// extract options
		if( !empty($field_group['options']) ) {
			
			$options = acf_extract_var($field_group, 'options');
			
			$field_group = array_merge($field_group, $options);
			
		}
		
		
		// some location rules have changed
		if( !empty($field_group['location']) ) {
			
			// param changes
		 	$param_replace = array(
		 		'taxonomy'		=> 'post_taxonomy',
		 		'ef_media'		=> 'attachment',
		 		'ef_taxonomy'	=> 'taxonomy',
		 		'ef_user'		=> 'user_role',
		 	);
		 	
		 	
			
			foreach( $field_group['location'] as $group_i => $group ) {
				
				if( !empty($group) ) {
					
					foreach( $group as $rule_i => $rule ) {
						
					 	if( array_key_exists($rule['param'], $param_replace) ) {
						 	
						 	$field_group['location'][ $group_i ][ $rule_i ]['param'] = $param_replace[ $rule['param'] ];
						 	
					 	}
					 	
					 	
					 	// category / taxonomy terms are saved differently
					 	if( $rule['param'] == 'post_category' || $rule['param'] == 'post_taxonomy' ) {
						 	
						 	if( is_numeric($rule['value']) ) {
							 	
							 	$term_id = $rule['value'];
							 	$taxonomy = $wpdb->get_var( $wpdb->prepare( "SELECT taxonomy FROM $wpdb->term_taxonomy WHERE term_id = %d LIMIT 1", $term_id) );
							 	$term = get_term( $term_id, $taxonomy );
							 	
							 	// update rule value
							 	$field_group['location'][ $group_i ][ $rule_i ]['value'] = "{$term->taxonomy}:{$term->slug}";
							 	
						 	}
						 	// if
						 	
					 	}
					 	// if
						
					}
					// foreach
					
				}
				// if
				
			}
			// foreach
			
		}
		// if
		
		
		// change layout to style
		if( !empty($field_group['layout']) ) {
		
			$field_group['style'] = acf_extract_var($field_group, 'layout');
			
		}
		
		
		// change no_box to seamless
		if( $field_group['style'] == 'no_box' ) {
		
			$field_group['style'] = 'seamless';
			
		}
		
		
		//return
		return $field_group;
	}
	
}

/*
*  acf_compatibility
*
*  @type	function
*  @date	22/05/2014
*  @since	5.0.0
*
*  @param	N/A
*  @return	(object)
*/

function acf_compatibility()
{
	global $acf_compatibility;
	
	if( !isset($acf_compatibility) )
	{
		$acf_compatibility = new acf_compatibility();
	}
	
	return $acf_compatibility;
}


// initialize
acf_compatibility();

endif; // class_exists check

?>