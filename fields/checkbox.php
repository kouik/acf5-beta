<?php

if( !class_exists('acf_field_checkbox') ):

class acf_field_checkbox extends acf_field
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
		$this->name = 'checkbox';
		$this->label = __("Checkbox",'acf');
		$this->category = 'choice';
		$this->defaults = array(
			'layout'		=>	'vertical',
			'choices'		=>	array(),
			'default_value'	=>	'',
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
		// value must be array
		if( !is_array($field['value']) )
		{
			// perhaps this is a default value with new lines in it?
			if( strpos($field['value'], "\n") !== false )
			{
				// found multiple lines, explode it
				$field['value'] = explode("\n", $field['value']);
			}
			else
			{
				$field['value'] = array( $field['value'] );
			}
		}
		
		
		// trim value
		$field['value'] = array_map('trim', $field['value']);
		
		
		// hiden input
		acf_hidden_input(array(
			'type'	=> 'hidden',
			'name'	=> $field['name'],
		));
		
		
		// vars
		$i = 0;
		
		
		// class
		$field['class'] .= ' acf-checkbox-list';
		$field['class'] .= ($field['layout'] == 'horizontal') ? ' acf-hl' : ' acf-bl';

		
		// e
		$e = '<ul ' . acf_esc_attr(array( 'class' => $field['class'] )) . '>';
		
		
		// checkbox saves an array
		$field['name'] .= '[]';
		
		
		// foreach choices
		foreach( $field['choices'] as $value => $label )
		{
			// increase counter
			$i++;
			
			
			// vars
			$atts = array(
				'type'	=> 'checkbox',
				'id'	=> $field['id'], 
				'name'	=> $field['name'],
				'value'	=> $value,
			);
			
			
			if( in_array($value, $field['value']) )
			{
				$atts['checked'] = 'checked';
			}
			if( isset($field['disabled']) && in_array($value, $field['disabled']) )
			{
				$atts['disabled'] = 'true';
			}
			
			
			// each checkbox ID is generated with the $key, however, the first checkbox must not use $key so that it matches the field's label for attribute
			if( $i > 1 )
			{
				$atts['id'] .= '-' . $value;
			}
			
			$e .= '<li><label><input ' . acf_esc_attr( $atts ) . '/>' . $label . '</label></li>';
		}
		
		$e .= '</ul>';
		
		
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
		// implode checkboxes so they work in a textarea
		if( is_array($field['choices']) )
		{		
			foreach( $field['choices'] as $k => $v )
			{
				if( $k === $v )
				{
					$field['choices'][ $k ] = $v;
				}
				else
				{
					$field['choices'][ $k ] = $k . ' : ' . $v;
				}
				
			}
			$field['choices'] = implode("\n", $field['choices']);
		}
		
		
		// choices
		acf_render_field_setting( $field, array(
			'label'			=> __('Choices','acf'),
			'instructions'	=> __('Enter each choice on a new line.','acf') . '<br /><br />' . __('For more control, you may specify both a value and label like this:','acf'). '<br /><br />' . __('red : Red','acf'),
			'type'			=> 'textarea',
			'name'			=> 'choices',
		));	
		
		
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Default Value','acf'),
			'instructions'	=> __('Enter each default value on a new line','acf'),
			'type'			=> 'textarea',
			'name'			=> 'default_value',
		));
		
		
		// layout
		acf_render_field_setting( $field, array(
			'label'			=> __('Layout','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'layout',
			'layout'		=> 'horizontal', 
			'choices'		=> array(
				'vertical'		=> __("Vertical",'acf'), 
				'horizontal'	=> __("Horizontal",'acf')
			)
		));
		
		
	}
	
}

/*
*  acf_field_checkbox
*
*  @type	function
*  @date	22/05/2014
*  @since	5.0.0
*
*  @param	N/A
*  @return	(object)
*/

function acf_field_checkbox()
{
	global $acf_field_checkbox;
	
	if( !isset($acf_field_checkbox) )
	{
		$acf_field_checkbox = new acf_field_checkbox();
	}
	
	return $acf_field_checkbox;
}


// initialize
acf_field_checkbox();

endif; // class_exists check

?>