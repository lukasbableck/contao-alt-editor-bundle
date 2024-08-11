<?php
namespace Lukasbableck\ContaoAltEditorBundle\Controller;

use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/%contao.backend.route_prefix%/alteditor', name: AltEditorBackendController::class, defaults: ['_scope' => 'backend'])]
class AltEditorBackendController extends AbstractBackendController {
	public function __construct(private Security $security) {
	}

	public function __invoke(): Response {
		if (!$this->security->isGranted('ROLE_ADMIN') && !$this->security->isGranted('contao_user.alteditor', 'alteditor')) {
			throw new AccessDeniedException('Not enough permissions to access this controller.');
		}

		return new Response();
	}
}
