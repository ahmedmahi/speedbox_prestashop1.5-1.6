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


<div id="speedbox_relais_filter" onclick="
var i = 1;
for (i=1; i<6; i++){
    document.getElementById('speedbox_relais_filter').style.display='none';
    document.getElementById('speedbox_relaydetail'+i).style.display='none';
}">
</div>

<table align="center" id="speedbox_relais_point_table" class="speedbox_relaistable" style="display:;">
{if isset($error)}
    <tr>
        <td colspan="5"><div class="speedbox_relais_error"> {$error|escape:'htmlall':'UTF-8'} </div></td>
    </tr>
{else}
    {if $speedbox_relais_status == 'error'}
        <tr>
            <td colspan="5" style="padding:0px;"><div class="speedbox_relais_error"><p>{l s='It seems that you haven\'t selected a Speedbox Pickup point, please pick one from this list' mod='speedbox'}</p></div></td>
        </tr>
    {/if}

    <tr>
        <td colspan="5" style="padding:0px;">
            <div id="speedbox_div_relais_header"><p>{l s='Please select your Speedbox Relais parcelshop among this list' mod='speedbox'}</p></div>
            {if $ssl == 0 || $ssl_everywhere == 1}
           
            {/if}
        </td>
    </tr>

    {if isset($speedbox_relais_empty)}
        <tr>
            <td colspan="5" style="padding:0px;"><div class="speedbox_relais_error"><p>{l s='There are no Pickup points near this address, please modify it.' mod='speedbox'}</p></div></td>
        </tr>
    {/if}

{foreach from=$speedbox_points_relais item=points name=speedboxRelaisLoop}

<tr class="speedbox_lignepr" onclick="document.getElementById('{$points.relay_id|escape:'htmlall':'UTF-8'}').checked=true">
        <td align="left" class="speedbox_logorelais"></td>
        <td align="left" class="speedbox_adressepr"><b>{$points.shop_name|escape:'htmlall':'UTF-8'}</b><br/>{$points.address|escape:'htmlall':'UTF-8'}<br/>{$points.postcode|escape:'htmlall':'UTF-8'} {$points.city|escape:'htmlall':'UTF-8'}<br/></td>
        <td align="right" class="speedbox_distancepr">{$points.distance|escape:'htmlall':'UTF-8'} km</td>
        <td align="center" class="speedbox_popinpr">
            <span onMouseOver="javascript:this.style.cursor='pointer';" onMouseOut="javascript:this.style.cursor='auto';"
                onClick="speedbox.popup_speedbox_view('speedbox_relaydetail{$smarty.foreach.speedboxRelaisLoop.index+1|escape:'htmlall':'UTF-8'}','map_canvas{$smarty.foreach.speedboxRelaisLoop.index+1|escape:'htmlall':'UTF-8'}',{$points.coord_lat|escape:'htmlall':'UTF-8'},{$points.coord_long|escape:'htmlall':'UTF-8'},'{if $ssl}{$base_dir_ssl|escape:'htmlall':'UTF-8'}{else}{$base_dir|escape:'htmlall':'UTF-8'}{/if}')">
                <u>{l s='More details' mod='speedbox'}</u>
            </span>
        </td>
        <td align="center" class="speedbox_radiopr">
        {if $speedbox_selectedrelay == $points.relay_id}
            <input type="radio" onclick="speedbox.write_point_relais_vlues('{$points.relay_id|escape:'htmlall':'UTF-8'}')" name="sb_relay_id" id="{$points.relay_id|escape:'htmlall':'UTF-8'}"  value='{$points|json_encode}' checked="checked">
        {else}
            <input type="radio" onclick="speedbox.write_point_relais_vlues('{$points.relay_id|escape:'htmlall':'UTF-8'}')"  name="sb_relay_id" id="{$points.relay_id|escape:'htmlall':'UTF-8'}" value='{$points|json_encode}' {if $smarty.foreach.speedboxRelaisLoop.first} checked="checked" {/if}>
        {/if}
        </td>
</tr>


