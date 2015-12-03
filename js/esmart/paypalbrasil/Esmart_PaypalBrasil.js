var EsmartPaypalBrasilPPPlus, EsmartPaypalBrasilBtnContinue;

if (typeof EsmartPaypalBrasilPPPlus !== 'object') {
    EsmartPaypalBrasilPPPlus = {
        ppp             : null,
        base_url        : null,

        generateUrl : function () {
            $jPPPlus("div#paypal_plus_loading").show();
            $jPPPlus('#paypal_plus_iframe').html('').removeAttr('style');

            $jPPPlus.ajax({
                dataType: 'json',
                type: 'post',
                url: this.base_url + 'paypalbrasil/express/generateUrl',
		data: $jPPPlus('#payment_form_paypal_plus').closest('form').first().serializeArray(),
//                data: $jPPPlus('form').serializeArray(),
                async: false,
                complete: function (response) {

                    var responseContent = $jPPPlus.parseJSON(response.responseText);

                    if (typeof responseContent.error !== 'undefined') {
                        if (responseContent.error == 'incomplete_customer') {
                            EsmartPaypalBrasilPPPlus.showAlert();
                        }

                        $jPPPlus("div#paypal_plus_loading").hide();
                        $jPPPlus("input[type=radio][name='payment[method]'][value='paypal_plus']:checked").attr('checked', false);
                        return false;
                    }

                    if (responseContent.success.approvalUrl === null) {
                        $jPPPlus("div#paypal_plus_loading").hide();
                        EsmartPaypalBrasilPPPlus.showAlert();
                        $jPPPlus("input[type=radio][name='payment[method]'][value='paypal_plus']:checked").attr('checked', false);
                        return false;
                    }

                    if (typeof responseContent.success !== 'undefined') {
                        EsmartPaypalBrasilPPPlus.ppp = PAYPAL.apps.PPP({
                            placeholder        : "paypal_plus_iframe",
                            buttonLocation     : "outside",
                            enableContinue     : function () {
                                EsmartPaypalBrasilBtnContinue.enable();
                            },
                            disableContinue    : function () {
                                EsmartPaypalBrasilBtnContinue.disable();
                            },
                            language           : "pt_BR",
                            country            : "BR",
                            approvalUrl        : responseContent.success.approvalUrl,
                            mode               : responseContent.success.mode,
                            payerFirstName     : responseContent.success.payerFirstName,
                            payerLastName      : responseContent.success.payerLastName,
                            payerEmail         : responseContent.success.payerEmail,
                            payerTaxId         : responseContent.success.payerTaxId,
                            payerTaxIdType     : responseContent.success.payerTaxIdType,
                            rememberedCards    : responseContent.success.rememberedCards
                        });

                        $jPPPlus("div#paypal_plus_loading").hide();
                        return true;
                    }
                }
            });
        },

        init : function () {
            if (window.addEventListener) {
                window.removeEventListener('message', esmartPaypalBrasilHandler);
                window.addEventListener('message', esmartPaypalBrasilHandler, false);
                return true;
            }

            if (window.attachEvent) {
                window.detachEvent("message", esmartPaypalBrasilHandler);
                window.attachEvent("message", esmartPaypalBrasilHandler);
                return true;
            }

            return false;
        },

        handler : function (event) {
            //debugger;
            var data = event.data.evalJSON();

            switch (data.action) {

                case 'checkout':
                    var dataPost = {
                        rememberedCards : data.result.rememberedCards,
                        payerId         : data.result.payer.payer_info.payer_id,
                        payerStatus     : data.result.payer.status,
                        checkoutId      : data.result.id,
                        checkoutState   : data.result. state,
                        cards           : []
                    };

                    if (undefined != data.result.payer.funding_option) {
                        for (key in data.result.payer.funding_option.funding_sources) {
                            if (Number(key) == key) {
                                var cardData = {
                                    termQty     : 1,
                                    termValue   : data.result.payer.funding_option.funding_sources[key].amount.value,
                                    total       : data.result.payer.funding_option.funding_sources[key].amount.value
                                };

                                if (typeof data.result.term !== 'undefined') {
                                    cardData.termQty    = data.result.term.term;
                                    cardData.termValue  = data.result.term.monthly_payment.value;
                                }

                                dataPost.cards.push(cardData);
                            }
                        }
                    }

                    $jPPPlus.ajax({
                        dataType    : 'json',
                        type        : 'post',
                        url         : this.base_url + 'paypalbrasil/express/savePaypalInformation',
                        data        : dataPost,
                        async       : false,
                        complete: function (response) {
                            setTimeout(function() { EsmartPaypalBrasilBtnContinue.executeOriginalEvents(); }, 2000);
                        }
                    });

                    break;
            }
        },

        showAlert : function() {
            alert("Prezado cliente, favor preencher os dados dos passos anteriores antes de selecionar a Forma de Pagamento.");
        }
    };
}

if (typeof EsmartPaypalBrasilBtnContinue !== 'object') {
    EsmartPaypalBrasilBtnContinue = {
        element: null,
        originalElement: null,

        disable: function () {
            this.element.attr('disabled', true);
        },

        enable: function () {
            this.element.attr('disabled', false);
        },

        executeOriginalEvents: function () {
            $jPPPlus('#original-btn-submit').trigger('click');
        },

        setElement: function (originalBtnElement, appendTo, removeEvents) {
            if (typeof removeEvents === 'undefined') {
                removeEvents = false;
            }

            if ($jPPPlus('#original-btn-submit').size() === 0) {
                $jPPPlus(originalBtnElement).clone().attr('id', 'esmart-paypalbrasil-btn-submit').appendTo(appendTo);

                this.element = $jPPPlus('#esmart-paypalbrasil-btn-submit');

                $jPPPlus(originalBtnElement).attr('style', '').attr('class', '').attr('id', 'original-btn-submit').hide();

                if (removeEvents) {
                    this.element.removeAttr('onclick');
                }

                this.element.click(function (e) {
                    if (typeof e === 'undefined') {
                        return;
                    }

                    e.preventDefault();

                    if ($jPPPlus("input[type=radio][name='payment[method]']:checked").val() === 'paypal_plus') {
                        EsmartPaypalBrasilPPPlus.ppp.doContinue();
                    } else {
                        EsmartPaypalBrasilBtnContinue.executeOriginalEvents();
                    }
                });
            }
        }
    };
}

function esmartPaypalBrasilHandler(event) {
    EsmartPaypalBrasilPPPlus.handler(event);
}
