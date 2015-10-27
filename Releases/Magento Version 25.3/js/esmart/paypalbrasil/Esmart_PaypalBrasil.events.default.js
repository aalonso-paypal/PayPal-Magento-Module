$jPPPlus(document).ready(function () {

    EsmartPaypalBrasilBtnContinue.setElement('#payment-buttons-container button:not(#esmart-paypalbrasil-btn-submit)', '#payment-buttons-container', true);

    $jPPPlus("input[type=radio][name='payment[method]']").unbind('change').change(function (e) {
        e.preventDefault();

        if (this.value == 'paypal_plus') {
            $jPPPlus('#ppplus').removeAttr('style').html('');
            if (!EsmartPaypalBrasilPPPlus.requestPending) {
                setTimeout(function () {
                    EsmartPaypalBrasilPPPlus.generateUrl();
                }, 500);
            }
        } else {
            EsmartPaypalBrasilBtnContinue.enable();
        }
    });

    $jPPPlus("li#opc-payment div.step-title").unbind('click').click(function (e) {
        $jPPPlus("input[type=radio][name='payment[method]'][value='paypal_plus']:checked").attr('checked', false);
        $jPPPlus("ul#payment_form_paypal_plus").hide();
    });

    EsmartPaypalBrasilPPPlus.init();
});
