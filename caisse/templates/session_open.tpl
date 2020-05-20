{include file="admin/_head.tpl" title="Ouverture de caisse" current="plugin_%s"|args:$plugin.id}

<form method="post" action="">
<fieldset>
	<legend>Ouvrir la caisse</legend>
	<dl>
		<dt><label for="f_amount">Solde de la caisse à l'ouverture</label></dt>
		<dd><input type="text" pattern="\d+(,\d+)?" name="amount" id="f_amount" size="5" placeholder="42,32" />&nbsp;€</dd>
	</dl>
	<p class="submit">
		<input type="submit" name="open" value="Enregistrer le paiement" />
	</p>
</fieldset>
</form>

{include file="admin/_foot.tpl"}