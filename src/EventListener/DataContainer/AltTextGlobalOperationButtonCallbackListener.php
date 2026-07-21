<?php
namespace Lukasbableck\ContaoAltEditorBundle\EventListener\DataContainer;

use Contao\Backend;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\StringUtil;
use Symfony\Bundle\SecurityBundle\Security;

#[AsCallback(table: 'tl_files', target: 'list.global_operations.missingAltTexts.button')]
class AltTextGlobalOperationButtonCallbackListener {
	public function __construct(
		private readonly Security $security,
	) {
	}

	public function __invoke(?string $href, string $label, string $title, string $class, string $attributes, string $table, array $rootRecordIds): string {
		if (!$this->security->isGranted('ROLE_ADMIN') && !$this->security->isGranted('contao_user.alteditor')) {
			return '';
		}
		dump($href, $label, $title, $class, $attributes, $table, $rootRecordIds);

		$href = Backend::addToUrl($href);

		return '<a href="'.$href.'" class="'.$class.'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.$label.'</a> ';
	}
}
