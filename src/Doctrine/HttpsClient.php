<?php

namespace Robsonvn\CouchDB\Doctrine;

use Doctrine\CouchDB\HTTP;

class HttpsClient extends StreamClient
{
    /**
     * Sets up the stream connection.
     *
     * @param $method
     * @param $path
     * @param $data
     * @param $headers
     *
     * @throws HTTPException
     */
    protected function checkConnection($method, $path, $data, $headers)
    {
        $basicAuth = '';
        if ($this->options['username']) {
            $basicAuth .= "{$this->options['username']}:{$this->options['password']}@";
        }
        if ($this->options['headers']) {
            $headers = array_merge($this->options['headers'], $headers);
        }
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }
        $stringHeader = '';
        if ($headers != null) {
            foreach ($headers as $key => $val) {
                $stringHeader .= $key . ': ' . $val . "\r\n";
            }
        }
        if ($this->httpFilePointer == null) {
            $protocol = 'http';
            if ($this->options['ssl']) {
                $protocol = 'https';
            }
            $host = $this->options['host'];
            if ($this->options['port'] != 80) {
                $host .= ":{$this->options['port']}";
            }
            $this->httpFilePointer = @fopen(
                $protocol . '://' . $basicAuth . $host . $path,
                'r',
                false,
                stream_context_create(
                    [
                        'http' => [
                            'method' => $method,
                            'content' => $data,
                            'ignore_errors' => true,
                            'max_redirects' => 0,
                            'user_agent' => 'Doctrine CouchDB ODM $Revision$',
                            'timeout' => $this->options['timeout'],
                            'header' => $stringHeader,
                        ],
                    ]
                )
            );
        }

        // Check if connection has been established successfully.
        if ($this->httpFilePointer === false) {
            $error = error_get_last();
            throw HTTPException::connectionFailure(
                $this->options['ip'],
                $this->options['port'],
                $error['message'],
                0
            );
        }
    }
}