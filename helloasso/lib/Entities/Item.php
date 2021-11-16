<?php

namespace Garradin\Plugin\HelloAsso\Entities;

use Garradin\DB;
use Garradin\Entity;
use Garradin\ValidationException;

use DateTime;

class Item extends Entity
{
	const TABLE = 'plugin_helloasso_items';

	protected int $id;
	protected int $id_order;
	protected int $id_form;
	protected ?int $id_user;
	protected string $type;
	protected string $state;
	protected string $label;
	protected string $person;
	protected int $amount;
	protected ?string $custom_fields;
	protected string $raw_data;

	const TYPES = [
		'Donation'        => 'Don',
		'Payment'         => 'Paiement',
		'Registration'    => 'Inscription',
		'Membership'      => 'Adhésion',
		'MonthlyDonation' => 'Don mensuel',
		'MonthlyPayment'  => 'Paiement mensuel',
		'OfflineDonation' => 'Don hors-ligne',
		'Contribution'    => 'Contribution',
		'Bonus'           => 'Bonus',
	];

	const STATES = [
		'Waiting'    => 'En attente',
		'Processed'  => 'Traité',
		'Registered' => 'Enregistré',
		'Deleted'    => 'Supprimé',
		'Refunded'   => 'Remboursé',
		'Unknown'    => 'Inconnu',
		'Canceled'   => 'Annulé',
		'Contested'  => 'Contesté',
	];
}
