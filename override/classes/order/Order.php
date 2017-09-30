<?php
/*
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2016 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *
 * @author      Speedbox ( http://www.speedbox.ma)
 * @copyright   2017 Speedbox  ( http://www.speedbox.ma)
 * @developer   Ahmed MAHI <1hmedmahi@gmail.com> (http://ahmedmahi.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Order extends OrderCore
{

    public $speedbox_numero_colis;
    public $speedbox_statut_colis;
    public $speedbox_code_barre_colis;
    public $speedbox_selected_relais_id;
    public $speedbox_selected_relais_infos;

    public function __construct($id = null, $id_lang = null)
    {
        $definition                                             = ObjectModel::getDefinition($this);
        $definition['fields']['speedbox_numero_colis']          = array('type' => self::TYPE_STRING, 'validate' => 'isGenericName');
        $definition['fields']['speedbox_statut_colis']          = array('type' => self::TYPE_STRING, 'validate' => 'isGenericName');
        $definition['fields']['speedbox_code_barre_colis']      = array('type' => self::TYPE_STRING, 'validate' => 'isGenericName');
        $definition['fields']['speedbox_selected_relais_id']    = array('type' => self::TYPE_STRING, 'validate' => 'isGenericName');
        $definition['fields']['speedbox_selected_relais_infos'] = array('type' => self::TYPE_STRING, 'validate' => 'isGenericName');
        parent::__construct($id, $id_lang);

    }

}
