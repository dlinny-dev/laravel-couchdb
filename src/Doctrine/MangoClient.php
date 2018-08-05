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
}