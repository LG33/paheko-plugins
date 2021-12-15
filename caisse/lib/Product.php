<?php

namespace Garradin\Plugin\Caisse;

use Garradin\DB;
use KD2\DB\EntityManager as EM;

class Product
{
	static public function listByCategory(bool $only_with_payment = true): array
	{
		$db = DB::getInstance();
		$categories = self::listCategoriesAssoc();

		$join = $only_with_payment ? 'INNER JOIN @PREFIX_products_methods m ON m.product = p.id' : '';

		// Don't select products that don't have any payment method linked: you wouldn't be able to pay for them
		$products = $db->get(POS::sql(sprintf('SELECT * FROM @PREFIX_products p %s
			GROUP BY p.id ORDER BY category, name COLLATE NOCASE;', $join)));

		$list = [];

		foreach ($products as $product) {
			$cat = $categories[$product->category];

			if (!array_key_exists($cat, $list)) {
				$list[$cat] = [];
			}

			$list[$cat][] = $product;
		}

		return $list;
	}

	static public function get(int $id): ?Entities\Product
	{
		return EM::findOneById(Entities\Product::class, $id);
	}

	static public function new(): Entities\Product
	{
		return new Entities\Product;
	}

	static public function listCategoriesAssoc(): array
	{
		$db = DB::getInstance();
		return $db->getAssoc(POS::sql('SELECT id, name FROM @PREFIX_categories ORDER BY name;'));
	}

	static public function getStatsPerMonth(int $year): array
	{
		$sql = 'SELECT strftime(\'%m\', i.added) AS month, i.added AS date, i.category_name, SUM(i.qty * i.price) AS sum, SUM(i.qty) AS count
			FROM @PREFIX_tabs_items i
			WHERE strftime(\'%Y\', i.added) = ?
			GROUP BY strftime(\'%m\', i.added), i.category_name
			ORDER BY month, i.category_name;';
		$sql = POS::sql($sql);

		return DB::getInstance()->get($sql, (string) $year);
	}

	static public function graphStatsPerMonth(int $year): string
	{
		$sql = 'SELECT strftime(\'%m\', i.added) AS month, i.category_name, SUM(i.qty * i.price) / 100
			FROM @PREFIX_tabs_items i
			WHERE strftime(\'%Y\', i.added) = ?
			GROUP BY strftime(\'%m\', i.added), i.category_name
			ORDER BY month, i.category_name;';
		$sql = POS::sql($sql);

		$data = DB::getInstance()->getAssocMulti($sql, (string) $year);
		return POS::barGraph(null, $data);
	}

	static public function graphStatsQtyPerMonth(int $year): string
	{
		$sql = 'SELECT strftime(\'%m\', i.added) AS month, i.category_name, SUM(i.qty)
			FROM @PREFIX_tabs_items i
			WHERE strftime(\'%Y\', i.added) = ?
			GROUP BY strftime(\'%m\', i.added), i.category_name
			ORDER BY month, i.category_name;';
		$sql = POS::sql($sql);

		$data = DB::getInstance()->getAssocMulti($sql, (string) $year);
		return POS::barGraph(null, $data);
	}
}
