<?php

namespace Garradin;

$db->import(__DIR__ . '/schema.sql');

$plugin->registerSignal('usertemplate.appendscript', 'Garradin\Plugin\Webstats\Stats::appendScript');

$plugin->registerSignal('home.button', [Stats::class, 'homeButton']);
