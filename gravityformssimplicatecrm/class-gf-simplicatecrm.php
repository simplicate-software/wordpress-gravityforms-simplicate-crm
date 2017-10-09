<?php

GFForms::include_feed_addon_framework();

class GFSimplicateCRM extends GFFeedAddOn {

	protected $_version = GF_SIMPLICATECRM_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'gravityformssimplicatecrm';
	protected $_path = 'gravityformssimplicatecrm/simplicate.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Simplicate CRM Add-On';
	protected $_short_title = 'Simplicate CRM';
	private static $_instance = null;

    /** @var SimplicateApi $api */
	protected $api = null;

	protected $custom_field_key = '';

	/**
	 * Get instance of this class.
	 *
	 * @access public
	 * @static
	 * @return $_instance
	 */
	public static function get_instance() {

		if ( self::$_instance == null ) {
			self::$_instance = new self;
		}

		return self::$_instance;

	}

	/**
	 * Feed Setting Fields.
	 *
	 * @return array
	 */
	public function feed_settings_fields() {

		// Base Fields Array.
		$base_fields = [
			'title'     => '',
			'fields'    => [
				[
					'name'          => 'feedName',
					'label'         => __( 'Feed Name', 'gravityformssimplicatecrm' ),
					'type'          => 'text',
					'class'         => 'large',
					'required'      => true,
					'default_value' => $this->get_default_feed_name(),
					'tooltip'       => ''
				],
				[
					'name'          => 'actionType',
					'label'         => __( 'Action Type', 'gravityformssimplicatecrm' ),
					'type'          => 'radio',
					'required'      => true,
					'onclick'        => "jQuery(this).parents('form').submit();",
					'choices'       => [
						[
							'name'  => 'createPerson',
							'label' => __( 'Create Person', 'gravityformssimplicatecrm' ),
							'value' => 'createPerson',
							'icon'  => 'fa-user',
						],
						[
							'name'  => 'createOrganization',
							'label' => __( 'Create Organization', 'gravityformssimplicatecrm' ),
							'value' => 'createOrganization',
							'icon'  => 'fa-building',
						],
						[
							'name'  => 'createPersonAndOrganization',
							'label' => __( 'Create Person & Organization', 'gravityformssimplicatecrm' ),
							'value' => 'createPersonAndOrganization',
							'icon'  => 'fa-users',
						],
					]
				],
				[
					'name'          => 'additionalActions',
					'label'         => __( 'Additional Actions', 'gravityformssimplicatecrm' ),
					'type'          => 'checkbox',
					'required'      => false,
					'onclick'        => "jQuery(this).parents('form').submit();",
					'choices'       => [
						[
							'name'  => 'createSales',
							'label' => __( 'Create Sales', 'gravityformssimplicatecrm' ),
							'icon'  => 'fa-bar-chart',
						],
//						[
//							'name'  => 'createTask',
//							'label' => __( 'Create Task', 'gravityformssimplicatecrm' ),
//							'icon'  => 'fa-check',
//						],
//						[
//							'name'  => 'createProject',
//							'label' => __( 'Create Project', 'gravityformssimplicatecrm' ),
//							'icon'  => 'fa-check',
//						],
//						[
//							'name'  => 'createInvoice',
//							'label' => __( 'Create Invoice', 'gravityformssimplicatecrm' ),
//							'icon'  => 'fa-check',
//						],
					]
				]
			]
		];

		$person_fields = [
			'title' => __( 'Person Details', 'gravityformssimplicatecrm' ),
			'dependency' => [ 'field' => 'actionType', 'values' => [ 'createPerson' ] ],
			'fields' => [
				[
					'name'           => 'contactStandardFields',
					'label'          => __( 'Map Fields', 'gravityformssimplicatecrm' ),
					'type'           => 'field_map',
					'field_map'      => $this->default_fields_for_feed_mapping(),
					'tooltip'        => '<h6>'. __( 'Map Fields', 'gravityformssimplicatecrm' ) .'</h6>' . __( 'Select which Gravity Form fields pair with their respective Simplicate CRM fields.', 'gravityformssimplicatecrm' )
				],
                [
                    'name'     => 'personNote',
                    'type'     => 'textarea',
                    'required' => false,
                    'class'    => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
                    'label'    => __('Person Note', 'gravityformssimplicatecrm'),
                ],
				[
					'name'          =>'contactCustomFields',
					'label'         => '',
					'type'          => 'dynamic_field_map',
					'field_map'     => $this->custom_fields_for_feed_mapping(),
				]
			]
		];

		$organization_fields = [
			'title' => __( 'Organization Details', 'gravityformssimplicatecrm' ),
			'dependency' => [ 'field' => 'actionType', 'values' => [ 'createOrganization' ] ],
			'fields' => [
				[
					'name'           => 'organizationStandardFields',
					'label'          => __( 'Map Fields', 'gravityformssimplicatecrm' ),
					'type'           => 'field_map',
					'field_map'      => $this->organization_fields_for_feed_mapping(),
					'tooltip'        => '<h6>'. __( 'Map Fields', 'gravityformssimplicatecrm' ) .'</h6>' . __( 'Select which Gravity Form fields pair with their respective Simplicate CRM fields.', 'gravityformssimplicatecrm' )
				],
                [
                    'name'     => 'organizationNote',
                    'type'     => 'textarea',
                    'required' => false,
                    'class'    => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
                    'label'    => __('Organization Note', 'gravityformssimplicatecrm'),
                ],
				[
					'name'          =>'organizationCustomFields',
					'label'         => '',
					'type'          => 'dynamic_field_map',
					'field_map'     => $this->organization_custom_fields_for_feed_mapping(),
				]
			]
		];

		$organization_and_person_fields = [
            'title' => __( 'Organization & Person Details', 'gravityformssimplicatecrm' ),
            'dependency' => [ 'field' => 'actionType', 'values' => [ 'createPersonAndOrganization' ] ],
            'fields' => [
                [
                    'name'           => 'contactStandardFields',
                    'label'          => __( 'Person Map Fields', 'gravityformssimplicatecrm' ),
                    'type'           => 'field_map',
                    'field_map'      => $this->default_fields_for_feed_mapping(),
                    'tooltip'        => '<h6>'. __( 'Person Map Fields', 'gravityformssimplicatecrm' ) .'</h6>' . __( 'Select which Gravity Form fields pair with their respective Simplicate CRM fields.', 'gravityformssimplicatecrm' )
                ],
                [
                    'name'     => 'personNote',
                    'type'     => 'textarea',
                    'required' => false,
                    'class'    => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
                    'label'    => __('Person Note', 'gravityformssimplicatecrm'),
                ],
                [
                    'name'          =>'contactCustomFields',
                    'label'         => '',
                    'type'          => 'dynamic_field_map',
                    'field_map'     => $this->custom_fields_for_feed_mapping(),
                ],
                [
                    'name'           => 'organizationStandardFields',
                    'label'          => __( 'Organization Map Fields', 'gravityformssimplicatecrm' ),
                    'type'           => 'field_map',
                    'field_map'      => $this->organization_fields_for_feed_mapping(),
                    'tooltip'        => '<h6>'. __( 'Organization Map Fields', 'gravityformssimplicatecrm' ) .'</h6>' . __( 'Select which Gravity Form fields pair with their respective Simplicate CRM fields.', 'gravityformssimplicatecrm' )
                ],
                [
                    'name'     => 'organizationNote',
                    'type'     => 'textarea',
                    'required' => false,
                    'class'    => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
                    'label'    => __('Organization Note', 'gravityformssimplicatecrm'),
                ],
                [
                    'name'          =>'organizationCustomFields',
                    'label'         => '',
                    'type'          => 'dynamic_field_map',
                    'field_map'     => $this->organization_custom_fields_for_feed_mapping(),
                ],
                [
                    'name'          =>'contactPersonStandardFields',
                    'label'         => __( 'Contact Person Map Fields', 'gravityformssimplicatecrm' ),
                    'type'          => 'field_map',
                    'field_map'     => $this->contact_person_fields_for_feed_mapping(),
                ],
                [
                    'name'          => 'work_function',
                    'type'          => 'text',
                    'required'      => false,
                    'class'         => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
                    'label'         => __( 'Work Function', 'gravityformssimplicatecrm' ),
                ],
//                [
//                    'name'          =>'contactPersonCustomFields',
//                    'label'         => '',
//                    'type'          => 'dynamic_field_map',
//                    'field_map'     => $this->contact_person_custom_fields_for_feed_mapping(),
//                ],
            ]
        ];

		$sales_fields = [
			'title' => __( 'Sales Details', 'gravityformssimplicatecrm' ),
			'dependency' => [ 'field' => 'createSales', 'values' => [ '1' ] ],
			'fields' => [
				[
					'name'                => 'salesSubject',
					'type'                => 'text',
					'required'            => true,
					'class'               => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
					'label'               => __( 'Subject', 'gravityformssimplicatecrm' ),
				],
                [
                    'name'                => 'salesNote',
                    'type'                => 'textarea',
                    'required'            => false,
                    'class'               => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
                    'label'               => __( 'Note', 'gravityformssimplicatecrm' ),
                ],
				[
					'name'                => 'timelineTitle',
					'type'                => 'text',
					'required'            => false,
					'class'               => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
					'label'               => __( 'Timeline Message Title', 'gravityformssimplicatecrm' ),
				],
                [
                    'name'                => 'timelineContent',
                    'type'                => 'textarea',
                    'required'            => false,
                    'class'               => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
                    'label'               => __( 'Timeline Content', 'gravityformssimplicatecrm' ),
                ],
			]
		];

		$conditional_fields = [
			'title'      => __( 'Conditionele Logica', 'gravityformssimplicatecrm' ),
			'dependency' => [ $this, 'show_conditional_logic_field' ],
			'fields'     => [
				[
					'name'           => 'feedCondition',
					'type'           => 'feed_condition',
					'label'          => __( 'Conditional logic', 'gravityformssimplicatecrm' ),
					'checkbox_label' => __( 'Activeer', 'gravityformssimplicatecrm' ),
					'instructions'   => __( 'Export to Simplicate CRM if', 'gravityformssimplicatecrm' ),
					'tooltip'        => '<h6>' . __( 'Conditional Logic', 'gravityformssimplicatecrm' ) . '</h6>' . __( 'When conditional logic is enabled, form submissions will only be exported to Simplicate CRM when the condition is met. When disabled, all form submissions will be posted.', 'gravityformssimplicatecrm' )
				],

			]
		];

		return [$base_fields, $person_fields, $organization_fields, $organization_and_person_fields, $sales_fields, $conditional_fields];

	}

