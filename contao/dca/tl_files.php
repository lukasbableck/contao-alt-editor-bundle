<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Lukasbableck\ContaoAltEditorBundle\DataContainer\MissingAltTextsFolderDataContainer;

$GLOBALS['TL_DCA']['tl_files']['config']['dataContainer'] = MissingAltTextsFolderDataContainer::class;

$GLOBALS['TL_DCA']['tl_files']['list']['operations'] = array_merge(
	[
		'altText' => [
			'primary' => true,
		],
	],
	$GLOBALS['TL_DCA']['tl_files']['list']['operations']
);

$GLOBALS['TL_DCA']['tl_files']['list']['global_operations']['missingAltTexts'] = [
	'icon' => 'editor.svg',
	'href' => '&missingAltTexts=1',
	'primary' => true,
];

$GLOBALS['TL_DCA']['tl_files']['fields']['ignoreEmptyAlt'] = [
	'inputType' => 'checkbox',
	'eval' => ['tl_class' => 'w50 clr'],
	'sql' => "char(1) NOT NULL default ''",
];

PaletteManipulator::create()
	->addField('ignoreEmptyAlt', 'meta_legend', PaletteManipulator::POSITION_APPEND)
	->applyToPalette('default', 'tl_files')
;
