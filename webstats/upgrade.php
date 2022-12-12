<?php

use Garradin\Plugin\Webstats\Stats;

$plugin->unregisterSignal('http.request.skeleton.before');
$plugin->unregisterSignal('http.request.skeleton.after');

$plugin->registerSignal('usertemplate.appendscript', 'Garradin\Plugin\Webstats\Stats::appendScript');
$plugin->registerSignal('home.button', [Stats::class, 'homeButton']);
