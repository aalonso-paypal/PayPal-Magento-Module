# PayPal Brazil Official Magento Module
PayPal Brazil Official Module Repositiory containing constant updated versioning

<h2>CURRENT COMPATIBILITIES</h2>
<b>[MAGENTO VERSIONS]</b>
- Magento 1.7.2 -> 1.9.X.

<b>[CHECKOUT VERSIONS]</b>
- Default OnePage.
- MOIP OneStepCheckout.

<h2>ROADMAP - FUTURE RELEASES</h2>
<b>[CHECKOUT COMPATIBILITIES]</b>
- Inovarti OneStepCheckout [v24].
- FireCheckout OneStepCheckout [v25].

<b>[FEATURES]</b>
- Shortcut Fix (Termos e Condições) [v23.1].
- Pay with Two Cards [v26].
- Hermes Minibrowser Compatibility [v26].

<h2>CHANGELOG</h2>

<b>[v23 - STABLE]</b><br/>
- Acrescentado opção na configuração do módulo cahamda 'Debug Mode' que quando habilitado gera um arquivo de log chamado 'ppplusbrasil_debug_mode.log', nele será logado todas as informações dos processos de createPayment, salvando dados de todos os parametros utilizado durante o processo;
- Correção de aplicação de desconto dentro do fluxo de Checkout transaparente.

<b>[v22 - STABLE]</b><br/>
- Log do generateUrl, responsável pela criação da URL, assim será possivel verificar quais informações a ação de criação de URL retornou (log: var/log/ppplusbrasil_controller_exception.log);
- Melhorias na captura do campos necessários para o createPayment, agora não será considerado apenas os dados já persistidos no banco de dados, mas também as informações preenchidas na interface do usuario (checkout) e que ainda não foram submetidas. (Esta ultima correção deve reduzir a ZERO os alertas equivocados de preenchimento dos dados do magento).

<b>[v21]</b><br/>
- Tratamento da flag 'payerTaxIdType', retornar BR_CPF quando o documento do cliente for compativel (quantidade de digitos) com CPF, e retornado BR_CNPJ quando a quantidade de digito não foi compativel com CPF, assumindo que é CNPJ;
- Forçado a limpeza do iframe no momento que loading for exibido;
- Adicionado novo tratamento DE/PARA para resolver o problema do 'line1' acusando pelo createPayment;

<b>[v20]</b><br/>
- Correção na manipulação no JSON;
- Exibição de Alert se o approvalUrl retornar null (situação quando o cliente não preenche os dados iniciais do checkout);
- Adicionado GIF de loading.

