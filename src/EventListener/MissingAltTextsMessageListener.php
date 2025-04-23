<?php
namespace Lukasbableck\ContaoAltEditorBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\System;
use Lukasbableck\ContaoAltEditorBundle\Classes\AltEditor;
use Lukasbableck\ContaoAltEditorBundle\Controller\AltEditorBackendController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

#[AsHook('getSystemMessages')]
class MissingAltTextsMessageListener {
	public function __construct(private readonly AltEditor $altEditor) {
	}

	public function __invoke(): string {
		$cache = new FilesystemAdapter();
		$cacheKey = 'contao_alt_editor_missing_alt_texts';

		$value = $cache->get($cacheKey, function (ItemInterface $item) {
			$item->expiresAfter(300);

			return \count($this->altEditor->getImagesWithoutAltTexts($this->altEditor->getImages()));
		});

		if (0 === $value) {
			return '';
		}

		$link = System::getContainer()->get('router')->generate(AltEditorBackendController::class);

		return '<p class="tl_error">Es befinden sich Bilder ohne Alternativ-Text in der Dateiverwaltung. <a href="'.$link.'">Jetzt beheben.</a></p>';
	}
}
