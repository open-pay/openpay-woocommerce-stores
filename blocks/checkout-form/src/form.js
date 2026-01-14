import React, { useState, useEffect, useRef } from "react";
import { decodeEntities } from "@wordpress/html-entities";
import axios from "axios";

const { getSetting } = window.wc.wcSettings;
const settings = getSetting("openpay_stores_data", {});
const label = decodeEntities(settings.title);

const MODAL_DATA = {
  MX: {
    title: "¿En dónde puedo pagar?",
    description: "Acude a cualquiera de las siguientes tiendas aliadas o ",
    linkText: "consulta la tienda más cercana",
    linkUrl: "https://www.paynet.com.mx/mapa-tiendas/index.html",
    columns: [
      [
        "Walmart",
        "Walmart Express",
        "Bodega Aurrerá",
        "Sam's Club",
        "Waldo's",
        "Farmacias del Ahorro",
        "Farmacias Guadalajara",
      ],
      ["7 Eleven", "Circle K", "Extra", "Kiosko", "SYS Tienda", "Otras"],
    ],
  },
  PE: {
    title: "¿En dónde puedo pagar?",
    description: "Acude a cualquiera de las siguientes tiendas aliadas o ",
    linkText: "consulta la tienda más cercana",
    linkUrl:
      "https://public.openpay.pe/web/descargables/2024/manual-pago-efectivo.pdf",
    columns: [
      ["BBVA", "Caja Arequipa", "Interbank", "Yape"],
      ["BCP", "Caja Huancayo", "Kasnet"],
    ],
  },
};

