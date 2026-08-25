<?php

define( 'ABSPATH', __DIR__ . '/' );
define( 'WC_VERSION', '10.9.1' );
define( 'DAY_IN_SECONDS', 86400 );

require_once __DIR__ . '/stubs/wordpress.php';
require_once __DIR__ . '/stubs/woocommerce.php';
require_once __DIR__ . '/stubs/quaderno_api.php';

require_once dirname( __DIR__ ) . '/classes/class-wc-qd-tax-code-field.php';
require_once dirname( __DIR__ ) . '/classes/class-wc-qd-calculate-tax.php';
require_once dirname( __DIR__ ) . '/classes/class-wc-qd-tax-manager.php';
require_once dirname( __DIR__ ) . '/classes/class-wc-qd-tax-id-field.php';
require_once dirname( __DIR__ ) . '/classes/class-wc-qd-transaction-manager.php';
require_once dirname( __DIR__ ) . '/classes/class-wc-qd-invoice-manager.php';

require_once dirname( __DIR__ ) . '/vendor/autoload.php';
