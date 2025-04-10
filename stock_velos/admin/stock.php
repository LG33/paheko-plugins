<?php

namespace Paheko;

require_once __DIR__ . '/_inc.php';

$stock = $velos->listStock();
$en_vente = $a_demonter = $autres = [];

foreach ($stock as $row) {
	if ($row->prix == $velos::A_DEMONTER) {
		$a_demonter[] = $row;
	}
	elseif ($row->prix > 0) {
		$en_vente[] = $row;
	}
	elseif ($row->prix !== false) {
		$autres[] = $row;
	}
}

$valeur = $velos->getValeurStock();

$tpl->assign('valeur_vente', $valeur);
$tpl->assign('prix_moyen', ($en_vente && $valeur) ? round($valeur / count($en_vente)) : 0);
$tpl->assign('en_vente', $en_vente);
$tpl->assign('a_demonter', $a_demonter);
$tpl->assign('autres', $autres);

$tpl->assign('total', $velos->countVelosStock());

$tpl->display(PLUGIN_ROOT . '/templates/stock.tpl');
