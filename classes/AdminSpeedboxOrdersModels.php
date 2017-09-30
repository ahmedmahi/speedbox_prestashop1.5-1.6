<?php

/**
 * @author      Speedbox ( http://www.speedbox.ma)
 * @copyright   2017 Speedbox  ( http://www.speedbox.ma)
 * @developer   Ahmed MAHI <1hmedmahi@gmail.com> (http://ahmedmahi.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class AdminSpeedboxOrdersModels
{
    public function SetSpeedboxAttributeOrder($id_order, $attribute, $val)
    {
        Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'orders SET ' . $attribute . ' = \'' . $val . '\' WHERE id_order = "' . $id_order . '"');
    }

    public function SetTrackingNumber($id_order, $shipping_number)
    {
        Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'orders SET shipping_number = "' . pSQL($shipping_number) . '" WHERE id_order = "' . $id_order . '"');
        Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'order_carrier SET tracking_number = "' . pSQL($shipping_number) . '" WHERE id_order = "' . $id_order . '"');
    }
    public function getSelectedOrdersList($id_orders)
    {
        $sql = 'SELECT    O.`id_order` AS id_order
                            FROM      ' . _DB_PREFIX_ . 'orders AS O,
                            ' . _DB_PREFIX_ . 'carrier AS CA
                            WHERE     CA.id_carrier=O.id_carrier AND
                            id_order IN (' . implode(',', array_map('intval', $id_orders)) . ')';
        return Db::getInstance()->ExecuteS($sql);

    }

    /* Get all orders but statuses cancelled, delivered, error */
    public function getListeExpeditions($id_shop)
    {
        if ($id_shop == 0) {
            $id_shop = 'LIKE "%"';
        } else {
            $id_shop = '= ' . (int) $id_shop;
        }

        $sql = '    SELECT id_order
                    FROM ' . _DB_PREFIX_ . 'orders O
                    WHERE `current_state` NOT IN(' . (int) Configuration::get('SPEEDBOX_ETAPE_LIVRE', null, null, (int) $id_shop) . ',0,5,6,7,8) AND O.id_shop ' . $id_shop . '
                    AND `speedbox_statut_colis` NOT IN("STATUT_RECU","STATUT_ANNULE")
                    ORDER BY id_order DESC
                    LIMIT 500';

        $result = Db::getInstance()->ExecuteS($sql);

        $orders = array();
        if (!empty($result)) {
            foreach ($result as $order) {
                $orders[] = (int) $order['id_order'];
            }
        }
        return $orders;
    }

    public function getExpeditionsInfos()
    {

        $orderlist = array();
        $fieldlist = array('O.`id_order`', 'O.`id_cart`', 'AD.`lastname`', 'AD.`firstname`', 'AD.`postcode`', 'AD.`city`', 'CL.`iso_code`', 'C.`email`', 'CA.`name`');

        $current_shop = (int) Tools::substr(Context::getContext()->cookie->shopContext, 2);

        $orders = $this->getListeExpeditions($current_shop);

        $liste_expeditions = 'O.id_order IN (' . implode(',', $orders) . ')';

        $relais_carrier_log = $relais_carrier_sql = '';
        $relais_carrier_arr = array();

        if ($current_shop == 0 && Shop::isFeatureActive()) {

            foreach (Shop::getShops(true) as $shop) {
                if (Configuration::get('SPEEDBOX_RELAIS_CARRIER', null, null, $shop['id_shop'])) {

                    $relais_carrier_arr[] = Configuration::get('SPEEDBOX_RELAIS_CARRIER', null, null, $shop['id_shop']);
                    $relais_carrier_log   = implode(',', $relais_carrier_arr);

                    $relais_carrier_sql = 'CA.id_carrier IN (' . $relais_carrier_log . ') ';
                }
            }
        } else {
            if (Configuration::get('SPEEDBOX_RELAIS_CARRIER', null, null, $current_shop)) {
                $relais_carrier_log = Configuration::get('SPEEDBOX_RELAIS_CARRIER', null, null, $current_shop);
                $relais_carrier_sql = 'CA.id_carrier IN (' . $relais_carrier_log . ')  ';
            }
        }

        if (!empty($orders)) {
            $sql = 'SELECT  ' . implode(', ', $fieldlist) . '
                    FROM    ' . _DB_PREFIX_ . 'orders AS O,
                            ' . _DB_PREFIX_ . 'carrier AS CA,
                            ' . _DB_PREFIX_ . 'customer AS C,
                            ' . _DB_PREFIX_ . 'address AS AD,
                            ' . _DB_PREFIX_ . 'country AS CL
                    WHERE   O.id_address_delivery=AD.id_address AND
                            C.id_customer=O.id_customer AND
                            CL.id_country=AD.id_country AND
                            CA.id_carrier=O.id_carrier AND
                            (' . $relais_carrier_sql . ') AND
                            (' . $liste_expeditions . ')
                    ORDER BY id_order DESC';

            $orderlist = Db::getInstance()->ExecuteS($sql);

        }
        return $orderlist;
    }

}
