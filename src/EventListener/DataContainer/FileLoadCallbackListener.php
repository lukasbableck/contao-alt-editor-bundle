<?php
namespace Lukasbableck\ContaoAltEditorBundle\EventListener\DataContainer;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\System;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCallback(table: 'tl_files', target: 'config.onload')]
class FileLoadCallbackListener {
	public function __construct(private readonly RequestStack $requestStack, private readonly Security $security) {
	}

	public function __invoke(?DataContainer $dc = null): void {
		if (!$this->security->isGranted('ROLE_ADMIN') && !$this->security->isGranted('contao_user.alteditor')) {
			unset($GLOBALS['TL_DCA']['tl_files']['list']['global_operations']['altEditor']);
		}

		if (null === $dc || !$dc->id || 'edit' !== $this->requestStack->getCurrentRequest()->query->get('act')) {
			return;
		}

		$projectDir = System::getContainer()->getParameter('kernel.project_dir');
		$blnIsFolder = is_dir($projectDir.'/'.$dc->id);

		if (!$blnIsFolder) {
			PaletteManipulator::create()
				->addField('ignoreEmptyAlt', 'meta_legend')
				->applyToPalette('default', 'tl_files')
			;
		}
	}
}
