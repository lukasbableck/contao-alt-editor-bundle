<?php
namespace Lukasbableck\ContaoAltEditorBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ContaoAltEditorBundle extends Bundle {
	public function getPath(): string {
		return \dirname(__DIR__);
	}
}
