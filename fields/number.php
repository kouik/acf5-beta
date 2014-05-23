<?php

if( !class_exists('acf_field_number') ):

class acf_field_number extends acf_field
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
		$this->name = 'number';
		$this->label = __("Number",'acf');
		$this->defaults = array(
			'default_value'	=>	'',
			'min'			=>	'',
			'max'			=>	'',
			'step'			=>	'',
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
		$o = array( 'type', 'id', 'class', 'min', 'max', 'step', 'name', 'value', 'placeholder' );
		$s = array( 'readonly', 'disabled' );
		$e = '';
		
		
		// step
		if( !$field['step'] )
		{
			$field['step'] = 'any';
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
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function render_field_settings( $field )
	{
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
		
		
		// min
		acf_render_field_setting( $field, array(
			'label'			=> __('Minimum Value','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'min',
		));
		
		
		// max
		acf_render_field_setting( $field, array(
			'label'			=> __('Maximum Value','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'max',
		));
		
		
		// max
		acf_render_field_setting( $field, array(
			'label'			=> __('Step Size','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'step',
		));
		
	}
	
	
	/*
	*  validate_value
	*
	*  description
	*
	*  @type	function
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function validate_value( $valid, $value, $field, $input ){
		
		// remove ','
		if( acf_str_exists(',', $value) ) {
			
			$value = str_replace(',', '', $value);
			
		}
				
		
		// if value is not numeric...
		if( !is_numeric($value) ) {
			
			// allow blank to be saved
			if( !empty($value) ) {
				
				$valid = __('Value must be a number', 'acf');
				
			}
			
			
			// return early
			return $valid;
			
		}
		
		
		// convert
		$value = floatval($value);
		
		
		// min
		if( is_numeric($field['min']) && $value < floatval($field['min'])) {
			
			$valid = sprintf(__('Value must be equal to or higher than %d', 'acf'), $field['min'] );
			
		}
		
		
		// max
		if( is_numeric($field['max']) && $value > floatval($field['max']) ) {
			
			$valid = sprintf(__('Value must be equal to or lower than %d', 'acf'), $field['max'] );
			
		}
		
		
		// return		
		return $valid;
		
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
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the $post_id of which the value will be saved
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {
		
		// no formatting needed for empty value
		if( empty($value) ) {
			
			return $value;
			
		}
		
		
		// remove ','
		if( acf_str_exists(',', $value) ) {
			
			$value = str_replace(',', '', $value);
			
		}
		
		
		// return
		return $value;
	}
	
	
}

/*
*  acf_field_number
*
*  @type	function
*  @date	22/05/2014
*  @since	5.0.0
*
*  @param	N/A
*  @return	(object)
*/

function acf_field_number()
{
	global $acf_field_number;
	
	if( !isset($acf_field_number) )
	{
		$acf_field_number = new acf_field_number();
	}
	
	return $acf_field_number;
}


// initialize
acf_field_number();

endif; // class_exists check

?>
