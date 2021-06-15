<?php

namespace PDNS\Docker\Communication;

interface IRecvState
{
    function recv(\Socket $socket);
}
