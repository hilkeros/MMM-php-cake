load 'deploy' if respond_to?(:namespace) # cap2 differentiator

set :user, 'mmm'
set :scm, :git

set :repository, 'git@github.com:hilkeros/MMM-php-cake.git'
set :branch, 'master'
set :deploy_via, :remote_cache
set :use_sudo, false
set :application, 'mmm'
set :deploy_to, "/home/#{user}/apps/#{application}/"

role :web, 'pro-004.openminds.be'

ssh_options[:forward_agent] = true

namespace :deploy do
  task :start do
  end
  
  task :stop do
  end
  
  task :restart do
  end

  desc <<-DESC
    Symlinks shared configuration and directories into the latest release
    Also clear persistent and model cache
  DESC
  task :finalize_update do
    run "rm -rf #{latest_release}/app/config; ln -s #{shared_path}/app/config #{latest_release}/app/config"
    run "rm -rf #{latest_release}/app/tmp/models/*"
    run "rm -rf #{latest_release}/app/tmp/persistent/*"
  end
  
  desc <<-DESC
    If you've updated the config, overwrite it on the server (Be very sure what you're doing,
    this breaks database etc!!!
  DESC
  task :copy_config do
    run "cp -r #{shared_path}/cached-copy/app/config/* #{shared_path}/app/config/"
  end
end