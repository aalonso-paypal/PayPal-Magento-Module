$jPPPlus(document).ready(function () {

    EsmartPaypalBrasilBtnContinue.setElement('#checkout-onepage-buttom', '#onestepcheckout_place_order_button');

    $jPPPlus("input[type=radio][name='payment[method]']").unbind('change').change(function (e) {
        e.preventDefault();

        if (this.value == 'paypal_plus') {
            $jPPPlus('#ppplus').removeAttr('style').html('');
            if (!EsmartPaypalBrasilPPPlus.requestPending) {
                setTimeout(function () {
                    EsmartPaypalBrasilPPPlus.generateUrl();
                }, 500);
            }
        }
    });

    $jPPPlus("input[type=radio][name='payment[method]'][value='paypal_plus']:checked").attr('checked', false);

    EsmartPaypalBrasilPPPlus.init();
});
