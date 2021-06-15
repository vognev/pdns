<?php

namespace PDNS\Docker\Communication;

interface ISendState
{
    function send(\Socket $socket);
}
