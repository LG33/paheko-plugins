<?php

namespace Garradin\Plugin\HelloAsso\Entities;

use Garradin\Entity;
use Garradin\DB;
use KD2\DB\EntityManager;
use Garradin\Entities\Services\Fee;
use Garradin\Entities\Services\Service;
use Garradin\Entities\Services\Service_User;
use Garradin\Entities\Users\DynamicField;
use Garradin\Entities\Users\Category;
use Garradin\Entities\Accounting\Account;
use Garradin\Users\Users;
use Garradin\Users\Session;

use Garradin\Plugin\HelloAsso\HelloAsso as HA;

class Chargeable extends Entity
{
	const TABLE = 'plugin_helloasso_chargeables';

	const SIMPLE_TYPE = 1;
	const DONATION_ITEM_TYPE = 2;
	const ONLY_ONE_ITEM_FORM_TYPE = 3;
	const FREE_TYPE = 4;
	const PAY_WHAT_YOU_WANT_TYPE = 5;
	const CHECKOUT_TYPE = 6;
	const TYPES = [
		self::SIMPLE_TYPE => 'Item/Option classique',
		self::DONATION_ITEM_TYPE => 'Don',
		self::ONLY_ONE_ITEM_FORM_TYPE => 'Don/Vente',
		self::FREE_TYPE => 'Gratuit',
		self::PAY_WHAT_YOU_WANT_TYPE => 'Prix libre',
		self::CHECKOUT_TYPE => 'Checkout'
	];
	const TYPE_FROM_FORM = [
		'Donation' => self::ONLY_ONE_ITEM_FORM_TYPE,
		'PaymentForm' => self::ONLY_ONE_ITEM_FORM_TYPE,
		'Payment' => self::SIMPLE_TYPE,
		'Membership' => self::SIMPLE_TYPE,
		'Checkout' => self::CHECKOUT_TYPE,
		'Shop' => self::SIMPLE_TYPE
	];

	const ITEM_TARGET_TYPE = 1;
	const OPTION_TARGET_TYPE = 2;
	const TARGET_TYPES = [
		self::ITEM_TARGET_TYPE => 'Item',
		self::OPTION_TARGET_TYPE => 'Option'
	];
	const TARGET_TYPE_FROM_CLASS = [
		Item::class => self::ITEM_TARGET_TYPE,
		Option::class => self::OPTION_TARGET_TYPE,
		Form::class => self::ITEM_TARGET_TYPE // Same behavior for Form and Item
	];

	protected int		$id;
	protected int		$id_form;
	protected ?int		$id_item; // Is the first item to generate the Chargeable, or null when handling ONLY_ONE_ITEM_FORM_TYPE forms/payments
	protected ?int		$id_credit_account = null;
	protected ?int		$id_debit_account = null;
	protected ?int		$id_category = null;
	protected ?int		$id_fee;
	protected ?int		$target_type;
	protected int		$type;
	protected string	$label;
	protected ?int		$amount; // When null, handles all amounts. See Chargeables::isMatchingAnyAmount() for null scenarii.
	protected ?int		$need_config; // Is zero until user fill the configuration form

	protected ?string	$_form_label = null;
	protected ?string	$_item_label = null;

	protected ?Fee		$_fee = null;
	protected ?Service	$_service = null;

	public function setForm_label(string $label): void
	{
		$this->_form_label = $label;
	}

	public function getForm_label(): string
	{
		return $this->_form_label;
	}

	public function setItem_label(?string $label): void
	{
		$this->_item_label = $label;
	}

	public function getItem_label(): ?string
	{
		return $this->_item_label;
	}

	public function getItemsIds(): array
	{
		return DB::getInstance()->getAssoc(sprintf('SELECT id, id FROM %s WHERE id_chargeable = :id_chargeable', Item::TABLE), (int)$this->id);
	}

	public function getOptionsIds(): array
	{
		return DB::getInstance()->getAssoc(sprintf('SELECT id, id FROM %s WHERE id_chargeable = :id_chargeable', Option::TABLE), (int)$this->id);
	}

	public function isMatchingAnyAmount(): bool
	{
		return (($this->type === Chargeable::ONLY_ONE_ITEM_FORM_TYPE) || ($this->type === Chargeable::DONATION_ITEM_TYPE) || ($this->type === Chargeable::PAY_WHAT_YOU_WANT_TYPE));
	}

	public function registerToService(int $id_user, \DateTime $date, bool $paid)
	{
		if (!$this->id_fee) {
			throw new \RuntimeException(sprintf('No fee associated to current chargeable #%d while trying to register user #%d to a service.', $this->id, $id_user));
		}
		if (!$this->service()) {
			throw new \RuntimeException(sprintf('No service associated to fee #%d for chargeable #%d.', $this->id_fee, $this->id));
		}
		if (!Users::idExists($id_user)) {
			throw new \RuntimeException(sprintf('Inexisting user #%d.', $id_user));
		}
		$source = [
			'id_user' => (int)$id_user,
			'id_service' => (int)$this->service()->id,
			'id_fee' => (int)$this->id_fee,
			'paid' => $paid,
			'amount' => $this->amount,
			//'expected_amount' => $this->amount,
			'date' => $date
		];
		return Service_User::createFromForm([ $id_user => HA::PROVIDER_LABEL . ' synchronization' ], $id_user, false, $source); // Second parameter should be HelloAsso user ID (to understand the plugin auto-registered the member)
	}

	public function fee(): ?Fee
	{
		if (!$this->id_fee) {
			return null;
		}
		if (null === $this->_fee) {
			$this->_fee = EntityManager::findOneById(Fee::class, (int)$this->id_fee);
		}
		return $this->_fee;
	}

	public function service(): ?Service
	{
		if (!$this->id_fee) {
			return null;
		}
		if (null === $this->_service) {
			$this->_service = $this->fee() ? EntityManager::findOneById(Service::class, (int)$this->_fee->id_service) : null;
		}
		return $this->_service;
	}

	public function selfCheck(): void
	{
		parent::selfCheck();
		$db = DB::getInstance();

		$this->assert(array_key_exists($this->type, Chargeable::TYPES), sprintf('Invalid Chargeable type: %s (Chargeable ID: #%d). Allowed types are: %s.', $this->type, $this->id ?? null, implode(', ', array_keys(Chargeable::TYPES))));
		$this->assert(in_array($this->need_config, [0, 1]), sprintf('Invalid Chargeable need_config option: %s (Chargeable ID: #%d). Allowed values are: %s.', $this->need_config, $this->id ?? null, implode(', ', [0, 1])));
		$this->assert(!$this->id_credit_account || $db->test(Account::TABLE, 'id = ? AND type = ?', (int)$this->id_credit_account, Account::TYPE_REVENUE), sprintf('Invalid credit account. Account type must be "%d".', Account::TYPE_REVENUE));
		$this->assert(!$this->id_debit_account || $db->test(Account::TABLE, 'id = ? AND type IN (?, ?, ?, ?)', (int)$this->id_debit_account, Account::TYPE_NONE, Account::TYPE_BANK, Account::TYPE_CASH, Account::TYPE_OUTSTANDING), sprintf('Invalid debit account. Allowed account types are: %s.', implode(', ', [Account::TYPE_NONE, Account::TYPE_BANK, Account::TYPE_CASH, Account::TYPE_OUTSTANDING])));
		$this->assert(!$this->id_category || $db->test(Category::TABLE, 'id = ? AND perm_config != ?', (int)$this->id_category, Session::ACCESS_ADMIN), 'Subscription as adminstrator is forbidden!');
	}
}
