<?php

/**
 * @author      Speedbox ( http://www.speedbox.ma)
 * @copyright   2017 Speedbox  ( http://www.speedbox.ma)
 * @developer   Ahmed MAHI <1hmedmahi@gmail.com> (http://ahmedmahi.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once 'SpeedboxObjectModel.php';

class AdminSpeedboxFraisportModels extends SpeedboxObjectModel
{

    public static $definition = [
        'table'     => 'speedbox_frais_port',
        'primary'   => 'id',
        'multilang' => false,
        'fields'    => [
            'id'        => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'default' => ''],
            'id_zone'   => ['type' => self::TYPE_INT, 'db_type' => 'int(11)', 'required' => 1, 'default' => 0],
            'condition' => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)', 'required' => 1, 'default' => ''],
            'min'       => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)', 'required' => 1, 'default' => ''],
            'max'       => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)', 'required' => 1, 'default' => ''],
            'cout'      => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)', 'required' => 1, 'default' => ''],
            'active'    => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'db_type' => 'int', 'required' => 1, 'default' => 1],
            'date_add'  => [
                'type'     => self::TYPE_DATE,
                'validate' => 'isDate',
                'db_type'  => 'datetime',
            ],
            'date_upd'  => [
                'type'     => self::TYPE_DATE,
                'validate' => 'isDate',
                'db_type'  => 'datetime',
            ],
        ],
    ];
    public $id;
    public $id_zone;
    public $condition;
    public $min;
    public $max;
    public $cout;
    public $active;
    public $date_add;
    public $date_upd;

    public function getCollection()
    {

        $sql = 'SELECT *
                FROM ' . _DB_PREFIX_ . 'speedbox_frais_port as a ' .
            '  ORDER BY a.id_zone DESC
                    LIMIT 500';
        $result = Db::getInstance()->ExecuteS($sql);
        return $result;

    }

}
