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

/**
 * In some cases you should not drop the tables.
 * Maybe the merchant will just try to reset the module
 * but does not want to loose all of the data associated to the module.
 */

$sql = array();

$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "speedbox`";
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "speedbox_zones`";
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "speedbox_frais_port`";

$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "orders` DROP speedbox_numero_colis";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "orders` DROP speedbox_statut_colis";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "orders` DROP speedbox_code_barre_colis";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "orders` DROP speedbox_selected_relais_id";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "orders` DROP speedbox_selected_relais_infos";

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