	/**
	 * Feed List Columns.
	 *
	 * @return array
	 */
	public function feed_list_columns() {

		return [
			'feedName'          => __( 'Name', 'gravityformssimplicatecrm' ),
			'actionType'        => __( 'Action Type', 'gravityformssimplicatecrm' ),
			'additionalActions' => __( 'Additional Actions', 'gravityformssimplicatecrm' ),
		];

	}

	/**
	 * Default fields for mapping feed.
	 *
	 * @return array
	 */
	public function default_fields_for_feed_mapping() {

		return [
			[
				'name'          => 'first_name',
				'label'         => __( 'First Name', 'gravityformssimplicatecrm'),
				'required'      => true,
				'field_type'    => array( 'name', 'text', 'hidden' ),
				'default_value' => $this->get_first_field_by_type( 'name', 3 ),
			],
			[
				'name'          => 'family_name',
				'label'         => __( 'Last Name', 'gravityformssimplicatecrm'),
				'required'      => true,
				'field_type'    => [ 'name', 'text', 'hidden' ],
				'default_value' => $this->get_first_field_by_type( 'name', 3 ),
			],
			[
				'name'          => 'email',
				'label'         => __( 'Email address', 'gravityformssimplicatecrm'),
				'required'      => true,
				'field_type'    => [ 'email', 'hidden' ],
				'default_value' => $this->get_first_field_by_type( 'email' ),
			],
		];

	}

