<?php

namespace Bonnier\WP\Trapp\Core;
use Bonnier\WP\Trapp\Admin;
use Bonnier\WP\Trapp\Frontend;

class Bootstrap {
	public function bootstrap() {
		if ( is_admin() ) {
			$admin = new Admin\Main;
			$admin->bootstrap();
		}
	}
}
