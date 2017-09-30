<?php

/**
 * @author      Speedbox ( http://www.speedbox.ma)
 * @copyright   2017 Speedbox  ( http://www.speedbox.ma)
 * @developer   Ahmed MAHI <1hmedmahi@gmail.com> (http://ahmedmahi.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once _PS_MODULE_DIR_ . 'speedbox/classes/AdminSpeedboxZonesModels.php';
require_once _PS_MODULE_DIR_ . 'speedbox/classes/Helper.php';
class AdminSpeedboxZonesController extends ModuleAdminController
{
    public function __construct()
    {

        $this->bootstrap  = true;
        $this->table      = 'speedbox_zones';
        $this->identifier = 'id_zone';
        $this->className  = 'AdminSpeedboxZonesModels';

        parent::__construct();

        $id_lang = $this->context->language->id;

        /* $this->_join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'speedbox_zones_lang b ON (b.id_zone = a.id_zone AND b.id_lang = ' . $id_lang . ')';
        $this->_select .= ' b.nom as nom, b.villes as villes';*/

        //data to the grid of the "view" action
        $this->fields_list = [
            'id_zone'  => [
                'title' => $this->l('ID ZONE'),
                'type'  => 'text',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'nom'      => [
                'title' => $this->l('Name'),
                'type'  => 'text',
            ],
            'villes'   => [
                'title' => $this->l('villes'),
                'type'  => 'text',
            ],
            'active'   => [
                'title'  => $this->l('Status'),
                'active' => 'status',
                'align'  => 'text-center',
                'class'  => 'fixed-width-sm',
            ],
            'date_add' => [
                'title' => $this->l('Created'),
                'type'  => 'datetime',
            ],
            'date_upd' => [
                'title' => $this->l('Updated'),
                'type'  => 'datetime',
            ],
        ];

        $this->actions = ['edit', 'delete'];

        $this->bulk_actions = array(
            'delete' => array(
                'text'    => $this->l('Delete selected'),
                'icon'    => 'icon-trash',
                'confirm' => $this->l('Delete selected items?'),
            ),
        );

        //fields to add/edit form
        $typeactive        = (version_compare(_PS_VERSION_, '1.6.0.0 ', '>')) ? 'switch' : 'radio';
        $classactive       = (version_compare(_PS_VERSION_, '1.6.0.0 ', '>')) ? '' : 't';
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('General Information'),
            ],
            'input'  => [
                'nom'      => [
                    'type'     => 'text',
                    'label'    => $this->l('Name'),
                    'name'     => 'nom',
                    'required' => true,
                ],
                'villes[]' => [
                    'type'     => 'select',
                    'multiple' => true,
                    'label'    => $this->l('Villes'),
                    'name'     => 'villes[]',
                    'required' => true,
                    'options'  => array(
                        'query' => $this->getVilles(),
                        'id'    => 'label',
                        'name'  => 'label',
                    ),

                ],
                'active'   => [
                    'type'   => $typeactive,
                    'label'  => $this->l('Active'),
                    'class'  => $classactive,
                    'name'   => 'active',
                    'values' => [
                        [
                            'id'    => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id'    => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];
        if (isset($_GET['updatespeedbox_zones']) && $this->loadObject()) {
            $this->fields_value['villes[]'] = explode(',', $this->loadObject()->villes);
        }
    }
    public function postProcess()
    {

        if (Tools::getValue('villes')) {
            $_POST['villes'] = implode(',', Tools::getValue('villes'));
        }

        parent::postProcess();
    }

    public function initContent()
    {
        parent::initContent();
    }

    public function getVilles()
    {

        $helper = new S3ibusiness_Speedbox_Helper();
        return $helper->getCitiesValues();
    }

}
