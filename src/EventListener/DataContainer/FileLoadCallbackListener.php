<?php
namespace Lukasbableck\ContaoAltEditorBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Symfony\Bundle\SecurityBundle\Security;

#[AsCallback(table: 'tl_files', target: 'config.onload')]
class FileLoadCallbackListener {
	public function __construct(private readonly Security $security) {
	}

	public function __invoke(?DataContainer $dc = null): void {
		if (!$this->security->isGranted('ROLE_ADMIN') && !$this->security->isGranted('contao_user.alteditor')) {
			unset($GLOBALS['TL_DCA']['tl_files']['list']['global_operations']['altEditor']);
		}
	}
}
