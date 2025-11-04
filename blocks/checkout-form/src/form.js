import React, { useState, useEffect, useRef} from 'react'
import { decodeEntities } from '@wordpress/html-entities';
import axios from "axios";


const { getSetting } = window.wc.wcSettings
const settings = getSetting( 'openpay_stores_data', {} )
const label = decodeEntities( settings.title )

const Form = ( props ) => {
    const { eventRegistration } = props;
    const { onPaymentSetup } = eventRegistration;
    const [isOpen, setIsOpen] = useState(false);
    const onOpen = (e) => {
        e.preventDefault();
        setIsOpen(true);
      };
    const onClose = (e) => {
        console.log("Se ejecuta");
        e.preventDefault();
        setIsOpen(false);
      };

    useEffect( () => {
        const unsubscribe = onPaymentSetup( async () => {} );

        // Unsubscribes when this component is unmounted.
        return () => {
            unsubscribe();
        };
    }, [] );

    //return Form;
    return (
        <div id="payment_form_openpay_stores" style={{ marginBottom: '20px', display: 'flex', flexWrap: 'wrap', gap: '0 16px', justifyContent: 'space-between'}}>
            <div className="openpay-store-checkout-style">
                <div className="step-guide">
                    <div className="step-guide__header">
                        <div className="step-guide__logo-right">
                            <img
                            src={settings.images_dir + `/newcheckout/paynet-logo.png`}
                            alt="Paynet"
                            className="step-guide__logo-img-right"
                            width="100px"
                            />
                        </div>
                        <div className="step-guide__logo-left">
                            <img
                            src={settings.images_dir + `/newcheckout/icon-lock.svg`}
                            alt="Openpay"
                            className="step-guide__logo-img"
                            />
                            <img
                            src={settings.images_dir + `/newcheckout/logo-openpay-small.svg`}
                            alt="Openpay"
                            className="step-guide__logo-img"
                            />
                            <span className="step-guide__security-text">
                                by BBVA asegura y protege tu pago.
                            </span>
                        </div>
                    </div>

                    <div className="step-guide__step step-guide__vertical-line">
                        <div className="step-guide__icon">
                            <img
                            src={settings.images_dir + `/newcheckout/logo-step-1.svg`}
                            alt="Paso 1"
                            />
                        </div>
                        <div className="step-guide__content">
                            <p className="step-guide__title">Confirma y reserva la compra</p>
                            <p className="step-guide__description">
                            Selecciona “Pagar”, tu compra quedará reservada hasta que completes el pago.
                            </p>
                        </div>
                    </div>

                <div className="step-guide__step step-guide__vertical-line">
                    <div className="step-guide__icon">
                        <img
                        src={settings.images_dir + `/newcheckout/logo-step-2.svg`}
                        alt="Paso 2"
                        />
                    </div>
                    <div className="step-guide__content">
                        <p className="step-guide__title">Guarda tu referencia de pago</p>
                        <p className="step-guide__description">
                        Descarga y guarda la referencia de pago, también la recibirás por correo.
                        </p>
                    </div>
                </div>

            <div className="step-guide__step">
                <div className="step-guide__icon">
                    <img
                    src={settings.images_dir + `/newcheckout/logo-step-3.svg`}
                    alt="Paso 3"
                    />
                </div>
                <div className="step-guide__content">
                    <p className="step-guide__title">Completa el pago</p>
                    <p className="step-guide__description">
                    Ve a una de las tiendas aliadas y dile a la persona en caja que harás un pago en efectivo,
                    proporciona el código de barras o núm. de referencia.
                    </p>
                </div>
            </div>

            <div className="step-guide__footer">
                <a className="step-guide__link" onClick={onOpen}>¿En dónde puedo pagar?</a>
            </div>
        </div>

        { isOpen ?
        <div id="stepGuideModal" className="step-guide__modal step-guide__modal--active">
      <div className="step-guide__modal-overlay" onClick={onClose}></div>

      {/* Contenido */}
      <div className="step-guide__modal-content">
        {/* Botón de cierre */}
        <button className="step-guide__modal-close" id="closeModalBtn" onClick={onClose}>
          &times;
        </button>

        <div className="step-guide__modal-header">
          <img
            src={settings.images_dir + `newcheckout/icon-info.svg`}
            alt="Ícono"
            className="step-guide__modal-icon"
          />
          <p className="step-guide__modal-title">¿En dónde puedo pagar?</p>
        </div>

        <p className="step-guide__modal-text">
          Acude a cualquiera de las siguientes tiendas aliadas o{' '}
          <a
            href="https://www.paynet.com.mx/mapa-tiendas/index.html"
            target="_blank"
            rel="noopener noreferrer"
            className="step-guide__modal-link"
          >
            consulta la tienda más cercana
          </a>
        </p>

        {/* Listas de tiendas */}
        <div className="step-guide__modal-columns">
          <ul className="store-list">
            <li>Waltmart</li>
            <li>WaltmartExpress</li>
            <li>Bodega Aurrerá</li>
            <li>Sam's Club</li>
            <li>Waldo's</li>
            <li>Farmacias del ahorro</li>
            <li>Farmacias Guadalajara</li>
          </ul>

          <ul className="store-list">
            <li>7 Eleven</li>
            <li>K</li>
            <li>Circle K</li>
            <li>Extra</li>
            <li>Kiosko</li>
            <li>SYS Tienda</li>
            <li>Otras.</li>
          </ul>
        </div>
      </div>
    </div> : null }
    </div> 
    </div>
    );
};

export default Form;