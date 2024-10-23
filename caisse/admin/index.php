<?php

namespace Paheko;
use Paheko\Plugin\Caisse\Locations;
use Paheko\Plugin\Caisse\Sessions;

if ($plugin->needUpgrade()) {
	$plugin->upgrade();
}

require __DIR__ . '/_inc.php';

$has_locations = Locations::count() > 0;
$list = Sessions::list($has_locations);
$list->loadFromQueryString();

$tpl->assign('current_pos_session', Sessions::getCurrentId());
$tpl->assign(compact('list', 'has_locations'));
$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
