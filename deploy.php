<?php

namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'Velmie-Reports');

// Project repository
set('repository', 'git@bitbucket.org:velmie/report.ikantam.git');

// Shared files/dirs between deploys
add('shared_files', ['.env']);
add('shared_dirs', ['vendor', 'storage']);

// Writable dirs by web server
add('writable_dirs', []);

set('allow_anonymous_stats', false);

set('keep_releases', 4);

set('default_stage', 'dev');

// Hosts

host('reports.velmie.com')
  ->stage('prod')
  ->set('branch', 'master')
  ->set('deploy_path', '/var/www')
  ->user(getenv('DEP_SSH_USER'))
  ->port(getenv('DEP_SSH_PORT') ? getenv('DEP_SSH_PORT') : 22)
  ->forwardAgent(true)
  ->multiplexing(true)
  ->addSshOption('UserKnownHostsFile', '/dev/null')
  ->addSshOption('StrictHostKeyChecking', 'no');


after('deploy:vendors', 'artisan:migrate');

// Generate stamp
task('stamp:generate', function() {
    run('cd {{release_path}} && php artisan stamp:generate');
});

after('deploy:vendors', 'stamp:generate');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'artisan:migrate');


// Other

task('crontab:install', function () {
  $cronConfigFile = '{{release_path}}/deploy/crontab.conf';

  $cronConfig = run("[[ ! -f {$cronConfigFile} ]] || (cat {$cronConfigFile})");

  $cronConfig = str_replace("{{release_path}}", get('release_path'), $cronConfig);

  run("echo \"{$cronConfig}\" | crontab -");

})->desc('Install crontab');

task('asset:dist', function () {
  $output = run('if [ -f {{release_path}}/artisan ]; then {{bin/php}} {{release_path}}/artisan asset:dist; fi');
  writeln('<info>' . $output . '</info>');
});

// Set maintenance mode ON
after('deploy:lock', 'artisan:down');

// Update Crontab
after('deploy:symlink', 'crontab:install');

// Generate Assets
//before('cleanup', 'asset:dist');

// Set maintenance mode OFF
after('cleanup', 'artisan:up');

