<?php

namespace Paheko\Plugin\Caisse;

use Paheko\Users\Session;
use Paheko\Utils;
use Paheko\UserTemplate\CommonModifiers;

function reload() {
	Utils::redirect(Utils::getSelfURI(true));
}

function get_amount(string $amount): int {
	return Utils::moneyToInteger($amount);
}

$tpl->register_modifier('image_base64', function (string $blob) {
	return 'data:image/png;base64,' . base64_encode($blob);
});

Session::getInstance()->requireAccess(Session::SECTION_USERS, Session::ACCESS_WRITE);

$tpl->assign('pos_templates_root', \Paheko\PLUGIN_ROOT . '/templates');

$tpl->assign('plugin_css', ['style.css']);
