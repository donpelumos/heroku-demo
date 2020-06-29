<?php 
/* 	
If you see this text in your browser, PHP is not configured correctly on this hosting provider. 
Contact your hosting provider regarding PHP configuration for your site.

PHP file generated by Adobe Muse CC 2018.0.0.379
*/

require_once('form_process.php');

$form = array(
	'subject' => 'Request A Quote Form Submission',
	'heading' => 'New Form Submission',
	'success_redirect' => '',
	'resources' => array(
		'checkbox_checked' => 'Selected',
		'checkbox_unchecked' => 'Unselected',
		'submitted_from' => 'Form submitted from website: %s',
		'submitted_by' => 'Visitor IP address: %s',
		'too_many_submissions' => 'Too many recent submissions from this IP',
		'failed_to_send_email' => 'Failed to send email',
		'invalid_reCAPTCHA_private_key' => 'Invalid reCAPTCHA private key.',
		'invalid_reCAPTCHA2_private_key' => 'Invalid reCAPTCHA 2.0 private key.',
		'invalid_reCAPTCHA2_server_response' => 'Invalid reCAPTCHA 2.0 server response.',
		'invalid_field_type' => 'Unknown field type \'%s\'.',
		'invalid_form_config' => 'Field \'%s\' has an invalid configuration.',
		'unknown_method' => 'Unknown server request method'
	),
	'email' => array(
		'from' => 'bicycle@waveustransit.com',
		'to' => 'bicycle@waveustransit.com'
	),
	'fields' => array(
		'custom_U59305' => array(
			'order' => 1,
			'type' => 'string',
			'label' => 'Pickup  Details',
			'required' => false,
			'errors' => array(
			)
		),
		'custom_U59323' => array(
			'order' => 2,
			'type' => 'string',
			'label' => 'Nick Name/Company Name',
			'required' => true,
			'errors' => array(
				'required' => 'Field \'Nick Name/Company Name\' is required.'
			)
		),
		'custom_U59331' => array(
			'order' => 3,
			'type' => 'string',
			'label' => 'First Name',
			'required' => true,
			'errors' => array(
				'required' => 'Field \'First Name\' is required.'
			)
		),
		'custom_U59315' => array(
			'order' => 4,
			'type' => 'string',
			'label' => 'Last Name',
			'required' => true,
			'errors' => array(
				'required' => 'Field \'Last Name\' is required.'
			)
		),
		'custom_U59276' => array(
			'order' => 5,
			'type' => 'string',
			'label' => 'Pickup Address',
			'required' => true,
			'errors' => array(
				'required' => 'Field \'Pickup Address\' is required.'
			)
		),
		'custom_U59343' => array(
			'order' => 6,
			'type' => 'string',
			'label' => 'Suite/Building Number',
			'required' => true,
			'errors' => array(
				'required' => 'Field \'Suite/Building Number\' is required.'
			)
		),
		'custom_U59319' => array(
			'order' => 7,
			'type' => 'string',
			'label' => 'Phone Number',
			'required' => true,
			'errors' => array(
				'required' => 'Field \'Phone Number\' is required.'
			)
		),
		'custom_U59347' => array(
			'order' => 8,
			'type' => 'string',
			'label' => 'Email',
			'required' => true,
			'errors' => array(
				'required' => 'Field \'Email\' is required.'
			)
		),
		'custom_U59293' => array(
			'order' => 9,
			'type' => 'string',
			'label' => 'Delivery Details',
			'required' => false,
			'errors' => array(
			)
		),
		'custom_U59327' => array(
			'order' => 10,
			'type' => 'string',
			'label' => 'Nick Name/Company Name',
			'required' => true,
			'errors' => array(
				'required' => 'Field \'Nick Name/Company Name\' is required.'
			)
		),
		'custom_U59289' => array(
			'order' => 11,
			'type' => 'string',
			'label' => 'First Name',
			'required' => true,
			'errors' => array(
				'required' => 'Field \'First Name\' is required.'
			)
		),
		'custom_U59297' => array(
			'order' => 12,
			'type' => 'string',
			'label' => 'Last Name',
			'required' => true,
			'errors' => array(
				'required' => 'Field \'Last Name\' is required.'
			)
		),
		'custom_U59335' => array(
			'order' => 13,
			'type' => 'string',
			'label' => 'Delivery Address',
			'required' => true,
			'errors' => array(
				'required' => 'Field \'Delivery Address\' is required.'
			)
		),
		'custom_U59285' => array(
			'order' => 14,
			'type' => 'string',
			'label' => 'Suite/Building Number',
			'required' => true,
			'errors' => array(
				'required' => 'Field \'Suite/Building Number\' is required.'
			)
		),
		'custom_U59339' => array(
			'order' => 15,
			'type' => 'string',
			'label' => 'Phone Number',
			'required' => true,
			'errors' => array(
				'required' => 'Field \'Phone Number\' is required.'
			)
		),
		'custom_U59310' => array(
			'order' => 16,
			'type' => 'string',
			'label' => 'Email',
			'required' => true,
			'errors' => array(
				'required' => 'Field \'Email\' is required.'
			)
		)
	)
);

process_form($form);
?>
