# Arc for WP

**Simple WordPress plugin** that allows you to sell products and services for **USDC on Arc Network**.

Adds a clean "Buy" button + QR code for mobile payments. No WooCommerce required.

### Features
- Installs with a single ZIP file (very easy)
- Beautiful "Buy X.XX USDC" button with Arc brand gradient
- Automatic QR code generation for phone payments
- Payments go directly to your Arc wallet address
- Saves orders to the WordPress database
- Works on Arc Testnet (ready for mainnet later)

### Installation

1. Go to the [Releases](https://github.com/Alekoko27/arc-for-wp/releases) section
2. Download the latest `arc-for-wp.zip`
3. In your WordPress admin panel go to **Plugins → Add New → Upload Plugin**
4. Upload and activate the plugin
5. Go to **Arc Shop → Settings** and enter your Arc wallet address
6. Use the shortcode on any page or post:

```php
[arc_buy price="9.99" title="Test Coffee"]
```
### Usage Examples php
```
[arc_buy price="29.00" title="Monthly Premium Access"]

[arc_buy price="149.00" title="Yearly Subscription"]
```
### Requirements

WordPress 6.0 or higher
MetaMask, Rabby or any EVM-compatible wallet

NotesThis is an early version (v1.0.0). The code is simple and raw, but it works.Feedback, bug reports, and pull requests are very welcome!

