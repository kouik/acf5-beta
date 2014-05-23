<?php

if( !class_exists('acf_field_message') ):

class acf_field_message extends acf_field
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
		$this->name = 'message';
		$this->label = __("Message",'acf');
		$this->category = 'layout';
		$this->defaults = array(
			'message'	=>	'',
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
		echo wpautop( $field['message'] );
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
			'label'			=> __('Message','acf'),
			'instructions'	=> __('Please note that all text will first be passed through the wp function ','acf') . 
							   '<a href="http://codex.wordpress.org/Function_Reference/wpautop" target="_blank">wpautop()</a>',
			'type'			=> 'textarea',
			'name'			=> 'message',
		));
		
	}
	
}

/*
*  acf_field_message
*
*  @type	function
*  @date	22/05/2014
*  @since	5.0.0
*
*  @param	N/A
*  @return	(object)
*/

function acf_field_message()
{
	global $acf_field_message;
	
	if( !isset($acf_field_message) )
	{
		$acf_field_message = new acf_field_message();
	}
	
	return $acf_field_message;
}


// initialize
acf_field_message();

endif; // class_exists check

?>
