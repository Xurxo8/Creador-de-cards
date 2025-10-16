<h1 id="cabecera-razones">&iquest;Porque comprar en Hardware Online&quest;</h1>
<div id="lista-motivos">
  {foreach $reasons as $reason}
    <div class="item">
      <div class="front">
        <img src="{$module_dir}views/img/{$reason.icon}" alt="{$reason.text|escape:'html':'UTF-8'}">
      </div>
      <div class="back">
        <p>{$reason.text}</p>
      </div>
    </div>
  {/foreach}
</div>

