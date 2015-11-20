<?php
/**
 * Plugin Name: Unmask
 * Plugin URI:  https://github.com/ericmann/unmask
 * Description: Expose support for WordPress
 * Version:     1.0.0
 * Author:      Eric Mann
 * Author URI:  https://eamann.com
 * License:     MIT
 * Text Domain: unmask
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2015 10up (email : eric@eamann.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built using yo wp-make:plugin
 * Copyright (c) 2015 10up, LLC
 * https://github.com/10up/generator-wp-make
 */

// Useful global constants
define( 'UNMASK_VERSION', '1.0.0' );
define( 'UNMASK_URL',     plugin_dir_url( __FILE__ ) );
define( 'UNMASK_PATH',    dirname( __FILE__ ) . '/' );
define( 'UNMASK_INC',     UNMASK_PATH . 'includes/' );

// Include files
require_once UNMASK_INC . 'functions/core.php';

// Activation/Deactivation
register_activation_hook(   __FILE__, '\EAMann\Unmask\Core\activate' );
register_deactivation_hook( __FILE__, '\EAMann\Unmask\Core\deactivate' );

// Bootstrap
EAMann\Unmask\Core\setup();