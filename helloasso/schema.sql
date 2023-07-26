-- Cache list of forms
CREATE TABLE IF NOT EXISTS plugin_helloasso_forms (
	id INTEGER PRIMARY KEY,
	id_chargeable INTEGER NULL REFERENCES plugin_helloasso_chargeables(id) ON DELETE SET NULL,

	org_name TEXT NOT NULL,
	org_slug TEXT NOT NULL,

	label TEXT NOT NULL,
	slug TEXT NOT NULL,
	type TEXT NOT NULL,
	state TEXT NOT NULL,
	need_config UNSIGNED INTEGER NOT NULL DEFAULT 0
);

CREATE UNIQUE INDEX IF NOT EXISTS plugin_helloasso_forms_key ON plugin_helloasso_forms(org_slug, slug);

CREATE TABLE IF NOT EXISTS plugin_helloasso_form_custom_fields (
	id INTEGER PRIMARY KEY NOT NULL,
	id_form INTEGER NOT NULL REFERENCES plugin_helloasso_forms(id) ON DELETE CASCADE,
	id_dynamic_field INTEGER NULL REFERENCES config_users_fields(id),
	name TEXT NOT NULL
);

CREATE UNIQUE INDEX IF NOT EXISTS plugin_helloasso_form_custom_fields_unique ON plugin_helloasso_form_custom_fields(id_form, id_dynamic_field);

CREATE TABLE IF NOT EXISTS plugin_helloasso_orders (
	id INTEGER PRIMARY KEY NOT NULL,
	id_form INTEGER NOT NULL REFERENCES plugin_helloasso_forms(id) ON DELETE CASCADE,
	id_payer INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
	date TEXT NOT NULL,
	payer_name TEXT NULL,
	amount INTEGER NOT NULL,
	status TEXT NOT NULL,
	raw_data TEXT NOT NULL
);

CREATE INDEX IF NOT EXISTS plugin_helloasso_orders_form ON plugin_helloasso_orders(id_form);
CREATE INDEX IF NOT EXISTS plugin_helloasso_orders_user ON plugin_helloasso_orders(id_payer);

CREATE TABLE IF NOT EXISTS plugin_helloasso_items (
	id INTEGER PRIMARY KEY NOT NULL,
	id_form INTEGER NOT NULL REFERENCES plugin_helloasso_forms(id) ON DELETE CASCADE,
	id_order INTEGER NOT NULL REFERENCES plugin_helloasso_orders(id) ON DELETE CASCADE,
	id_user INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
	id_transaction INTEGER NULL REFERENCES acc_transactions(id) ON DELETE SET NULL,
	id_chargeable INTEGER NULL REFERENCES plugin_helloasso_chargeables(id) ON DELETE SET NULL,
	type TEXT NOT NULL,
	state TEXT NOT NULL,
	price_type UNSIGNED INT NOT NULL,
	person TEXT NULL,
	label TEXT NOT NULL,
	amount INTEGER NOT NULL,
	has_options INTEGER NOT NULL,
	raw_data TEXT NOT NULL,
	custom_fields TEXT NULL
);

CREATE INDEX IF NOT EXISTS plugin_helloasso_items_form ON plugin_helloasso_items(id_form);
CREATE INDEX IF NOT EXISTS plugin_helloasso_items_order ON plugin_helloasso_items(id_order);
CREATE INDEX IF NOT EXISTS plugin_helloasso_items_user ON plugin_helloasso_items(id_user);
CREATE INDEX IF NOT EXISTS plugin_helloasso_items_transaction ON plugin_helloasso_items(id_transaction);
CREATE INDEX IF NOT EXISTS plugin_helloasso_items_chargeable ON plugin_helloasso_items(id_chargeable);

