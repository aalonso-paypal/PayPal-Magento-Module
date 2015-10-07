jQuery(document).ready(function () {

    EsmartPaypalBrasilBtnContinue.setElement('#onestepcheckout-place-order button:not(#esmart-paypalbrasil-btn-submit)', '#onestepcheckout-place-order', true);

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

    jQuery("input[type=radio][name='payment[method]'][value='paypal_plus']:checked").attr('checked', false);

    EsmartPaypalBrasilPPPlus.init();
});
