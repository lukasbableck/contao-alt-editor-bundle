<?php
namespace Lukasbableck\ContaoAltEditorBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\System;
use Lukasbableck\ContaoAltEditorBundle\Classes\AltEditor;
use Lukasbableck\ContaoAltEditorBundle\Controller\AltEditorBackendController;
use Symfony\Bundle\SecurityBundle\Security;

#[AsHook('getSystemMessages')]
class MissingAltTextsMessageListener {
	public function __construct(private readonly AltEditor $altEditor, private readonly Security $security) {
	}

	public function __invoke(): string {
		if (!$this->security->isGranted('ROLE_ADMIN') && !$this->security->isGranted('contao_user.alteditor')) {
			return '';
		}

		if (!$this->altEditor->areAltTextsMissing()) {
			return '';
		}

		$link = System::getContainer()->get('router')->generate(AltEditorBackendController::class);

		return '<p class="tl_error">Es befinden sich Bilder ohne Alternativ-Text in der Dateiverwaltung. <a href="'.$link.'">Jetzt beheben.</a></p>';
	}
}
