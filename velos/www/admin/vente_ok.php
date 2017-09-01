<?php

namespace Garradin;

require_once __DIR__ . '/_inc.php';

if (!qg('id'))
    throw new UserException('Impossible de trouver le vélo indiqué');

$id = (int) qg('id');

$velo = $velos->getVelo($id);

if (!$velo)
    throw new UserException('Ce vélo n\'existe pas !');

if (empty($velo->date_sortie) || $velo->raison_sortie != 'Vendu')
    throw new UserException('Ce vélo n\'a pas été vendu');

$tpl->assign('velo', $velo);
$tpl->assign('adherent', $velos->getMembre($velo->details_sortie));
$tpl->assign('etat', qg('etat'));

$tpl->display(PLUGIN_ROOT . '/templates/vente_ok.tpl');
