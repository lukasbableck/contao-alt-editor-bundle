<?php
namespace Lukasbableck\ContaoAltEditorBundle\Classes;

use Contao\FilesModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class AltEditor {
	private array $imageCache = [];
	private array $imageWithoutAltCache = [];
	private array $ignoredImageCache = [];
	private array $rootPageLanguageCache = [];

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
			if ($this->hasAltTexts($file)) {
				continue;
			}

			$arrFiles[] = $file;
		}
		$this->imageWithoutAltCache = $arrFiles;

		return $arrFiles;
	}

	private function getRootPageLanguages(): array {
		if (!empty($this->rootPageLanguageCache)) {
			return $this->rootPageLanguageCache;
		}

		$rootPages = PageModel::findPublishedRootPages();
		$languages = [];
		if ($rootPages) {
			foreach ($rootPages as $page) {
				$languages[] = $page->language;
			}
		}
		$languages = array_unique($languages);
		sort($languages);

		return $languages;
	}

	public function hasAltTexts(FilesModel $file): bool {
		if ($file->ignoreEmptyAlt) {
			return true;
		}

		if (!$file->meta) {
			$arrFiles[] = $file;

			return false;
		}

		$meta = StringUtil::deserialize($file->meta, true);
		if (empty($meta)) {
			$arrFiles[] = $file;

			return false;
		}
		$keys = array_keys($meta);
		sort($keys);

		$languages = $this->getRootPageLanguages();

		if (array_intersect($keys, $languages) !== $languages) {
			return false;
		}

		foreach ($meta as $language => $values) {
			if (($values['alt'] ?? '') == '') {
				return false;
			}
		}

		return true;
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

	public function areAltTextsMissing(): bool {
		$cache = new FilesystemAdapter();
		$cacheKey = 'contao_alt_editor_missing_alt_texts';

		$value = $cache->get($cacheKey, function (ItemInterface $item) {
			$item->expiresAfter(86400);

			return \count($this->getImagesWithoutAltTexts($this->getImages()));
		});

		if (0 === $value) {
			return false;
		}

		return true;
	}

	public function updateMissingAltTextCount($count): void {
		$cache = new FilesystemAdapter();
		$cacheKey = 'contao_alt_editor_missing_alt_texts';

		$cacheItem = $cache->getItem($cacheKey);
		$cacheItem->set($count);
		$cache->save($cacheItem);
	}
}
