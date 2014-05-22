<?php

if( !class_exists('acf_field_textarea') ):

class acf_field_textarea extends acf_field
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
		$this->name = 'textarea';
		$this->label = __("Text Area",'acf');
		$this->defaults = array(
			'default_value'	=> '',
			'formatting' 	=> 'html',
			'maxlength'		=> '',
			'placeholder'	=> '',
			'readonly'		=> 0,
			'disabled'		=> 0,
			'rows'			=> ''
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
	
	function render_field( $field ) {
		
		// vars
		$o = array( 'id', 'class', 'name', 'placeholder', 'rows' );
		$s = array( 'readonly', 'disabled' );
		$e = '';
		
		
		// maxlength
		if( $field['maxlength'] !== '' )
		{
			$o[] = 'maxlength';
		}
		
		
		// rows
		if( empty($field['rows']) )
		{
			$field['rows'] = 8;
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
		

		$e .= '<textarea ' . acf_esc_attr( $atts ) . ' >';
		$e .= esc_textarea( $field['value'] );
		$e .= '</textarea>';
		
		
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
	
	function render_field_settings( $field )
	{
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Default Value','acf'),
			'instructions'	=> __('Appears when creating a new post','acf'),
			'type'			=> 'textarea',
			'name'			=> 'default_value',
		));
		
		
		// placeholder
		acf_render_field_setting( $field, array(
			'label'			=> __('Placeholder Text','acf'),
			'instructions'	=> __('Appears within the input','acf'),
			'type'			=> 'text',
			'name'			=> 'placeholder',
		));
		
		
		// maxlength
		acf_render_field_setting( $field, array(
			'label'			=> __('Character Limit','acf'),
			'instructions'	=> __('Leave blank for no limit','acf'),
			'type'			=> 'number',
			'name'			=> 'maxlength',
		));
		
		
		// rows
		acf_render_field_setting( $field, array(
			'label'			=> __('Rows','acf'),
			'instructions'	=> __('Sets the textarea height','acf'),
			'type'			=> 'number',
			'name'			=> 'rows',
			'placeholder'	=> 8
		));
		
		
		// formatting
		acf_render_field_setting( $field, array(
			'label'			=> __('Formatting','acf'),
			'instructions'	=> __('Effects value on front end','acf'),
			'type'			=> 'select',
			'name'			=> 'formatting',
			'choices'		=> array(
				'html'			=> __("Automatically add paragraphs",'acf'),
				'br'			=> __("Automatically add &lt;br&gt;",'acf'),
				'none'			=> __("Plain text",'acf')
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
			$value = wpautop($value);
		}
		elseif( $field['formatting'] == 'br' )
		{
			$value = nl2br($value);
		}
		
		
		// return
		return $value;
	}
	
}

/*
*  acf_field_textarea
*
*  @type	function
*  @date	22/05/2014
*  @since	5.0.0
*
*  @param	N/A
*  @return	(object)
*/

function acf_field_textarea()
{
	global $acf_field_textarea;
	
	if( !isset($acf_field_textarea) )
	{
		$acf_field_textarea = new acf_field_textarea();
	}
	
	return $acf_field_textarea;
}


// initialize
acf_field_textarea();

endif; // class_exists check

?>