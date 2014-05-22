<?php

if( !class_exists('acf_field_relationship') ):

class acf_field_relationship extends acf_field
{
	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function __construct()
	{
		// vars
		$this->name = 'relationship';
		$this->label = __("Relationship",'acf');
		$this->category = 'relational';
		$this->defaults = array(
			'post_type'			=> array(),
			'taxonomy'			=> array(),
			'max' 				=> 0,
			'filters'			=> array('search', 'post_type', 'taxonomy'),
			'elements' 			=> array(),
			'return_format'		=> 'object'
		);
		$this->l10n = array(
			'max'		=> __("Maximum values reached ( {max} values )",'acf'),
			'loading'	=> __('Loading','acf'),
			'empty'		=> __('No matches found','acf'),
			'tmpl_li'	=> '<li>
								<input type="hidden" name="<%= name %>[]" value="<%= value %>" />
								<span data-id="<%= value %>" class="acf-relationship-item">
									<%= text %>
									<a href="#" class="acf-icon small dark"><i class="acf-sprite-remove"></i></a>
								</span>
							</li>'
		);
		
		
		// do not delete!
    	parent::__construct();
    	
    	
    	// extra
		add_action('wp_ajax_acf/fields/relationship/query',			array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/relationship/query',	array($this, 'ajax_query'));
	}
	
	
	/*
	*  query_posts
	*
	*  description
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_query()
   	{
   		// options
   		$options = acf_parse_args( $_GET, array(
			'post_id'					=>	0,
			's'							=>	'',
			'post_type'					=>	'',
			'taxonomy'					=>	'',
			'lang'						=>	false,
			'field_key'					=>	'',
			'nonce'						=>	'',
		));
		
		
		// args
		$args = array(
			'posts_per_page'			=> -1,
			'post_type'					=> 'post',
			'orderby'					=> 'menu_order title',
			'order'						=> 'ASC',
			'post_status'				=> 'any',
			'suppress_filters'			=> false,
			'update_post_meta_cache'	=> false,
		);
		
		
   		// vars
   		$r = array();
   		
		
		// validate
		if( ! wp_verify_nonce($options['nonce'], 'acf_nonce') )
		{
			die();
		}
		
		
		// WPML
		if( $options['lang'] )
		{
			global $sitepress;
			
			if( !empty($sitepress) )
			{
				$sitepress->switch_lang( $options['lang'] );
			}
		}
		
		
		// load field
		$field = acf_get_field( $options['field_key'] );
		
		if( !$field )
		{
			die();
		}
		
		
		// update post_type
		$pt = '';
		
		if( $options['post_type'] )
		{
			$pt = $options['post_type'];
		}
		else
		{
			if( empty($field['post_type']) )
			{
				$pt = acf_get_post_types();
			}
			else
			{
				$pt = $field['post_type'];
			}	
		}
		
		if( $pt )
		{
			$args['post_type'] = $pt;
		}
		
		
		// attachment doesn't work if it is the only item in an array???
		if( is_array($args['post_type']) && count($args['post_type']) == 1 )
		{
			$args['post_type'] = $args['post_type'][0];
		}
		
		
		// update taxonomy
		$t = array();
		
		if( $options['taxonomy'] )
		{
			$t[] = $options['taxonomy'];
		}
		else
		{
			if( empty($field['taxonomy']) )
			{
				// do nothing
			}
			else
			{
				$t = $field['taxonomy'];
			}
		}
		
		if( !empty($t) )
		{
			$taxonomies = acf_decode_taxonomy_terms( $t );
			
			foreach( $taxonomies as $k => $v )
			{
				$args['tax_query'][] = array(
					'taxonomy'	=> $k,
					'field'		=> 'slug',
					'terms'		=> $v,
				);
			}
				
		}
		
		
		// search
		if( $options['s'] )
		{
			$args['s'] = $options['s'];
		}
		
		
		// filters
		$args = apply_filters('acf/fields/relationship/query', $args, $field, $options['post_id']);
		$args = apply_filters('acf/fields/relationship/query/name=' . $field['name'], $args, $field, $options['post_id'] );
		$args = apply_filters('acf/fields/relationship/query/key=' . $field['key'], $args, $field, $options['post_id'] );
		
		
		// find array of post_type
		$post_types = $args['post_type'];
		
		if( !is_array($post_types) )
		{
			$post_types = array( $post_types );
		}
		
		
		// get posts
		$posts = get_posts( $args );
		
		foreach( $post_types as $post_type )
		{
			// vars
			$post_type_object = get_post_type_object( $post_type );
			$this_posts = array();
			$this_json = array();
			$this_search_weight = array();
			
			
			$keys = array_keys($posts);
			foreach( $keys as $key )
			{
				if( $posts[ $key ]->post_type == $post_type )
				{
					$this_posts[] = acf_extract_var( $posts, $key );
				}
			}
			
			
			// bail early if no posts for this post type
			if( empty($this_posts) )
			{
				continue;
			}
			
			
			// sort into hierachial order!
			if( is_post_type_hierarchical( $post_type ) )
			{
				// this will fail if a search has taken place because parents wont exist
				if( empty($args['s']) )
				{
					$this_posts = get_page_children( 0, $this_posts );
				}
			}
			
			
			foreach( $this_posts as $post )
			{
				// vars
				$title = $this->get_result( $post, $field, $options['post_id'] );
				$search_weight = 0;
				
				
				// add to json
				$this_json[] = array(
					'id'	=> $post->ID,
					'text'	=> $title
				);
				
				
				// add search weight
				if( !empty($args['s']) ) {
					
					// vars
					$haystack = strtolower($title);
					$needle = strtolower($args['s']);
					
					if( strpos($haystack, $needle) !== false ) {
						
						$search_weight = strlen($needle);
						
					}
					
				}
				
				$this_search_weight[] = $search_weight;

			}
			
			
			// order by weight
			if( !empty($args['s']) ) {
				
				// sort the array with menu_order ascending
				array_multisort( $this_search_weight, SORT_DESC, $this_json );
				
			}
			
			
			// add as optgroup or results
			if( count($post_types) == 1 )
			{
				$r = $this_json;
			}
			else
			{
				$r[] = array(
					'text'		=> $post_type_object->labels->singular_name,
					'children'	=> $this_json
				);
			}
						
		}
		
		
		// return JSON
		echo json_encode( $r );
		die();
			
	}
	
	
	/*
	*  get_result
	*
	*  This function returns the HTML for a result
	*
	*  @type	function
	*  @date	1/11/2013
	*  @since	5.0.0
	*
	*  @param	$post (object)
	*  @param	$field (array)
	*  @param	$post_id (int) the post_id to which this value is saved to
	*  @return	(string)
	*/
	
	function get_result( $post, $field, $post_id = 0 ) {
		
		// get post_id
		if( !$post_id ) {
			
			$form_data = acf_get_setting('form_data');
			
			if( !empty($form_data['post_id']) ) {
				
				$post_id = $form_data['post_id'];
				
			} else {
				
				$post_id = get_the_ID();
				
			}
		}
		
		
		// vars
		$title = '';
		
		
		// elements
		if( !empty($field['elements']) )
		{
			if( in_array('featured_image', $field['elements']) )
			{
				$image = '';
				
				if( $post->post_type == 'attachment' ) {
					
					$image = wp_get_attachment_image( $post->ID, array(17, 17) );
					
				} else {
					
					$image = get_the_post_thumbnail( $post->ID, array(17, 17) );
					
				}
				
				
				$title .= '<div class="thumbnail">' . $image . '</div>';
			}
		}
		
		
		// ancestors
		$ancestors = get_ancestors( $post->ID, $post->post_type );
		
		if( !empty($ancestors) )
		{
			foreach( $ancestors as $a )
			{
				$title .= '- ';
			}
		}
		
		
		// title
		$title .= get_the_title( $post->ID );
		
		
		// status
		if( get_post_status( $post->ID ) != "publish" )
		{
			$title .= ' (' . get_post_status( $post->ID ) . ')';
		}
					
		
		// filters
		$title = apply_filters('acf/fields/relationship/result', $title, $post, $field, $post_id);
		$title = apply_filters('acf/fields/relationship/result/name=' . $field['name'] , $title, $post, $field, $post_id);
		$title = apply_filters('acf/fields/relationship/result/key=' . $field['key'], $title, $post, $field, $post_id);
		
		
		// return
		return $title;
	}
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field( $field ) {
		
		// vars
		$values = array();
		$atts = array(
			'id'				=> $field['id'],
			'class'				=> "acf-relationship {$field['class']}",
			'data-max'			=> $field['max'],
			'data-s'			=> '',
			'data-post_type'	=> '',
			'data-taxonomy'		=> '',
		);
		
		
		// Lang
		if( defined('ICL_LANGUAGE_CODE') )
		{
			$atts['data-lang'] = ICL_LANGUAGE_CODE;
		}
		
		
		// filters
		$post_types = acf_get_post_types();
		$terms = acf_get_taxonomy_terms();
		
		
		// populate values
		if( !empty($field['value']) )
		{
			$field['value'] = array_map('intval', $field['value']);
			
			$posts = get_posts(array(
				'posts_per_page'	=> -1,
				'post_type'			=> $post_types,
				'post_status'		=> 'any',
				'post__in'			=> $field['value'],
				'orderby'			=> 'post__in'
			));
			
			if( !empty($posts) )
			{
				foreach( $posts as $p )
				{
					$values[ $p->ID ] = $this->get_result( $p, $field );
				}
			}
		}
		
		
		// remove from filters
		if( !empty($field['post_type']) )
		{
			// loop through all available post types
			foreach( array_keys($post_types) as $k )
			{
				// if post type was not selected for this field, remove it
				if( !in_array( $k, $field['post_type']) )
				{
					unset($post_types[ $k ]);
				}
			}
		}
		
		
		if( !empty($field['taxonomy']) )
		{
			$new_terms = array();
			
			foreach( $terms as $k => $v )
			{
				foreach( $v as $k2 => $v2 )
				{
					// Bail early if the current term was not selected as a field option
					if( !in_array($k2, $field['taxonomy']) )
					{
						continue;
					}
					
					
					// add blank array
					if( !array_key_exists($k, $new_terms) )
					{
						$new_terms[ $k ] = array();
					}
					
					
					// add to array
					$new_terms[ $k ][ $k2 ] = $v2;
					
				}
			}
			
			
			// update $terms
			$terms = $new_terms;
			
			unset($new_terms);
		}
		
		
		// width for select filters
		$width = array(
			'search'	=> 0,
			'post_type'	=> 0,
			'taxonomy'	=> 0
		);
		
		if( !empty($field['filters']) )
		{
			$width = array(
				'search'	=> 50,
				'post_type'	=> 25,
				'taxonomy'	=> 25
			);
			
			foreach( array_keys($width) as $k )
			{
				if( ! in_array($k, $field['filters']) )
				{
					$width[ $k ] = 0;
				}
			}
			
			// search
			if( $width['search'] == 0 )
			{
				$width['post_type'] = ( $width['post_type'] == 0 ) ? 0 : 50;
				$width['taxonomy'] = ( $width['taxonomy'] == 0 ) ? 0 : 50;
			}
			
			// post_type
			if( $width['post_type'] == 0 )
			{
				$width['taxonomy'] = ( $width['taxonomy'] == 0 ) ? 0 : 50;
			}
			
			// taxonomy
			if( $width['taxonomy'] == 0 )
			{
				$width['post_type'] = ( $width['post_type'] == 0 ) ? 0 : 50;
			}
			
			// search
			if( $width['post_type'] == 0 && $width['taxonomy'] == 0 )
			{
				$width['search'] = ( $width['search'] == 0 ) ? 0 : 100;
			}
		}
			
		?>
<div <?php acf_esc_attr_e($atts); ?>>
	
	<div class="acf-hidden">
		<input type="hidden" name="<?php echo $field['name']; ?>" value="" />
	</div>
	
	<?php if( $width['search'] > 0 || $width['post_type'] > 0 || $width['taxonomy'] > 0 ): ?>
	<div class="filters">
		
		<ul class="acf-hl">
		
			<?php if( $width['search'] > 0 ): ?>
			<li style="width:<?php echo $width['search']; ?>%;">
				<div class="inner">
				<input class="filter" data-filter="s" placeholder="<?php _e("Search...",'acf'); ?>" type="text" />
				</div>
			</li>
			<?php endif; ?>
			
			<?php if( $width['post_type'] > 0 ): 
				
				// vars
				$pretty_post_types = acf_get_pretty_post_types($post_types);
				
			?>
			<li style="width:<?php echo $width['post_type']; ?>%;">
				<div class="inner">
				<select class="filter" data-filter="post_type">
					<option value=""><?php _e('All post types','acf'); ?></option>
					<?php foreach( $pretty_post_types as $k => $v ): ?>
						<option value="<?php echo $k; ?>"><?php echo $v; ?></option>
					<?php endforeach; ?>
				</select>
				</div>
			</li>
			<?php endif; ?>
			
			<?php if( $width['taxonomy'] > 0 ): ?>
			<li style="width:<?php echo $width['taxonomy']; ?>%;">
				<div class="inner">
				<select class="filter" data-filter="taxonomy">
					<option value=""><?php _e('All taxonomies','acf'); ?></option>
					<?php foreach( $terms as $k_opt => $v_opt ): ?>
						<optgroup label="<?php echo $k_opt; ?>">
							<?php foreach( $v_opt as $k => $v ): ?>
								<option value="<?php echo $k; ?>"><?php echo $v; ?></option>
							<?php endforeach; ?>
						</optgroup>
					<?php endforeach; ?>
				</select>
				</div>
			</li>
			<?php endif; ?>
		</ul>
		
	</div>
	<?php endif; ?>
	
	<div class="selection acf-cf">
		<div class="choices">
			<ul class="acf-bl list">
				
			</ul>
		</div>
		<div class="values">
			<ul class="acf-bl list">
				<?php foreach( $values as $k => $v ): ?>
					<li>
						<input type="hidden" name="<?php echo $field['name']; ?>[]" value="<?php echo $k; ?>" />
						<span data-id="<?php echo $k; ?>" class="acf-relationship-item">
							<?php echo $v; ?>
							<a href="#" class="acf-icon small dark"><i class="acf-sprite-remove"></i></a>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	
	
</div>
		<?php
	}
	
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function render_field_settings( $field ) {
		
		// post_type
		acf_render_field_setting( $field, array(
			'label'			=> __('Filter by Post Type','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'post_type',
			'choices'		=> acf_get_pretty_post_types(),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All post types",'acf'),
		));
		
		
		// taxonomy
		acf_render_field_setting( $field, array(
			'label'			=> __('Filter by Taxonomy','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'taxonomy',
			'choices'		=> acf_get_taxonomy_terms(),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("No taxonomy filter",'acf'),
		));
		
		
		// filters
		acf_render_field_setting( $field, array(
			'label'			=> __('Filters','acf'),
			'instructions'	=> '',
			'type'			=> 'checkbox',
			'name'			=> 'filters',
			'choices'		=> array(
				'search'		=> __("Search",'acf'),
				'post_type'		=> __("Post Type",'acf'),
				'taxonomy'		=> __("Taxonomy",'acf'),
			),
		));
		
		
		// filters
		acf_render_field_setting( $field, array(
			'label'			=> __('Elements','acf'),
			'instructions'	=> __('Selected elements will be displayed in each result','acf'),
			'type'			=> 'checkbox',
			'name'			=> 'elements',
			'choices'		=> array(
				'featured_image'	=> __("Featured Image",'acf'),
			),
		));
		
		
		// max
		if( $field['max'] < 1 )
		{
			$field['max'] = '';
		}
		
		acf_render_field_setting( $field, array(
			'label'			=> __('Maximum posts','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'max',
		));
		
		
		// return_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Return Format','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'choices'		=> array(
				'object'		=> __("Post Object",'acf'),
				'id'			=> __("Post ID",'acf'),
			),
			'layout'	=>	'horizontal',
		));
		
		
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed to the render_field action
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @param	$template (boolean) true if value requires formatting for front end template function
	*
	*  @return	$value (mixed) the modified value
	*/
	
	function format_value( $value, $post_id, $field, $template ) {
		
		// bail early if no value
		if( empty($value) )
		{
			return $value;
		}
		
		
		// convert to int
		$value = array_map('intval', $value);
		
		
		// bail early if not formatting for template use
		if( !$template )
		{
			return $value;
		}
		
		
		// return format
		if( $field['return_format'] == 'object' )
		{
			$value = $this->get_posts( $value );	
		}
		
		
		// return
		return $value;
		
	}
	
	
	/*
	*  get_posts
	*
	*  This function will take an array of post_id's ($value) and return an array of post_objects
	*
	*  @type	function
	*  @date	7/08/13
	*
	*  @param	$post_ids (array) the array of post ID's
	*  @return	(array) an array of post objects
	*/
	
	function get_posts( $post_ids ) {
		
		// validate
		if( empty($post_ids) )
		{
			return false;
		}
		
		
		// find posts (DISTINCT POSTS)
		$posts = get_posts(array(
			'posts_per_page'	=> -1,
			'post_type'			=> acf_get_post_types(),
			'post_status'		=> 'any',
			'post__in'			=> $post_ids,
			'orderby'			=> 'post__in'
		));
		
		
		// return
		return $posts;

	}
	
	
	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {
		
		
		// validate
		if( empty($value) ) {
		
			return $value;
			
		}
		
		
		// force value to array
		$value = acf_force_type_array( $value );
		
					
		// array
		foreach( $value as $k => $v ){
		
			// object?
			if( is_object($v) && isset($v->ID) )
			{
				$value[ $k ] = $v->ID;
			}
		}
		
		
		// save value as strings, so we can clearly search for them in SQL LIKE statements
		$value = array_map('strval', $value);
		
	
		// return
		return $value;
		
	}
		
}

/*
*  acf_field_relationship
*
*  @type	function
*  @date	22/05/2014
*  @since	5.0.0
*
*  @param	N/A
*  @return	(object)
*/

function acf_field_relationship()
{
	global $acf_field_relationship;
	
	if( !isset($acf_field_relationship) )
	{
		$acf_field_relationship = new acf_field_relationship();
	}
	
	return $acf_field_relationship;
}


// initialize
acf_field_relationship();

endif; // class_exists check

?>