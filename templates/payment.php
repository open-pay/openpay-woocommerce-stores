<?php
/*
  Title:	Openpay Payment extension for WooCommerce
  Author:	Openpay
  URL:		http://www.openpay.mx
  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
?>
<style>
    /* styles.css */

    @font-face {
        font-family: 'BentonBBVA';
        src: url('<?= $this->fonts_dir ?>/BentonBBVA/BentonSansBBVA-Book.ttf');
        font-weight: normal;
        font-style: normal;
        font-display: swap;
    }

.openpay-store-checkout-style {
  font-family: 'BentonBBVA', sans-serif;
  background-color: #ffffff;
  margin: 0;
  padding: 10px;
}

.step-guide {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
  border: 1px solid #eee;
  border-radius: 10px;
  background-color: #fff;
}

.step-guide__header {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  margin-bottom: 40px;
}

.step-guide__logo-left {
  display: flex;
  align-items: center;
}

.step-guide__logo-right {
  display: flex;
  align-self: end;
  margin-bottom: 20px;
}

.step-guide__logo-img {
  height: 15px;
  margin-right: 8px;
}

.step-guide__logo-img-right {
  height: 32px;
}

.step-guide__security-text {
  color: #003366;
  font-weight: bolder;
  font-size: 12px;
}

.step-guide__step {
  display: flex;
  align-items: flex-start;
  margin-bottom: 30px;
  position: relative;
}

.step-guide__icon {
  width: 60px;
  height: 60px;
  min-width: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 20px;
  position: relative;
}

.step-guide__icon img {
  width: 70px;
  height: 70px;
}

.step-guide__content {
  flex: 1;
}

.step-guide__title {
  font-size: 18px;
  font-weight: bold;
  margin: 0 0 5px;
}

.step-guide__description {
  margin: 0;
  font-size: 15px;
  color: #333;
}

.step-guide__footer {
  margin-top: 20px;
}

.step-guide__link {
  color: #3366BB;
  text-decoration: none;
  font-weight: bold;
  font-size: 15px;
}

.step-guide__link:hover {
  text-decoration: underline;
  cursor: pointer;
}

.step-guide__vertical-line::before {
  content: "";
  position: absolute;
  top: 60px;
  left: 29px;
  width: 2px;
  height: calc(100% - 35px);
  background-image: linear-gradient(#ccc 43%, rgba(255, 255, 255, 0) 0%);
  background-position: right;
  background-size: 2px 6px;
  background-repeat: repeat-y;
  z-index: 0;
}

/* MODAL */
.step-guide__modal {
  display: none;
  position: fixed;
  z-index: 10000;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  
  align-items: center;
  justify-content: center;
}

.step-guide__modal-overlay {
  position: absolute;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
}

.step-guide__modal-content {
  position: relative;
  max-width: 453px;
  margin: 100px auto;
  background: white;
  padding: 30px;
  border-radius: 7px;
  z-index: 10001;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.25);
}

.step-guide__modal-close {
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 24px;
  background: none;
  border: none;
  cursor: pointer;
}

.step-guide__modal-title {
  font-size: 18px;
  font-weight: bold;
}

.step-guide__modal-text {
  font-size: 15px;
}

.step-guide__modal-header {
  display: flex;
  align-items: center;
  gap: 20px;
}

.step-guide__modal-icon {
  width: 24px;
  height: 24px;
}

.step-guide__modal-link {
  color: #3366BB;
  text-decoration: underline;
  font-weight: bold;
}

.step-guide__modal-link:hover {
  text-decoration: none;
}

/* Mostrar modal */
.step-guide__modal--active {
  display: flex;
}

.step-guide__modal-columns {
  display: flex;
  gap: 90px;
  margin-top: 20px;
  margin-left: 20px;
  flex-wrap: wrap;
}

.store-list {
    list-style: disc;
    padding-left: 40px;
    padding: 0;
    margin: 0;
}

.store-list li {
  font-size: 15px;
  margin-bottom: 8px;
  color: #333;
}
</style>
<div class="openpay-store-checkout-style">
  <div class="step-guide">
    <div class="step-guide__header">
    <div class="step-guide__logo-right">
        <img src="<?= $this->images_dir ?>/newcheckout/paynet-logo.png" alt="Paynet" class="step-guide__logo-img-right" width="100px">
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

    <div class="step-guide__footer">
      <a class="step-guide__link">¿En dónde puedo pagar?</a>
    </div>
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