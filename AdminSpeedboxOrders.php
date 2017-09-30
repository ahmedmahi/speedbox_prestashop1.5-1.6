<?php

/**
 * @author      Speedbox ( http://www.speedbox.ma)
 * @copyright   2017 Speedbox  ( http://www.speedbox.ma)
 * @developer   Ahmed MAHI <1hmedmahi@gmail.com> (http://ahmedmahi.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once _PS_MODULE_DIR_ . 'speedbox/classes/AdminSpeedboxOrdersModels.php';
require_once _PS_MODULE_DIR_ . 'speedbox/classes/Helper.php';

class AdminSpeedboxOrders extends AdminTab
{
    private $module = 'speedbox';

    public $controller_type;

    public $helper;
    public $speedbox_orders_model;
    public $speedbox_error        = '';
    public $speedbox_confirmation = '';

    public function __construct()
    {
        $this->name = 'speedbox';

        $this->multishop_context       = Shop::CONTEXT_ALL | Shop::CONTEXT_GROUP | Shop::CONTEXT_SHOP;
        $this->multishop_context_group = Shop::CONTEXT_GROUP;

        $this->helper                = new S3ibusiness_Speedbox_Helper();
        $this->speedbox_orders_model = $this->helper->getSpeedboxOrdersModel();

        parent::__construct();

    }

    public function fetchTemplate($path, $name)
    {

        return Context::getContext()->smarty->fetch(_PS_MODULE_DIR_ . $this->name . $path . $name . '.tpl');
    }

    /* Get eligible orders and builds up display */
    public function display()
    {

        //envoi
        $speedbox_zones_model = $this->helper->getSpeedboxZonesModel();
        $lang_id              = Context::getContext()->language->id;
        if (Tools::getIsset('envoi')) {
            $orderlist = $this->getSelectedOrdersList();
            $weights   = Tools::getValue('parcelweight');
            if (!empty($orderlist)) {
                $numero_prise_en_charge = $this->helper->generate_token(true);
                foreach ($orderlist as $orders) {

                    $order_id = $orders['id_order'];
                    if (Validate::isLoadedObject($order = new Order($order_id))) {

                        $address_delivery      = new Address($order->id_address_delivery, (int) $lang_id);
                        $address_invoice       = new Address($order->id_address_invoice, (int) $lang_id);
                        $customer              = new Customer((int) $order->id_customer);
                        $pointrelaist          = $order->speedbox_selected_relais_id;
                        $colis_numero_speedbox = $order->speedbox_numero_colis;
                        if ($pointrelaist && !$order->speedbox_numero_colis) {
                            $numero_colis = substr($this->helper->generate_token(), 0, 6);
                            $poids        = number_format($weights[$order_id], 2, '.', '');
                            $tel_dest     = (($address_delivery->phone_mobile) ? $address_delivery->phone_mobile : (($address_invoice->phone_mobile) ? $address_invoice->phone_mobile : (($address_delivery->phone) ? $address_delivery->phone : (($address_invoice->phone) ? $address_invoice->phone : ''))));
                            $coli         = array(
                                'date_de_commande' => date('d/m/Y', strtotime($order->date_add)),
                                'numero_colis'     => $numero_colis,
                                'pointrelais'      => $pointrelaist,
                                'nom_du_client'    => $address_delivery->firstname . ' ' . $address_delivery->lastname,
                                'email_du_client'  => $customer->email,
                                'numero_du_client' => $this->helper->formatTel($tel_dest),
                                'cash_due'         => $this->getCashDue($order),
                                'poids'            => $poids,
                            );
                            $result = $this->helper->get_api()->colis->create($coli);
                            if (is_array($result) && $result['result'] == 'ok') {

                                $this->speedbox_orders_model->SetSpeedboxAttributeOrder($order_id, 'speedbox_numero_colis', $result['numero_speedbox']);
                                $this->speedbox_orders_model->SetSpeedboxAttributeOrder($order_id, 'speedbox_code_barre_colis', $result['code_barre']);
                                $this->speedbox_orders_model->SetSpeedboxAttributeOrder($order_id, 'speedbox_statut_colis', $result['statut']);
                                $this->apiPriseEnCharge($numero_prise_en_charge, $result['numero_speedbox'], $order);
                                $this->apiTracker($result['numero_speedbox'], $order);

                            } else {
                                $this->speedbox_error .= $this->l('Commande ID :' . $order_id . ' ' . $result);
                            }

                        } elseif ($colis_numero_speedbox) {

                            $track = $this->apiTracker($colis_numero_speedbox, $order, true);

                            if (isset($track['statut']) && $track['statut'] == 100) {
                                $this->apiPriseEnCharge($numero_prise_en_charge, $colis_numero_speedbox, $order);
                            }

                        }

                    }
                }
            }

        }

        // delivered
        if (Tools::getIsset('delivered')) {
            $orderlist = $this->getSelectedOrdersList();
            if (!empty($orderlist)) {
                foreach ($orderlist as $orders) {
                    $id_order = $orders['id_order'];

                    if (Validate::isLoadedObject($order = new Order((int) $id_order))) {

                        $colis_numero_speedbox = $order->speedbox_numero_colis;

                        if ($colis_numero_speedbox) {
                            $data = array('speedbox_statut_colis' => 'STATUT_RECU');
                            $this->valdatePrintMessage(array('resultat' => 'ok'), $order, $this->l('Delivered order status was updated'), $data);
                            $this->LivrerShipment($order);
                        } else {
                            $this->valdatePrintMessage($this->l('Package should be treated first'), $order);
                        }

                    }

                }
                //$this->speedbox_confirmation .= $this->l('Delivered orders statuses were updated');
            }

        }
        // tracker
        if (Tools::getIsset('tracker')) {
            $orderlist = $this->getSelectedOrdersList();
            if (!empty($orderlist)) {
                foreach ($orderlist as $orders) {
                    $id_order = $orders['id_order'];
                    if (Validate::isLoadedObject($order = new Order((int) $id_order))) {
                        $colis_numero_speedbox = $order->speedbox_numero_colis;
                        if ($colis_numero_speedbox) {
                            $this->apiTracker($colis_numero_speedbox, $order);
                        } else {
                            $this->valdatePrintMessage($this->l('Package should be treated first'), $order);
                        }

                    }

                }
            }

        }

        // cancel
        if (Tools::getIsset('cancel')) {
            $orderlist = $this->getSelectedOrdersList();
            if (!empty($orderlist)) {
                foreach ($orderlist as $orders) {
                    $id_order = $orders['id_order'];
                    if (Validate::isLoadedObject($order = new Order((int) $id_order))) {
                        $colis_numero_speedbox = $order->speedbox_numero_colis;
                        $result                = $this->helper->get_api()->colis->cancel($colis_numero_speedbox);
                        $post_metas            = array('speedbox_statut_colis' => 'STATUT_ANNULE');
                        $this->valdatePrintMessage($result, $order, $this->l('Package well removed'), $post_metas);

                    }

                }
            }
        }

        $order_info     = '';
        $statuses_array = array();
        $statuses       = OrderState::getOrderStates((int) Context::getContext()->language->id);

        foreach ($statuses as $status) {
            $statuses_array[$status['id_order_state']] = $status['name'];
        }
        $orderlist = $this->speedbox_orders_model->getExpeditionsInfos();

        if (!empty($orderlist)) {
            foreach ($orderlist as $order_var) {

                $order            = new Order($order_var['id_order']);
                $address_delivery = new Address($order->id_address_delivery, (int) Context::getContext()->language->id);

                $current_state_id   = $order->current_state;
                $current_state_name = $statuses_array[$order->current_state];

                $weight = number_format($order->getTotalWeight(), 2, '.', '.');
                $amount = number_format($order->total_paid, 2, '.', '.');

                $selected_relais_infos = json_decode($order->speedbox_selected_relais_infos, true);
                $address               = $selected_relais_infos['shop_name'] . '<br/>' . $selected_relais_infos['address'] . '<br/>' . $selected_relais_infos['postcode'] . ' ' . $selected_relais_infos['city'];

                $order_info[] = array(
                    'id'                    => $order->id,
                    'reference'             => $order->reference,
                    'date'                  => date('d/m/Y H:i:s', strtotime($order->date_add)),
                    'nom'                   => $address_delivery->firstname . ' ' . $address_delivery->lastname,
                    'address'               => $address,
                    'id'                    => $order->id,
                    'poids'                 => $weight,
                    'weightunit'            => Configuration::get('PS_WEIGHT_UNIT', null, null, (int) $order->id_shop),
                    'prix'                  => $amount,
                    'statut'                => $current_state_name,
                    'speedbox_statut_colis' => $order->speedbox_statut_colis,

                );
            }
        } else {
            $order_info = 'error';
        }

        // Assign smarty variables and fetches template
        Context::getContext()->smarty->assign(array(
            'psVer'                 => _PS_VERSION_,
            'token'                 => $this->token,
            'order_info'            => $order_info,
            'speedbox_confirmation' => $this->speedbox_confirmation,
            'speedbox_error'        => $this->speedbox_error,
        ));
        echo $this->fetchTemplate('/views/templates/admin/', 'orderslist');
    }

    public function getSelectedOrdersList()
    {
        if (Tools::getIsset('checkbox')) {
            $orders = Tools::getValue('checkbox');
            if (is_string($orders)) {
                $orders = explode(',', $orders);
            }
            if (!empty($orders)) {

                $orderlist = $this->speedbox_orders_model->getSelectedOrdersList($orders);

                if (!empty($orderlist)) {
                    return $orderlist;

                } else {
                    $this->speedbox_error .= $this->l('No Speedbox trackings to generate.');
                    return array();
                }
            } else {
                $this->speedbox_error .= $this->l('No order selected.');
                return array();
            }
        } else {
            $this->speedbox_error .= $this->l('No order selected.');
            return array();
        }

    }

    public function ExpedieShipment($id_order, $shipmentnumber)
    {

        if (Validate::isLoadedObject($order = new Order((int) $id_order))) {

            $current_state_id = $order->current_state;
            $internalref      = $order->reference;

            if ($current_state_id != (int) Configuration::get('SPEEDBOX_ETAPE_EXPEDIEE', null, null, (int) $order->id_shop) && $current_state_id != (int) Configuration::get('SPEEDBOX_ETAPE_LIVRE', null, null, (int) $order->id_shop)) {

                $customer = new Customer((int) $order->id_customer);
                $carrier  = new Carrier((int) $order->id_carrier, (int) Context::getContext()->language->id);

                $url                    = 'https://api.speedbox.ma/api/getstatus/' . $shipmentnumber;
                $order->shipping_number = $shipmentnumber;
                $this->speedbox_orders_model->SetTrackingNumber($id_order, $order->shipping_number);

                $order->update();

                $history                 = new OrderHistory();
                $history->id_order       = (int) $id_order;
                $history->id_employee    = (int) Context::getContext()->employee->id;
                $history->id_order_state = (int) Configuration::get('SPEEDBOX_ETAPE_EXPEDIEE', null, null, (int) $order->id_shop);
                $history->changeIdOrderState((int) Configuration::get('SPEEDBOX_ETAPE_EXPEDIEE', null, null, (int) $order->id_shop), $id_order);

                $template_vars = array('{followup}' => $url, '{firstname}' => $customer->firstname, '{lastname}' => $customer->lastname, '{order_name}' => $order->reference, '{id_order}' => (int) $order->id);
                switch (Language::getIsoById((int) $order->id_lang)) {
                    case 'fr':
                        $subject = 'Votre commande sera livrée par Speedbox';
                        break;
                    case 'en':
                        $subject = 'Your parcel will be delivered by Speedbox';
                        break;

                }
                if (!$history->addWithemail(true, $template_vars)) {
                    $this->speedbox_error .= $this->l('an error occurred while changing status or was unable to send e-mail to the customer');
                }
                if (!Validate::isLoadedObject($customer) || !Validate::isLoadedObject($carrier)) {
                    $this->speedbox_error .= $this->l(Tools::displayError());

                }
                Mail::Send((int) $order->id_lang, 'in_transit', $subject, $template_vars, $customer->email, $customer->firstname . ' ' . $customer->lastname);
                $this->speedbox_confirmation .= $this->l('Order') . ' ' . $internalref . ' - ' . $this->l('Parcel') . ' ' . $shipmentnumber . ' ' . $this->l('is handled by Speedbox') . '<br/>';
            } else {
                $this->speedbox_error .= $this->l('Order') . ' ' . $internalref . ' - ' . $this->l('No update for parcel') . ' ' . $shipmentnumber . '<br/>';
            }

        }
    }

    public function LivrerShipment($order)
    {

        $current_state_id = $order->current_state;
        $internalref      = $order->reference;

        if ($current_state_id != (int) Configuration::get('SPEEDBOX_ETAPE_LIVRE', null, null, (int) $order->id_shop)) {
            $history                 = new OrderHistory();
            $history->id_order       = (int) $order->id;
            $history->id_employee    = (int) Context::getContext()->employee->id;
            $history->id_order_state = (int) Configuration::get('SPEEDBOX_ETAPE_LIVRE', null, null, (int) $order->id_shop);
            $history->changeIdOrderState((int) Configuration::get('SPEEDBOX_ETAPE_LIVRE', null, null, (int) $order->id_shop), $order->id);
            $history->addWithemail();
            $this->speedbox_confirmation .= $this->l('Order') . ' ' . $internalref . ' - ' . $this->l('Parcel') . ' ' . $order->shipping_number . ' ' . $this->l('is delivered') . '<br/>';
        }

    }

    public function getCashDue($order)
    {
        $conf = (string) Configuration::get('SPEEDBOX_PAIEMENT_LIVRAISON');
        if ($conf == '0') {
            return '0';
        }
        return ($conf == $order->module) ? number_format($order->total_paid, 2, '.', '') : '0';

    }

    public function apiPriseEnCharge($numero_prise_en_charge, $numero_speedbox, $order)
    {
        $infos_depc = array(
            'numero_prise_en_charge' => $numero_prise_en_charge,
            'numero_speedbox'        => $numero_speedbox,

        );

        $result_depc = $this->helper->get_api()->colis->demandePriseEnCharge($infos_depc);

        if (is_array($result_depc) && $result_depc['resultat'] == 'ok') {
            $shipmentnumber = $order->shipping_number;
            if (!$shipmentnumber) {
                $this->ExpedieShipment($order->id, $numero_speedbox);
            }
        }
        $this->valdatePrintMessage($result_depc, $order, $this->l('Support well sent:'));

    }

    public function valdatePrintMessage($result, $order, $message = '', $post_metas = array())
    {

        if (is_array($result) && $result['resultat'] == 'ok') {
            foreach ($post_metas as $key => $value) {
                $initial = $order->$key;
                if ($initial != $value) {
                    $this->speedbox_orders_model->SetSpeedboxAttributeOrder($order->id, $key, $value);
                }
            }
            unset($result['resultat']);

            $this->speedbox_confirmation .= $this->l('Commande ' . $order->reference . ' : ') . $message . $this->helper->html_show_array($result);

        } else {

            $this->speedbox_error .= $this->l('Commande ' . $order->reference . ' : ' . $result);
        }

    }
    public function apiTracker($colis_numero_speedbox, $order, $dajatraite = false)
    {

        $track      = $this->helper->get_api()->colis->track($colis_numero_speedbox);
        $post_metas = array();
        if ($track['statut'] !== 100) {

            $track['Statut']                 = $this->helper->getStatus($track['statut']);
            $track['Historique des statuts'] = implode("=>", $this->helper->getStatutHistorique($track['statut_historique']));
            $track['Dernière mise à jour'] = date('d/m/Y H:i', $track['last_updated_timestamp']);
            unset($track['last_updated_timestamp']);
            unset($track['statut']);
            unset($track['statut_historique']);
            unset($track['numero_prise_en_charge']);

            $post_metas = array('speedbox_statut_colis' => $track['Statut']);
        }

        $message = ($dajatraite ? $this->l('Parcels already treated here is the information:') : '');

        $this->valdatePrintMessage($track, $order, $message, $post_metas);

        return $track;
    }
}
