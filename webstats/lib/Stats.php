<?php

namespace Garradin\Plugin\Webstats;

use Garradin\UserTemplate\CommonFunctions;
use Garradin\DB;
use Garradin\Plugin;
use Garradin\Users\Session;

use KD2\Graphics\SVG\Plot;
use KD2\Graphics\SVG\Plot_Data;

class Stats
{
	static public function homeButton(array $params, array &$buttons): void
	{
		if (!Session::getInstance()->canAccess(Session::SECTION_WEB, Session::ACCESS_READ)) {
			return;
		}

		$buttons['taima'] = CommonFunctions::linkbutton([
			'label' => 'Statistiques web',
			'icon' => Plugin::getURL('webstats', 'stats.svg'),
			'href' => Plugin::getURL('webstats'),
		]);
	}

	static public function appendScript(array $params, array &$scripts): void
	{
		$scripts[] = Plugin::getPublicURL('webstats', 'stats.js');
	}

	static public function store(\stdClass $data): void
	{
		$db = DB::getInstance();

		$sql = sprintf('BEGIN; INSERT INTO plugin_webstats_stats (year, month, day, mobile_visits) VALUES (%d, %d, %d, %d)
			ON CONFLICT (year, month, day) DO UPDATE SET hits = hits + 1, ',
			(int) date('Y'),
			(int) date('m'),
			(int) date('d'),
			!empty($data->is_mobile) ? 1 : 0
		);

		if (!empty($data->is_new_visitor) && !empty($data->is_mobile)) {
			$sql .= 'mobile_visits = mobile_visits + 1, ';
		}

		if (!empty($data->is_new_visitor)) {
			$sql .= 'visits = visits + 1, ';
		}

		$sql = rtrim($sql, ', ');
		$sql .= ';';

		$uri = $params['uri'] ?? '';
		$uri = strtok($uri, '?');
		$uri = trim($uri, '/');

		$sql .= sprintf('INSERT INTO plugin_webstats_hits (uri) VALUES (%s) ON CONFLICT (uri) DO UPDATE SET hits = hits + 1; END',
			$db->quote($uri));

		$db->exec($sql);
	}

	static public function getStats()
	{
		$db = DB::getInstance();
		return $db->get('SELECT
			printf(\'%04d-%02d-01\', year, month) AS date,
			SUM(visits) AS visits,
			SUM(mobile_visits) AS mobile_visits,
			SUM(hits) AS hits
			FROM plugin_webstats_stats
			GROUP BY year, month
			ORDER BY year DESC, month DESC;');
	}

	static public function getHits()
	{
		$db = DB::getInstance();
		return $db->get('SELECT
			uri,
			hits
			FROM plugin_webstats_hits
			ORDER BY hits DESC LIMIT 50;');
	}

	static public function graph()
	{
		$plot = new Plot(900, 300);

		$data = [];
		$stats = self::getStats();
		$stats = array_reverse($stats);

		foreach ($stats as $month) {
			foreach ((array)$month as $key => $value) {
				if (!isset($data[$key])) {
					$data[$key] = [];
				}

				$data[$key][] = $value;
			}
		}

		$graph = new Plot_Data($data['hits']);
		$graph->title = 'Pages vues';
		$graph->color = 'Crimson';
		$graph->width = 3;
		$plot->add($graph);

		$graph = new Plot_Data($data['visits']);
		$graph->title = 'Visites';
		$graph->color = 'CadetBlue';
		$graph->width = 3;
		$plot->add($graph);

		$graph = new Plot_Data($data['mobile_visits']);
		$graph->title = 'Mobiles';
		$graph->color = 'Salmon';
		$graph->width = 3;
		$plot->add($graph);

		$data['date'] = array_map(fn($a) => substr($a, 5, 2) . '/' . substr($a, 2, 2), $data['date']);
		$plot->setLabels($data['date']);

		return $plot->output();

	}
}
