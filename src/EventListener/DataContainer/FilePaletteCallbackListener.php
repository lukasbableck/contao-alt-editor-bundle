<?php
namespace Lukasbableck\ContaoAltEditorBundle\EventListener\DataContainer;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\System;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCallback(table: 'tl_files', target: 'config.onpalette')]
class FilePaletteCallbackListener {
	public function __construct(private readonly RequestStack $requestStack) {
	}

	public function __invoke(string $strPalette, DataContainer $dc): string {
		if (!$dc->id) {
			return $strPalette;
		}

		$projectDir = System::getContainer()->getParameter('kernel.project_dir');
		$blnIsFolder = is_dir($projectDir.'/'.$dc->id);

		if ($blnIsFolder) {
			$strPalette = PaletteManipulator::create()
				->removeField('ignoreEmptyAlt')
				->applyToString($strPalette)
			;
		}

		return $strPalette;
	}
}
