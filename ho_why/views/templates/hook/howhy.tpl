<h1 id="cabecera-razones">&iquest;Porque comprar en Hardware Online&quest;</h1>

<div id="lista-motivos">
  {foreach from=$reasons item=card}
    <div class="item">
      <div class="front">
        {if $card.image}
          <img src="{$module_dir}views/img/{$card.image}" alt="{$card.name}">
        {/if}
      </div>
      <div class="back">
        <p>{$card.description}</p>
      </div>
    </div>
  {/foreach}
</div>


