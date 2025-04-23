<?php
namespace Lukasbableck\ContaoAltEditorBundle\Classes;

use Contao\FilesModel;
use Contao\StringUtil;
use Contao\System;

class AltEditor {
	private array $imageCache = [];
	private array $imageWithoutAltCache = [];
	private array $ignoredImageCache = [];

	public function getImages(): array {
		if (!empty($this->imageCache)) {
			return $this->imageCache;
		}

		$files = FilesModel::findBy(['type=?'], ['file']);
		$arrFiles = [];
		$imageExtensions = System::getContainer()->getParameter('contao.image.valid_extensions');

		foreach ($files as $file) {
			if (!file_exists($file->getAbsolutePath())) {
				continue;
			}
			$path = $file->path;
			$extension = strtolower(pathinfo($path, \PATHINFO_EXTENSION));

			if (!\in_array($extension, $imageExtensions)) {
				continue;
			}

			$arrFiles[] = $file;
		}
		$this->imageCache = $arrFiles;

		return $arrFiles;
	}

	public function getImagesWithoutAltTexts(array $images): array {
		if (!empty($this->imageWithoutAltCache)) {
			return $this->imageWithoutAltCache;
		}

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
			if (empty($meta)) {
				$arrFiles[] = $file;
				continue;
			}
			foreach ($meta as $language => $values) {
				if (($values['alt'] ?? '') == '') {
					$arrFiles[] = $file;
					continue 2;
				}
			}
		}
		$this->imageWithoutAltCache = $arrFiles;

		return $arrFiles;
	}

	public function getIgnoredImages(array $images): array {
		if (!empty($this->ignoredImageCache)) {
			return $this->ignoredImageCache;
		}

		$arrFiles = [];
		foreach ($images as $file) {
			if ($file->ignoreEmptyAlt) {
				$arrFiles[] = $file;
			}
		}
		$this->ignoredImageCache = $arrFiles;

		return $arrFiles;
	}
}