	/**
	 * Default fields for mapping feed.
	 *
	 * @return array
	 */
	public function contact_person_fields_for_feed_mapping() {
		return [
			[
				'name'          => 'work_email',
				'label'         => __( 'Work Email', 'gravityformssimplicatecrm'),
				'required'      => false,
				'field_type'    => [ 'email', 'hidden' ],
				'default_value' => $this->get_first_field_by_type( 'email', 3 ),
			],
			[
				'name'          => 'work_mobile',
				'label'         => __( 'Work Mobile', 'gravityformssimplicatecrm'),
				'required'      => false,
				'field_type'    => [ 'name', 'text', 'hidden' ],
				'default_value' => $this->get_first_field_by_type( 'name' ),
			],
		];

	}

	/**
	 * Default fields for mapping feed.
	 *
	 * @return array
	 */
	public function contact_person_custom_fields_for_feed_mapping() {
		return [
//			[
//				'name'          => 'work_function',
//				'label'         => __( 'Work Function', 'gravityformssimplicatecrm'),
//				'required'      => false,
//				'field_type'    => [ 'name', 'text', 'hidden' ],
//                'default_value' => $this->get_first_field_by_type( 'name', 3 ),
//			],
//            [
//                'name'     => 'work_function',
//                'label'    => __('Work Function', 'gravityformssimplicatecrm'),
//                'required' => false,
//                'class'    => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
//                'type'     => 'text',
//            ],
		];

	}

