{*
* 2007-2025 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2025 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{* === Template de administración del módulo === *}
<div class="panel">
  <h3>
    {l s='Lista de tarjetas' mod='ho_why'}
  </h3>

  {if $cards|@count > 0}
    {foreach from=$cards item=card}
      <div class="card-item" style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
        <h2 style="margin: 5px;"><strong>{$card.name}</strong></h2>
        <h3 style="margin: 5px;">{$card.description}</h3>

        {if $card.image}
          <img src="{$module_dir}views/img/{$card.image}"
               style="max-width:100px; display:block; margin-bottom:5px;"
               alt="Imagen">
        {/if}

        <div class="btn-group-lg">
          {* Botón Editar *}
          <a href="{$current}&edit={$card.id}&token={$token}" class="btn btn-default btn-sm"
            class="btn btn-sm">
            <i class="icon-pencil"></i> {l s='Editar' mod='ho_why'}
          </a>

          {* Botón Borrar *}
          <a href="{$current}&delete={$card.id}&token={$token}"
             class="btn btn-danger btn-sm"
             onclick="return confirm('{l s='¿Seguro que quieres eliminar esta tarjeta?' mod='ho_why'}');">
            <i class="icon-trash"></i> {l s='Eliminar' mod='ho_why'}
          </a>
        </div>
      </div>
    {/foreach}
  {else}
    <p>{l s='No hay tarjetas creadas todavía.' mod='ho_why'}</p>
  {/if}
</div>


