# PayPal Brazil Official Magento Module
PayPal Brazil Official Module Repositiory containing constant updated versioning

<h2>CURRENT COMPATIBILITIES</h2>
<b>[MAGENTO VERSIONS]</b>
- Magento 1.7.2 -> 1.9.X.

<b>[CHECKOUT VERSIONS]</b>
- Default OnePage.
- MOIP OneStepCheckout.
- Inorvarti OneStepCheckout.
- FireCheckout OneStepCheckout.

<h2>ROADMAP - FUTURE RELEASES</h2>
<b>[CHECKOUT COMPATIBILITIES]</b>
- FireCheckout OneStepCheckout fixes.
- OneStepCheckout Improvements.

<b>[FEATURES]</b>
- Hermes Minibrowser Compatibility [v27].
- Pay with Two Cards [v28].

<h2>CHANGELOG</h2>

<b>[26.4 - BETA]</b><br/>
- Correção do checkbox de mesmo endereço de cobrança e envio.

<b>[26.3 - STABLE]</b><br/>
- Correção do placeholder de email sendo enviado no lugar do Email do cliente.

<b>[26.2 - STABLE]</b><br/>
- Correção Termos e Condições para ExpressCheckout.

<b>[26.1 - STABLE]</b><br/>
- Correção de constante não existente, que ocorre em versões antigas do modulo principal do Paypal.

<b>[26 - BETA]</b><br/>
- Compatibilidade com FireCheckout.

<b>[25.3 - STABLE]</b><br/>
- Correção de problemas de conflito com jQuery.

<b>[25.2 - STABLE]</b><br/>
- Bugfix relacionado a jQuery.

<b>[25.1 - STABLE]</b><br/>
- Bug fix na atualização de dados de retorno no checkout padrão e shortcut dados de nome, sobrenome, email e  cpf estavam sendo sobrescritos;

<b>[25 - STABLE]</b><br/>
- Integração com OSC Inovarti;

<b>[24.4 - STABLE]</b><br/>
- Remoção de handle duplicado no layout.xml;

<b>[24.3 - STABLE]</b><br/>
- Bugfix, conflito encontrado na pagina do checkout moip gerado pela solução Shorcut;
- Acrescentado no Painel de Admin, na seção de configuração do módulo PayPalPlus, campos extras para o parâmetro 'line1';
- Remoção do módulo Esmart_AddressNormalization;<br/>
<strong>Para lojas que já possuíam o módulo instalado, é necessário remover os seguinte arquivos e diretórios:</strong>       app/code/community/Esmart/AddressNormalization;
app/design/frontend/base/default/layout/esmart/addressnormalization.xml;
app/design/frontend/base/default/template/esmart/addressnormalization;
app/etc/modules/Esmart_AddressNormalization.xml;
app/locale/pt_BR/Esmart_AddressNormalization.csv;

<b>[24.2 - STABLE]</b><br/>
- Bugfix blocos relacionados ao Shortcut não estavam sendo carregados em todas as páginas no magento;

<b>[24.1]</b><br/>
- Bugfix blocos relacionados ao Shortcut não estavam sendo carregados em todas as páginas no magento;

<b>[v24]</b><br/>
- Desenvolvido modal para exibir os 'termos e condições' antes de entrar no fluxo do Shorcut;

<b>[v23.1 - STABLE]</b><br/>
- Corrigido bug referente a mensagem 'Transaction amount details (subtotal, tax, shipping) must add up to specified amount total', o módulo não estava transmitindo corretamente os valores relacionado ao 'total' quando existia algum tipo de descontro aplicado ao pedido;

<b>[v23 - STABLE]</b><br/>
- Acrescentado opção na configuração do módulo cahamda 'Debug Mode' que quando habilitado gera um arquivo de log chamado 'ppplusbrasil_debug_mode.log', nele será logado todas as informações dos processos de createPayment, salvando dados de todos os parametros utilizado durante o processo;

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

