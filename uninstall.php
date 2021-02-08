<?php

if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

delete_option( 'smodinrewriter-settings' );
delete_option( 'smodinrewriter-activated' );
