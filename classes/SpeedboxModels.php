<?php

/**
 * @author      Speedbox ( http://www.speedbox.ma)
 * @copyright   2017 Speedbox  ( http://www.speedbox.ma)
 * @developer   Ahmed MAHI <1hmedmahi@gmail.com> (http://ahmedmahi.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class SpeedboxModels
{
    public function setGroups($carrier, $groups)
    {
        if (!is_array($groups) || !count($groups)) {
            return true;
        }
        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'carrier_group (id_carrier, id_group) VALUES ';
        foreach ($groups as $id_group) {
            $sql .= '(' . (int) $carrier->id . ', ' . (int) $id_group . '),';
        }

        return Db::getInstance()->execute(rtrim($sql, ','));
    }
    public function disableOldCarriers()
    {
        // Disable old carriers
        if (!Db::getInstance()->Execute('UPDATE ' . _DB_PREFIX_ . 'carrier SET deleted = 1 WHERE external_module_name LIKE \'%speedbox%\'')) {
            return false;
        }
        return true;
    }

}
