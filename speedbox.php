<?php
/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author      Speedbox ( http://www.speedbox.ma)
 * @copyright   2017 Speedbox  ( http://www.speedbox.ma)
 * @developer   Ahmed MAHI <1hmedmahi@gmail.com> (http://ahmedmahi.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
require_once _PS_MODULE_DIR_ . 'speedbox/classes/Helper.php';
class Speedbox extends CarrierModule
{
    protected $config_form = false;
    public $models         = ['AdminSpeedboxFraisportModels', 'AdminSpeedboxZonesModels'];

    //tabs to be created in the backoffice menu
    protected $tabs = [
        [
            'name'      => 'Speedbox',
            'className' => 'AdminSpeedboxZones',
            'active'    => 1,
            //submenus
            'childs'    => [
                [
                    'active'    => 1,
                    'name'      => 'Gestion des zones',
                    'className' => 'AdminSpeedboxZones',
                ],
                [
                    'active'    => 1,
                    'name'      => 'Gestion de frais de livraison',
                    'className' => 'AdminSpeedboxFraisport',
                ],
                [
                    'active'    => 1,
                    'name'      => 'Gestion des expéditions',
                    'className' => 'AdminSpeedboxOrders',
                ],
            ],
        ],
    ];
    public $helper;
    public $speedbox_orders_model;
    public function __construct()
    {
        $this->name          = 'speedbox';
        $this->tab           = 'shipping_logistics';
        $this->version       = '1.0.0';
        $this->author        = 'S3i Business';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        $this->displayName = $this->l('Speedbox');
        $this->description = $this->l('Module de livraison en points relais pour Prestashop 1.6');

        $this->confirmUninstall      = $this->l('Are you sure you want to uninstall my module?');
        $this->helper                = new S3ibusiness_Speedbox_Helper();
        $this->speedbox_orders_model = $this->helper->getSpeedboxOrdersModel();

        parent::__construct();
    }

    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }
        if (_PS_VERSION_ >= '1.7' || _PS_VERSION_ < '1.5') {
            die('This version of the Speedbox module only works on Prestashop 1.5 to 1.6. Please install the suitable module for your Prestashop version.');
        }
        $this->helper->getSpeedboxModel()->disableOldCarriers();
        $carrier = $this->addCarrier();
        $this->addZones($carrier);
        $this->addGroups($carrier);

        $this->addRanges($carrier);

        Configuration::updateValue('SPEEDBOX_NOM', 'Speedbox');
        Configuration::updateValue('SPEEDBOX_API_TOKEN', '1486547626.3-636a07639e39a5be5958d1820ff75ec59af7f127204994f0d3df782058c36e1b');
        Configuration::updateValue('SPEEDBOX_CLE_SECURITE', '1486547656.28-7a57e41bf184925eb563851b9a6be30afa770c9351eab87f23fabd4458dd3c4a');
        Configuration::updateValue('SPEEDBOX_URL_WEBSERVICE', 'http://core.speedbox.ma:8001');
        Configuration::updateValue('SPEEDBOX_PAIEMENT_LIVRAISON', 0);
        Configuration::updateValue('SPEEDBOX_ETAPE_EXPEDIEE', 4);
        Configuration::updateValue('SPEEDBOX_ETAPE_LIVRE', 5);
        Configuration::updateValue('SPEEDBOX_RELAIS_CARRIER', Configuration::get('SPEEDBOX_RELAIS_CARRIER', null, null));
        Configuration::updateValue('SPEEDBOX_GESTION_FRAIS_LIVRAISON', 2);
        Configuration::updateValue('SPEEDBOX_SUPPLEMENT', 0);
        Configuration::updateValue('SPEEDBOX_PRIX_DEFAUT', 0);
        Configuration::updateValue('SPEEDBOX_GOOGLE_API_KEY', 'AIzaSyBzwB02FINvKvmQi2A3EulIU8Mz9IexMNk');

        include dirname(__FILE__) . '/sql/install.php';
        if (PS_VERSION_ >= '1.5') {
            foreach ($this->models as $model) {
                require_once 'classes/' . $model . '.php';
                $modelInstance = new $model();

                $modelInstance->createDatabase();

                $modelInstance->createMissingColumns();
            }
        }

        //create the tabs in the backoffice menu
        $this->addTab($this->tabs);
        return parent::install() &&

        $this->registerHook('backOfficeHeader') &&

        $this->registerHook('header') &&
        $this->registerHook('extraCarrier') &&
        $this->registerHook('orderConfirmation') &&
        $this->registerHook('newOrder');
    }

    public function uninstall()
    {
        include dirname(__FILE__) . '/sql/uninstall.php';
        if (!parent::uninstall()
            || !$this->removeTab($this->tabs)
            || !Configuration::deleteByName('SPEEDBOX_NOM')
            || !Configuration::deleteByName('SPEEDBOX_API_TOKEN')
            || !Configuration::deleteByName('SPEEDBOX_CLE_SECURITE')
            || !Configuration::deleteByName('SPEEDBOX_URL_WEBSERVICE')
            || !Configuration::deleteByName('SPEEDBOX_PAIEMENT_LIVRAISON')
            || !Configuration::deleteByName('SPEEDBOX_ETAPE_EXPEDIEE')
            || !Configuration::deleteByName('SPEEDBOX_ETAPE_LIVRE')
            || !Configuration::deleteByName('SPEEDBOX_RELAIS_CARRIER', '')
            || !Configuration::deleteByName('SPEEDBOX_GESTION_FRAIS_LIVRAISON', '')
            || !Configuration::deleteByName('SPEEDBOX_SUPPLEMENT', '')
            || !Configuration::deleteByName('SPEEDBOX_PRIX_DEFAUT', '')
            || !Configuration::deleteByName('SPEEDBOX_GOOGLE_API_KEY', '')) {
            return false;
        }
        return true;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submitSpeedboxModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $helper->module                   = $this;
        $helper->default_form_language    = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier    = $this->identifier;
        $helper->submit_action = 'submitSpeedboxModule';
        $helper->currentIndex  = $this->context->link->getAdminLink('AdminModules', false)
        . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon'  => 'icon-cogs',
                ),
                'input'  => array(
                    array(
                        'col'   => 3,
                        'type'  => 'text',
                        'desc'  => $this->l('The title which the user sees during checkout.'),
                        'name'  => 'SPEEDBOX_NOM',
                        'label' => $this->l('Name'),
                    ),
                    array(
                        'col'   => 3,
                        'type'  => 'text',
                        'desc'  => $this->l('Contact your SpeedBox sales representative to obtain this data'),
                        'name'  => 'SPEEDBOX_API_TOKEN',
                        'label' => $this->l('Api token'),
                    ),
                    array(
                        'col'   => 3,
                        'type'  => 'text',
                        'name'  => 'SPEEDBOX_CLE_SECURITE',
                        'label' => $this->l('Security Key'),
                        'desc'  => $this->l('Please enter the secuity key provided by SpeedBox.'),
                    ),
                    array(
                        'col'   => 3,
                        'type'  => 'text',
                        'desc'  => $this->l('Please enter the SpeedBox Relais WebService URL.'),
                        'name'  => 'SPEEDBOX_URL_WEBSERVICE',
                        'label' => $this->l('SpeedBox Relais Webservice URL'),
                    ),
                    array(
                        'col'     => 3,
                        'type'    => 'select',
                        'desc'    => $this->l('Select your cash on delivery method paiement'),
                        'name'    => 'SPEEDBOX_PAIEMENT_LIVRAISON',
                        'label'   => $this->l('Cash on Delivery'),
                        'options' => $this->getPaymentMethods(),
                    ),

                    array(
                        'col'     => 3,
                        'type'    => 'select',
                        'desc'    => $this->l('Une fois les demandes de prises en charge de colis envoyées, les commandes passeront à ce statut.'),
                        'name'    => 'SPEEDBOX_ETAPE_EXPEDIEE',
                        'label'   => $this->l('Statut "En cours de livraison"'),
                        'options' => $this->getOrderStates(),
                    ),
                    array(
                        'col'     => 3,
                        'type'    => 'select',
                        'desc'    => $this->l('Une fois les colis livrés, les commandes passeront à ce statut.'),
                        'name'    => 'SPEEDBOX_ETAPE_LIVRE',
                        'label'   => $this->l('Statut "Livré"'),
                        'options' => $this->getOrderStates(),
                    ),

                    array(
                        'col'     => 3,
                        'type'    => 'select',
                        'desc'    => $this->l(''),
                        'name'    => 'SPEEDBOX_RELAIS_CARRIER',
                        'label'   => $this->l('Sélection du transporteur'),
                        'options' => $this->getCarriers(),
                    ),

                    array(
                        'col'     => 3,
                        'type'    => 'select',
                        'desc'    => $this->l('Use of Speedbox rates (via API) with possibility of a supplement or specify delivery charges'),
                        'name'    => 'SPEEDBOX_GESTION_FRAIS_LIVRAISON',
                        'label'   => $this->l('Delivery Fee Management'),
                        'options' => array('id' => 'value',
                            'name'                  => 'label',
                            'query'                 => array(
                                array(
                                    'value' => 1,
                                    'label' => $this->l('Use of Speedbox rates (via API) + supplement'),
                                ),
                                array(
                                    'value' => 2,
                                    'label' => $this->l('Specify shipping costs'),
                                ),
                            )),
                    ),
                    array(
                        'col'   => 3,
                        'type'  => 'text',
                        'name'  => 'SPEEDBOX_SUPPLEMENT',
                        'label' => $this->l('Overcost'),
                    ),
                    array(
                        'col'   => 3,
                        'type'  => 'text',
                        'name'  => 'SPEEDBOX_PRIX_DEFAUT',
                        'label' => $this->l('default price.'),
                        'desc'  => $this->l('Default price when the relay point is not yet selected.'),
                    ),
                    array(
                        'col'    => 3,
                        'type'   => 'text',
                        'name'   => 'SPEEDBOX_GOOGLE_API_KEY',
                        'label'  => $this->l('Google Maps API Key'),
                        'desc'   => $this->l(' Click here to retrieve your Google API Key'),
                        'prefix' => '<a href="https://console.developers.google.com/flows/enableapi?apiid=maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend,places_backend&keyType=CLIENT_SIDE&reusekey=true" target="_blank"> Lien</a>',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'SPEEDBOX_NOM'                     => Configuration::get('SPEEDBOX_NOM', null, null),
            'SPEEDBOX_API_TOKEN'               => Configuration::get('SPEEDBOX_API_TOKEN', null, null),
            'SPEEDBOX_CLE_SECURITE'            => Configuration::get('SPEEDBOX_CLE_SECURITE', null, null),
            'SPEEDBOX_URL_WEBSERVICE'          => Configuration::get('SPEEDBOX_URL_WEBSERVICE', null, null),
            'SPEEDBOX_PAIEMENT_LIVRAISON'      => Configuration::get('SPEEDBOX_PAIEMENT_LIVRAISON', null, null),
            'SPEEDBOX_ETAPE_EXPEDIEE'          => Configuration::get('SPEEDBOX_ETAPE_EXPEDIEE', null, null),
            'SPEEDBOX_ETAPE_LIVRE'             => Configuration::get('SPEEDBOX_ETAPE_LIVRE', null, null),
            'SPEEDBOX_RELAIS_CARRIER'          => Configuration::get('SPEEDBOX_RELAIS_CARRIER', null, null),
            'SPEEDBOX_GESTION_FRAIS_LIVRAISON' => Configuration::get('SPEEDBOX_GESTION_FRAIS_LIVRAISON', null, null),
            'SPEEDBOX_SUPPLEMENT'              => Configuration::get('SPEEDBOX_SUPPLEMENT', null, null),
            'SPEEDBOX_PRIX_DEFAUT'             => Configuration::get('SPEEDBOX_PRIX_DEFAUT', null, null),
            'SPEEDBOX_GOOGLE_API_KEY'          => Configuration::get('SPEEDBOX_GOOGLE_API_KEY', null, null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {

        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }

    }

    public function getOrderShippingCost($params, $shipping_cost)
    {
        if (Context::getContext()->customer->logged == true) {
            $id_address_delivery = Context::getContext()->cart->id_address_delivery;
            $address             = new Address($id_address_delivery);

            $cost = $this->calculateShippingCost($address->city);

            return $cost;
        }

        return $shipping_cost;
    }

    public function getOrderShippingCostExternal($params)
    {
        return true;
    }

    protected function addCarrier()
    {
        $carrier = new Carrier();

        $carrier->name                 = $this->l('Livraison en relais Speedbox');
        $carrier->is_module            = true;
        $carrier->active               = 1;
        $carrier->range_behavior       = 1;
        $carrier->need_range           = 1;
        $carrier->shipping_external    = true;
        $carrier->range_behavior       = 1;
        $carrier->external_module_name = 'speedbox';
        $carrier->shipping_method      = 2;

        foreach (Language::getLanguages() as $lang) {
            $carrier->delay[$lang['id_lang']] = $this->l('Livraison au Maroc vers des relais Pickup');
        }

        if ($carrier->add() == true) {
            copy(dirname(__FILE__) . '/views/img/speedbox_logo.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg');
            Configuration::updateValue('SPEEDBOX_RELAIS_CARRIER', (int) $carrier->id);
            return $carrier;
        }

        return false;
    }

    protected function addGroups($carrier)
    {
        $groups_ids = array();
        $groups     = Group::getGroups(Context::getContext()->language->id);
        foreach ($groups as $group) {
            $groups_ids[] = $group['id_group'];
        }

        if (version_compare(_PS_VERSION_, '1.6.0.0 ', '>')) {
            $carrier->setGroups($groups_ids);
        } else {
            $this->helper->getSpeedboxModel()->setGroups($carrier, $groups_ids);

        }

    }

    protected function addRanges($carrier)
    {
        $range_price             = new RangePrice();
        $range_price->id_carrier = $carrier->id;
        $range_price->delimiter1 = '0';
        $range_price->delimiter2 = '10000';
        $range_price->add();

        $range_weight             = new RangeWeight();
        $range_weight->id_carrier = $carrier->id;
        $range_weight->delimiter1 = '0';
        $range_weight->delimiter2 = '20';
        $range_weight->add();
    }

    protected function addZones($carrier)
    {
        $zones = Zone::getZones();

        foreach ($zones as $zone) {
            $carrier->addZone($zone['id_zone']);
        }

    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {

        if ($_GET['controller'] == 'AdminSpeedboxOrders') {
// ajouté via template

            /* $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
        $this->context->controller->addJS($this->_path . 'views/js/admin/jquery/plugins/marquee/jquery.marquee.min.js');*/

        }
    }

    public function hookExtraCarrier()
    {
        // finalement template ajouté via ajax et les javascript ajouté via le hookHeader
    }
    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
        $inOrderPage = false;
        if (!($file = basename(Tools::getValue('controller')))) {
            $file = str_replace('.php', '', basename($_SERVER['SCRIPT_NAME']));
        }
        if (in_array($file, array('order-opc', 'order', 'orderopc'))) {
            $inOrderPage = true;
            $this->context->controller->addJS('https://maps.googleapis.com/maps/api/js?key=' . Configuration::get('SPEEDBOX_GOOGLE_API_KEY'));
        }
        $options         = json_encode($this->helper->get_cities_values());
        $address_details = $this->getAddressDetails();
        $this->context->smarty->assign(array(
            'options'                    => $options,
            'speedbox_relais_carrier_id' => (int) Configuration::get('SPEEDBOX_RELAIS_CARRIER'),
            'speedbox_carrier_button_id' => $this->getIdSpeedboxButton(),
            'selectedCity'               => $address_details['city'],
            'inOrderPage'                => $inOrderPage,
        ));
        return $this->display(__FILE__, 'views/templates/front/header.tpl');

    }

    //add a tab in the backoffice menu
    public function addTab($tabs, $id_parent = 0)
    {

        copy(_PS_MODULE_DIR_ . $this->name . '/logo.gif', _PS_IMG_DIR_ . 't/' . 'AdminSpeedboxZones.gif');
        foreach ($tabs as $tab) {
            $tabModel             = new Tab();
            $tabModel->module     = $this->name;
            $tabModel->active     = $tab['active'];
            $tabModel->class_name = $tab['className'];
            $tabModel->id_parent  = $id_parent;

            //tab text in each language
            foreach (Language::getLanguages(true) as $lang) {
                $tabModel->name[$lang['id_lang']] = $tab['name'];
            }

            $tabModel->add();

            //submenus of the tab
            if (isset($tab['childs']) && is_array($tab['childs'])) {
                $this->addTab($tab['childs'], Tab::getIdFromClassName($tab['className']));
            }
        }
        return true;
    }

    //remove a tab and its childrens from the backoffice menu
    public function removeTab($tabs)
    {
        foreach ($tabs as $tab) {
            $id_tab = (int) Tab::getIdFromClassName($tab["className"]);
            if ($id_tab) {
                $tabModel = new Tab($id_tab);
                $tabModel->delete();
            }

            if (isset($tab["childs"]) && is_array($tab["childs"])) {
                $this->removeTab($tab["childs"]);
            }
        }

        return true;
    }

    public function getOrderStates()
    {

        $orderStates = OrderState::getOrderStates((int) $this->context->language->id);
        foreach ($orderStates as $state) {
            $query[] = array(
                'value' => $this->l($state['id_order_state']),
                'label' => $this->l($state['name']),
            );

        }
        return array('id' => 'value',
            'name'            => 'label',
            'query'           => $query);
    }
    public function getCarriers()
    {

        $carriers = Carrier::getCarriers($this->context->language->id, false, false, false, null, (defined('ALL_CARRIERS') ? ALL_CARRIERS : null));
        foreach ($carriers as $carrier) {
            $query[] = array(
                'value' => $this->l($carrier['id_carrier']),
                'label' => $this->l($carrier['name']),
            );
        }
        return array('id' => 'value',
            'name'            => 'label',
            'query'           => $query);

    }
    public function getPaymentMethods()
    {
        $query   = array();
        $query[] = array(
            'value' => '0',
            'label' => $this->l('--Please Select--'),
        );

        foreach (PaymentModule::getInstalledPaymentModules() as $payment) {
            $module = Module::getInstanceByName($payment['name']);
            if (Validate::isLoadedObject($module) && $module->active) {
                $query[] = array(
                    'value' => $this->l($module->name),
                    'label' => $this->l($module->displayName),
                );

            }
        }

        return array('id' => 'value',
            'name'            => 'label',
            'query'           => $query);

    }

    public function hookNewOrder($params)
    {

        if ($params['order']->id_carrier == Configuration::get('SPEEDBOX_RELAIS_CARRIER')) {
            $order = $params['order'];

            $relay_info = Context::getContext()->cookie->speedbox_selected_relais_infos;
            $relay_id   = Context::getContext()->cookie->speedbox_selected_relais_id;

            $this->speedbox_orders_model->SetSpeedboxAttributeOrder($order->id, 'speedbox_selected_relais_id', $relay_id);
            $this->speedbox_orders_model->SetSpeedboxAttributeOrder($order->id, 'speedbox_selected_relais_infos', $relay_info);
            $this->speedbox_orders_model->SetSpeedboxAttributeOrder($order->id, 'speedbox_statut_colis', '-');

            $order->speedbox_selected_relais_id    = $relay_id;
            $order->speedbox_selected_relais_infos = $relay_info;
            $order->update();

        }

    }

    public function hookOrderConfirmation($params)
    {
        // utilisation du hookNewOrder  si jamais le client n'arrive pas a la page de confirmation
    }
    public function calculateShippingCost($city)
    {

        try {
            $cost = 0;
            if (Configuration::get('SPEEDBOX_GESTION_FRAIS_LIVRAISON', null, null) == 2) {

                $available_table_rates = $this->helper->get_available_table_rates($city);
                $table_rate            = $this->helper->pick_cheapest_table_rate($available_table_rates);

                if ($table_rate != false) {
                    $cost = $table_rate['cout'];
                }

            } else {
                $cost        = Configuration::get('SPEEDBOX_PRIX_DEFAUT', null, null);
                $point_relai = Context::getContext()->cookie->speedbox_selected_relais_id;
                if ($point_relai) {
                    $cout_temps = $this->helper->get_api()->colis->coutTemps($point_relai);
                    if (isset($cout_temps['frais'])) {
                        $cost = Configuration::get('SPEEDBOX_SUPPLEMENT', null, null) + (double) $cout_temps['frais'];
                    }

                }

            }

            return $cost;
        } catch (Exception $e) {
            echo $this->l($e->getMessage());
        }

    }

    public function UpdatePoints($params)
    {

        if ($this->context->country->iso_code == 'MA') {
            $params['country'] = $this->context->country->iso_code;

            $speedbox_points_relais = $this->helper->get_speedbox_points_relais($params);
            $prefix_url             = (Configuration::get('PS_SSL_ENABLED') || Tools::usingSecureMode()) ? 'https://' : 'http://';
            $this->context->smarty->assign(array(
                'ps_version'                 => (float) _PS_VERSION_,
                'ssl'                        => (int) Configuration::get('PS_SSL_ENABLED'),
                'ssl_everywhere'             => (int) Configuration::get('PS_SSL_ENABLED_EVERYWHERE'),
                'base_dir'                   => $prefix_url . Tools::getShopDomain() . __PS_BASE_URI__,
                'base_dir_ssl'               => $prefix_url . Tools::getShopDomainSsl() . __PS_BASE_URI__,
                'speedbox_points_relais'     => (!isset($speedbox_points_relais['error']) ? $speedbox_points_relais : null),
                'error'                      => (isset($speedbox_points_relais['error']) ? $this->l($speedbox_points_relais['error']) : null),
                'speedbox_selectedrelay'     => (isset(Context::getContext()->cookie->speedbox_selected_relais_id) ? Context::getContext()->cookie->speedbox_selected_relais_id : null),
                'speedbox_relais_status'     => (Tools::getValue('speedboxrelais') ? Tools::getValue('speedboxrelais') : null),
                'speedbox_relais_carrier_id' => (int) Configuration::get('SPEEDBOX_RELAIS_CARRIER'),
                'selectedCity'               => $params['city'],
            ));

            if ((int) $params['id'] == Configuration::get('SPEEDBOX_RELAIS_CARRIER')) {
                return $this->display(__FILE__, 'views/templates/front/speedbox_relais.tpl');
            }

        }
    }

    public function getIdSpeedboxButton()
    {
        foreach ($this->context->cart->getDeliveryOptionList() as $id_address => $option_list) {
            $ind = 0;
            foreach ($option_list as $key => $option) {
                if ((int) $key == Configuration::get('SPEEDBOX_RELAIS_CARRIER')) {
                    return 'delivery_option_' . $id_address . '_' . $ind;
                }
                $ind++;
            }
        }
        return false;

    }

    public function getAddressDetails()
    {
        $id_address_delivery = $this->context->cart->id_address_delivery;

        $address = new Address($id_address_delivery);

        return $address->getFields();
    }

}
