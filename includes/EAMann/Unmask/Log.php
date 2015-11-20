<?php

namespace EAMann\Unmask;

use Psr\Log\LoggerInterface;

class Logger implements  LoggerInterface {

	/**
	 * Log emergency messages
	 *
	 * @param string $message Log message
	 * @param array  $context Extra context information
	 *
	 * @return boolean Log pass/fail
	 */
	public function emergency( $message, array $context = array() ) {
		return $this->log('emergency', $message, $context);
	}

	/**
	 * Log alert messages
	 *
	 * @param string $message Log message
	 * @param array  $context Extra context information
	 *
	 * @return boolean Log pass/fail
	 */
	public function alert( $message, array $context = array() ) {
		return $this->log('alert', $message, $context);
	}

	/**
	 * Log critical messages
	 *
	 * @param string $message Log message
	 * @param array  $context Extra context information
	 *
	 * @return boolean Log pass/fail
	 */
	public function critical( $message, array $context = array() ) {
		return $this->log('critical', $message, $context);
	}

	/**
	 * Log error messages
	 *
	 * @param string $message Log message
	 * @param array  $context Extra context information
	 *
	 * @return boolean Log pass/fail
	 */
	public function error( $message, array $context = array() ) {
		return $this->log('error', $message, $context);
	}

	/**
	 * Log warning messages
	 *
	 * @param string $message Log message
	 * @param array  $context Extra context information
	 *
	 * @return boolean Log pass/fail
	 */
	public function warning( $message, array $context = array() ) {
		return $this->log('warning', $message, $context);
	}

	/**
	 * Log notice messages
	 *
	 * @param string $message Log message
	 * @param array  $context Extra context information
	 *
	 * @return boolean Log pass/fail
	 */
	public function notice( $message, array $context = array() ) {
		return $this->log('notice', $message, $context);
	}

	/**
	 * Log info messages
	 *
	 * @param string $message Log message
	 * @param array  $context Extra context information
	 *
	 * @return boolean Log pass/fail
	 */
	public function info( $message, array $context = array() ) {
		return $this->log('info', $message, $context);
	}

	/**
	 * Log debug messages
	 *
	 * @param string $message Log message
	 * @param array  $context Extra context information
	 *
	 * @return boolean Log pass/fail
	 */
	public function debug( $message, array $context = array() ) {
		return $this->log('debug', $message, $context);
	}

	/**
	 * Push the log message and context information into WordPress as a CPT
	 *
	 * @param string $level   Logging level (ex. info, debug, notice...)
	 * @param string $message Log message
	 * @param array  $context Extra context information
	 *
	 * @return boolean Success/fail of logging
	 */
	public function log( $level, $message, array $context = array() ) {
		// Don't store empty contexts
		if ( empty( $context ) ) {
			return;
		}

		$post = array(
			'post_title'   => ucfirst( $level ) . ' - ' . $message,
			'post_content' => base64_encode( serialize( $context ) ),
			'post_status'  => 'publish',
			'post_type'    => 'unmask_log',
		);

		wp_insert_post( $post );
	}
}