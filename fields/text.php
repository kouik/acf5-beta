<?php

if( !class_exists('acf_field_text') ):

class acf_field_text extends acf_field
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
		$this->name = 'text';
		$this->label = __("Text",'acf');
		$this->defaults = array(
			'default_value'	=>	'',
			'formatting' 	=>	'html',
			'maxlength'		=>	'',
			'placeholder'	=>	'',
			'prepend'		=>	'',
			'append'		=>	'',
			'readonly'		=>	0,
			'disabled'		=>	0,
		);
		
		
		// do not delete!
    	parent::__construct();
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
	
	function render_field( $field )
	{
		// vars
		$o = array( 'type', 'id', 'class', 'name', 'value', 'placeholder' );
		$s = array( 'readonly', 'disabled' );
		$e = '';
		
		
		// maxlength
		if( $field['maxlength'] !== "" )
		{
			$o[] = 'maxlength';
		}
		
		
		// prepend
		if( $field['prepend'] !== "" )
		{
			$field['class'] .= ' acf-is-prepended';
			$e .= '<div class="acf-input-prepend">' . $field['prepend'] . '</div>';
		}
		
		
		// append
		if( $field['append'] !== "" )
		{
			$field['class'] .= ' acf-is-appended';
			$e .= '<div class="acf-input-append">' . $field['append'] . '</div>';
		}
		
		
		// populate atts
		$atts = array();
		foreach( $o as $k )
		{
			$atts[ $k ] = $field[ $k ];	
		}
		
		
		// special atts
		foreach( $s as $k )
		{
			if( $field[ $k ] )
			{
				$atts[ $k ] = $k;
			}
		}
		
		
		// render
		$e .= '<div class="acf-input-wrap">';
		$e .= '<input ' . acf_esc_attr( $atts ) . ' />';
		$e .= '</div>';
		
		
		// return
		echo $e;
	}
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @param	$field	- an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field_settings( $field ) {
		
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Default Value','acf'),
			'instructions'	=> __('Appears when creating a new post','acf'),
			'type'			=> 'text',
			'name'			=> 'default_value',
		));
		
		
		// placeholder
		acf_render_field_setting( $field, array(
			'label'			=> __('Placeholder Text','acf'),
			'instructions'	=> __('Appears within the input','acf'),
			'type'			=> 'text',
			'name'			=> 'placeholder',
		));
		
		
		// prepend
		acf_render_field_setting( $field, array(
			'label'			=> __('Prepend','acf'),
			'instructions'	=> __('Appears before the input','acf'),
			'type'			=> 'text',
			'name'			=> 'prepend',
		));
		
		
		// append
		acf_render_field_setting( $field, array(
			'label'			=> __('Append','acf'),
			'instructions'	=> __('Appears after the input','acf'),
			'type'			=> 'text',
			'name'			=> 'append',
		));
		
		
		// maxlength
		acf_render_field_setting( $field, array(
			'label'			=> __('Character Limit','acf'),
			'instructions'	=> __('Leave blank for no limit','acf'),
			'type'			=> 'number',
			'name'			=> 'maxlength',
		));
		
		
		// formatting
		acf_render_field_setting( $field, array(
			'label'			=> __('Formatting','acf'),
			'instructions'	=> __('Effects value on front end','acf'),
			'type'			=> 'select',
			'name'			=> 'formatting',
			'choices'		=> array(
				'none'			=>	__("No formatting",'acf'),
				'html'			=>	__("Convert HTML into tags",'acf')
			)
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
		if( empty($value) || !is_string($value) )
		{
			return $value;
		}
		
		
		// format for text input
		$value = htmlspecialchars($value, ENT_QUOTES);
		
		
		// bail early if not formatting for template use
		if( !$template )
		{
			return $value;
		}
		
		
		// format
		if( $field['formatting'] == 'none' )
		{
			$value = htmlspecialchars($value, ENT_QUOTES);
		}
		elseif( $field['formatting'] == 'html' )
		{
			$value = nl2br($value);
		}
		
		
		// return
		return $value;
	}
	
}

/*
*  acf_field_text
*
*  @type	function
*  @date	22/05/2014
*  @since	5.0.0
*
*  @param	N/A
*  @return	(object)
*/

function acf_field_text()
{
	global $acf_field_text;
	
	if( !isset($acf_field_text) )
	{
		$acf_field_text = new acf_field_text();
	}
	
	return $acf_field_text;
}


// initialize
acf_field_text();

endif; // class_exists check

?>