<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Zakeke_Cart {

	/**
	 * Setup class.
	 */
	public static function init() {
		add_filter( 'woocommerce_add_to_cart_product_id', array( __CLASS__, 'add_to_cart_product_id' ), 10 );
		add_filter( 'woocommerce_add_cart_item', array( __CLASS__, 'add_cart_item' ), 10 );
		add_filter( 'woocommerce_add_to_cart_redirect', array( __CLASS__, 'add_to_cart_redirect' ), 10, 2 );
		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'add_cart_item_data' ), 10, 4 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'get_cart_item_from_session' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_thumbnail', array( __CLASS__, 'change_cart_item_thumbnail' ), 1000, 3 );
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'item_meta_display' ), 20, 2 );
		add_filter( 'woocommerce_update_cart_action_cart_updated', array( __CLASS__, 'cart_updated' ) );
		add_action( 'woocommerce_checkout_init', array( __CLASS__, 'checkout_init' ) );
		add_action( 'woocommerce_after_cart_item_name', array( __CLASS__, 'after_cart_item_name' ), 20, 2 );
		add_action( 'woocommerce_after_cart_item_name', array( __CLASS__, 'after_cart_item_previews' ), 21, 2 );
	}

	/**
	 * Check if the product added to cart is customized
	 *
	 * @return bool
	 */
	private static function is_zakeke_product() {
		return ( isset( $_REQUEST['zdesign'] ) && ( 'new' !== $_REQUEST['zdesign'] && ! empty( $_REQUEST['zdesign'] ) ) );
	}

	/**
	 * Check if the product added to cart is configured
	 *
	 * @return bool
	 */
	private static function is_zakeke_configurator_product() {
		return ( isset( $_REQUEST['zconfiguration'] ) && ( 'new' !== $_REQUEST['zconfiguration'] && ! empty( $_REQUEST['zconfiguration'] ) ) );
	}

	public static function add_to_cart_product_id( $product_id ) {
		if ( ( self::is_zakeke_product() || self::is_zakeke_configurator_product() ) ) {
			$adding_to_cart = wc_get_product( $product_id );
			$variation_id   = empty( $_REQUEST['variation_id'] ) ? '' : absint( wp_unslash( $_REQUEST['variation_id'] ) );

			if ( $adding_to_cart->is_type( 'variable' ) && empty( $variation_id ) ) {
				$data_store               = WC_Data_Store::load( 'product' );
				$_REQUEST['variation_id'] = $data_store->find_matching_product_variation( $adding_to_cart, wp_unslash( $_REQUEST ) );
			}
		}

		return $product_id;
	}

	public static function add_cart_item( $cart_item ) {
		$integration = new Zakeke_Integration();

		if ( 'yes' === $integration->hide_price ) {
			return $cart_item;
		}

		$zakeke_data = null;
		if ( isset( $cart_item['zakeke_data'] ) ) {
			$zakeke_data = $cart_item['zakeke_data'];
		} elseif ( isset( $cart_item['zakeke_configurator_data'] ) ) {
			$zakeke_data = $cart_item['zakeke_configurator_data'];
		}

		if ( $zakeke_data ) {
			$product = $cart_item['data'];

			$final_price = $zakeke_data['price'];
			$product->set_price( $product->get_price('edit') + $final_price );
		}

		return $cart_item;
	}

	public static function add_to_cart_redirect( $url, $adding_to_cart = null ) {
		if ( isset( $_REQUEST['zakeke_return_url'] ) ) {
			return $_REQUEST['zakeke_return_url'];
		}

		return $url;
	}

	public static function add_configurator_cart_item( $cart_item ) {
		$integration = new Zakeke_Integration();

		if ( isset( $cart_item['zakeke_configurator_data'] ) && 'yes' !== $integration->hide_price ) {
			$zakeke_data = $cart_item['zakeke_configurator_data'];

			$product = $cart_item['data'];

			$final_price = $zakeke_data['price'];
			$product->set_price( $product->get_price('edit') + $final_price );
		}

		return $cart_item;
	}

	public static function add_cart_item_data( $cart_item_meta, $product_id, $variation_id, $qty ) {
		if ( self::is_zakeke_product() && ! isset( $cart_item_meta['zakeke_data'] ) ) {
			$webservice = new Zakeke_Webservice();

			if ( empty( $_REQUEST['zdesign'] ) ) {
				return $cart_item_meta;
			}

			$design = sanitize_text_field( wp_unslash( $_REQUEST['zdesign'] ) );

			$zakeke_cart_data = $webservice->cart_info( $design, $qty );

			if ( ! empty( $_REQUEST['product_id'] ) ) {
				$product_id = sanitize_text_field( wp_unslash( $_REQUEST['product_id'] ) );
			} elseif ( ! empty( $_REQUEST['add-to-cart'] ) ) {
				$product_id = sanitize_text_field( wp_unslash( $_REQUEST['add-to-cart'] ) );
			} else {
				return $cart_item_meta;
			}

			$product        = wc_get_product( absint( $product_id ) );
			$original_price = (float) $product->get_price();

			$zakeke_price = zakeke_calculate_price( $original_price, $zakeke_cart_data->pricing, $qty );

			if ( get_option( 'woocommerce_tax_display_shop' ) === 'excl' ) {
				$zakeke_tax_price = (float) zakeke_wc_get_price_excluding_tax( $product, array( 'price' => $zakeke_price ) );
			} else {
				$zakeke_tax_price = (float) zakeke_wc_get_price_including_tax( $product, array( 'price' => $zakeke_price ) );
			}

			$zakeke_excl_tax_price = (float) zakeke_wc_get_price_excluding_tax( $product, array( 'price' => $zakeke_price ) );

			$original_final_excl_tax_price = (float) wc_get_price_excluding_tax( $product );

			$cart_item_meta['zakeke_data'] = array(
				'design'                        => $design,
				'previews'                      => $zakeke_cart_data->previews,
				'pricing'                       => $zakeke_cart_data->pricing,
				'price'                         => $zakeke_price,
				'price_tax'                     => $zakeke_tax_price,
				'price_excl_tax'                => $zakeke_excl_tax_price,
				'original_final_price'          => $original_price,
				'original_final_excl_tax_price' => $original_final_excl_tax_price
			);
		} elseif ( self::is_zakeke_configurator_product() ) {
			$webservice = new Zakeke_Webservice();

			// Sanitize
			$qty = 1;
			if ( ! empty( $_REQUEST['quantity'] ) ) {
				$qty = wc_stock_amount( preg_replace( '/[^0-9\.]/', '', sanitize_text_field( wp_unslash( $_REQUEST['quantity'] ) ) ) );
				if ( $qty <= 0 ) {
					$qty = 1;
				}
			}

			if ( empty( $_REQUEST['zconfiguration'] ) ) {
				return $cart_item_meta;
			}

			$zakeke_configuration = sanitize_text_field( wp_unslash( $_REQUEST['zconfiguration'] ) );

			$zakeke_cart_data = $webservice->configurator_cart_info( $zakeke_configuration, $qty );

			if ( ! empty( $_REQUEST['product_id'] ) ) {
				$product_id = sanitize_text_field( wp_unslash( $_REQUEST['product_id'] ) );
			} elseif ( ! empty( $_REQUEST['add-to-cart'] ) ) {
				$product_id = sanitize_text_field( wp_unslash( $_REQUEST['add-to-cart'] ) );
			} else {
				return $cart_item_meta;
			}

			$product        = wc_get_product( absint( $product_id ) );
			$original_price = (float) $product->get_price();

			$zakeke_price = $zakeke_cart_data['price'];

			if ( get_option( 'woocommerce_tax_display_shop' ) === 'excl' ) {
				$zakeke_tax_price = (float) zakeke_wc_get_price_excluding_tax( $product, array( 'price' => $zakeke_price ) );
			} else {
				$zakeke_tax_price = (float) zakeke_wc_get_price_including_tax( $product, array( 'price' => $zakeke_price ) );
			}

			$zakeke_excl_tax_price = (float) zakeke_wc_get_price_excluding_tax( $product, array( 'price' => $zakeke_price ) );

			$original_final_excl_tax_price = (float) wc_get_price_excluding_tax( $product );

			$additional_properties = null;
			if ( isset( $_REQUEST['zakeke_additional_properties'] ) ) {
				$additional_properties = $_REQUEST['zakeke_additional_properties'];
			}

			$cart_item_meta['zakeke_configurator_data'] = array(
				'composition'                   => $zakeke_configuration,
				'design'                        => $zakeke_cart_data['designID'],
				'preview'                       => $zakeke_cart_data['preview'],
				'price'                         => $zakeke_price,
				'price_tax'                     => $zakeke_tax_price,
				'price_excl_tax'                => $zakeke_excl_tax_price,
				'original_final_price'          => $original_price,
				'original_final_excl_tax_price' => $original_final_excl_tax_price,
				'items'                         => wp_json_encode( $zakeke_cart_data['items'], JSON_HEX_QUOT ),
				'additional_properties'         => $additional_properties
			);
		}

		return $cart_item_meta;
	}

	public static function change_cart_item_thumbnail( $thumbnail, $cart_item = null ) {
		if ( is_null( $cart_item ) || ! class_exists( 'DOMDocument' ) ) {
			return $thumbnail;
		}

		$preview = null;
		if ( isset( $cart_item['zakeke_data'] ) ) {
			if ( function_exists( 'wc_pb_get_bundled_cart_item_container' ) ) {
				$bundle_container_item = wc_pb_get_bundled_cart_item_container( $cart_item );
				if ( $bundle_container_item ) {
					$bundled_item_id = $cart_item['bundled_item_id'];
					$bundled_item    = $bundle_container_item['data']->get_bundled_item( $bundled_item_id );
					if ( $bundled_item ) {
						return $thumbnail;
					}
				}
			}

			$zakeke_data = $cart_item['zakeke_data'];
			$previews    = $zakeke_data['previews'];

			if ( $previews ) {
				$preview = $previews[0]->url;
			}
		} elseif ( isset( $cart_item['zakeke_configurator_data'] ) ) {
			$zakeke_data = $cart_item['zakeke_configurator_data'];
			$preview     = $zakeke_data['preview'];
		}

		if ( $preview ) {
			$integration = new Zakeke_Integration();
			if ( 'no' !== $integration->show_custom_thumbnail ) {
				$dom = new DOMDocument();
				libxml_use_internal_errors( true );

				if (!$thumbnail) {
					$thumbnail = '<img />';
				}

				$dom->loadHTML( $thumbnail );
				$xpath = new DOMXPath( $dom );
				libxml_clear_errors();
				$doc    = $dom->getElementsByTagName( 'img' )->item( 0 );
				$src    = $xpath->query( './/@src' );
				$srcset = $xpath->query( './/@srcset' );

				foreach ( $src as $s ) {
					$s->nodeValue = $preview;
				}

				foreach ( $srcset as $s ) {
					$s->nodeValue = $preview;
				}

				$doc->setAttribute( 'data-src', $preview );
				$doc->setAttribute( 'data-srcset', $preview );

				return $dom->saveXML( $doc );
			}
		}

		return $thumbnail;
	}

	public static function get_cart_item_from_session( $cart_item, $values ) {
		if ( isset( $values['zakeke_data'] ) ) {
			$cart_item['zakeke_data'] = $values['zakeke_data'];
		} elseif ( isset( $values['zakeke_configurator_data'] ) ) {
			$cart_item['zakeke_configurator_data'] = $values['zakeke_configurator_data'];
		}

		if ( isset( $cart_item['zakeke_data'] ) ) {
			self::add_cart_item( $cart_item );
		} elseif ( isset( $cart_item['zakeke_configurator_data'] ) ) {
			self::add_configurator_cart_item( $cart_item );
		}

		return $cart_item;
	}

	public static function item_meta_display( $item_data, $cart_item ) {
		if ( isset( $cart_item['zakeke_data'] ) ) {
			$zakeke_data = $cart_item['zakeke_data'];

			if ( $zakeke_data['price_tax'] > 0.0 ) {
				$integration = new Zakeke_Integration();
				if ( 'yes' === $integration->show_price_in_cart ) {
					$zakeke_price = array(
						'key'   => __( 'Customization Price', 'zakeke' ),
						'value' => wc_price( $zakeke_data['price_tax'] )
					);
					$item_data[]  = $zakeke_price;
				}
			}
		} elseif ( isset( $cart_item['zakeke_configurator_data'] ) ) {
			$zakeke_data = $cart_item['zakeke_configurator_data'];

			$items = apply_filters('zakeke_configurator_items', json_decode( $zakeke_data['items'], true ), $zakeke_data );

			foreach ( $items as $item ) {
				if ( strpos( $item['attributeCode'], 'zakekePlatform' ) !== false ) {
					continue;
				}

				$item_data[] = array(
					'key'   => $item['attributeName'],
					'value' => $item['selectedOptionName']
				);
			}
		}

		return $item_data;
	}

	/**
	 * Handles cart updates
	 *
	 * @param bool $cart_updated
	 *
	 * @return bool
	 * @throws Exception
	 */
	public static function cart_updated( $cart_updated ) {
		$cart_totals = isset( $_REQUEST['cart'] ) ? wp_unslash( $_REQUEST['cart'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$integration = new Zakeke_Integration();

		if ( ! $cart_updated
			 || WC()->cart->is_empty()
			 || ! is_array( $cart_totals )
			 || 'yes' === $integration->hide_price ) {
			return $cart_updated;
		}

		$webservice = new Zakeke_Webservice();

		$cart = WC()->cart->get_cart();
		foreach ( $cart as $cart_item_key => $values ) {
			// Skip product if no updated quantity was posted
			if ( ! isset( $cart_totals[ $cart_item_key ] ) || ! isset( $cart_totals[ $cart_item_key ]['qty'] ) ) {
				continue;
			}

			if ( isset( $values['zakeke_data'] ) ) {
				$cart_updated = true;

				$zakeke_data = $values['zakeke_data'];

				$cart_item_data = &WC()->cart->cart_contents[ $cart_item_key ];

				$qty = zakeke_cart_total_qty_for_design( $zakeke_data['design'], $cart );

				$zakeke_cart_data = $webservice->cart_info( $zakeke_data['design'], $qty );

				$cart_item_data['zakeke_data']['previews'] = $zakeke_cart_data->previews;

				$cart_item_data['zakeke_data']['pricing'] = $zakeke_cart_data->pricing;

				$original_price = $zakeke_data['original_final_price'];

				$zakeke_price = zakeke_calculate_price(
					$original_price,
					$cart_item_data['zakeke_data']['pricing'],
					$qty
				);

				/**
				 * Get the product information.
				 *
				 * @var WC_Product $product
				 */
				$product = $values['data'];

				if ( get_option( 'woocommerce_tax_display_shop' ) === 'excl' ) {
					$zakeke_tax_price = (float) zakeke_wc_get_price_excluding_tax( $product, array( 'price' => $zakeke_price ) );
				} else {
					$zakeke_tax_price = (float) zakeke_wc_get_price_including_tax( $product, array( 'price' => $zakeke_price ) );
				}

				$zakeke_excl_tax_price = (float) zakeke_wc_get_price_excluding_tax( $product, array( 'price' => $zakeke_price ) );

				$cart_item_data['zakeke_data']['price']          = $zakeke_price;
				$cart_item_data['zakeke_data']['price_tax']      = $zakeke_tax_price;
				$cart_item_data['zakeke_data']['price_excl_tax'] = $zakeke_excl_tax_price;

				$product->set_price( $original_price + $zakeke_price );
			} elseif ( isset( $values['zakeke_configurator_data'] ) ) {
				$zakeke_data = $values['zakeke_configurator_data'];

				$cart_item_data = &WC()->cart->cart_contents[ $cart_item_key ];

				$qty = (int) $cart_totals[ $cart_item_key ]['qty'];
				if ( $qty <= 0 ) {
					$qty = 1;
				}

				$zakeke_cart_data = $webservice->configurator_cart_info( $zakeke_data['composition'], $qty );

				$cart_item_data['zakeke_configurator_data']['preview'] = $zakeke_cart_data['preview'];
				$cart_item_data['zakeke_configurator_data']['price']   = $zakeke_cart_data['price'];
				$cart_item_data['zakeke_configurator_data']['items']   = wp_json_encode( $zakeke_cart_data['items'], JSON_HEX_QUOT );

				$original_price = $zakeke_data['original_final_price'];

				$zakeke_price = $cart_item_data['zakeke_configurator_data']['price'];

				/**
				 * Get the product information.
				 *
				 * @var WC_Product $product
				 */
				$product = $values['data'];

				if ( get_option( 'woocommerce_tax_display_shop' ) === 'excl' ) {
					$zakeke_tax_price = (float) zakeke_wc_get_price_excluding_tax( $product, array( 'price' => $zakeke_price ) );
				} else {
					$zakeke_tax_price = (float) zakeke_wc_get_price_including_tax( $product, array( 'price' => $zakeke_price ) );
				}

				$zakeke_excl_tax_price = (float) zakeke_wc_get_price_excluding_tax( $product, array( 'price' => $zakeke_price ) );

				$cart_item_data['zakeke_configurator_data']['price']          = $zakeke_price;
				$cart_item_data['zakeke_configurator_data']['price_tax']      = $zakeke_tax_price;
				$cart_item_data['zakeke_configurator_data']['price_excl_tax'] = $zakeke_excl_tax_price;

				$product->set_price( $original_price + $zakeke_price );
			}
		}

		return $cart_updated;
	}

	public static function checkout_init() {
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'checkout_cart_item_data' ), 20, 2 );
	}

	public static function checkout_cart_item_data( $item_data, $cart_item ) {
		if ( is_cart() ) {
			return $item_data;
		}

		if ( isset( $cart_item['zakeke_data'] ) ) {
			$zakeke_data = $cart_item['zakeke_data'];
			if ( isset( $zakeke_data['previews'] ) ) {
				$previews = $zakeke_data['previews'];

				$integration = new Zakeke_Integration();

				if ( count( $previews ) > 1 && 'yes' === $integration->show_all_sides) {
					$display = '';
					array_shift($previews);
					foreach ($previews as $preview) {
						$display .= '<img src="' . esc_url( $preview->url ) . '" alt="' . esc_attr( $preview->label ) . '" title="' . esc_attr( $preview->label ) . '" width="150" height="150">';
					}
				} elseif ( count( $previews ) >= 3 ) {
					$display = '<img src="' . esc_url( $previews[1]->url ) . '" alt="' . esc_attr( $previews[1]->label ) . '" title="' . esc_attr( $previews[1]->label ) . '" width="150" height="150"><img src="' . esc_url( $previews[2]->url ) . '" alt="' . esc_attr( $previews[2]->label ) . '" title="' . esc_attr( $previews[2]->label ) . '" width="150" height="150">';
				} elseif ( count( $previews ) >= 2 ) {
					$display = '<img src="' . esc_url( $previews[1]->url ) . '" alt="' . esc_attr( $previews[1]->label ) . '" title="' . esc_attr( $previews[1]->label ) . '" width="150" height="150">';
				} else {
					$display = '<img src="' . esc_url( $previews[0]->url ) . '" alt="' . esc_attr__( 'Customization', 'zakeke' ) . '" title="' . esc_attr__( 'Customization', 'zakeke' ) . '" width="150" height="150">';
				}

				$item_data['zakeke_data'] = array(
					'key'     => __( 'Customization', 'zakeke' ),
					'display' => $display
				);
			}
		} elseif ( isset( $cart_item['zakeke_configurator_data'] ) ) {
			$display = '<img src="' . esc_url( $cart_item['zakeke_configurator_data']['preview'] ) . '" width="150" height="150">';

			$item_data['zakeke_data'] = array(
				'key'     => __( 'Preview', 'zakeke' ),
				'display' => $display
			);
		}

		return $item_data;
	}

	public static function after_cart_item_previews( $cart_item, $cart_item_key ) {
		if ( ! isset( $cart_item['zakeke_data'] ) ) {
			return;
		}

		$zakeke_previews = $cart_item['zakeke_data']['previews'];
		array_shift( $zakeke_previews );
		if ( empty( $zakeke_previews ) ) {
			return;
		}

		include zakeke_template_loader( 'zakeke-cart-item.php' );
	}

	public static function after_cart_item_name( $cart_item, $cart_item_key ) {
		if ( ! isset( $cart_item['zakeke_data'] ) ) {
			return;
		}

		$product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

		$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $product->get_permalink( $cart_item ), $cart_item, $cart_item_key );

		$prefix = '?';
		if ( strpos( $product_permalink, '?' ) !== false ) {
			$prefix = '&';
		}

		$zakeke_edit_link = $product_permalink . $prefix . 'zdesign_edit=' . $cart_item['zakeke_data']['design']
							. '&product_id=' . $cart_item['product_id'];

		echo sprintf(
			'<div class="zakeke-cart-edit"><a href="%s">%s</a></div>',
			esc_url( $zakeke_edit_link ),
			esc_html__( 'Edit', 'woocommerce' )
		);
	}
}

Zakeke_Cart::init();
