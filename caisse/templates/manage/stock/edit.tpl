{include file="admin/_head.tpl" title="Gestion stock" current="plugin_%s"|args:$plugin.id}

{form_errors}

<form method="post" action="{$self_url}" data-focus="1">
	<fieldset>
		<legend>Créer un événement de stock</legend>
		<dl>
			{input type="text" name="label" label="Libellé" required=true source=$event help="Par exemple 'Inventaire annuel' ou 'Réception commande n°53-44 du 21/12/2022'"}
			{input type="select" name="type" options=$types required=true label="Type d'événement" default=1}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
	</p>
</form>

{include file="admin/_foot.tpl"}