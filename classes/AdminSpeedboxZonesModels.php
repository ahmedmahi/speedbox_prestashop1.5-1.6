<?php

/**
 * @author      Speedbox ( http://www.speedbox.ma)
 * @copyright   2017 Speedbox  ( http://www.speedbox.ma)
 * @developer   Ahmed MAHI <1hmedmahi@gmail.com> (http://ahmedmahi.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once 'SpeedboxObjectModel.php';

class AdminSpeedboxZonesModels extends SpeedboxObjectModel
{

    public static $definition = [
        'table'     => 'speedbox_zones',
        'primary'   => 'id_zone',
        'multilang' => false,
        'fields'    => [
            'id_zone'  => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'nom'      => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'],
            'villes'   => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'],
            'active'   => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'db_type' => 'int'],
            'date_add' => [
                'type'     => self::TYPE_DATE,
                'validate' => 'isDate',
                'db_type'  => 'datetime',
            ],
            'date_upd' => [
                'type'     => self::TYPE_DATE,
                'validate' => 'isDate',
                'db_type'  => 'datetime',
            ],
        ],
    ];

    public $id_zone;
    public $nom;
    public $villes;
    public $active;
    public $date_add;
    public $date_upd;

    public function getCollectionLng()
    {
        $id_lang = Context::getContext()->language->id;
        $join    = ' WHERE (b.id_zone = a.id_zone AND b.id_lang = ' . $id_lang . ')';

        $sql = 'SELECT b.nom as nom, b.villes as villes,  a.id_zone as id_zone
                FROM ' . _DB_PREFIX_ . 'speedbox_zones as a, ' .
            _DB_PREFIX_ . 'speedbox_zones_lang as b ' .
            $join .
            '  ORDER BY b.id_zone DESC
                    LIMIT 500';
        $result = Db::getInstance()->ExecuteS($sql);
        return $result;

    }
    public function getCollection()
    {

        $sql = 'SELECT a.nom as nom, a.villes as villes,  a.id_zone as id_zone
                FROM ' . _DB_PREFIX_ . 'speedbox_zones as a ' .
            '  ORDER BY a.id_zone DESC
                    LIMIT 500';
        $result = Db::getInstance()->ExecuteS($sql);
        return $result;

    }
}
