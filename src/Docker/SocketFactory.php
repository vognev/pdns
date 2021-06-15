<?php

namespace PDNS\Docker;

class SocketFactory
{
    public static function createFromEnvironment() : \Socket
    {
        if ($docker_host = getenv('DOCKER_HOST')) {
            throw new \RuntimeException('DOCKER_HOST not supported yet');
        }

        return self::createUnixSocket('/var/run/docker.sock');
    }

    public static function createUnixSocket(string $unixSocketPath) : \Socket
    {
        if (false === ($socket = socket_create(AF_UNIX, SOCK_STREAM, 0))) {
            throw new \RuntimeException(
                socket_strerror(socket_last_error())
            );
        }

        if (
            false === socket_set_nonblock($socket) ||
            false === socket_connect($socket, $unixSocketPath)
        ) {
            throw new \RuntimeException(
                socket_strerror(socket_last_error($socket))
            );
        }

        return $socket;
    }
}