CREATE TABLE IF NOT EXISTS plugin_helloasso_item_options (
	id INTEGER PRIMARY KEY NOT NULL,
	id_item INTEGER NOT NULL REFERENCES plugin_helloasso_items(id) ON DELETE CASCADE,
	id_order INTEGER NOT NULL REFERENCES plugin_helloasso_orders(id) ON DELETE CASCADE,
	id_user INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
	id_transaction INTEGER NULL REFERENCES acc_transactions(id) ON DELETE SET NULL,
	id_chargeable INTEGER NULL REFERENCES plugin_helloasso_chargeables(id) ON DELETE SET NULL,
	price_type UNSIGNED INT NOT NULL,
	label TEXT NOT NULL,
	amount INTEGER NOT NULL,
	raw_data TEXT NOT NULL,
	custom_fields TEXT NULL
);

CREATE INDEX IF NOT EXISTS plugin_helloasso_item_options_item ON plugin_helloasso_item_options(id_item);
CREATE INDEX IF NOT EXISTS plugin_helloasso_item_options_order ON plugin_helloasso_item_options(id_order);
CREATE INDEX IF NOT EXISTS plugin_helloasso_item_options_user ON plugin_helloasso_item_options(id_user);
CREATE INDEX IF NOT EXISTS plugin_helloasso_item_options_transaction ON plugin_helloasso_item_options(id_transaction);
CREATE INDEX IF NOT EXISTS plugin_helloasso_item_options_chargeable ON plugin_helloasso_item_options(id_chargeable);

CREATE TABLE IF NOT EXISTS plugin_helloasso_chargeables (
	id INTEGER PRIMARY KEY NOT NULL,
	id_form INTEGER NOT NULL REFERENCES plugin_helloasso_forms(id) ON DELETE CASCADE,
	id_item INTEGER NULL REFERENCES plugin_helloasso_items(id) ON DELETE SET NULL,
	id_credit_account INTEGER NULL REFERENCES acc_accounts (id) ON DELETE SET NULL,
	id_debit_account INTEGER NULL REFERENCES acc_accounts (id) ON DELETE SET NULL,
	id_category INTEGER NULL REFERENCES users_categories (id) ON DELETE SET NULL,
	id_fee INTEGER NULL DEFAULT NULL REFERENCES services_fees (id) ON DELETE SET NULL,
	target_type UNSIGNED INTEGER NOT NULL,
	type UNSIGNED INTEGER NOT NULL,
	label TEXT NOT NULL,
	amount INTEGER NULL,
	need_config UNSIGNED INTEGER NOT NULL DEFAULT 1
);

CREATE INDEX IF NOT EXISTS plugin_helloasso_chargeables_get ON plugin_helloasso_chargeables (id_form, target_type, type, label, amount);

CREATE UNIQUE INDEX IF NOT EXISTS plugin_helloasso_chargeables_key ON plugin_helloasso_chargeables(id_form, id_item, type, label, amount);

CREATE UNIQUE INDEX IF NOT EXISTS plugin_helloasso_chargeables_unique ON plugin_helloasso_chargeables(id_form, type, label, amount);

CREATE INDEX IF NOT EXISTS plugin_helloasso_chargeables_form ON plugin_helloasso_chargeables(id_form);
CREATE INDEX IF NOT EXISTS plugin_helloasso_chargeables_item ON plugin_helloasso_chargeables(id_item);
CREATE INDEX IF NOT EXISTS plugin_helloasso_chargeables_credit_account ON plugin_helloasso_chargeables(id_credit_account);
CREATE INDEX IF NOT EXISTS plugin_helloasso_chargeables_debit_account ON plugin_helloasso_chargeables(id_debit_account);
CREATE INDEX IF NOT EXISTS plugin_helloasso_chargeables_category ON plugin_helloasso_chargeables(id_category);
CREATE INDEX IF NOT EXISTS plugin_helloasso_chargeables_fee ON plugin_helloasso_chargeables(id_fee);

-----------------------------------
---- Indexes for Paheko core tables

CREATE INDEX IF NOT EXISTS plugin_helloasso_payment_form ON payments (json_extract(extra_data, '$.id_form'));
CREATE INDEX IF NOT EXISTS plugin_helloasso_payment_form_date ON payments (json_extract(extra_data, '$.id_form'), 'date');

