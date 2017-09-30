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

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'speedbox` (
    `id_speedbox` int(11) NOT NULL AUTO_INCREMENT,
    PRIMARY KEY  (`id_speedbox`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "speedbox_zones` (
   `id_zone` int(11) unsigned NOT NULL AUTO_INCREMENT,
   `nom` varchar(255) NOT NULL default '' ,
   `villes` varchar(255) NOT NULL default '' ,
   `active` int NOT NULL default 1 ,
   `date_add` datetime NULL ,
   `date_upd` datetime NULL ,
   PRIMARY KEY (`id_zone`)
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "speedbox_frais_port` (
    `id` int(11) unsigned NOT NULL auto_increment,
    `id_zone`  int(11) NOT NULL default 0 ,
    `condition` varchar(255) NOT NULL default '' ,
    `min` varchar(255) NOT NULL default '' ,
    `max` varchar(255) NOT NULL  default '' ,
    `cout` varchar(255) NOT NULL default '' ,
    `active` int NOT NULL default 1 ,
    `date_add` datetime NULL ,
    `date_upd` datetime NULL ,
     PRIMARY KEY(`id`)
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;";

$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "orders` ADD speedbox_numero_colis TEXT";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "orders` ADD speedbox_statut_colis TEXT";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "orders` ADD speedbox_code_barre_colis TEXT";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "orders` ADD speedbox_selected_relais_id TEXT";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "orders` ADD speedbox_selected_relais_infos TEXT";
foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
