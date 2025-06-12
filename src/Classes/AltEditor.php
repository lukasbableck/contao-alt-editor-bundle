<?php
namespace Lukasbableck\ContaoAltEditorBundle\Classes;

use Composer\InstalledVersions;
use Contao\FilesModel;
use Contao\Image;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use InspiredMinds\ContaoFileUsage\Controller\ShowFileReferencesController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Cache\ItemInterface;

class AltEditor {
	private array $imageCache = [];
	private array $imageWithoutAltCache = [];
	private array $ignoredImageCache = [];
	private array $rootPageLanguageCache = [];

	private ?FilesystemAdapter $fileUsageCache = null;
	private string $ref = '';

	public function __construct(
		private readonly RequestStack $requestStack,
		private readonly UrlGeneratorInterface $router
	) {
		if (InstalledVersions::isInstalled('inspiredminds/contao-file-usage')) {
			$this->fileUsageCache = new FilesystemAdapter('fileusage', 0, System::getContainer()->getParameter('contao_file_usage.file_usage_cache_dir'));
			$this->ref = $this->requestStack->getCurrentRequest()->attributes->get('_contao_referer_id');
		}
	}

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

			if ($this->fileUsageCache) {
				$this->handleFileUsage($file);
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
				if ($this->fileUsageCache) {
					$this->handleFileUsage($file);
				}
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

	private function handleFileUsage(&$file): void {
		$file = $file->cloneDetached();

		$file->usageLink = $this->router->generate(ShowFileReferencesController::class, ['uuid' => StringUtil::binToUuid($file->uuid), 'ref' => $this->ref]);
		$file->usageImage = Image::getHtml('bundles/contaofileusage/link-off.svg');
		$uuid = StringUtil::binToUuid($file->uuid);

		$cacheItem = $this->fileUsageCache->getItem($uuid);
		if (!$cacheItem->isHit()) {
			$file->usageHit = false;
			$file->usageImage = Image::getHtml('bundles/contaofileusage/search.svg');

			return;
		}

		$results = $cacheItem->get();
		$file->usageHit = true;
		if ($results->count() > 0) {
			$file->inUse = true;
			$file->usageImage = Image::getHtml('bundles/contaofileusage/link.svg');
		} else {
			$file->inUse = false;
		}
	}
}