	/**
	 * Custom field for feed mapping.
	 *
	 * @return array
	 */
	public function custom_fields_for_feed_mapping() {

		return [
			[
				'label' => __( 'Choose a Field', 'gravityformssimplicatecrm' ),
			],
			[
				'value'     => 'phone',
				'label'     => __( 'Phone', 'gravityformssimplicatecrm')
			],
			[
				'value'     => 'website_url',
				'label'     => __( 'Website URL', 'gravityformssimplicatecrm')
			],
			[
				'value'     => 'twitter_url',
				'label'     => __( 'Twitter URL', 'gravityformssimplicatecrm')
			],
			[
				'value'     => 'linkedin_url',
				'label'     => __( 'Linkedin URL', 'gravityformssimplicatecrm')
			],
			[
				'value'     => 'facebook_url',
				'label'     => __( 'Facebook URL', 'gravityformssimplicatecrm')
			],
			[
				'value'     => 'bank_account',
				'label'     => __( 'Bank Account', 'gravityformssimplicatecrm')
			],
		];

	}

	public function organization_fields_for_feed_mapping() {
		return [
			[
				'name'          => 'name',
				'label'         => __( 'Organization name', 'gravityformssimplicatecrm'),
				'required'      => true,
				'field_type'    => array( 'name', 'text', 'hidden' ),
				'default_value' => $this->get_first_field_by_type( 'name', 3 ),
			],
			[
				'name'     => 'email',
				'label'     => __( 'Email Address', 'gravityformssimplicatecrm'),
				'required'      => true,
				'field_type'    => [ 'email', 'hidden' ],
				'default_value' => $this->get_first_field_by_type( 'email' ),
			],
		];
	}

	public function organization_custom_fields_for_feed_mapping() {
		return [
			[
				'label' => __( 'Choose a Field', 'gravityformssimplicatecrm' ),
			],
			[
				'value'     => 'coc_code',
				'label'     => __( 'Camber Of Commerce', 'gravityformssimplicatecrm')
			],
			[
				'value'     => 'vat_number',
				'label'     => __( 'Vat number', 'gravityformssimplicatecrm')
			],
			[
				'value'     => 'phone',
				'label'     => __( 'Phone', 'gravityformssimplicatecrm')
			],
			[
				'value'     => 'url',
				'label'     => __( 'Website URL', 'gravityformssimplicatecrm')
			],
		];
	}

	/**
	 * Process the feed.
	 *
	 * @param $feed
	 * @param $entry
	 * @param $form
	 */
	public function process_feed( $feed, $entry, $form ) {

		$this->log_debug( __METHOD__ . '(): Processing feed.' );

		$personSales = $organizationSales = $personAndOrganizationSales = false;

		if ( ! $this->initialize_api() ) {

			$this->add_feed_error( esc_html__( 'Feed was not processed because API was not initialized.', 'gravityformssimplicatecrm' ), $feed, $entry, $form );
			return;

		}

		if ( rgars( $feed, 'meta/actionType') == 'createPerson' ) {
			$person = $this->create_person( $feed, $entry, $form );
			$personSales = true;
		}

		if ( rgars( $feed, 'meta/actionType') == 'createOrganization' ) {
			$organization = $this->create_organization( $feed, $entry, $form );
			$organizationSales = true;
		}

		if ( rgars( $feed, 'meta/actionType') == 'createPersonAndOrganization' ) {
            $organization = $this->create_organization( $feed, $entry, $form );
            $person = $this->create_person( $feed, $entry, $form, $organization );
			$personAndOrganizationSales = true;
		}

        if(rgars($feed, 'meta/createSales') == 1) {
            if($personSales) {
                $sales = $this->create_sales($feed, $entry, $form, 'person', $person['data']['id']);
            }
            if($organizationSales) {
                $sales = $this->create_sales($feed, $entry, $form, 'organization', $organization['data']['id']);
            }
            if($personAndOrganizationSales) {
                $sales = $this->create_sales($feed, $entry, $form, 'organization', $organization['data']['id'], [
                    'person'       => $person['data']['id'],
                    'organization' => $organization['data']['id'],
                ]);
            }
        }
	}

