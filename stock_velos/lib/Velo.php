<?php

namespace Paheko\Plugin\Stock_Velos;

use Paheko\DB;
use Paheko\Entity;
use Paheko\UserException;
use Paheko\Utils;
use Paheko\Users\DynamicFields;
use Paheko\Users\Users;
use KD2\DB\Date;

class Velo extends Entity
{
	const TABLE = 'plugin_stock_velos';

	protected ?int $id = null;
	protected ?int $etiquette = null;
	protected ?string $bicycode = null;

	protected string $source = '';
	protected ?string $source_details = null;

	protected ?string $type = null;
	protected ?string $roues = null;
	protected ?string $taille = null;
	protected ?string $genre = null;
	protected ?string $couleur = null;
	protected ?string $modele = null;
	protected ?float $prix = null;

	protected Date $date_entree;
	protected ?string $etat_entree = null;

	protected ?Date $date_sortie = null;
	protected ?string $raison_sortie = null;
	protected ?string $details_sortie = null;

	protected ?string $notes = null;
	protected ?int $poids = null;

	public function selfCheck(): void
	{
		$db = DB::getInstance();

		$this->assert(null === $this->date_sortie || $this->date_sortie >= $this->date_entree, 'La date de sortie ne peut pas être antérieure à la date d\'entrée.');

		if ($this->source == 'Rachat') {
			$this->assert(!empty($this->source_details), "Pour le rachat il est obligatoire de fournir un numéro unique de vélo.");

			$velo = $db->firstColumn('SELECT raison_sortie FROM plugin_stock_velos WHERE id = '.(int)$this->source_details.';');

			if (!$velo || $velo !== 'Vendu') {
				throw new UserException("Le vélo indiqué pour le rachat n'existe pas ou n'a pas été vendu.");
			}
		}

		if (!$this->exists()) {
			$this->assert(!$db->test('plugin_stock_velos', 'date_sortie IS NULL AND etiquette = ?', (int)$this->etiquette), "Ce numéro d'étiquette est déjà attribué à un autre vélo en stock.");
		}
		else {
			$this->assert(!$db->test('plugin_stock_velos', 'date_sortie IS NULL AND etiquette = ? AND id != ?', (int)$this->etiquette, $this->id), "Ce numéro d'étiquette est déjà attribué à un autre vélo en stock.");
		}

		if (!empty($this->source_details) && is_numeric($this->source_details))
		{
			$field = DynamicFields::getNumberField();
			$this->assert($db->test('users', $field . ' = ?', (int)$this->source_details), "Le numéro de membre indiqué comme provenance n'existe pas.");
		}
	}

	public function importForm(array $source = null)
	{
		$source ??= $_POST;

		if (isset($source['poids'])) {
			$source['poids'] = Utils::weightToInteger($source['poids']);
		}

		parent::importForm($source);
	}

	public function sortie(string $raison, string $details, $date = null)
	{
		$data = array(
			'raison_sortie' => (string) $raison,
			'details_sortie' => (string) $details,
			'date_sortie' => is_null($date)
				? gmdate('Y-m-d')
				: (int) $date,
		);

		DB::getInstance()->update('plugin_stock_velos', $data, 'id = '.(int)$id);
		return true;
	}

	public function buyback(int $etiquette, string $etat, float $prix): Velo
	{
		$new = new Velo;
		$new->import([
			'etiquette'     =>  $etiquette,
			'source'        =>  'Rachat',
			'source_details'=>  $this->id,
			'type'          =>  $this->type,
			'genre'         =>  $this->genre,
			'roues'         =>  $this->roues,
			'taille'        =>  $this->taille,
			'couleur'       =>  $this->couleur,
			'modele'        =>  $this->modele,
			'date_entree'   =>  date('Y-m-d'),
			'etat_entree'   =>  $etat,
			'notes'         =>  'Racheté à l\'adhérent pour '.$prix.' €',
		]);
		$new->save();
		return $new;
	}

	public function sell(?string $num_adherent, string $prix): void
	{
		$this->import([
			'raison_sortie' => 'Vendu',
			'details_sortie' => (int) $num_adherent ?: null,
			'date_sortie' => date('d/m/Y'),
			'prix' => (float) $prix,
		]);

		$this->save();
	}

	public function membre_source(): ?string
	{
		if ($this->source != 'Don' || !is_numeric($this->source_details)) {
			return null;
		}

		return Users::getNameFromNumber((int)$this->source_details);
	}

	public function membre_sortie(): ?string
	{
		if ($this->raison_sortie != 'Vendu' || !is_numeric($this->details_sortie)) {
			return null;
		}

		return Users::getNameFromNumber((int)$this->details_sortie);
	}

	public function membre_rachat(): ?string
	{
		if ($this->source != 'Rachat' || !is_numeric($this->source_details)) {
			return null;
		}

		$n = DB::getInstance()->firstColumn('SELECT details_sortie FROM plugin_stock_velos WHERE id = ?;', $this->source_details);
		return Users::getNameFromNumber((int)$n);
	}

	public function get_buyback(): ?int
	{
		return DB::getInstance()->firstColumn('SELECT id FROM plugin_stock_velos WHERE source_details = ? AND source = \'Rachat\';', $this->id) ?: null;
	}
}
