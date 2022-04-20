<?php
/**
 * Plugin Name: Web-PYME Ajustes Quotes for WooCommerce
 * Plugin URI: https://web-pyme.cl
 * Author: Arne Saknussemm
 * Author URI: https://web-pyme.cl
 * Description: Esconde campos y modifica los títulos de las páginas condicionalmente
 * Version: 0.1.0
 * License: 0.1.0
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: web-pyme
*/

// Verifica que WooComerce está activo.
$plugin_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';
if ( in_array( $plugin_path, wp_get_active_and_valid_plugins() ) || in_array( $plugin_path, wp_get_active_network_plugins() ) )
{
	add_action( 'woocommerce_init', 'dale_cotizador' );
}
/**
 * Funciones para mejorar características del plugin "Quotes for WooCommerce"
 */
function dale_cotizador()
{
	add_filter( 'the_title', 'titulos_cotizador', 10, 2 );
	add_filter( 'body_class', 'clases_cotizador' );
	add_filter( 'gettext', 'textos_cotizador', 10, 3 );
	// Agrega el estilo para que se oculten precios y otras cosas mientras se cotiza.
	add_action( 'wp_enqueue_scripts', 'estilo_web_pyme_cotizador' );
	}

function estilo_web_pyme_cotizador()
{
	wp_enqueue_style( 'web-pyme-cotizador', plugin_dir_url( __FILE__ ) . './css/style.css', array(), 100 );
}

function orden_recibida_estado()
{
    global $wp;
    $order_id  = $wp->query_vars['order-received'];
    return (new WC_Order( $order_id ))->get_status('edit'); // "edit" para que no devuelva el estado por defecto: "pending".
}

function titulos_cotizador($title, $post_id)
{
	if (is_checkout() && in_the_loop())
	{
		if (cart_contains_quotable()) return 'Finalizar Cotización';
/* 		add_filter( 'woocommerce_endpoint_order-received_title', 'misha_thank_you_title' );
 
		function misha_thank_you_title( $old_title ){
		
			 return 'You\'re awesome!';
		
		}	 */
		elseif (orden_recibida_estado()=='pending')	return 'Solicitud de cotización enviada';
		elseif (!empty(orden_recibida_estado())) return 'Pago realizado';
	}
	return $title;
}

function clases_cotizador( $classes )
{
	if ( cart_contains_quotable() ) $classes[]= 'cotizacion';
	elseif ( is_checkout() && orden_recibida_estado()=='pending') $classes[]='cotizacion pendiente';
	return $classes;
}

function textos_cotizador( $traducido, $original, $dominio )
{
	if ($dominio == 'woocommerce')
	{
		switch ( $original )
		{
			case 'Checkout' :
			case 'Proceed to checkout' :
				if (cart_contains_quotable()) return 'Finalizar Cotización';
				break;
			case 'Order notes' :
				if (cart_contains_quotable()) return 'Notas de la Cotización';
				break;
			case 'Notes about your order, e.g. special notes for delivery.' :
				if (cart_contains_quotable()) return 'Notas sobre tu cotización, por ejemplo, notas especiales para la entrega.';
				break;
			case 'Your order' :
				if (cart_contains_quotable()) return 'Tu cotización';
				break;
			case 'Thank you. Your order has been received.':
				if (orden_recibida_estado()=='pending') return 'Gracias. Tu cotización ha sido recibida.';
				break;
			case 'Order details';
				if (orden_recibida_estado()=='pending') return 'Detalles de la cotización';
				break;
			case 'Payment method:':
				if (orden_recibida_estado()=='pending') return 'Operación:';
				break;
			case 'Order number:':
				return 'Número de la orden';
				break;
			case 'Billing details':
				return 'Datos del cliente y despacho';
				break;
		}
	}
	return $traducido;
}