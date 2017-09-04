<?php

namespace Garradin;

require_once __DIR__ . '/_inc.php';

if (f('save') && $form->check('ajout_velo'))
{
    $data = [
        'etiquette'     =>  (int) f('etiquette'),
        'bicycode'      =>  f('bicycode'),
        'prix'          =>  (double) f('prix'),
        'source'        =>  f('source'),
        'source_details'=>  f('source_details'),
        'type'          =>  f('type'),
        'genre'         =>  f('genre'),
        'roues'         =>  f('roues'),
        'couleur'       =>  f('couleur'),
        'modele'        =>  f('modele'),
        'date_entree'   =>  f('date_entree'),
        'etat_entree'   =>  f('etat_entree'),
        'date_sortie'   =>  f('date_sortie'),
        'raison_sortie' =>  f('raison_sortie'),
        'details_sortie'=>  f('details_sortie'),
        'notes'         =>  f('notes'),
    ];

    try {
        $velos->checkData($data);
        $id = $velos->addVelo($data);
        utils::redirect(utils::plugin_url(['query' => 'id=' . $id]));
    }
    catch (UserException $e)
    {
        $form->addError($e->getMessage());
    }
}

$tpl->assign('sources', $velos->listSources());
$tpl->assign('types', $velos->listTypes());
$tpl->assign('genres', $velos->listGenres());
$tpl->assign('roues', $velos->listTailles());
$tpl->assign('raisons_sortie', $velos->listRaisonsSortie());

$tpl->assign('libre', $velos->getEtiquetteLibre());

$tpl->assign('now_ymd', date('Y-m-d'));

$tpl->display(PLUGIN_ROOT . '/templates/ajout.tpl');
