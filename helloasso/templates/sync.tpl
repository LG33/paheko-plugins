{include file="_head.tpl" title="HelloAsso" current="plugin_%s"|args:$plugin.id}

{include file="%s/templates/_menu.tpl"|args:$plugin_root current="home"}

{if $last_sync}
	<p class="help">
		La dernière synchronisation date du {$last_sync|date}.
	</p>
{else}
	<p class="alert block">Cliquer sur le bouton ci-dessous pour récupérer les données depuis HelloAsso.</p>
{/if}

{if !$last_sync && $last_sync > (new \DateTime('1 hour ago'))}
	<p class="alert block">Il n'est pas possible d'effectuer plus d'une synchronisation manuelle par heure.</p>
{else}
	<form method="post" action="{$self_url}">
		<p class="submit">
			{csrf_field key=$csrf_key}
			{button type="submit" name="sync" value=1 label="Synchroniser les données" shape="right" class="main"}
		</p>
	</form>
{/if}

{include file="_foot.tpl"}
