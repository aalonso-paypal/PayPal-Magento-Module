jQuery(document).ready(function () {

    EsmartPaypalBrasilBtnContinue.setElement('#payment-buttons-container button:not(#esmart-paypalbrasil-btn-submit)', '#payment-buttons-container', true);

    jQuery("input[type=radio][name='payment[method]']").unbind('change').change(function (e) {
        e.preventDefault();

        if (this.value == 'paypal_plus') {
            jQuery('#ppplus').removeAttr('style').html('');
            if (!EsmartPaypalBrasilPPPlus.requestPending) {
                setTimeout(function () {
                    EsmartPaypalBrasilPPPlus.generateUrl();
                }, 500);
            }
        } else {
            EsmartPaypalBrasilBtnContinue.enable();
        }
    });

    jQuery("li#opc-payment div.step-title").unbind('click').click(function (e) {
        jQuery("input[type=radio][name='payment[method]'][value='paypal_plus']:checked").attr('checked', false);
        jQuery("ul#payment_form_paypal_plus").hide();
    });

    EsmartPaypalBrasilPPPlus.init();
});
