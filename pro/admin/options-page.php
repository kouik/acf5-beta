<?php

class acf_pro_options_page {
	
	var $view;
	
	
	/*
	*  __construct
	*
	*  Initialize filters, action, variables and includes
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// defualts
		$this->view = array(
			'have_fields'	=> 1,
			'slug'			=> ''
		);
		
		
		// add parent page
		acf_add_options_page(array(
			'page_title' 	=> __('Options','acf'),
			'menu_title'	=> __('Options','acf'),
			'menu_slug' 	=> 'acf-options',
			'capability'	=> 'edit_posts',
			'parent_slug'	=> false,
			'position'		=> '90.025',
			'icon_url'		=> false,
		));
			
		
		// actions
		add_action('admin_menu', array($this,'admin_menu'), 99, 0);
		
		
		// filters
		add_filter( 'acf/location/rule_types', 					array($this, 'rule_types'), 10, 1 );
		add_filter( 'acf/location/rule_values/options_page',	array($this, 'rule_values'), 10, 1 );
		add_filter( 'acf/location/rule_match/options_page',		array($this, 'rule_match'), 10, 3 );
	}
		
	
	/*
	*  acf_location_rules_types
	*
	*  this function will add "Options Page" to the ACF location rules
	*
	*  @type	function
	*  @date	2/02/13
	*
	*  @param	{array}	$choices
	*  @return	{array}	$choices
	*/
	
	function rule_types( $choices ) {
		
	    $choices[ __("Forms",'acf') ]['options_page'] = __("Options Page",'acf');
		
	    return $choices;
	}
	
	
	/*
	*  acf_location_rules_values_options_page
	*
	*  this function will populate the available pages in the ACF location rules
	*
	*  @type	function
	*  @date	2/02/13
	*
	*  @param	{array}	$choices
	*  @return	{array}	$choices
	*/
	
	function rule_values( $choices ) {
		
		// vars
		$pages = acf_get_options_pages();
		
		
		if( !empty($pages) )
		{
			foreach( $pages as $page )
			{
				$choices[ $page['menu_slug'] ] = $page['menu_title'];
			}
		}
		
		
	    return $choices;
	}
	
	
	/*
	*  rule_match
	*
	*  description
	*
	*  @type	function
	*  @date	24/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function rule_match( $match, $rule, $options ) {
		
		// vars
		$options_page = false;
		
		
		// $options does not contain a default for "options_page"
		if( isset($options['options_page']) )
		{
			$options_page = $options['options_page'];
		}
		

		if( !$options_page )
		{
			global $plugin_page;
			
			$options_page = $plugin_page;
		}
		
		
		// match
		if( $rule['operator'] == "==" )
        {
        	$match = ( $options_page === $rule['value'] );
        }
        elseif( $rule['operator'] == "!=" )
        {
        	$match = ( $options_page !== $rule['value'] );
        }
        
        
        // return
        return $match;
        
    }
    
	
	/*
	*  admin_menu
	*
	*  description
	*
	*  @type	function
	*  @date	24/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function admin_menu() {
		
		// vars
		$pages = acf_get_options_pages();
		
		
		if( !empty($pages) )
		{
			foreach( $pages as $page )
			{
				// vars
				$slug = '';
				
				
				if( empty($page['parent_slug']) )
				{
					// add page
					$slug = add_menu_page( $page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], array($this, 'html'), $page['icon_url'], $page['position'] );
					
				}
				else
				{
					// add page
					$slug = add_submenu_page( $page['parent_slug'], $page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], array($this, 'html') );
					
				}
				
				
				// actions
				add_action("load-{$slug}", array($this,'admin_load'));
			}
		}
	}
	
	
	/*
	*  load
	*
	*  @description: 
	*  @since: 3.6
	*  @created: 2/02/13
	*/
	
