<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_user']['fields']['alteditor'] = [
	'exclude' => true,
	'inputType' => 'checkbox',
	'eval' => ['multiple' => true],
	'options' => [
		'alteditor' => 'Edit alt texts',
	],
	'sql' => ['type' => 'blob', 'notnull' => false],
];

PaletteManipulator::create()
	->addField('alteditor', 'filemounts_legend', PaletteManipulator::POSITION_APPEND)
	->applyToPalette('extend', 'tl_user')
	->applyToPalette('custom', 'tl_user')
;
