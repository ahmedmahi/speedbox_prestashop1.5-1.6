{**
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
 *}


<script type="text/javascript">
    var speedboxRelaisCarrierId = "{$speedbox_relais_carrier_id|escape:'javascript':'UTF-8'}";
    var speedboxCarrierButtonId = "{$speedbox_carrier_button_id|escape:'javascript':'UTF-8'}";
    var selectedCity = "{$selectedCity|escape:'javascript':'UTF-8'}";
    var ajaxurl= 'modules/speedbox/ajax.php';
var json = JSON.parse({$options|json_encode});
	 var speedbox = new Speedbox(ajaxurl, speedboxCarrierButtonId); 
$( window ).load(function() {
  var speedboxCity = new SpeedboxCity(json);
  speedboxCity.changeCityToSelect();
  {if $inOrderPage }
    speedbox.setCity(selectedCity); 
{/if}

});

$(document).bind('ready ajaxComplete', function()
    { 
         speedbox.isDivconteneurExist(); 
    });

</script>