	function admin_load() {
		
		// globals
		global $plugin_page;
		
		
		// set currrent
		$this->view['slug'] = $plugin_page;
		
		
		// verify and remove nonce
		if( acf_verify_nonce('options') )
		{
			// save data
		    if( acf_validate_save_post(true) )
			{
				acf_save_post( "options" );
				
				// redirect
				wp_redirect( admin_url("admin.php?page={$plugin_page}&message=1") );
				exit;
			}
		}
		
		
		add_action('admin_enqueue_scripts', 	array($this,'admin_enqueue_scripts'));
	
	}
	
	
	/*
	*  admin_enqueue_scripts
	*
	*  This action is run after post query but before any admin script / head actions. 
	*  It is a good place to register all actions.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @date	26/01/13
	*  @since	3.6.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function admin_enqueue_scripts() {
		
		// load acf scripts
		acf_enqueue_scripts();
		
		
		// actions
		add_action( 'acf/input/admin_head',		array($this,'admin_head') );
	}
	
	
	/*
	*  admin_head
	*
	*  This action will find and add field groups to the current edit page
	*
	*  @type	action (admin_head)
	*  @date	23/06/12
	*  @since	3.1.8
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_head() {
		
		// get field groups
		$field_groups = acf_get_field_groups(array(
			'options_page' => $this->view['slug']
		));
		
		
		// notices
		if( !empty($_GET['message']) && $_GET['message'] == '1' )
		{
			acf_add_admin_notice( __("Options Updated",'acf') );
		}
		
		if( empty($field_groups) )
		{
			$this->view['have_fields'] = 0;
			
			acf_add_admin_notice('<p>' . __("No Custom Field Groups found for this options page",'acf') . '. <a href="' . admin_url() . 'post-new.php?post_type=acf-field-group">' . __("Create a Custom Field Group",'acf') . '</a></p>', 'error', '');
		}
		else
		{
			foreach( $field_groups as $i => $field_group )
			{
				// vars
				$id = "acf-{$field_group['key']}";
				$title = $field_group['title'];
				$context = $field_group['position'];
				$priority = 'high';
				$args = array( 'field_group' => $field_group );
				
				
				// tweaks to vars
				if( $context == 'side' )
				{
					$priority = 'core';
				}
				
				
				// filter for 3rd party customization
				$priority = apply_filters('acf/input/meta_box_priority', $priority, $field_group);
				
				
				// add meta box
				add_meta_box( $id, $title, array($this, 'render_meta_box'), 'acf_options_page', $context, $priority, $args );
				
				
			}
			// foreach($acfs as $acf)
		}
		// if($acfs)
	}
	
	
	/*
	*  render_meta_box
	*
	*  description
	*
	*  @type	function
	*  @date	24/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function render_meta_box( $post, $args ) {
		
		// extract args
		extract( $args ); // all variables from the add_meta_box function
		extract( $args ); // all variables from the args argument
		
		
		// classes
		$class = 'acf-postbox ' . $field_group['style'];
		$toggle_class = 'acf-postbox-toggle';
		
		
		// load fields
		$fields = acf_get_fields( $field_group );
		
		
		// render
		if( $field_group['label_placement'] == 'left' )
		{
			?>
			<table class="acf-table">
				<tbody>
					<?php acf_render_fields( 'options', $fields, 'tr', $field_group['instruction_placement'] ); ?>
				</tbody>
			</table>
			<?php
		}
		else
		{
			acf_render_fields( 'options', $fields, 'div', $field_group['instruction_placement'] );
		}
		
		
		// inline script
		echo '<div class="acf-hidden">';
			?>
			<script type="text/javascript">
			(function($) {
				
				$('#<?php echo $id; ?>').addClass('<?php echo $class; ?>').removeClass('hide-if-js');
				$('#adv-settings label[for="<?php echo $id; ?>-hide"]').addClass('<?php echo $toggle_class; ?>');
				
			})(jQuery);	
			</script>
			<?php
		echo '</div>';
		
	}
	
	
	/*
	*  html
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 5/12/12
	*/
	
	function html() {
		
		// load view
		acf_pro_get_view('options-page', $this->view);
				
	}
	
	
}

new acf_pro_options_page();

?>