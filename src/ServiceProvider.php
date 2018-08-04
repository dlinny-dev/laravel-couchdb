<?php

namespace Robsonvn\CouchDB;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Robsonvn\CouchDB\Eloquent\Model;
use Robsonvn\CouchDB\Queue\CouchConnector;


class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        Model::setConnectionResolver($this->app['db']);

        Model::setEventDispatcher($this->app['events']);
    }

    /**
     * Register the provider.
     *
     * @return void
     */
    public function register()
    {
        // Add couchdb to the database manager
        \Illuminate\Database\Connection::resolverFor('couchdb', function ($connection, $database, $prefix, $config) {
            return new Connection($config);
        });
        // Add connector for queue support.
        $this->app->resolving('queue', function ($queue) {
            $queue->addConnector('couchdb', function () {
                return new CouchConnector($this->app['db']);
            });
        });
    }
}
