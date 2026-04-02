<?php
if (!current_user_can('manage_options')) {
    wp_die('Insufficient permissions');
}

$merchant_address = get_option('arc_merchant_address', '');
$updated = false;

if (isset($_POST['arc_save_settings'])) {
    check_admin_referer('arc_settings_nonce');
    $new_address = sanitize_text_field($_POST['arc_merchant_address']);
    update_option('arc_merchant_address', $new_address);
    $merchant_address = $new_address;
    $updated = true;
}
?>

<div class="wrap">
    <h1>Arc for WP — Settings</h1>

    <?php if ($updated): ?>
        <div class="notice notice-success"><p>✅ Settings saved</p></div>
    <?php endif; ?>

    <form method="post">
        <?php wp_nonce_field('arc_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">Your Arc address (payment recipient)</th>
                <td>
                    <input type="text" name="arc_merchant_address" value="<?php echo esc_attr($merchant_address); ?>" 
                           class="regular-text" placeholder="0x1234..." style="width:100%; max-width:520px;">
                    <p class="description">All USDC payments will be sent to this address. Make sure it is an Arc Testnet address (for now).</p>
                </td>
            </tr>
        </table>

        <p class="submit">
            <input type="submit" name="arc_save_settings" class="button button-primary" value="Save Settings">
        </p>
    </form>

    <hr>
    <h2>How to use</h2>
    <p>Insert the shortcode on any page or post:</p>
    <code>[arc_buy id="1" price="49.99" title="Premium Access"]</code>
</div>