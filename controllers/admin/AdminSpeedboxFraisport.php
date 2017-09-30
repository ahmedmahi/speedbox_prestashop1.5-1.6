<?php

/**
 * @author      Speedbox ( http://www.speedbox.ma)
 * @copyright   2017 Speedbox  ( http://www.speedbox.ma)
 * @developer   Ahmed MAHI <1hmedmahi@gmail.com> (http://ahmedmahi.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once _PS_MODULE_DIR_ . 'speedbox/classes/AdminSpeedboxFraisportModels.php';
class AdminSpeedboxFraisportController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap  = true;
        $this->table      = 'speedbox_frais_port';
        $this->identifier = 'id';
        $this->className  = 'AdminSpeedboxFraisportModels';

        parent::__construct();

        $id_lang = $this->context->language->id;

        /* $this->_join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'speedbox_frais_port_lang b ON (b.id = a.id AND b.id_lang = ' . $id_lang . ')';
        $this->_select .= ' b.id_zone as id_zone, b.min as min,b.max as max';*/
        $this->_join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'speedbox_zones z ON  (z.`id_zone` = a.`id_zone`)';
        $this->_select .= " z.nom as nom_zone, CASE WHEN a.`condition` = '0' THEN 'Poids (Kg)'
         WHEN a.`condition` = '1' THEN 'Total ( MAD)' END AS conditiontext";

        $conditions_array = array();
        foreach ($this->getConditions() as $condition) {
            $conditions_array[$condition['value']] = $condition['nom'];
        }
        $zones_array = array();
        foreach ($this->zonesCollection() as $zone) {
            $zones_array[$zone['id_zone']] = $zone['nom'];
        }
        //data to the grid of the "view" action
        $this->fields_list = [
            'id'            => [
                'title' => $this->l('ID'),
                'type'  => 'text',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'nom_zone'      => [
                'title'      => $this->l('Zone'),
                'type'       => 'select',
                'list'       => $zones_array,
                'filter_key' => 'a!id_zone',
            ],
            'conditiontext' => [
                'title'      => $this->l('Condition'),
                'type'       => 'select',
                'list'       => $conditions_array,
                'filter_key' => 'a!condition',
            ],
            'min'           => [
                'title' => $this->l('Min'),
                'type'  => 'text',
            ],
            'max'           => [
                'title' => $this->l('Max'),
                'type'  => 'text',
            ],
            'cout'          => [
                'title' => $this->l('Coût'),
                'type'  => 'text',
            ],
            'active'        => [
                'title'  => $this->l('Status'),
                'active' => 'status',
                'align'  => 'text-center',
                'class'  => 'fixed-width-sm',
            ],
            'date_add'      => [
                'title' => $this->l('Created'),
                'type'  => 'datetime',
            ],
            'date_upd'      => [
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

                'id_zone'   => [
                    'type'     => 'select',
                    'label'    => $this->l('Zone'),
                    'name'     => 'id_zone',
                    'options'  => array(
                        'query' => $this->zonesCollection(),
                        'id'    => 'id_zone',
                        'name'  => 'nom',
                    ),
                    'required' => true,
                ],
                'condition' => [
                    'label'    => $this->l('Condition'),
                    'type'     => 'select',
                    'name'     => 'condition',
                    'options'  => array(
                        'id'    => 'value',
                        'name'  => 'nom',
                        'query' => $this->getConditions(),
                    ),
                    'required' => true,
                ],
                'min'       => [
                    'label'    => $this->l('Min'),
                    'type'     => 'text',
                    'name'     => 'min',
                    'required' => true,
                ],
                'max'       => [
                    'label'    => $this->l('Max'),
                    'type'     => 'text',
                    'name'     => 'max',
                    'required' => true,
                ],
                'cout'      => [
                    'label'    => $this->l('Coût'),
                    'type'     => 'text',
                    'name'     => 'cout',
                    'required' => true,
                ],

                'active'    => [
                    'type'   => $typeactive,
                    'label'  => $this->l('Active'),
                    'name'   => 'active',
                    'class'  => $classactive,
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
    }

    public function initContent()
    {
        parent::initContent();
    }
    public function zonesCollection()
    {
        $zones = new AdminSpeedboxZonesModels();
        return $zones->getCollection();

    }
    public function getConditions()
    {
        return array(array(
            'value' => 0,
            'nom'   => $this->l('Poids (Kg)'),
        ), array(
            'value' => 1,
            'nom'   => $this->l('Total ( MAD)')));
    }

}
