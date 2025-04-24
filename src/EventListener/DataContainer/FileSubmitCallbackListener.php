<?php
namespace Lukasbableck\ContaoAltEditorBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCallback(table: 'tl_files', target: 'config.onsubmit')]
class FileSubmitCallbackListener {
	public function __construct(private readonly RequestStack $requestStack, private readonly Security $security) {
	}

	public function __invoke(?DataContainer $dc = null): void {
		$cache = new FilesystemAdapter();
		$cacheKey = 'contao_alt_editor_missing_alt_texts';
		$cache->delete($cacheKey);
	}
}
