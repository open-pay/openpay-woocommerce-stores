<?php
/*  
  Title:	Openpay Payment extension for WooCommerce
  Author:	Openpay
  URL:		http://www.openpay.mx
  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
use OpenpayStores\Includes\OpenpayUtils;
?>

<h3>
    <?php _e('Openpay Stores', 'woothemes'); ?>
</h3>

<?php if(!$this->validateCurrency()): ?>
    <div class="inline error">
        <?php
            $countryName = OpenpayUtils::getCountryName($this->country);
            $message = OpenpayUtils::getMessageError($countryName, $this->currencies[0]);
            echo $message;
        ?>
    </div>
<?php endif; ?>

<table class="form-table">
    <?php $this->generate_settings_html(); ?>
</table>
