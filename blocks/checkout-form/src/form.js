import React, { useState, useEffect, useRef} from 'react'
import { decodeEntities } from '@wordpress/html-entities';
import axios from "axios";


const { getSetting } = window.wc.wcSettings
const settings = getSetting( 'wc_openpay_stores_data', {} )
const label = decodeEntities( settings.title )

const Form = ( props ) => {
    const { eventRegistration } = props;
    const { onPaymentSetup } = eventRegistration;

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
            <p>Formulario de pago en tiendas</p>
        </div>
    );
};

export default Form;