<?php
/**
 * Bangladesh delivery charge shipping method.
 *
 * Area rules (first match wins):
 *  1. Per-district rate from "District rates"
 *  2. Inside Dhaka
 *  3. Dhaka sub-area (if a sub-area charge is set)
 *  4. Outside Dhaka
 *
 * @package BDDC
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shipping method class.
 */
class BDDC_Shipping_Method extends WC_Shipping_Method {

	/**
	 * Dhaka district code in WooCommerce.
	 */
	const DHAKA = 'BD-13';

	/**
	 * Dhaka Metropolitan Police thanas, used when "Inside Dhaka" means Dhaka city only.
	 *
	 * @var string[]
	 */
	public static $dhaka_city_areas = array(
		'Adabor', 'Airport', 'Badda', 'Banani', 'Bangshal', 'Bhashantek', 'Cantonment', 'Chawkbazar',
		'Dakshinkhan', 'Darus Salam', 'Demra', 'Dhanmondi', 'Gendaria', 'Gulshan', 'Hatirjheel',
		'Hazaribagh', 'Jatrabari', 'Kadamtali', 'Kafrul', 'Kalabagan', 'Kamrangirchar', 'Khilgaon',
		'Khilkhet', 'Kotwali', 'Lalbagh', 'Mirpur', 'Mohammadpur', 'Motijheel', 'Mugda', 'New Market',
		'Pallabi', 'Paltan', 'Ramna', 'Rampura', 'Rupnagar', 'Sabujbagh', 'Shah Ali', 'Shahbagh',
		'Shahjahanpur', 'Sher-e-Bangla Nagar', 'Shyampur', 'Sutrapur', 'Tejgaon', 'Tejgaon Industrial Area',
		'Turag', 'Uttara', 'Uttara East', 'Uttara West', 'Uttarkhan', 'Vatara', 'Wari', 'Dhaka', 'Dhaka City',
	);

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Shipping zone method instance ID.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'bd_delivery_charge';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'BD Delivery Charge', 'bddc' );
		$this->method_description = __( 'Inside Dhaka, Dhaka sub-area and Outside Dhaka delivery charges, with per-district rates and free delivery.', 'bddc' );
		$this->supports           = array( 'shipping-zones', 'instance-settings', 'instance-settings-modal' );

		$this->init();
	}

	/**
	 * Load settings.
	 */
	public function init() {
		$this->instance_form_fields = $this->get_fields();
		$this->title                = $this->get_option( 'title' );
		$this->tax_status           = $this->get_option( 'tax_status' );

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * District options for the sub-area field.
	 *
	 * @return array<string, string>
	 */
	private function district_options() {
		$states = WC()->countries ? WC()->countries->get_states( 'BD' ) : array();
		return is_array( $states ) ? $states : array();
	}

	/**
	 * Settings fields.
	 *
	 * @return array
	 */
	private function get_fields() {
		return array(
			'title'           => array(
				'title'   => __( 'Name', 'bddc' ),
				'type'    => 'text',
				'default' => __( 'Delivery charge', 'bddc' ),
			),
			'show_area'       => array(
				'title'   => __( 'Show area in name', 'bddc' ),
				'type'    => 'checkbox',
				'label'   => __( 'e.g. "Delivery charge (Inside Dhaka)"', 'bddc' ),
				'default' => 'yes',
			),
			'inside_cost'     => array(
				'title'       => __( 'Inside Dhaka charge', 'bddc' ),
				'type'        => 'price',
				'default'     => '60',
				'placeholder' => '60',
			),
			'inside_area'     => array(
				'title'   => __( '"Inside Dhaka" means', 'bddc' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'default' => 'district',
				'options' => array(
					'district' => __( 'The whole Dhaka district', 'bddc' ),
					'city'     => __( 'Dhaka city thanas only (rest of Dhaka district uses the sub-area charge)', 'bddc' ),
				),
			),
			'suburb_cost'     => array(
				'title'       => __( 'Dhaka sub-area charge', 'bddc' ),
				'type'        => 'price',
				'default'     => '',
				'placeholder' => __( 'Leave empty to use the Outside Dhaka charge', 'bddc' ),
				'desc_tip'    => __( 'For areas near Dhaka such as Gazipur, Narayanganj, Savar and Keraniganj.', 'bddc' ),
			),
			'suburb_districts' => array(
				'title'   => __( 'Sub-area districts', 'bddc' ),
				'type'    => 'multiselect',
				'class'   => 'wc-enhanced-select',
				'default' => array( 'BD-18', 'BD-40' ),
				'options' => $this->district_options(),
			),
			'outside_cost'    => array(
				'title'       => __( 'Outside Dhaka charge', 'bddc' ),
				'type'        => 'price',
				'default'     => '120',
				'placeholder' => '120',
			),
			'district_rates'  => array(
				'title'       => __( 'District rates', 'bddc' ),
				'type'        => 'textarea',
				'default'     => '',
				'placeholder' => "Chattogram = 100\nSylhet = 130",
				'description' => __( 'Optional. One per line: district name (or code like BD-10) = charge. Overrides the charges above.', 'bddc' ),
				'css'         => 'height:90px;',
			),
			'free_above'      => array(
				'title'       => __( 'Free delivery above', 'bddc' ),
				'type'        => 'price',
				'default'     => '',
				'placeholder' => __( 'Leave empty to turn off', 'bddc' ),
				'desc_tip'    => __( 'Cart subtotal from which delivery is free.', 'bddc' ),
			),
			'free_scope'      => array(
				'title'   => __( 'Free delivery applies to', 'bddc' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'default' => 'all',
				'options' => array(
					'all'    => __( 'All areas', 'bddc' ),
					'inside' => __( 'Inside Dhaka only', 'bddc' ),
				),
			),
			'tax_status'      => array(
				'title'   => __( 'Tax status', 'bddc' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'default' => 'none',
				'options' => array(
					'taxable' => __( 'Taxable', 'bddc' ),
					'none'    => _x( 'None', 'Tax status', 'bddc' ),
				),
			),
		);
	}

	/**
	 * Parse the "District rates" textarea into code => cost.
	 *
	 * @return array<string, float>
	 */
	public function get_district_rates() {
		$states = $this->district_options();
		$lookup = array();

		foreach ( $states as $code => $name ) {
			$lookup[ strtolower( $code ) ] = $code;
			$lookup[ self::simplify( $name ) ] = $code;
		}

		$rates = array();
		$lines = preg_split( '/[\r\n]+/', (string) $this->get_option( 'district_rates' ) );

		foreach ( (array) $lines as $line ) {
			if ( false === strpos( $line, '=' ) ) {
				continue;
			}

			list( $district, $cost ) = array_map( 'trim', explode( '=', $line, 2 ) );
			$key = isset( $lookup[ strtolower( $district ) ] ) ? strtolower( $district ) : self::simplify( $district );

			if ( isset( $lookup[ $key ] ) && is_numeric( $cost ) ) {
				$rates[ $lookup[ $key ] ] = (float) $cost;
			}
		}

		return $rates;
	}

	/**
	 * Lowercase letters only, for forgiving name matching.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	private static function simplify( $text ) {
		return preg_replace( '/[^a-z]/', '', strtolower( remove_accents( (string) $text ) ) );
	}

	/**
	 * Work out the area and charge for a destination.
	 *
	 * @param string $state District code (BD-XX).
	 * @param string $city  City / upazila / thana.
	 * @return array{area: string, cost: float}
	 */
	public function get_area_and_cost( $state, $city ) {
		$state  = strtoupper( (string) $state );
		$rates  = $this->get_district_rates();
		$inside = (float) $this->get_option( 'inside_cost' );
		$out    = (float) $this->get_option( 'outside_cost' );
		$suburb = '' === trim( (string) $this->get_option( 'suburb_cost' ) ) ? null : (float) $this->get_option( 'suburb_cost' );

		if ( isset( $rates[ $state ] ) ) {
			$result = array(
				'area' => 'district',
				'cost' => $rates[ $state ],
			);
		} elseif ( self::DHAKA === $state && ( 'district' === $this->get_option( 'inside_area' ) || $this->is_dhaka_city( $city ) ) ) {
			$result = array(
				'area' => 'inside',
				'cost' => $inside,
			);
		} elseif ( null !== $suburb && ( self::DHAKA === $state || in_array( $state, (array) $this->get_option( 'suburb_districts', array() ), true ) ) ) {
			$result = array(
				'area' => 'suburb',
				'cost' => $suburb,
			);
		} else {
			$result = array(
				'area' => 'outside',
				'cost' => $out,
			);
		}

		return apply_filters( 'bddc_area_and_cost', $result, $state, $city, $this );
	}

	/**
	 * Whether a city / thana name is inside Dhaka city.
	 *
	 * @param string $city City.
	 * @return bool
	 */
	public function is_dhaka_city( $city ) {
		$city  = self::simplify( $city );
		$areas = apply_filters( 'bddc_dhaka_city_areas', self::$dhaka_city_areas );

		if ( '' === $city ) {
			return false;
		}

		foreach ( $areas as $area ) {
			$area = self::simplify( $area );

			// Exact match, or a typed address like "Mirpur 10" / "Uttara Sector 7".
			if ( $city === $area || ( strlen( $area ) >= 5 && 0 === strpos( $city, $area ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Area label shown next to the method name.
	 *
	 * @param string $area  Area key.
	 * @param string $state District code.
	 * @return string
	 */
	public function area_label( $area, $state ) {
		switch ( $area ) {
			case 'inside':
				return __( 'Inside Dhaka', 'bddc' );
			case 'suburb':
				return __( 'Dhaka sub-area', 'bddc' );
			case 'district':
				$states = $this->district_options();
				return isset( $states[ $state ] ) ? $states[ $state ] : $state;
			default:
				return __( 'Outside Dhaka', 'bddc' );
		}
	}

	/**
	 * Calculate the rate.
	 *
	 * @param array $package Shipping package.
	 */
	public function calculate_shipping( $package = array() ) {
		$destination = isset( $package['destination'] ) ? $package['destination'] : array();
		$country     = isset( $destination['country'] ) ? $destination['country'] : '';

		if ( 'BD' !== $country ) {
			return; // This method is only for Bangladesh addresses.
		}

		$state = isset( $destination['state'] ) ? $destination['state'] : '';
		$city  = isset( $destination['city'] ) ? $destination['city'] : '';
		$calc  = $this->get_area_and_cost( $state, $city );
		$cost  = $calc['cost'];

		$free_above = trim( (string) $this->get_option( 'free_above' ) );
		$subtotal   = isset( $package['contents_cost'] ) ? (float) $package['contents_cost'] : 0;

		if ( '' !== $free_above && $subtotal >= (float) $free_above
			&& ( 'all' === $this->get_option( 'free_scope' ) || 'inside' === $calc['area'] )
		) {
			$cost = 0;
		}

		$label = $this->get_title();

		if ( 'yes' === $this->get_option( 'show_area' ) && '' !== $state ) {
			$label .= ' (' . $this->area_label( $calc['area'], $state ) . ')';
		}

		$this->add_rate(
			array(
				'id'        => $this->get_rate_id(),
				'label'     => $label,
				'cost'      => max( 0, $cost ),
				'package'   => $package,
				'meta_data' => array(
					'bddc_area' => $calc['area'],
				),
			)
		);
	}
}
