<?php
namespace Lukasbableck\ContaoAltEditorBundle\Controller;

use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\System;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%contao.backend.route_prefix%/alt-editor', name: self::class, defaults: ['_scope' => 'backend'])]
class AltEditorBackendController extends AbstractBackendController {
	public function __construct(private readonly Security $security, private readonly TranslatorInterface $translator) {
	}

	public function __invoke(): Response {
		if (!$this->security->isGranted('ROLE_ADMIN') && !$this->security->isGranted('contao_user.alteditor')) {
			throw new AccessDeniedException('Not enough permissions to access this controller.');
		}

		$allImages = $this->getImages();
		$imagesWithoutAlt = $this->getImagesWithoutAltTexts($allImages);

		return $this->render('@Contao/be_alt_editor.html.twig', [
			'headline' => $this->translator->trans('MOD.alt_editor.0', [], 'contao_modules'),
			'title' => $this->translator->trans('MOD.alt_editor.0', [], 'contao_modules'),
			'imagesWithoutAlt' => $imagesWithoutAlt,
			'ignoredImages' => $this->getIgnoredImages($allImages),
		]);
	}

	private function getImages(): array {
		$files = FilesModel::findBy(['type=?'], ['file']);
		$arrFiles = [];
		$imageExtensions = System::getContainer()->getParameter('contao.image.valid_extensions');

		foreach ($files as $file) {
			$path = $file->path;
			$extension = strtolower(pathinfo($path, \PATHINFO_EXTENSION));

			if (!\in_array($extension, $imageExtensions)) {
				continue;
			}

			$arrFiles[] = $file;
		}

		return $arrFiles;
	}

	private function getImagesWithoutAltTexts(array $images): array {
		$arrFiles = [];
		foreach ($images as $file) {
			if ($file->ignoreEmptyAlt) {
				continue;
			}

			if (!$file->meta) {
				$arrFiles[] = $file;
				continue;
			}

			$meta = StringUtil::deserialize($file->meta, true);
			foreach ($meta as $language => $values) {
				if (($values['alt'] ?? '') == '') {
					$arrFiles[] = $file;
					continue 2;
				}
			}
		}

		return $arrFiles;
	}

	private function getIgnoredImages(array $images): array {
		$arrFiles = [];
		foreach ($images as $file) {
			if ($file->ignoreEmptyAlt) {
				$arrFiles[] = $file;
			}
		}

		return $arrFiles;
	}
}
