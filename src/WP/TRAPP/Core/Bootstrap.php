<?php

namespace PCo\WP\TRAPP\Core;
use PCo\WP\TRAPP\Admin;
use PCo\WP\TRAPP\Frontend;

class Bootstrap {
	public function bootstrap() {
		if ( is_admin() ) {
			$admin = new Admin;
			$admin->main();
		}
	}
}