<div id="speedbox_relaydetail{$smarty.foreach.speedboxRelaisLoop.index+1|escape:'htmlall':'UTF-8'}" class="speedbox_relaisbox" style="display:none;">

    <div class="speedbox_relaisboxclose" onclick="
        document.getElementById('speedbox_relaydetail{$smarty.foreach.speedboxRelaisLoop.index+1|escape:'htmlall':'UTF-8'}').style.display='none';
        document.getElementById('speedbox_relais_filter').style.display='none'">
        <img src="{if $ssl}{$base_dir_ssl|escape:'htmlall':'UTF-8'}{else}{$base_dir|escape:'htmlall':'UTF-8'}{/if}modules/speedbox/views/img/front/box-close.png"/>
    </div>

    <div class="speedbox_relaisboxcarto" id="map_canvas{$smarty.foreach.speedboxRelaisLoop.index+1|escape:'htmlall':'UTF-8'}"></div>

    <div id="relaisboxbottom" class="speedbox_relaisboxbottom">
        <div id="relaisboxadresse" class="speedbox_relaisboxadresse">
        <div class="speedboxboxadresseheader"><!--{l s='Your Speedbox Pickup point' mod='speedbox'}--></div><br/>
            <b>{$points.shop_name|escape:'htmlall':'UTF-8'}</b><br/>
            {$points.address|escape:'htmlall':'UTF-8'}<br/>
            {if isset($points.address2)}
                {$points.address2|escape:'htmlall':'UTF-8'}<br/>
            {/if}
            {$points.postcode|escape:'htmlall':'UTF-8'} {$points.city|escape:'htmlall':'UTF-8'}<br/>
            {if isset($points.local_hint)}
                <p>{l s='Landmark' mod='speedbox'} : {$points.local_hint|escape:'htmlall':'UTF-8'}</p>
            {/if}
        </div>

        <div class="speedbox_relaisboxhoraires">
            <div class="speedbox_relaisboxhorairesheader">{l s='Opening hours' mod='speedbox'}</div><br/>
                <p>
                    <span class="speedbox_relaisboxjour">{l s='Monday' mod='speedbox'} : </span>
                    {if !isset($points.monday)} {l s='Closed' mod='speedbox'}
                    {else}
                        {if $points.monday[0]}
                            {$points.monday[0]|escape:'htmlall':'UTF-8'}
                            {if isset($points.monday[1])}
                                & {$points.monday[1]|escape:'htmlall':'UTF-8'}
                            {/if}
                        {/if}
                    {/if}
                </p>

                <p>
                    <span class="speedbox_relaisboxjour">{l s='Tuesday' mod='speedbox'} : </span>
                    {if !isset($points.tuesday)} {l s='Closed' mod='speedbox'}
                    {else}
                        {if $points.tuesday[0]}
                            {$points.tuesday[0]|escape:'htmlall':'UTF-8'}
                            {if isset($points.tuesday[1])}
                                & {$points.tuesday[1]|escape:'htmlall':'UTF-8'}
                            {/if}
                        {/if}
                    {/if}
                </p>

                <p>
                    <span class="speedbox_relaisboxjour">{l s='Wednesday' mod='speedbox'} : </span>
                    {if !isset($points.wednesday)} {l s='Closed' mod='speedbox'}
                    {else}
                        {if $points.wednesday[0]}
                            {$points.wednesday[0]|escape:'htmlall':'UTF-8'}
                            {if isset($points.wednesday[1])}
                                & {$points.wednesday[1]|escape:'htmlall':'UTF-8'}
                            {/if}
                        {/if}
                    {/if}
                </p>

                <p>
                    <span class="speedbox_relaisboxjour">{l s='Thursday' mod='speedbox'} : </span>
                    {if !isset($points.thursday)} {l s='Closed' mod='speedbox'}
                    {else}
                        {if $points.thursday[0]}
                            {$points.thursday[0]|escape:'htmlall':'UTF-8'}
                            {if isset($points.thursday[1])}
                                & {$points.thursday[1]|escape:'htmlall':'UTF-8'}
                            {/if}
                        {/if}
                    {/if}
                </p>

                <p>
                    <span class="speedbox_relaisboxjour">{l s='Friday' mod='speedbox'} : </span>
                    {if !isset($points.friday)} {l s='Closed' mod='speedbox'}
                    {else}
                        {if $points.friday[0]}
                            {$points.friday[0]|escape:'htmlall':'UTF-8'}
                            {if isset($points.friday[1])}
                                & {$points.friday[1]|escape:'htmlall':'UTF-8'}
                            {/if}
                        {/if}
                    {/if}
                </p>

                <p>
                    <span class="speedbox_relaisboxjour">{l s='Saturday' mod='speedbox'} : </span>
                    {if !isset($points.saturday)} {l s='Closed' mod='speedbox'}
                    {else}
                        {if $points.saturday[0]}
                            {$points.saturday[0]|escape:'htmlall':'UTF-8'}
                            {if isset($points.saturday[1])}
                                & {$points.saturday[1]|escape:'htmlall':'UTF-8'}
                            {/if}
                        {/if}
                    {/if}
                </p>

                <p>
                    <span class="speedbox_relaisboxjour">{l s='Sunday' mod='speedbox'} : </span>
                    {if !isset($points.sunday)} {l s='Closed' mod='speedbox'}
                    {else}
                        {if $points.sunday[0]}
                            {$points.sunday[0]|escape:'htmlall':'UTF-8'}
                            {if isset($points.sunday[1])}
                                & {$points.sunday[1]|escape:'htmlall':'UTF-8'}
                            {/if}
                        {/if}
                    {/if}
                </p>
            </div>

            <div id="relaisboxinfos" class="speedbox_relaisboxinfos">
                <div class="speedbox_relaisboxinfosheader">{l s='More info' mod='speedbox'}</div><br/>
                <h5>{l s='Distance in km' mod='speedbox'} : </h5>{$points.distance|escape:'htmlall':'UTF-8'} km <br/>
                <h5>{l s='Speedbox Relais code' mod='speedbox'} : </h5>{$points.relay_id|escape:'htmlall':'UTF-8'} <br/>
                {if isset($points.closing_period[0])}
                    <h4><img src="{if $ssl}{$base_dir_ssl|escape:'htmlall':'UTF-8'}{else}{$base_dir|escape:'htmlall':'UTF-8'}{/if}modules/speedbox/views/img/front/warning.png"/> {l s='Closing period' mod='speedbox'} : </h4>{$points.closing_period[0]|escape:'htmlall':'UTF-8'} <br/>
                {/if}
                {if isset($points.closing_period[1])}
                    <h4></h4>{$points.closing_period[1]|escape:'htmlall':'UTF-8'} <br/>
                {/if}
                {if isset($points.closing_period[2])}
                    <h4></h4>{$points.closing_period[2]|escape:'htmlall':'UTF-8'} <br/>
                {/if}
            </div>
        </div>
    </div>
{/foreach}
    <tr>
        <td colspan="5" style="padding:0px;">
              <div class="speedbox_relais_selected">
    <label>{l s='Point relais selectioné' mod='speedbox'}</label>
<span id="speedbox_relais_selected"></span><div id="speedbox_please_wait_relais" class="speedbox_wait" style="display:none;"></div>
</div>
        </td>
    </tr>
{/if}

</table>
 