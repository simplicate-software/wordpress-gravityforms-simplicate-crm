<?php

class Simplicate {
    private static $initiated = false;

    public static function init() {
        if ( ! self::$initiated) {
            self::init_hooks();
        }
    }

    private static function init_hooks() {
        self::$initiated;
    }

    public static function plugin_activation() {
        if (version_compare($GLOBALS['wp_version'], SIMPLICATE__MINIMUM_WP_VERSION ,'<')) {
            load_plugin_textdomain('simplicate');

            $message = '<strong>'.sprintf(esc_html__('Simplicate %s requires Wordpress %s or higher.', 'simplicate'), SIMPLICATE_VERSION, SIMPLICATE__MINIMUM_WP_VERSION).'</strong>';

            Simplicate::bail_on_activation($message);
        }
    }

    private static function bail_on_activation( $message, $deactivate = true ) {
?>
<!doctype html>
<html>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <style>
        html, body {

            height: 100%;
        }
        * {
            margin: 0;
            padding: 0;
            font-family: "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;
        }
        .body {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        p {
            font-size: 18px;
        }
    </style>
<body class="body">
<p><?php echo $message; ?></p>
</body>
</html>
<?php
        exit;
    }
}