	/**
	 * Initialize the api for simplicate.
	 *
	 * @return bool|null
	 */
	protected function initialize_api() {

		if ( ! class_exists('SimplicateApi') ) {
			require_once __DIR__. '/../src/SimplicateApi.php';
		}

		if ( 1 != get_option('simplicate_active') ) {
			return null;
		}

		try {

			$simplicate = new SimplicateApi(
				get_option('simplicate_domain').'.simplicate.nl',
				get_option('simplicate_key'),
				get_option('simplicate_secret')
			);

			$simplicate->testConnection();

			$this->api = $simplicate;

			return true;

		} catch ( Exception $e ) {

			$this->log_error( __METHOD__ . '(): API credentials are invalid; '. $e->getMessage() );
			return false;

		}

	}

	/**
 * Create person.
 *
 * @access public
 * @param array $feed
 * @param array $entry
 * @param array $form
 * @param array $contactPersonByOrganization
 * @return array $person
 */
	public function create_person( $feed, $entry, $form, $contactPersonByOrganization = null ) {

		$this->log_debug( __METHOD__ . '(): Creating person.' );

		/* Setup mapped fields array. */
		$contact_standard_fields = $this->get_field_map_fields( $feed, 'contactStandardFields' );
		$contact_person_standard_fields = $this->get_field_map_fields( $feed, 'contactPersonStandardFields' );
		$contact_custom_fields   = $this->get_dynamic_field_map_fields( $feed, 'contactCustomFields' );

		$first_name     = $this->get_field_value( $form, $entry, $contact_standard_fields['first_name'] );
		$family_name    = $this->get_field_value( $form, $entry, $contact_standard_fields['family_name'] );
		$email          = $this->get_field_value( $form, $entry, $contact_standard_fields['email'] );

		if ( rgblank( $first_name ) || rgblank( $family_name ) ) {

			$this->add_feed_error( esc_html__( 'Contact could not be created as first and/or last name were not provided.', 'gravityformssimplicatecrm' ), $feed, $entry, $form );
			return null;

		}

		if ( GFCommon::is_invalid_or_empty_email( $email ) ) {

			$this->add_feed_error( esc_html__( 'Contact could not be created as email address was not provided.', 'gravityformssimplicatecrm' ), $feed, $entry, $form );
			return null;

		}

        $person = [
            'first_name'  => $first_name,
            'family_name' => $family_name,
            'email'       => $email,
            'note'        => GFCommon::replace_variables( $feed['meta']['personNote'], $form, $entry, false, false, false, 'text' ),
        ];
		if($contactPersonByOrganization) {
            $this->log_debug( __METHOD__ . '(): Person is contact by organization: ' . print_r($contactPersonByOrganization, true) );
		    $contact = [];
		    if(!empty( $feed['meta']['work_function'] )) {
                $contact['work_function'] = GFCommon::replace_variables( $feed['meta']['work_function'], $form, $entry, false, false, false, 'text' );
            }
		    foreach([
                'work_email',
                'work_mobile',
            ] as $cpField) {
		        if($val = $this->get_field_value( $form, $entry, $contact_person_standard_fields[$cpField] )) {
		            $contact[$cpField] = $val;
                }
            }
            if($contact) {
                $this->log_debug( __METHOD__ . '(): At least one of the contact fields was filled, so filling linked_as_contact_to_organization' );
                $contact['organization_id'] = $contactPersonByOrganization['data']['id'];
                $person['linked_as_contact_to_organization'][] = $contact;
            }
        }

		foreach ( $contact_custom_fields as $field_key => $field_id ) {

			/* Get the field value. */
			$this->custom_field_key = $field_key;
			$field_value = $this->get_field_value( $form, $entry, $field_id );

			/* If the field value is empty, skip this field. */
			if ( rgblank( $field_value ) ) {
				continue;
			}

			$person[$field_key] = $field_value;

		}

		$this->log_debug( __METHOD__ . '(): Creating person: ' . print_r( $person, true ) );

		try {

			/* Create contact. */
			$person = $this->api->createPerson( $person );

			/* Save contact ID to entry. */
			gform_update_meta( $entry['id'], 'simplicatecrm_person_id', $person['data']['id'] );

			/* Log that contact was created. */
			$this->log_debug( __METHOD__ . '(): Person #' . $person['data']['id'] . ' created.' );

		} catch ( Exception $e ) {

			$this->add_feed_error( sprintf( esc_html__( 'Contact could not be created. %s', 'gravityformssimplicatecrm' ), $e->getMessage() ), $feed, $entry, $form );

			return null;

		}

		return $person;

	}

