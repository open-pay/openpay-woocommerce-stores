<?php
/*
  Title:	Openpay Payment extension for WooCommerce
  Author:	Openpay
  URL:		http://www.openpay.mx
  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
?>
<div class="openpay-store-checkout-style">
  <div class="step-guide">
    <div class="step-guide__header">
    <div class="step-guide__logo-right">
        <?php if($this->country == 'MX'): ?>
        <img src="<?= $this->images_dir ?>/newcheckout/paynet-logo.png" alt="Paynet" class="step-guide__logo-img-right" width="100px">
        <?php endif; ?>
        <?php if($this->country == 'CO'): ?>
        <img src="<?= $this->images_dir ?>/newcheckout/efecty-logo.png" alt="efecty" class="step-guide__logo-img-right" width="100px">
        <?php endif; ?>
      </div>
      <div class="step-guide__logo-left">
        <img src="<?= $this->images_dir ?>/newcheckout/icon-lock.svg" alt="Openpay" class="step-guide__logo-img">
        <img src="<?= $this->images_dir ?>/newcheckout/logo-openpay-small.svg" alt="Openpay" class="step-guide__logo-img">
        <span class="step-guide__security-text"> by BBVA asegura y protege tu pago.</span>
    </div>
</div>

    <div class="step-guide__step step-guide__vertical-line">
      <div class="step-guide__icon">
        <img src="<?= $this->images_dir ?>/newcheckout/logo-step-1.svg" alt="Paso 1">
      </div>
      <div class="step-guide__content">
        <p class="step-guide__title">Confirma y reserva la compra</p>
        <p class="step-guide__description">Selecciona “Pagar”, tu compra quedará reservada hasta que completes el pago.</p>
      </div>
    </div>

    <div class="step-guide__step step-guide__vertical-line">
      <div class="step-guide__icon">
        <img src="<?= $this->images_dir ?>/newcheckout/logo-step-2.svg" alt="Paso 2">
      </div>
      <div class="step-guide__content">
        <p class="step-guide__title">Guarda tu referencia de pago</p>
        <p class="step-guide__description">Descarga y guarda la referencia de pago, también la recibirás por correo.</p>
      </div>
    </div>

    <div class="step-guide__step">
      <div class="step-guide__icon">
        <img src="<?= $this->images_dir ?>/newcheckout/logo-step-3.svg" alt="Paso 3">
      </div>
      <div class="step-guide__content">
        <p class="step-guide__title">Completa el pago</p>
        <p class="step-guide__description">Ve a una de las tiendas aliadas y dile a la persona en caja que harás un pago en efectivo, proporciona el código de barras o núm. de referencia.</p>
      </div>
    </div>

      <?php if($this->country == 'MX'): ?>
        <div class="step-guide__footer">
          <a class="step-guide__link">¿En dónde puedo pagar?</a>
        </div>
      <?php endif; ?>
</div>

  <div id="stepGuideModal" class="step-guide__modal">
    <div class="step-guide__modal-overlay"></div>
        <div class="step-guide__modal-content">
            <button class="step-guide__modal-close" id="closeModalBtn">&times;</button>
            <div class="step-guide__modal-header">
                <img src="<?= $this->images_dir ?>/newcheckout/icon-info.svg" alt="Ícono" class="step-guide__modal-icon">
                <p class="step-guide__modal-title">¿En dónde puedo pagar?</p>
            </div>
            <p class="step-guide__modal-text">Acude a cualquiera de las siguientes tiendas aliadas o <a href="https://www.paynet.com.mx/mapa-tiendas/index.html" target="_blank" class="step-guide__modal-link"> consulta la tienda más cercana </a> </p>
            <div class="step-guide__modal-columns">
                <ul class="store-list">
                    <li>Waltmart</li>
                    <li>Waltmart Express</li>
                    <li>Bodega Aurrerá</li>
                    <li>Sam's Club</li>
                    <li>Waldo's</li>
                    <li>Farmacias del ahorro</li>
                    <li>Farmacias Guadalajara</li>
                </ul>
                <ul class="store-list">
                    <li>7 Eleven</li>
                    <li>K</li>
                    <li>Circle K</li>
                    <li>Extra</li>
                    <li>Kiosko</li>
                    <li>SYS Tienda</li>
                    <li>Otras</li>
                </ul>
            </div>
        <!-- Aquí puedes insertar logos, mapas o más info -->
        </div>
    </div>
</div>