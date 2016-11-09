<?php

if( !function_exists('view') ) {
    function view($name, $data = []) {
        extract($data);

        return require SIMPLICATE__PLUGIN_DIR . "views/{$name}.php";
    }
}