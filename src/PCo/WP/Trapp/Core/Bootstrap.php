<?php

namespace PCo\WP\Trapp\Core;
use PCo\WP\Trapp\Admin;
use PCo\WP\Trapp\Frontend;

class Bootstrap {
	public function bootstrap() {
		if ( is_admin() ) {
			$admin = new Admin\Main;
			$admin->bootstrap();
		}
	}
}
