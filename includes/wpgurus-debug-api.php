<?php
/**
 * Error logging and notices for the CF7 to Post plugin.
 *
 * This file provides debugging utilities for development environments.
 *
 * @link       https://profiles.wordpress.org/aurovrata/
 * @since      1.0.0
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/includes
 * @author     Aurovrata V. <vrata@syllogic.in>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define debug constant if not already defined.
if ( ! defined( 'WP_GURUS_DEBUG' ) ) {
	define( 'WP_GURUS_DEBUG', false );
}

/**
 * Debug logging function for the CF7 to Post plugin.
 *
 * Logs debug messages to the WordPress error log when WP_GURUS_DEBUG is true.
 * Requires WP_DEBUG, WP_DEBUG_LOG, and WP_DEBUG_DISPLAY to be configured properly.
 *
 * Usage example:
 * ```php
 * wpg_debug( 'User data', 'USER: ', 2 );
 * ```
 *
 * @since 1.0.0
 *
 * @param mixed  $message The message or data to log. Can be string, array, or object.
 * @param string $prefix  Optional. A prefix string to add before the message.
 *                        Default empty string.
 * @param int    $trace   Optional. Number of backtrace lines to include.
 *                        0 = no trace, positive integer = number of lines.
 *                        Default 0.
 * @return void
 */
function wpg_debug( $message, $prefix = '', $trace = 0 ) {
	// Exit early if debugging is disabled.
	if ( ! WP_GURUS_DEBUG ) {
		return;
	}

	// Sanitize and prepare the message.
	$debug_data = wpg_prepare_debug_message( $message, $prefix, $trace );
	
	// Log the message.
	error_log( $debug_data );
}

/**
 * Prepare debug message for logging.
 *
 * @since 5.3.0
 * @access private
 *
 * @param mixed  $message The message or data to log.
 * @param string $prefix  The prefix string.
 * @param int    $trace   Number of backtrace lines to include.
 * @return string The formatted debug message.
 */
function wpg_prepare_debug_message( $message, $prefix, $trace ) {
	static $last_logged_file = '';
	static $last_logged_line = '';

	$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, max( $trace, 1 ) + 1 );
	$caller    = isset( $backtrace[1] ) ? $backtrace[1] : $backtrace[0];
	$file      = wpg_sanitize_file_path( $caller['file'] );
	$line      = isset( $caller['line'] ) ? $caller['line'] : '';

	// Check if this is a duplicate message.
	if ( $file === $last_logged_file && $line === $last_logged_line && 0 === $trace ) {
		return '';
	}

	$msg = 'DEBUG_MSG:';

	// Add separator for trace messages.
	if ( $trace > 0 ) {
		$msg .= ' --------------- ';
	}

	// Format the message content.
	$msg .= wpg_format_debug_content( $message, $prefix );

	// Add backtrace if requested.
	if ( $trace > 0 ) {
		$msg .= wpg_format_backtrace( $backtrace, $trace );
	} else {
		$msg .= PHP_EOL . wpg_format_file_location( $file, $line );
	}

	// Update last logged location.
	$last_logged_file = $file;
	$last_logged_line = $line;

	return $msg;
}

/**
 * Format the debug message content.
 *
 * @since 5.3.0
 * @access private
 *
 * @param mixed  $message The message or data to log.
 * @param string $prefix  The prefix string.
 * @return string The formatted content.
 */
function wpg_format_debug_content( $message, $prefix ) {
	if ( is_array( $message ) || is_object( $message ) ) {
		// Limit array/object depth to prevent memory issues.
		$content = wp_json_encode( $message, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		if ( false === $content ) {
			$content = print_r( $message, true ); // Fallback.
		}
		return $prefix . $content;
	}

	return $prefix . (string) $message;
}

/**
 * Format backtrace for debug logging.
 *
 * @since 5.3.0
 * @access private
 *
 * @param array $backtrace The debug backtrace.
 * @param int   $trace     Number of lines to include.
 * @return string The formatted backtrace.
 */
function wpg_format_backtrace( $backtrace, $trace ) {
	$msg = PHP_EOL;

	// Get the number of entries to show (skip the first entry which is this function).
	$entries = array_slice( $backtrace, 1, $trace );

	foreach ( $entries as $index => $entry ) {
		$line = isset( $entry['line'] ) ? $entry['line'] : '?';
		$file = isset( $entry['file'] ) ? wpg_sanitize_file_path( $entry['file'] ) : 'unknown';
		$msg .= sprintf(
			'[%s]->/%s',
			$line,
			$file
		) . PHP_EOL;
	}

	// Add closing separator.
	if ( ! empty( $entries ) ) {
		$msg .= '-----------------------------------------------------';
	}

	return $msg;
}

/**
 * Format file location for debug logging.
 *
 * @since 5.3.0
 * @access private
 *
 * @param string $file The file path.
 * @param string $line The line number.
 * @return string The formatted file location.
 */
function wpg_format_file_location( $file, $line ) {
	return "/{$file}:{$line}";
}

/**
 * Sanitize file path for debug logging.
 *
 * Removes the plugin directory path to make paths more readable.
 *
 * @since 5.3.0
 * @access private
 *
 * @param string $file The full file path.
 * @return string The sanitized file path.
 */
function wpg_sanitize_file_path( $file ) {
	// Remove plugin directory path.
	$plugin_dir = plugin_dir_path( __FILE__ );
	$file = str_replace( $plugin_dir, '', $file );
	
	// Remove ABSPATH if plugin path removal didn't work.
	if ( false !== strpos( $file, ABSPATH ) ) {
		$file = str_replace( ABSPATH, '', $file );
	}
	
	return $file;
}

/**
 * Check if debug mode is enabled.
 *
 * @since 5.3.0
 * @return bool True if debug mode is enabled.
 */
function wpg_is_debug_enabled() {
	return defined( 'WP_GURUS_DEBUG' ) && WP_GURUS_DEBUG;
}
/**
 * Log memory usage.
 *
 * @since 5.3.0
 * @param string $context The context for the memory log.
 */
function wpg_log_memory_usage( $context = '' ) {
    $memory = memory_get_usage( true );
    $peak   = memory_get_peak_usage( true );
    
    wpg_debug(
        sprintf(
            'Memory usage: %s (peak: %s) %s',
            size_format( $memory ),
            size_format( $peak ),
            $context
        ),
        'MEMORY: '
    );
}