	/**
	 * Create Organization.
	 *
	 * @access public
	 * @param array $feed
	 * @param array $entry
	 * @param array $form
	 * @return array $organization
	 */
	public function create_organization( $feed, $entry, $form  ) {

		$this->log_debug( __METHOD__ . '(): Creating organization.' );

		/* Setup mapped fields array. */
		$contact_standard_fields = $this->get_field_map_fields( $feed, 'organizationStandardFields' );
		$contact_custom_fields   = $this->get_dynamic_field_map_fields( $feed, 'organizationCustomFields' );

		$name           = $this->get_field_value( $form, $entry, $contact_standard_fields['name'] );
		$email          = $this->get_field_value( $form, $entry, $contact_standard_fields['email'] );

		if ( rgblank( $name ) ) {

			$this->add_feed_error( esc_html__( 'Contact could not be created as first and/or last name were not provided.', 'gravityformssimplicatecrm' ), $feed, $entry, $form );
			return null;

		}

		if ( GFCommon::is_invalid_or_empty_email( $email ) ) {

			$this->add_feed_error( esc_html__( 'Contact could not be created as email address was not provided.', 'gravityformssimplicatecrm' ), $feed, $entry, $form );
			return null;

		}

		$organization = [
			'name'  => $name,
			'email' => $email,
            'note'  => GFCommon::replace_variables( $feed['meta']['organizationNote'], $form, $entry, false, false, false, 'text' ),
		];

		foreach ( $contact_custom_fields as $field_key => $field_id ) {

			/* Get the field value. */
			$this->custom_field_key = $field_key;
			$field_value = $this->get_field_value( $form, $entry, $field_id );

			/* If the field value is empty, skip this field. */
			if ( rgblank( $field_value ) ) {
				continue;
			}

			$organization[$field_key] = $field_value;

		}

		$this->log_debug( __METHOD__ . '(): Creating organization: ' . print_r( $organization, true ) );

		try {

			/* Create contact. */
			$organization = $this->api->createOrganization( $organization );

			/* Save contact ID to entry. */
			gform_update_meta( $entry['id'], 'simplicatecrm_person_id', $organization['data']['id'] );

			/* Log that contact was created. */
			$this->log_debug( __METHOD__ . '(): Organization #' . $organization['data']['id'] . ' created.' );

		} catch ( Exception $e ) {

			$this->add_feed_error( sprintf( esc_html__( 'Organization could not be created. %s', 'gravityformssimplicatecrm' ), $e->getMessage() ), $feed, $entry, $form );

			return null;

		}

		return $organization;

	}

