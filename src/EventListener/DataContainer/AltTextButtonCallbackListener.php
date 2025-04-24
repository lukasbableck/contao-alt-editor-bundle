<?php
namespace Lukasbableck\ContaoAltEditorBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\FilesModel;
use Contao\Image;
use Contao\StringUtil;
use Lukasbableck\ContaoAltEditorBundle\Classes\AltEditor;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCallback(table: 'tl_files', target: 'list.operations.altText.button')]
class AltTextButtonCallbackListener {
	public function __construct(private readonly AltEditor $altEditor, private readonly TranslatorInterface $translator) {
	}

	public function __invoke(array $row, ?string $href, string $label, string $title, ?string $icon, string $attributes, string $table, array $rootRecordIds, ?array $childRecordIds, bool $circularReference, ?string $previous, ?string $next, DataContainer $dc): string {
		$file = FilesModel::findByPath(urldecode((string) $row['id']));

		if (null === $file || 'file' !== $file->type) {
			return '';
		}

		if ($this->altEditor->hasAltTexts($file)) {
			return '';
		}

		$image = 'important.svg';
		$title = $this->translator->trans('alt_editor.missingAltText', [], 'contao_alt_editor');

		return '<span title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($image).'</span> ';
	}
}
