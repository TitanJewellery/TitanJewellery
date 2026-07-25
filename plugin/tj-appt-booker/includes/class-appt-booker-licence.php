<?php
/**
 * TJ Appointment Booker offline licence enforcement.
 *
 * Frozen key format 5. This file is product-specific and must remain embedded
 * in TJ Appointment Booker. Do not share it between products.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TJ_Appt_Booker_Licence' ) ) {
	final class TJ_Appt_Booker_Licence {
		const KEY_LENGTH         = 100;
		const CONSTANT           = 125870;
		const KEY_FORMAT_VERSION = 5;
		const TRIAL_GRACE_DAYS   = 0;
		const ANNUAL_GRACE_DAYS  = 14;
		const PRODUCT_SLUG       = 'tj-appt-booker';
		const PRODUCT_LABEL      = 'TJ Appointment Booker';
		const PURCHASE_URL       = 'https://titanjewellery.co.uk';

		private static $instance = null;
		private $validation_cache = array();
		private $option_key = 'tj_licence_key_tj-appt-booker';
		private $highest_serial_key = 'tj_licence_serial_tj-appt-booker';

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			add_action( 'admin_menu', array( $this, 'register_menu' ), 20 );
			add_action( 'admin_init', array( $this, 'handle_form' ) );
			add_action( 'admin_notices', array( $this, 'admin_notice' ) );
		}

		private function positions() {
			return array(
				61, 4, 88, 17, 45, 72, 9, 33,
				96, 25,
				12, 57, 80, 41, 68, 2,
				91, 20, 50, 76, 6, 37, 84, 15,
				63, 29, 94, 47,
				70, 18, 53,
				43,
				98,
				86, 31,
			);
		}

		private function cipher_decode( $letters ) {
			$map = array(
				'J' => '1', 'O' => '2', 'H' => '3', 'N' => '4', 'W' => '5',
				'A' => '6', 'L' => '7', 'K' => '8', 'E' => '9',
				'R' => '0', 'X' => '0', 'Y' => '0',
			);
			$out = '';
			$letters = strtoupper( (string) $letters );
			for ( $i = 0, $length = strlen( $letters ); $i < $length; $i++ ) {
				if ( ! isset( $map[ $letters[ $i ] ] ) ) {
					return false;
				}
				$out .= $map[ $letters[ $i ] ];
			}
			return $out;
		}

		private function checksum( $digits ) {
			$sum = 0;
			for ( $i = 0, $length = strlen( $digits ); $i < $length; $i++ ) {
				$sum += (int) $digits[ $i ] * ( $i + 1 );
			}
			return sprintf( '%02d', $sum % 97 );
		}

		private function domain_digits( $name ) {
			$name = strtolower( trim( (string) $name ) );
			$name = preg_replace( '/[^a-z0-9\-]/', '', $name );
			if ( '' === $name ) {
				return '';
			}
			$hex = substr( hash( 'sha256', 'tjdomain:' . $name ), 0, 8 );
			return sprintf( '%08d', hexdec( $hex ) % 100000000 );
		}

		private function product_digits() {
			$hex = substr( hash( 'sha256', 'tjproduct:' . self::PRODUCT_SLUG ), 0, 8 );
			return sprintf( '%04d', hexdec( $hex ) % 10000 );
		}

		private function expiry_ts( $date ) {
			$dt = DateTimeImmutable::createFromFormat( '!Y-m-d H:i:s', $date . ' 23:59:59', new DateTimeZone( 'UTC' ) );
			$errors = DateTimeImmutable::getLastErrors();
			if ( ! $dt || ( $errors && ( $errors['warning_count'] || $errors['error_count'] ) ) ) {
				return 0;
			}
			return $dt->getTimestamp();
		}

		private function add_one_calendar_month( $date ) {
			$dt = DateTimeImmutable::createFromFormat( '!Y-m-d', $date, new DateTimeZone( 'UTC' ) );
			if ( ! $dt ) {
				return '';
			}
			$day = (int) $dt->format( 'd' );
			$first_next = $dt->modify( 'first day of next month' );
			$last_day = (int) $first_next->format( 't' );
			return $first_next->setDate( (int) $first_next->format( 'Y' ), (int) $first_next->format( 'm' ), min( $day, $last_day ) )->format( 'Y-m-d' );
		}

		public function decode( $key ) {
			$key = preg_replace( '/[\s-]+/', '', (string) $key );
			if ( strlen( $key ) !== self::KEY_LENGTH || preg_match( '/[^A-Za-z0-9]/', $key ) ) {
				return new WP_Error( 'tj_licence_length', 'The licence key is not the correct length.' );
			}

			// Position 98 is permanently reserved for the format digit.
			$format_digit = $this->cipher_decode( $key[98] );
			if ( false === $format_digit ) {
				return new WP_Error( 'tj_licence_chars', 'The licence key contains invalid characters.' );
			}
			$format = (int) $format_digit;
			if ( self::KEY_FORMAT_VERSION !== $format ) {
				return new WP_Error( 'tj_licence_format', 'This licence key uses format ' . $format . ', which this version does not support.' );
			}

			$cipher = '';
			foreach ( $this->positions() as $position ) {
				$cipher .= $key[ $position ];
			}
			$digits = $this->cipher_decode( $cipher );
			if ( false === $digits ) {
				return new WP_Error( 'tj_licence_chars', 'The licence key contains invalid characters.' );
			}
			$body = substr( $digits, 0, 33 );
			$check = substr( $digits, 33, 2 );
			if ( $check !== $this->checksum( $body ) ) {
				return new WP_Error( 'tj_licence_checksum', 'The licence key checksum failed.' );
			}
			$input1 = (int) substr( $body, 0, 8 );
			$mult = (int) substr( $body, 8, 2 );
			if ( $mult < 10 || $mult > 99 || $input1 !== self::CONSTANT * $mult ) {
				return new WP_Error( 'tj_licence_constant', 'The licence key is not valid.' );
			}
			$day = (int) substr( $body, 10, 2 );
			$month = (int) substr( $body, 12, 2 );
			$year = 2000 + (int) substr( $body, 14, 2 );
			if ( ! checkdate( $month, $day, $year ) ) {
				return new WP_Error( 'tj_licence_date', 'The licence key contains an invalid expiry date.' );
			}
			$type_code = substr( $body, 31, 1 );
			if ( ! in_array( $type_code, array( '1', '2' ), true ) ) {
				return new WP_Error( 'tj_licence_type', 'The licence type is not recognised.' );
			}
			return array(
				'key_format_version' => $format,
				'expires_on' => sprintf( '%04d-%02d-%02d', $year, $month, $day ),
				'domain_digits' => substr( $body, 16, 8 ),
				'product_digits' => substr( $body, 24, 4 ),
				'serial' => (int) substr( $body, 28, 3 ),
				'licence_type' => '2' === $type_code ? 'trial' : 'annual',
			);
		}

		public function validate( $key = null ) {
			if ( null === $key ) {
				$key = get_option( $this->option_key, '' );
			}
			$cache_key = md5( (string) $key );
			if ( array_key_exists( $cache_key, $this->validation_cache ) ) {
				return $this->validation_cache[ $cache_key ];
			}
			if ( '' === trim( (string) $key ) ) {
				return $this->validation_cache[ $cache_key ] = new WP_Error( 'tj_licence_missing', 'No licence key has been entered.' );
			}
			$data = $this->decode( $key );
			if ( is_wp_error( $data ) ) {
				return $this->validation_cache[ $cache_key ] = $data;
			}
			if ( $data['product_digits'] !== $this->product_digits() ) {
				return $this->validation_cache[ $cache_key ] = new WP_Error( 'tj_licence_product', 'This key belongs to another product.' );
			}
			$host = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
			$domain_match = false;
			foreach ( explode( '.', $host ) as $label ) {
				if ( $this->domain_digits( $label ) === $data['domain_digits'] ) {
					$domain_match = true;
					break;
				}
			}
			if ( ! $domain_match ) {
				return $this->validation_cache[ $cache_key ] = new WP_Error( 'tj_licence_domain', 'This key belongs to another website.' );
			}
			$highest = (int) get_option( $this->highest_serial_key, 0 );
			if ( $data['serial'] < $highest ) {
				return $this->validation_cache[ $cache_key ] = new WP_Error( 'tj_licence_replaced', 'This key has been replaced by a newer one.' );
			}
			$expiry_ts = $this->expiry_ts( $data['expires_on'] );
			$now = time();
			$grace_days = 'trial' === $data['licence_type'] ? self::TRIAL_GRACE_DAYS : self::ANNUAL_GRACE_DAYS;
			$grace_end_ts = $expiry_ts + ( $grace_days * DAY_IN_SECONDS );
			$restriction_date = 'annual' === $data['licence_type'] ? $this->add_one_calendar_month( $data['expires_on'] ) : '';
			$restriction_ts = $restriction_date ? strtotime( $restriction_date . ' 00:00:00 UTC' ) : 0;
			$data['days_left'] = (int) floor( ( $expiry_ts - $now ) / DAY_IN_SECONDS );
			$data['grace_days'] = $grace_days;
			$data['grace_end_ts'] = $grace_end_ts;
			$data['restriction_date'] = $restriction_date;
			$data['restriction_ts'] = $restriction_ts;
			if ( 'trial' === $data['licence_type'] ) {
				$data['status'] = $now > $expiry_ts ? 'disabled' : 'active';
			} elseif ( $now <= $expiry_ts ) {
				$data['status'] = 'active';
			} elseif ( $now <= $grace_end_ts ) {
				$data['status'] = 'grace';
			} elseif ( $restriction_ts && $now < $restriction_ts ) {
				$data['status'] = 'expired';
			} else {
				$data['status'] = 'restricted';
			}
			return $this->validation_cache[ $cache_key ] = $data;
		}

		public function save_key( $key ) {
			$data = $this->validate( $key );
			if ( is_wp_error( $data ) ) {
				return $data;
			}
			$clean = preg_replace( '/[\s-]+/', '', (string) $key );
			if ( false === update_option( $this->option_key, $clean, false ) && get_option( $this->option_key, '' ) !== $clean ) {
				return new WP_Error( 'tj_licence_save', 'WordPress could not save the licence key.' );
			}
			$highest = max( (int) get_option( $this->highest_serial_key, 0 ), (int) $data['serial'] );
			update_option( $this->highest_serial_key, $highest, false );
			$this->validation_cache = array();
			return $this->validate( $clean );
		}

		public function booking_form_allowed() {
			$status = $this->validate();
			return ! is_wp_error( $status ) && ! ( 'trial' === $status['licence_type'] && 'disabled' === $status['status'] );
		}

		public function booking_date_allowed( $date ) {
			$status = $this->validate();
			if ( is_wp_error( $status ) ) {
				return false;
			}
			if ( 'trial' === $status['licence_type'] ) {
				return 'disabled' !== $status['status'] && $date <= $status['expires_on'];
			}
			return true;
		}

		public function booking_date_is_restricted( $date ) {
			$status = $this->validate();
			return ! is_wp_error( $status ) && 'annual' === $status['licence_type'] && ! empty( $status['restriction_date'] ) && $date >= $status['restriction_date'];
		}

		public function should_brand_email() {
			$status = $this->validate();

			// A missing, invalid, replaced or wrong-domain key is unlicensed.
			if ( is_wp_error( $status ) ) {
				return true;
			}

			// A valid trial remains fully licensed until the end of its expiry date.
			if ( 'trial' === $status['licence_type'] ) {
				return 'disabled' === $status['status'];
			}

			// Annual emails are branded only once the one-booking-per-day
			// restriction has actually begun, not during grace or warning time.
			return 'restricted' === $status['status'];
		}

		private function append_html_fragment( $message, $fragment ) {
			$position = stripos( $message, '</body>' );
			if ( false === $position ) {
				return $message . $fragment;
			}
			return substr( $message, 0, $position ) . $fragment . substr( $message, $position );
		}

		public function append_email_notice( $message, $is_html = true, $audience = 'internal' ) {
			if ( ! $this->should_brand_email() ) {
				return $message;
			}

			$audience = ( 'customer' === sanitize_key( $audience ) ) ? 'customer' : 'internal';

			if ( 'customer' === $audience ) {
				if ( $is_html ) {
					$footer = '<div style="margin:22px auto 0;max-width:640px;padding:12px 16px;border-top:1px solid #d8d8d8;color:#777;font:400 11px/1.5 Arial,sans-serif;text-align:center;">Online booking powered by Appointment Booker from <a href="' . esc_url( self::PURCHASE_URL ) . '" style="color:#666;text-decoration:underline;">Titan Jewellery</a>.</div>';
					return $this->append_html_fragment( $message, $footer );
				}
				return $message . "\n\nOnline booking powered by Appointment Booker from Titan Jewellery: " . self::PURCHASE_URL;
			}

			if ( $is_html ) {
				$warning = '<div style="margin:28px auto 0;max-width:640px;padding:18px;border:4px solid #b32d2e;background:#fff0f0;color:#7a1616;font:700 16px/1.5 Arial,sans-serif;text-align:center;">UNLICENSED SOFTWARE<br>This email was generated by unlicensed software.<br><a href="' . esc_url( self::PURCHASE_URL ) . '" style="color:#7a1616;text-decoration:underline;">Renew the Appointment Booker licence</a> to remove restrictions and customer-facing branding.</div>';
				return $this->append_html_fragment( $message, $warning );
			}
			return $message . "\n\n========================================\nUNLICENSED SOFTWARE\nThis email was generated by unlicensed software.\nRenew the Appointment Booker licence to remove restrictions and customer-facing branding: " . self::PURCHASE_URL . "\n========================================";
		}

		public function public_block_notice() {
			$status = $this->validate();
			if ( ! is_wp_error( $status ) && ! ( 'trial' === $status['licence_type'] && 'disabled' === $status['status'] ) ) {
				return '';
			}
			return '<div class="tj-unlicensed-software" style="margin:18px 0;padding:20px;border:4px solid #b32d2e;background:#fff0f0;color:#7a1616;font-weight:700;text-align:center;">This booking system is unlicensed and cannot accept bookings. <a href="' . esc_url( self::PURCHASE_URL ) . '" rel="follow" style="color:#7a1616;text-decoration:underline;">Contact Titan Jewellery to resolve this</a>.</div>';
		}

		private function format_date( $date ) {
			$ts = strtotime( $date . ' 12:00:00 UTC' );
			return $ts ? wp_date( 'j F Y', $ts, new DateTimeZone( 'UTC' ) ) : $date;
		}

		public function register_menu() {
			add_submenu_page(
				'appt-booker',
				self::PRODUCT_LABEL . ' Licence',
				'Licence',
				'manage_options',
				'tj-appt-booker-licence',
				array( $this, 'render_page' )
			);
		}

		public function handle_form() {
			if ( empty( $_POST['tj_appt_licence_action'] ) ) {
				return;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			check_admin_referer( 'tj_appt_licence_save' );
			$key = isset( $_POST['tj_appt_licence_key'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tj_appt_licence_key'] ) ) : '';
			$result = $this->save_key( $key );
			set_transient( 'tj_appt_licence_notice_' . get_current_user_id(), is_wp_error( $result ) ? array( 'error', $result->get_error_message() ) : array( 'success', 'Licence key saved.' ), 60 );
			wp_safe_redirect( admin_url( 'admin.php?page=tj-appt-booker-licence' ) );
			exit;
		}

		public function render_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			$notice = get_transient( 'tj_appt_licence_notice_' . get_current_user_id() );
			if ( $notice ) {
				delete_transient( 'tj_appt_licence_notice_' . get_current_user_id() );
				echo '<div class="notice notice-' . ( 'error' === $notice[0] ? 'error' : 'success' ) . '"><p>' . esc_html( $notice[1] ) . '</p></div>';
			}
			$status = $this->validate();
			echo '<div class="wrap"><h1>' . esc_html( self::PRODUCT_LABEL ) . ' Licence</h1>';
			$this->render_status_panel( $status );
			echo '<form method="post">';
			wp_nonce_field( 'tj_appt_licence_save' );
			echo '<input type="hidden" name="tj_appt_licence_action" value="save">';
			echo '<table class="form-table"><tr><th><label for="tj_appt_licence_key">Licence key</label></th><td><textarea id="tj_appt_licence_key" name="tj_appt_licence_key" class="large-text code" rows="4">' . esc_textarea( get_option( $this->option_key, '' ) ) . '</textarea><p class="description">Paste the complete licence key exactly as supplied.</p></td></tr></table>';
			submit_button( 'Save licence key' );
			echo '</form></div>';
		}

		private function render_status_panel( $status ) {
			if ( is_wp_error( $status ) ) {
				echo '<div style="max-width:850px;padding:20px;border:3px solid #b32d2e;background:#fff0f0;"><h2 style="margin-top:0;color:#8a1f1f;">UNLICENSED</h2><p>' . esc_html( $status->get_error_message() ) . '</p></div>';
				return;
			}
			$label = 'trial' === $status['licence_type'] ? '30-day trial' : 'Annual licence';
			echo '<div style="max-width:850px;padding:18px;border:1px solid #ccd0d4;background:#fff;"><h2 style="margin-top:0;">' . esc_html( ucfirst( $status['status'] ) ) . '</h2><p><strong>Type:</strong> ' . esc_html( $label ) . '<br><strong>Expires:</strong> ' . esc_html( $this->format_date( $status['expires_on'] ) ) . '<br><strong>Serial:</strong> ' . (int) $status['serial'] . '<br><strong>Key format:</strong> ' . (int) $status['key_format_version'] . '</p>';
			if ( 'annual' === $status['licence_type'] ) {
				echo '<p><strong>Booking restriction date:</strong> ' . esc_html( $this->format_date( $status['restriction_date'] ) ) . '<br>Appointment dates on or after this date are limited to 1 booking per day unless the licence is renewed.</p>';
			}
			echo '</div>';
		}

		public function admin_notice() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			$status = $this->validate();
			$licence_url = admin_url( 'admin.php?page=tj-appt-booker-licence' );
			if ( is_wp_error( $status ) ) {
				echo '<div class="notice notice-error" style="border-left-width:8px;padding:14px 18px;"><h2 style="margin:5px 0;">' . esc_html( self::PRODUCT_LABEL ) . ' is unlicensed</h2><p style="font-size:15px;">The public booking form is disabled until a valid licence key is entered.</p><p><a class="button button-primary" href="' . esc_url( $licence_url ) . '">Enter licence key</a></p></div>';
				return;
			}
			if ( 'trial' === $status['licence_type'] ) {
				if ( 'disabled' === $status['status'] ) {
					echo '<div class="notice notice-error" style="border-left-width:8px;padding:14px 18px;"><h2 style="margin:5px 0;">' . esc_html( self::PRODUCT_LABEL ) . ' trial has ended</h2><p>The public booking form is disabled. Enter an annual licence key to restore bookings.</p></div>';
					return;
				}
				$days = max( 0, (int) $status['days_left'] );
				if ( $days <= 7 ) {
					echo '<div class="notice notice-warning" style="border-left-width:6px;padding:12px 16px;"><h3 style="margin:5px 0;">Trial ends in ' . $days . ' days</h3><p>The booking form will stop accepting bookings after ' . esc_html( $this->format_date( $status['expires_on'] ) ) . ' unless an annual licence is entered.</p></div>';
				} elseif ( $days <= 30 ) {
					echo '<div class="notice notice-info"><p><strong>' . esc_html( self::PRODUCT_LABEL ) . ' trial:</strong> ' . $days . ' days remaining.</p></div>';
				}
				return;
			}

			$restriction = $this->format_date( $status['restriction_date'] );
			$days = (int) $status['days_left'];
			if ( 'restricted' === $status['status'] ) {
				echo '<div class="notice notice-error" style="border-left-width:10px;padding:16px 20px;"><h2 style="margin:4px 0;">' . esc_html( self::PRODUCT_LABEL ) . ' is restricted</h2><p style="font-size:15px;"><strong>Appointment dates on or after ' . esc_html( $restriction ) . ' are limited to 1 booking per day.</strong> Cancelling or moving that booking does not reopen the date. Please renew the licence to lift this restriction.</p><p><a class="button button-primary" href="' . esc_url( $licence_url ) . '">Enter renewed licence</a></p></div>';
				return;
			}
			if ( in_array( $status['status'], array( 'grace', 'expired' ), true ) ) {
				echo '<div class="notice notice-error" style="border-left-width:8px;padding:13px 18px;"><h2 style="margin:4px 0;">' . esc_html( self::PRODUCT_LABEL ) . ' licence has expired</h2><p>Existing bookings continue normally. Please note that appointment dates on or after <strong>' . esc_html( $restriction ) . '</strong> will be limited to just <strong>1 booking per day</strong>. Please renew the licence to lift this restriction.</p></div>';
				return;
			}
			if ( $days > 30 ) {
				return;
			}
			if ( $days <= 7 ) {
				echo '<div class="notice notice-warning" style="border-left-width:7px;padding:12px 17px;"><h3 style="margin:5px 0;">Licence expires in ' . max( 0, $days ) . ' days</h3><p>Please note that appointment dates on or after <strong>' . esc_html( $restriction ) . '</strong> will be limited to just <strong>1 booking per day</strong> unless the licence is renewed.</p></div>';
			} else {
				echo '<div class="notice notice-warning"><p><strong>' . esc_html( self::PRODUCT_LABEL ) . ' licence expires in ' . $days . ' days.</strong> Appointment dates on or after ' . esc_html( $restriction ) . ' will be limited to 1 booking per day unless renewed.</p></div>';
			}
		}
	}
}

TJ_Appt_Booker_Licence::instance();

function tj_appt_licence_status() {
	return TJ_Appt_Booker_Licence::instance()->validate();
}
function tj_appt_licence_booking_form_allowed() {
	return TJ_Appt_Booker_Licence::instance()->booking_form_allowed();
}
function tj_appt_licence_booking_date_allowed( $date ) {
	return TJ_Appt_Booker_Licence::instance()->booking_date_allowed( $date );
}
function tj_appt_licence_booking_date_restricted( $date ) {
	return TJ_Appt_Booker_Licence::instance()->booking_date_is_restricted( $date );
}
function tj_appt_licence_public_notice() {
	return TJ_Appt_Booker_Licence::instance()->public_block_notice();
}
function tj_appt_licence_email_message( $message, $is_html = true, $audience = 'internal' ) {
	return TJ_Appt_Booker_Licence::instance()->append_email_notice( $message, $is_html, $audience );
}