    /**
     * Create Sales.
     *
     * @access public
     *
     * @param array  $feed
     * @param array  $entry
     * @param array  $form
     * @param string $type
     * @param        $typeId
     * @param array  $orgAndPerson
     *
     * @return array $organization
     */
	public function create_sales( $feed, $entry, $form, $type = 'organization', $typeId = null, $orgAndPerson = [] ) {

		$this->log_debug( __METHOD__ . '(): Creating sales.' );

		$sales = [
			'subject' => GFCommon::replace_variables( $feed['meta']['salesSubject'], $form, $entry, false, false, false, 'text' ),
			'status_id' => 'salesstatus:6448ba9b8aa6045c', // Status is fixed (open)
            'note'      => GFCommon::replace_variables( $feed['meta']['salesNote'], $form, $entry, false, false, false, 'text' ),
		];

		$sales["{$type}_id"] = $typeId;

		if ( rgblank( $sales['subject'] ) || rgblank( $sales["{$type}_id"] )) {

			$this->add_feed_error( esc_html__( 'Sales could not be created as subject/Id was not provided.', 'gravityformssimplicatecrm' ), $feed, $entry, $form );

			return [];

		}
		
		if($orgAndPerson) {
            $this->log_debug( __METHOD__ . '(): Getting person by its ID: ' . print_r($orgAndPerson['person'], true) );
		    $persons = $this->api->getPersonById($orgAndPerson['person']); // Returns a single person, not a collection of persons
		    if(!empty($persons['data'])) {
		        $person = $persons['data'];
		        /** @var bool|array $contactPersons */
		        $contactPersons = isset($person['linked_as_contact_to_organization']) && !empty($person['linked_as_contact_to_organization'])
                    ? $person['linked_as_contact_to_organization']
                    : false;
		        if($contactPersons) {
		            $contactPerson = array_shift($contactPersons);
		            if(isset($contactPerson['id'])) {
                        $this->log_debug( __METHOD__ . '(): Found contact person and its id, filling sales its contact_id' );
		                $sales['contact_id'] = $contactPerson['id'];
                    } else {
                        $this->log_debug( __METHOD__ . '(): No contact person id found' );
                    }
                } else {
                    $this->log_debug( __METHOD__ . '(): No contact persons found' );
                }
            } else {
                $this->log_debug( __METHOD__ . '(): No entries for person with ID: ' . print_r($orgAndPerson['person'], true) );
            }
        }

		$this->log_debug( __METHOD__ . '(): Creating sales: ' . print_r( $sales, true ) );

		try {

			/* Create sales. */
			$sales = $this->api->createSales( $sales );

			/* Save contact ID to entry. */
			gform_update_meta( $entry['id'], 'simplicatecrm_sales_id', $sales['data']['id'] );

			/* Log that contact was created. */
			$this->log_debug( __METHOD__ . '(): Sales #' . $sales['data']['id'] . ' created.' );

		} catch ( Exception $e ) {

			$this->add_feed_error( sprintf( esc_html__( 'Sales could not be created. %s', 'gravityformssimplicatecrm' ), $e->getMessage() ), $feed, $entry, $form );

			return null;

		}

		try {

            $title   = GFCommon::replace_variables( $feed['meta']['timelineTitle'],   $form, $entry, false, false, false, 'text' );
            $content = GFCommon::replace_variables( $feed['meta']['timelineContent'], $form, $entry, false, false, false, 'text' );
            
            if(!empty($title) || !empty($content)) {

                $this->log_debug( __METHOD__ . '(): Creating timeline message... ' );

                $timelineMessage = [
                    'title'        => $title,
                    'content'      => $content,
                    'message_type' => [
                        'id' => 'messagetype:852d47ec82cb8055',
                    ],
                    'linked_to'    => [
                        "{$type}_id" => $typeId,
                        'sales_id'   => $sales['data']['id'],
                    ],
                ];

                $timelineMessage = $this->api->createTimelineMessage($timelineMessage);

                $this->log_debug( __METHOD__ . '(): Timeline Message #' . $timelineMessage['data']['id'] . ' created.' );
                
            }

        } catch ( Exception $e ) {

            $this->add_feed_error( sprintf( esc_html__( 'Timeline message could not be created. %s', 'gravityformssimplicatecrm' ), $e->getMessage() ), $feed, $entry, $form );

        }

		return $sales;

	}

	/**
	 * @param $feed
	 * @param $field_name
	 *
	 * @return array
	 */
	public static function get_field_map_fields( $feed, $field_name ) {

		$fields = array();
		$prefix = "{$field_name}_";

		foreach ( $feed['meta'] as $name => $value ) {
			if ( strpos( $name, $prefix ) === 0 ) {
				$name          = str_replace( $prefix, '', $name );
				$fields[ $name ] = $value;
			}
		}

		return $fields;
	}

	/**
	 * @param $feed
	 * @param $field_name
	 *
	 * @return array
	 */
	public static function get_dynamic_field_map_fields( $feed, $field_name ) {

		$fields = array();
		$dynamic_fields = $feed['meta'][$field_name];

		if ( ! empty( $dynamic_fields ) ) {

			foreach ( $dynamic_fields as $dynamic_field ) {

				$field_key = ( $dynamic_field['key'] == 'gf_custom' ) ? $dynamic_field['custom_key'] : $dynamic_field['key'];
				$fields[$field_key] = $dynamic_field['value'];

			}

		}

		return $fields;
	}

}