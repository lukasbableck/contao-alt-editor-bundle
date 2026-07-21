<?php
namespace Lukasbableck\ContaoAltEditorBundle\DataContainer;

use Contao\DC_Folder;
use Contao\Message;
use Contao\System;
use InspiredMinds\ContaoFileUsage\DataContainer\FolderDataContainer;
use Lukasbableck\ContaoAltEditorBundle\Classes\AltEditor;
use Symfony\Bundle\SecurityBundle\Security;

if (class_exists(FolderDataContainer::class)) {
	class BaseFolderDataContainer extends FolderDataContainer {
	}
} else {
	class BaseFolderDataContainer extends DC_Folder {
	}
}

class MissingAltTextsFolderDataContainer extends BaseFolderDataContainer {
	protected function generateTree($path, $intMargin, $mount = false, $blnProtected = true, $arrClipboard = null, $arrFound = []) {
		$container = System::getContainer();
		$altEditor = $container->get(AltEditor::class);
		/** @var Security $security */
		$security = $container->get('security.helper');
		$translator = $container->get('translator');

		System::loadLanguageFile('alt_editor');

		$request = $container->get('request_stack')->getCurrentRequest();

		if (!$security->isGranted('contao_user.alteditor') || !$request->query->get('missingAltTexts')) {
			return parent::generateTree($path, $intMargin, $mount, $blnProtected, $arrClipboard, $arrFound);
		}

		$imagesWithoutAltTexts = $altEditor->getImagesWithoutAltTexts($altEditor->getImages());
		if (empty($imagesWithoutAltTexts)) {
			Message::addConfirmation($translator->trans('alt_editor.noImagesWithoutAlt', [], 'contao_alt_editor'));
		} else {
			Message::addInfo($translator->trans('alt_editor.filteredForImagesWithoutAltTexts', [], 'contao_alt_editor'));
		}

		return parent::generateTree($path, $intMargin, $mount, $blnProtected, $arrClipboard, array_map(static function ($file) {
			return $file->path;
		}, $imagesWithoutAltTexts));
	}
}
