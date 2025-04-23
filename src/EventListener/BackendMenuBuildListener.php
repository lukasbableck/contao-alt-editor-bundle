<?php
namespace Lukasbableck\ContaoAltEditorBundle\EventListener;

use Contao\CoreBundle\Event\ContaoCoreEvents;
use Contao\CoreBundle\Event\MenuEvent;
use Lukasbableck\ContaoAltEditorBundle\Controller\AltEditorBackendController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsEventListener(ContaoCoreEvents::BACKEND_MENU_BUILD)]
class BackendMenuBuildListener {
	public function __construct(
		private readonly RouterInterface $router,
		private readonly RequestStack $requestStack,
		private readonly Security $security,
		private readonly TranslatorInterface $translator
	) {
	}

	public function __invoke(MenuEvent $event): void {
		if (!$this->security->isGranted('ROLE_ADMIN') && !$this->security->isGranted('contao_user.alteditor')) {
			return;
		}

		$factory = $event->getFactory();
		$tree = $event->getTree();

		if ('mainMenu' === $tree->getName()) {
			$contentNode = $tree->getChild('content');
			$children = $contentNode->getChildren();
			$arrChildren = [];

			$node = $factory
				->createItem('alt_editor')
					->setLabel('MOD.alt_editor.0')
					->setLinkAttribute('title', $this->translator->trans('MOD.alt_editor.1', [], 'contao_modules'))
					->setUri($this->router->generate(AltEditorBackendController::class))
					->setCurrent(AltEditorBackendController::class === $this->requestStack->getCurrentRequest()->get('_controller'))
					->setExtra('translation_domain', 'contao_modules')
			;

			foreach ($children as $child) {
				$arrChildren[] = $child;
				if ('files' === $child->getName()) {
					$arrChildren[] = $node;
				}
			}

			$contentNode->setChildren($arrChildren);
		}
	}
}
