{include file="_head.tpl" title="Connexion à HelloAsso"}

{include file="./_menu.tpl" current="config_client"}

{if isset($_GET['ok'])}
<p class="confirm block">
	{if $_GET.ok === 'connection'}
		Connexion à l'API HelloAsso effectuée !
	{elseif $_GET.ok === 'config'}
		Configuration mise à jour avec succès.
	{/if}
</p>
{/if}

{form_errors}

<div class="help block">
	<p>Cette extension permet d'importer les données des personnes ayant effectué un règlement à votre association sur la plateforme HelloAsso : création de membre, inscription à une cotisation ou activité, et enregistrement en comptabilité.</p>
	<p>Cette extension est accessible aux membres qui ont le droit de modifier les membres.</p>
</div>

<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Connexion à HelloAsso</legend>
		<p class="help">
			Pour renseigner ces informations, rendez-vous dans <a href="https://admin.helloasso.com/" target="_blank">votre administration HelloAsso</a> et allez dans <em>«&nbsp;Mon compte&nbsp;»</em>, puis <em>«&nbsp;Intégrations et API&nbsp;»</em> et recopiez ici les valeurs indiquées.
		</p>
		<dl>
			{input type="text" name="client_id" default=$client_id label="ID (Mon clientId)" required=true}
			{input type="password" name="client_secret" value="1" default=$secret label="Secret (Mon clientSecret)" required=false} {* If not provided only the rest of the configuration is updated *}
		</dl>
	</fieldset>

	<fieldset>
		<legend>Comptabilité <b title="Champ obligatoire">(obligatoire)</b></legend>
		<dl>
			<dd class="radio-btn">
				{input type="radio" name="accounting" label=null value="1" source=$plugin->config required=true}
				<label for="f_accounting_1">
					<div>
						<h3>Enregistrer les entrées dans la comptabilité</h3>
						<p class="help">Génère automatiquement les saisies comptables correspondant aux paiements HelloAsso.</p>
					</div>
				</label>
			</dd>
			<dd class="radio-btn">
				{input type="radio" name="accounting" label=null value="0" source=$plugin->config required=true}
				<label for="f_accounting_0">
					<div>
						<h3>Uniquement récupérer les infos HelloAsso</h3>
						<p class="help">Permet de visualiser les formulaires, commandes et paiements HelloAsso sans impacter votre comptabilité.</p>
					</div>
				</label>
			</dd>
		</dl>
		<p class="help block">Cette option n'est pas définitive et pourra être changée plus tard.</p>
		<dl>
			{input type="list" target="!acc/charts/accounts/selector.php?targets=%s&chart=%d"|args:'6':$chart_id name="default_credit" label="Type de recette par défaut" required=false default=$default_credit_account help="Sera proposé par défaut pour vous faire gagner du temps lors de vos saisies."}
			{input type="list" target="!acc/charts/accounts/selector.php?targets=%s&chart=%d"|args:'1:2:3':$chart_id name="default_debit" label="Compte d'encaissement par défaut" required=false default=$default_debit_account}
		</dl>
	</fieldset>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
	</p>
</form>

{include file="_foot.tpl"}