-- Listing payments (one index by view (e.g. ordered by status))
CREATE INDEX IF NOT EXISTS plugin_helloasso_payment_form_reference ON payments (json_extract(extra_data, '$.id_form'), 'reference');
CREATE INDEX IF NOT EXISTS plugin_helloasso_payment_form_amount ON payments (json_extract(extra_data, '$.id_form'), 'amount');
CREATE INDEX IF NOT EXISTS plugin_helloasso_payment_form_payer_name ON payments (json_extract(extra_data, '$.id_form'), 'payer_name');
CREATE INDEX IF NOT EXISTS plugin_helloasso_payment_form_status ON payments (json_extract(extra_data, '$.id_form'), 'status');
CREATE INDEX IF NOT EXISTS plugin_helloasso_payment_form_label ON payments (json_extract(extra_data, '$.id_form'), 'label');
CREATE INDEX IF NOT EXISTS plugin_helloasso_payment_form_order ON payments (json_extract(extra_data, '$.id_form'), json_extract(extra_data, '$.id_order'));
-- Listing payments on the order page
CREATE INDEX IF NOT EXISTS plugin_helloasso_payment_order_date ON payments (json_extract(extra_data, '$.id_order'), 'date');
-- Synchronizing multiple payments for the same order
CREATE INDEX IF NOT EXISTS plugin_helloasso_payment_first_item ON payments (json_extract(extra_data, '$.items[0].id'));
-- Search index
CREATE INDEX IF NOT EXISTS plugin_helloasso_search_user_date ON users (date_inscription);
CREATE INDEX IF NOT EXISTS plugin_helloasso_search_service_fee ON services_fees (label);

-----------------------------------
---- Plugin indexes

-- Listing orders (one index by view (e.g. ordered by status))
CREATE INDEX IF NOT EXISTS plugin_helloasso_orders_form_date ON plugin_helloasso_orders (id_form, date); -- Default
CREATE INDEX IF NOT EXISTS plugin_helloasso_orders_form_amount ON plugin_helloasso_orders (id_form, amount);
CREATE INDEX IF NOT EXISTS plugin_helloasso_orders_form_payer ON plugin_helloasso_orders (id_form, payer_name);
CREATE INDEX IF NOT EXISTS plugin_helloasso_orders_form_status ON plugin_helloasso_orders (id_form, status);
CREATE INDEX IF NOT EXISTS plugin_helloasso_orders_form_payment ON plugin_helloasso_orders (id_form, json_extract(raw_data, '$.payments[0].id'));
-- Listing orders for a specific payer
CREATE INDEX IF NOT EXISTS plugin_helloasso_orders_user_date ON plugin_helloasso_orders (id_payer, date); -- Default
CREATE INDEX IF NOT EXISTS plugin_helloasso_orders_payer_email ON plugin_helloasso_orders (json_extract(raw_data, '$.payer.email'));
CREATE INDEX IF NOT EXISTS plugin_helloasso_orders_payer_name ON plugin_helloasso_orders (json_extract(raw_data, '$.payer.firstName'), json_extract(raw_data, '$.payer.lastName'));
-- Forcing Group by and Order by to use index when listing chargeables' orders list
CREATE INDEX IF NOT EXISTS plugin_helloasso_orders_id_date ON plugin_helloasso_orders (id, date);

-- Listings chargeables
CREATE INDEX IF NOT EXISTS plugin_helloasso_chargeables_form_label ON plugin_helloasso_chargeables(id_form, label);
CREATE INDEX IF NOT EXISTS plugin_helloasso_chargeables_form_amount ON plugin_helloasso_chargeables(id_form, amount);

CREATE INDEX IF NOT EXISTS plugin_helloasso_chargeables_need_config ON plugin_helloasso_chargeables(type, id_credit_account, need_config) WHERE (type != 4 AND id_credit_account IS NULL) OR (need_config = 1);
