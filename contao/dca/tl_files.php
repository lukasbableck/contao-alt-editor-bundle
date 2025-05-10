<?php

use Lukasbableck\ContaoAltEditorBundle\Controller\AltEditorBackendController;

$GLOBALS['TL_DCA']['tl_files']['list']['operations'] = array_merge(
	[
		'altText' => [],
	],
	$GLOBALS['TL_DCA']['tl_files']['list']['operations']
);

$GLOBALS['TL_DCA']['tl_files']['list']['global_operations']['altEditor'] = [
	'icon' => 'editor.svg',
	'route' => AltEditorBackendController::class,
];

$GLOBALS['TL_DCA']['tl_files']['fields']['ignoreEmptyAlt'] = [
	'inputType' => 'checkbox',
	'eval' => ['tl_class' => 'w50 clr'],
	'sql' => "char(1) NOT NULL default ''",
];
