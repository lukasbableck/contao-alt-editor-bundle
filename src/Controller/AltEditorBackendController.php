<?php
namespace Lukasbableck\ContaoAltEditorBundle\Controller;

use Composer\InstalledVersions;
use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Input;
use Lukasbableck\ContaoAltEditorBundle\Classes\AltEditor;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%contao.backend.route_prefix%/alt-editor', name: self::class, defaults: ['_scope' => 'backend'])]
class AltEditorBackendController extends AbstractBackendController {
	public function __construct(
		private readonly AltEditor $altEditor,
		private readonly ContaoCsrfTokenManager $csrfTokenManager,
		private readonly Security $security,
		private readonly TranslatorInterface $translator
	) {
	}

	public function __invoke(): Response {
		if (!$this->security->isGranted('ROLE_ADMIN') && !$this->security->isGranted('contao_user.alteditor')) {
			throw new AccessDeniedException('Not enough permissions to access this controller.');
		}

		$allImages = $this->altEditor->getImages();
		$imagesWithoutAlt = $this->altEditor->getImagesWithoutAltTexts($allImages);

		$this->altEditor->updateMissingAltTextCount(\count($imagesWithoutAlt));

		$bilderAltInstalled = InstalledVersions::isInstalled('bluebranch/bilder-alt');
		if ($bilderAltInstalled) {
			$GLOBALS['TL_JAVASCRIPT'][] = 'bundles/bilderalt/js/tl_files.js|static';
			$GLOBALS['TL_CSS'][] = 'bundles/bilderalt/css/tl_files.css';
		}

		return $this->render('@Contao/backend/alt_editor/main.html.twig', [
			'headline' => $this->translator->trans('MOD.alt_editor.0', [], 'contao_modules'),
			'title' => $this->translator->trans('MOD.alt_editor.0', [], 'contao_modules'),
			'imagesWithoutAlt' => $imagesWithoutAlt,
			'ignoredImages' => $this->altEditor->getIgnoredImages($allImages),
			'select' => 'select' == Input::get('do'),
			'bilderAltInstalled' => $bilderAltInstalled,
			'csrfToken' => $this->csrfTokenManager->getDefaultTokenValue(),
		]);
	}
}
