<?php

namespace Deployer;

use Symfony\Component\Dotenv\Dotenv;

require 'vendor/deployer/deployer/recipe/symfony4.php';
require_once 'vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env.local');

set('ssh_type', 'native');
set('ssh_multiplexing', true);
set('keep_releases', 3);
set('repository', 'ssh://git@github.com/wayatech/symfony-api.git');
set('git_tty', false);
set('shared_dirs', ['config/jwt', 'var/log']);
//set('writable_mode', 'chown');
//set('writable_chmod_mode', '0775');
set('http_user', 'www-data');

host('production')
    ->hostname($_ENV['DEPLOY_HOST'])
    ->user($_ENV['DEPLOY_USER'])
    ->port($_ENV['DEPLOY_PORT'])
    ->forwardAgent()
    ->set('branch', $_ENV['DEPLOY_BRANCH'])
    ->set('deploy_path', $_ENV['DEPLOY_PATH']);

task('composer:dump-env', function () {
    run('cd {{release_path}} && {{bin/composer}} dump-env prod');
})->desc('Composer dump-env');

after('deploy:vendors', 'composer:dump-env');

after('deploy:vendors', 'database:migrate');

after('deploy:vendors', 'composer:dump-autoload');

task('composer:dump-autoload', function () {
    run('cd {{release_path}} && {{bin/composer}} dump-autoload --optimize --classmap-authoritative');
})->desc('Composer dump-autoload');

after('deploy:failed', 'deploy:unlock');

task('database:migrate', function () {
    run('cd {{release_path}} && {{bin/php}} {{bin/console}} doctrine:migrations:migrate');
})->desc('Migrate databases');
