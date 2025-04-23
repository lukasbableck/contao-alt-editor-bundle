<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_user_group']['fields']['alteditor'] = [
	'exclude' => true,
	'inputType' => 'checkbox',
	'eval' => [
		'multiple' => true,
	],
	'options' => [
		'alteditor',
	],
	'reference' => &$GLOBALS['TL_LANG']['tl_user_group'],
	'sql' => ['type' => 'blob', 'notnull' => false],
];

PaletteManipulator::create()
	->addField('alteditor', 'filemounts_legend', PaletteManipulator::POSITION_APPEND)
	->applyToPalette('default', 'tl_user_group')
;
