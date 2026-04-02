<?php
/**
 * Plugin Name:       Arc for WP
 * Description:       Simple way to sell products for USDC on Arc Network. Button + QR code.
 * Version:           1.0.1
 * Author:            @AlexandrB27
 * Text Domain:       arc-for-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

class ArcForWP {
    private $usdc_address = '0x3600000000000000000000000000000000000000';
    private $chain_id     = 5042002;
    private $rpc_url      = 'https://rpc.testnet.arc.network';
    private $explorer     = 'https://testnet.arcscan.app';

    public function __construct() {
        add_action('init', [$this, 'init']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_shortcode('arc_buy', [$this, 'render_buy_button']);

        // AJAX
        add_action('wp_ajax_arc_save_order', [$this, 'save_order']);
        add_action('wp_ajax_nopriv_arc_save_order', [$this, 'save_order']);
    }

    public function init() {
        register_activation_hook(__FILE__, [$this, 'create_orders_table']);
        // Register settings
        register_setting('arc_for_wp_settings', 'arc_merchant_address');
    }

    public function create_orders_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'arc_orders';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id bigint(20) unsigned NOT NULL auto_increment,
            product_title varchar(255) NOT NULL,
            price decimal(18,6) NOT NULL,
            buyer_address varchar(66) NOT NULL,
            tx_hash varchar(66) NOT NULL,
            email varchar(100) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function add_admin_menu() {
        add_menu_page('Arc for WP', 'Arc Shop', 'manage_options', 'arc-for-wp', [$this, 'settings_page'], 'dashicons-cart', 58);
        add_submenu_page('arc-for-wp', 'Settings', 'Settings', 'manage_options', 'arc-for-wp', [$this, 'settings_page']);
        add_submenu_page('arc-for-wp', 'Products', 'Products', 'manage_options', 'arc-products', [$this, 'products_page']);
    }

    public function settings_page() {
        include plugin_dir_path(__FILE__) . 'admin/settings.php';
    }

    public function products_page() {
        include plugin_dir_path(__FILE__) . 'admin/products.php';
    }

        public function enqueue_assets() {
        // Load ethers.js first
        wp_enqueue_script('ethers', 'https://cdnjs.cloudflare.com/ajax/libs/ethers/6.13.0/ethers.umd.min.js', [], '6.13.0', true);

        // Load our script
        wp_enqueue_script('arc-buy-js', plugin_dir_url(__FILE__) . 'public/arc-buy.js', ['jquery', 'ethers'], '1.0.2', true);

        $merchant_address = get_option('arc_merchant_address', '');

        wp_localize_script('arc-buy-js', 'arcParams', [
            'rpc'            => $this->rpc_url,
            'chainId'        => $this->chain_id,
            'usdcAddress'    => $this->usdc_address,
            'explorer'       => $this->explorer,
            'ajaxurl'        => admin_url('admin-ajax.php'),
            'nonce'          => wp_create_nonce('arc_nonce'),
            'merchantAddress'=> $merchant_address
        ]);

        wp_enqueue_style('arc-styles', plugin_dir_url(__FILE__) . 'public/style.css');
    }

    public function render_buy_button($atts) {
        $atts = shortcode_atts([
            'id'    => '',
            'price' => '0',
            'title' => 'Product'
        ], $atts);

        $price = floatval($atts['price']);
        $merchant_address = get_option('arc_merchant_address', '');

        if (empty($merchant_address)) {
            return '<p style="color:#EA7125;">⚠️ Plugin is not configured. Please set your Arc address in Arc Shop → Settings.</p>';
        }

        $amount_wei = bcmul($price, '1000000', 0); // 6 decimals
        $qr_data = "ethereum:{$merchant_address}@{$this->chain_id}/transfer?address={$merchant_address}&uint256={$amount_wei}&token={$this->usdc_address}";

        $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' . urlencode($qr_data);

        ob_start();
        ?>
        <div class="arc-buy-container">
            <h3><?php echo esc_html($atts['title']); ?> — <?php echo number_format($price, 2); ?> USDC</h3>
            
            <button onclick="buyWithArc('<?php echo esc_attr($atts['id']); ?>', <?php echo $price; ?>, '<?php echo esc_attr($atts['title']); ?>')" 
                    class="arc-buy-btn">
                Buy <?php echo number_format($price, 2); ?> USDC
            </button>

            <div class="arc-qr-section">
                <p><strong>Or scan the QR code to pay from your phone</strong></p>
                <img src="<?php echo esc_url($qr_url); ?>" alt="Payment QR code" class="arc-qr-code">
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function save_order() {
        check_ajax_referer('arc_nonce', 'nonce');

        if (empty($_POST['tx_hash']) || empty($_POST['buyer_address'])) {
            wp_send_json_error(['message' => 'Insufficient data']);
        }

        global $wpdb;

        $data = [
            'product_title' => sanitize_text_field($_POST['title'] ?? 'Unknown product'),
            'price'         => floatval($_POST['price']),
            'buyer_address' => sanitize_text_field($_POST['buyer_address']),
            'tx_hash'       => sanitize_text_field($_POST['tx_hash']),
            'email'         => is_user_logged_in() ? wp_get_current_user()->user_email : ''
        ];

        $inserted = $wpdb->insert($wpdb->prefix . 'arc_orders', $data);

        if ($inserted) {
            wp_send_json_success([
                'message'  => 'Order saved successfully',
                'order_id' => $wpdb->insert_id
            ]);
        } else {
            wp_send_json_error(['message' => 'Failed to save order to database']);
        }
    }
}

new ArcForWP();