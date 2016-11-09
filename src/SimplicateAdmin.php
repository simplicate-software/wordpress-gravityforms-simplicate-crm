<?php

class SimplicateAdmin {
	/**
	 * @var bool
	 */
	private static $initiated = false;

	/**
	 * Constructor
	 */
	public static function init() {
        if ( ! self::$initiated ) {
            self::init_hooks();
        }
    }

	/**
	 * Initialize hooks.
	 */
	public static function init_hooks() {
        self::$initiated = true;

        add_action( 'admin_menu', [ 'SimplicateAdmin', 'adminMenu' ] );
        add_action( 'admin_enqueue_scripts', [ 'SimplicateAdmin', 'loadResources' ] );
        add_action( 'admin_notices', [ 'SimplicateAdmin', 'displayNotice' ] );
	    add_action( 'admin_init', [ 'SimplicateAdmin', 'updatePostInfo' ] );
    }

	/**
	 * Admin Menu.
	 */
	public static function adminMenu() {
        self::load_menu();
    }

	/**
	 * post Info.
	 */
	public static function updatePostInfo() {
    	if(isset($_POST['simplicate_domain'])) {
		    foreach(['simplicate_domain', 'simplicate_key', 'simplicate_secret'] as $option) {
			    update_option( $option, isset( $_POST[$option] ) ? $_POST[$option] : '' );
		    }
		    update_option ( 'simplicate_active', isset( $_POST['simplicate_domain'] ) && (strlen( $_POST['simplicate_domain'] ) > 0) ? 1 : 0);
	    }
    }

	/**
	 * Load Menu.
	 */
	private static function load_menu(){
        $hook = add_options_page( __('Simplicate CRM', 'simplicate'), __('Simplicate CRM', 'simplicate'), 'manage_options', 'simplicate-key-config', array( 'SimplicateAdmin', 'displayPage' ) );

        if ( version_compare( $GLOBALS['wp_version'], '3.3', '>=' ) ) {
            add_action( "load-$hook", array( 'SimplicateAdmin', 'adminHelp' ) );
        }
    }

	/**
	 * Shows Admin Help.
	 */
	public static function adminHelp() {
        $currentScreen = get_current_screen();
        if( current_user_can('manage_options')) {

//            if(isset($_GET['view'])) {
                $currentScreen->add_help_tab([
                    'id' => 'overview',
                    'title' => __('Overview', 'simplicate'),
                    'content' => '<p><strong>'.esc_html__('Simplicate Setup', 'simplicate').'</strong></p>' .
                        '<p>' . esc_html__('Om de website te koppelen aan Simplicate heb je allereerst natuurlijk
                         een Simplicate account nodig. Mocht je dit nog niet hebben, dan kan je dit hier vrijblijvend 
                         aanvragen. De eerste 14 dagen zijn gewoon gratis! Jouw bestaande formulieren kun je 
                         vervolgens zelf (of door de webbouwer van jouw website) uitbreiden met een koppeling naar 
                         Simplicate via Wordpress.', 'simplicate') . '</p>'
                ]);
//            }
        }
    }

	/**
	 * Display Page.
	 */
	public static function displayPage() {
        echo '<h2 class="ss-header">Simplicate CRM</h2>';
        view('overview');
    }

	/**
	 * Display Notification message
	 */
	public static function displayNotice()
    {
        global $hook_suffix;

        $data = [
            'type' => $hook_suffix == 'plugins.php' ? 'plugin' : null
        ];
        view('notice', $data);
    }

	/**
	 * Load Resources (styles and js)
	 */
	public static function loadResources() {
        global $hook_suffix;
        wp_register_style( 'simplicate.css', plugin_dir_url( __FILE__ ) . '../_inc/simplicate.css', array(), SIMPLICATE_VERSION );
        wp_enqueue_style( 'simplicate.css');
    }

	/**
	 * @param string $page
	 *
	 * @return string
	 */
	public static function getPageUrl( $page = 'config' ) {
        $args = ['page' => 'simplicate-key-config'];
        $url = add_query_arg( $args, admin_url( 'options-general.php' ) );
        return $url;
    }
}