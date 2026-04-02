<?php
if (!current_user_can('manage_options')) wp_die('Insufficient permissions');
?>

<div class="wrap">
    <h1>Arc Shop — Products</h1>
    <p>For now, products are managed via shortcodes. A full product table will be added in the next version.</p>
    
    <h2>Shortcode examples</h2>
    <ul>
        <li><code>[arc_buy price="9.99" title="E-book"]</code></li>
        <li><code>[arc_buy price="49.00" title="Monthly Subscription"]</code></li>
        <li><code>[arc_buy price="299.00" title="Yearly Access"]</code></li>
    </ul>

    <p><strong>Tip:</strong> Create a dedicated "Shop" page and add multiple shortcodes there.</p>
</div>