const Form = (props) => {
  const { eventRegistration } = props;
  const { onPaymentSetup } = eventRegistration;

  const [isOpen, setIsOpen] = useState(false);
  const [activeTab, setActiveTab] = useState("webapp");

  const country = settings.country || "MX";
  const isModalEnabled = ["MX", "PE"].includes(country);
  const content = MODAL_DATA[country] || MODAL_DATA.MX;

  // Función para renderizar la descripción con el link embebido
  const renderDescriptionWithLink = () => {
    const parts = content.description.split(content.linkText);
    return (
      <p className="step-guide__modal-text">
        {parts[0]}{" "}
        <a
          href={content.linkUrl}
          target="_blank"
          rel="noopener noreferrer"
          className="step-guide__modal-link"
        >
          {content.linkText}
        </a>
        {parts[1]}
      </p>
    );
  };

  const onOpen = (e) => {
    e.preventDefault();
    setIsOpen(true);
  };
  const onClose = (e) => {
    console.log("Se ejecuta");
    e.preventDefault();
    setIsOpen(false);
  };

  useEffect(() => {
    const unsubscribe = onPaymentSetup(async () => {});

    // Unsubscribes when this component is unmounted.
    return () => {
      unsubscribe();
    };
  }, []);

  // Renderizado dinámico de la descripción para Perú
  const getStep3DescriptionPE = () => {
    if (activeTab === "webapp") {
      return 'Ingresa a tu banca móvil o web. Selecciona "Pago de servicio" y elige KASHIO PERÚ. Luego, ingresa el número de referencia, lo encontrarás en el PDF enviado a tu correo.';
    }
    return "Acude a una agencia autorizada e indica al cajero que harás un pago de servicio KASHIO PERÚ, proporciona el numero de referencia. Antes de pagar, verifica que los datos coincidan.";
  };

  //return Form;
  return (
    <div
      id="payment_form_openpay_stores"
      className="openpay-store-checkout-container"
    >
      {/* SECCIÓN DE TABS: Solo visible para Perú */}
      {settings.country === "PE" && (
        <div className="openpay-tabs">
          <button
            type="button"
            className={`openpay-tab-button ${
              activeTab === "webapp" ? "active" : ""
            }`}
            onClick={() => setActiveTab("webapp")}
          >
            Pago en web app
          </button>
          <button
            type="button"
            className={`openpay-tab-button ${
              activeTab === "agencias" ? "active" : ""
            }`}
            onClick={() => setActiveTab("agencias")}
          >
            Pago en agencias
          </button>
        </div>
      )}

      <div className="openpay-store-checkout-style">
        <div className="step-guide">
          <div className="step-guide__header">
            <div className="step-guide__logo-left">
              <img
                src={`${settings.images_dir}/newcheckout/icon-lock.svg`}
                alt="Lock"
              />
              <img
                src={`${settings.images_dir}/newcheckout/logo-openpay-small.svg`}
                alt="Openpay"
              />
              <span className="step-guide__security-text">
                by BBVA asegura y protege tu pago.
              </span>
            </div>

            <div className="step-guide__logo-right">
              {settings.country === "MX" && (
                <img
                  src={`${settings.images_dir}/newcheckout/paynet-logo.png`}
                  alt="Paynet"
                  width="100px"
                />
              )}
              {settings.country === "CO" && (
                <img
                  src={`${settings.images_dir}/newcheckout/efecty-logo.png`}
                  alt="Efecty"
                  width="100px"
                />
              )}
            </div>
          </div>

          {/* Paso 1 */}
          <div className="step-guide__step step-guide__vertical-line">
            <div className="step-guide__icon">
              <img
                src={`${settings.images_dir}/newcheckout/logo-step-1.svg`}
                alt="Paso 1"
              />
            </div>
            <div className="step-guide__content">
              <p className="step-guide__title">Confirma y reserva la compra</p>
              <p className="step-guide__description">
                Selecciona “Pagar”, tu compra quedará reservada hasta que
                completes el pago.
              </p>
            </div>
          </div>

          {/* Paso 2 */}
          <div className="step-guide__step step-guide__vertical-line">
            <div className="step-guide__icon">
              <img
                src={`${settings.images_dir}/newcheckout/logo-step-2.svg`}
                alt="Paso 2"
              />
            </div>
            <div className="step-guide__content">
              <p className="step-guide__title">Guarda tu referencia de pago</p>
              <p className="step-guide__description">
                Descarga y guarda la referencia de pago, también la recibirás
                por correo.
              </p>
            </div>
          </div>

          {/* Paso 3: Dinámico */}
          <div className="step-guide__step">
            <div className="step-guide__icon">
              <img
                src={`${settings.images_dir}/newcheckout/logo-step-3.svg`}
                alt="Paso 3"
              />
            </div>
            <div className="step-guide__content">
              <p className="step-guide__title">Completa el pago</p>
              <p className="step-guide__description">
                {settings.country === "PE"
                  ? getStep3DescriptionPE()
                  : "Ve a una de las tiendas aliadas y dile a la persona en caja que harás un pago en efectivo, proporciona el código de barras o núm. de referencia."}
              </p>
            </div>
          </div>

          {/* Enlace de ayuda (Visible en MX y PE según diseño) */}
          {(settings.country === "MX" || settings.country === "PE") && (
            <div className="step-guide__footer">
              <a className="step-guide__link" onClick={onOpen} href="#">
                ¿En dónde puedo pagar?
              </a>
            </div>
          )}
        </div>

        {isOpen ? (
          <div
            id="stepGuideModal"
            className="step-guide__modal step-guide__modal--active"
          >
            <div className="step-guide__modal-overlay" onClick={onClose}></div>

            {/* Contenido */}
            <div className="step-guide__modal-content">
              {/* Botón de cierre */}
              <button
                className="step-guide__modal-close"
                id="closeModalBtn"
                onClick={onClose}
              >
                &times;
              </button>

              <div className="step-guide__modal-header">
                <img
                  src={settings.images_dir + `newcheckout/icon-info.svg`}
                  alt="Ícono"
                  className="step-guide__modal-icon"
                />
                <p className="step-guide__modal-title">
                  ¿En dónde puedo pagar?
                </p>
              </div>

              {/* Llamada a la función dinámica */}
              {renderDescriptionWithLink()}

              {/* Listas de tiendas */}
              <div className="step-guide__modal-columns">
                {content.columns.map((column, idx) => (
                  <ul key={idx} className="store-list">
                    {column.map((store, sIdx) => (
                      <li key={sIdx}>{store}</li>
                    ))}
                  </ul>
                ))}
              </div>
            </div>
          </div>
        ) : null}
      </div>
    </div>
  );
};

export default Form;
