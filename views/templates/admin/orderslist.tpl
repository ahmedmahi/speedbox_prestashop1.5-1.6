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
 *
 *}

<link rel="stylesheet" type="text/css" href="../modules/speedbox/views/css/admin.css"/>
<script type="text/javascript" src="../modules/speedbox/views/js/admin/jquery/plugins/marquee/jquery.marquee.min.js"></script>

{literal}
<script type='text/javascript'>
    $(document).ready(function(){
        $('.marquee').marquee({
            duration: 20000,
            gap: 50,
            delayBeforeStart: 0,
            direction: 'left',
            duplicated: true,
            pauseOnHover: true
        });
        jQuery.expr[':'].contains = function(a, i, m) { 
            return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0; 
        };
    $("#tableFilter").keyup(function () {
        //split the current value of tableFilter
        var data = this.value.split(";");
        //create a jquery object of the rows
        var jo = $("#fbody").find("tr");
        if (this.value == "") {
            jo.show();
            return;
        }
        jo.hide();

       jo.filter(function (i, v) {
            var t = $(this);
            for (var d = 0; d < data.length; ++d) {
                if (t.is(":contains('" + data[d] + "')")) {
                    return true;
                }
            }
            return false;
        })
        //show the rows that match.
        .show();
        }).focus(function () {
            this.value = "";
            $(this).css({
                "color": "black"
            });
            $(this).unbind('focus');
        }).css({
            "color": "#C0C0C0"
        });
    });
    function checkallboxes(ele) {
        var checkboxes = $("#fbody").find(".checkbox:visible");
        if (ele.checked) {
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].type == 'checkbox') {
                    checkboxes[i].checked = true;
                }
            }
        } else {
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].type == 'checkbox') {
                    checkboxes[i].checked = false;
                }
            }
        }
    }
</script>
{/literal}
{if $speedbox_confirmation !== ''}
<div class="okmsg">{$speedbox_confirmation|unescape:'html':'UTF-8'}</div>
{/if}
{if $speedbox_error !== ''}
<div class="warnmsg">{$speedbox_error|escape:'htmlall':'UTF-8'}</div>
{/if}
<fieldset id="fieldset_grid"><legend><img src="../modules/speedbox/views/img/admin/admin.png"/>{l s='Orders management' mod='speedbox'}</legend>

{if $order_info !== 'error'}
    <input id="tableFilter" value="{l s='Search something, separate values with ; ' mod='speedbox'}"/><img id="filtericon" src="../modules/speedbox/views/img/search.png"/><br/><br/>
        <form id="exportform" action="index.php?tab=AdminSpeedboxOrders&token={$token|escape:'htmlall':'UTF-8'}" method="POST" enctype="multipart/form-data">
        <body><table>
                <thead>
                    <tr>
                        <th class="hcheckexport"><input type="checkbox" onchange="checkallboxes(this)"/></th>
                        <th class="hid">{l s='Order #' mod='speedbox'}</th>
                        {if $psVer >= 1.5}<th class="href">{l s='Reference' mod='speedbox'}</th>{/if}
                        <th class="hdate">{l s='Purchased On' mod='speedbox'}</th>
                        <th class="hnom">{l s='Ship to Name' mod='speedbox'}</th>
                        <th class="hpr">{l s='Point relais' mod='speedbox'}</th>
                        <th class="hpr">{l s='Speedbox status' mod='speedbox'}</th>
                        <th class="hpoids">{l s='Weight' mod='speedbox'}</th>
                        <th  class="hprix" align="right">{l s='G.T. (Base)' mod='speedbox'}</th>
                        <th class="hstatutcommande" align="center">{l s='Order status' mod='speedbox'}</th>
                    </tr>
                </thead><tbody id="fbody">

        {foreach from=$order_info item=order}
            <tr>
                <td><input class="checkbox" type="checkbox" name="checkbox[]"  value="{$order.id|escape:'htmlall':'UTF-8'}"></td><td class="id">{$order.id|escape:'htmlall':'UTF-8'}</td>
                {if $psVer >= 1.5}<td class="ref">{$order.reference|escape:'htmlall':'UTF-8'}</td>{/if}
                <td class="date">{$order.date|escape:'htmlall':'UTF-8'}</td>
                <td class="nom">{$order.nom|escape:'htmlall':'UTF-8'}</td>
                <td class="pr">{$order.address|escape:'quotes':'UTF-8'}</td>
                <td class="pr">  {if $order.speedbox_statut_colis == '-'}         
                <span style="color:#d51f4f;font-weight: bold;">{l s='Non traité' mod='speedbox'}</span>
        {else}{$order.speedbox_statut_colis|escape:'quotes':'UTF-8'}{/if}</td>
                <td class="poids"><input name="parcelweight[{$order.id|escape:'htmlall':'UTF-8'}]" type="text" value="{$order.poids|escape:'htmlall':'UTF-8'}" /> {$order.weightunit|escape:'htmlall':'UTF-8'}</td>
                <td class="prix" align="right">{$order.prix|escape:'htmlall':'UTF-8'}</td>
                <td class="statutcommande" align="center">{$order.statut|escape:'quotes':'UTF-8'}</td>
            </tr>
        {/foreach}
    </tbody></table>
    <p>
        <input type="submit" class="button" name="envoi" value="{l s='Injection of treatment requests' mod='speedbox'}" />
        <input type="submit" class="button" name="delivered" value="{l s='Update delivered orders' mod='speedbox'}" />
        <input type="submit" class="button" name="tracker" value="{l s='Parcels trace' mod='speedbox'}" />
        <input type="submit" class="button" name="cancel" value="{l s='Cancel of treatment requests' mod='speedbox'}" />
    </p>
    </form></fieldset>
{else}
    <div class="alert warn">{l s='There are no orders' mod='speedbox'}</div>
{/if}

