<?php
/**
 * 2007-2016 PrestaShop
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

require_once realpath(dirname(__FILE__) . '/../../config/config.inc.php');
require_once realpath(dirname(__FILE__) . '/../../init.php');
require_once dirname(__FILE__) . '/speedbox.php';

$params = array(
    'city'     => Tools::getValue('city'),
    'PR_infos' => Tools::getValue('PR_infos'),
    'id'       => Tools::getValue('id'),
);

if ($params['city']) {
    echo Module::getInstanceByName('speedbox')->UpdatePoints($params);
} elseif ($params['PR_infos']) {
    $PR_infos                                                     = json_decode($params['PR_infos'], true);
    Context::getContext()->cookie->speedbox_selected_relais_id    = $PR_infos['relay_id'];
    Context::getContext()->cookie->speedbox_selected_relais_infos = $params['PR_infos'];

    echo $PR_infos['shop_name'] . ' ( ' . $PR_infos['relay_id'] . ' )';

}

//   if (Tools::getValue('action_ajax') == 'ajaxUpdate') {

//  }
