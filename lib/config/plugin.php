<?php
return array(
	'name'            => 'Модульбанк: интернет-эквайринг',
	'description'     => 'Платежная система <a href="https://modulbank.ru" target="_blank">Модульбанк</a>',
	'icon'            => 'img/modulbank16.png',
	'logo'            => 'img/modulbank.png',
	'vendor'          => '1200271',
	'version'         => '2.0.1',
	'locale'          => array('ru_RU'),
	'type'            => waPayment::TYPE_ONLINE,
	'partial_capture' => true,
);
