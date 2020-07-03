<?php

$config['system.logging']['error_level'] = 'verbose';

$databases['default']['default'] = array (
  'database' => getenv('DB_NAME'),
  'username' => getenv('DB_USER'),
  'password' => getenv('DB_PASSWORD'),
  'prefix' => 'd8_',
  'host' => 'mariadb',
  'port' => 3306,
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

//$config['private_message_nodejs.settings']['nodejs_url'] = 'http://node.amas-test.cyb/';
$config['system.performance']['css']['preprocess'] = false;
$config['system.performance']['js']['preprocess'] = false;
$config['system.performance']['cache']['page']['max_age'] = 0;//not cacheable

$settings['cache']['bins']['render'] = 'cache.backend.null';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
$settings['cache']['bins']['page'] = 'cache.backend.null';


//$config['captcha.captcha_point.user_register_form']['captchaType'] = 'captcha/Math';
//$config['captcha.captcha_point.user_register_in_layer_form']['captchaType'] = 'captcha/Math';


$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';

$config['user.role.anonymous']['permissions'][9999] = 'access kint';
$config['user.role.authenticated']['permissions'][9999] = 'access kint';




