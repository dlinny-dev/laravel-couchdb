<?php

namespace Robsonvn\CouchDB\Doctrine;

use Doctrine\CouchDB\MangoClient as Prototype;

class MangoClient extends Prototype
{
    protected static $clients = [
        'socket' => 'Doctrine\CouchDB\HTTP\SocketClient',
        'stream' => 'Doctrine\CouchDB\HTTP\StreamClient',
        'https' => 'Robsonvn\CouchDB\Doctrine\HttpsClient',
    ];

    /**
     * Factory method for CouchDBClients.
     *
     * @param array $options
     *
     * @throws \InvalidArgumentException
     *
     * @return CouchDBClient
     */
    public static function create(array $options)
    {
        if (isset($options['url'])) {
            $urlParts = parse_url($options['url']);

            foreach ($urlParts as $part => $value) {
                switch ($part) {
                    case 'host':
                    case 'user':
                    case 'port':
                        $options[$part] = $value;
                        break;

                    case 'path':
                        $path = explode('/', $value);
                        $options['dbname'] = array_pop($path);
                        $options['path'] = trim(implode('/', $path), '/');
                        break;

                    case 'pass':
                        $options['password'] = $value;
                        break;

                    case 'scheme':
                        $options['ssl'] = ($value === 'https');
                        break;

                    default:
                        break;
                }
            }
        }

        if (!isset($options['dbname'])) {
            throw new \InvalidArgumentException("'dbname' is a required option to create a CouchDBClient");
        }

        $defaults = [
            'type'     => 'socket',
            'host'     => 'localhost',
            'port'     => 5984,
            'user'     => null,
            'password' => null,
            'ip'       => null,
            'ssl'      => false,
            'path'     => null,
            'logging'  => false,
            'timeout'  => 10,
            'headers'  => [],
        ];
        $options = array_merge($defaults, $options);

        if (!isset(static::$clients[$options['type']])) {
            throw new \InvalidArgumentException(sprintf('There is no client implementation registered for %s, valid options are: %s',
                $options['type'], implode(', ', array_keys(static::$clients))
            ));
        }
        $connectionClass = static::$clients[$options['type']];
        $connection = new $connectionClass(
            $options['host'],
            $options['port'],
            $options['user'],
            $options['password'],
            $options['ip'],
            $options['ssl'],
            $options['path'],
            $options['timeout'],
            $options['headers']
        );
        if ($options['logging'] === true) {
            $connection = new HTTP\LoggingClient($connection);
        }

        return new static($connection, $options['dbname']);
    }
}