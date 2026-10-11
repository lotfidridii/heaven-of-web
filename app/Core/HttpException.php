<?php
declare(strict_types=1);

namespace App\Core;

final class HttpException extends \RuntimeException
{
    public function __construct(public readonly int $status = 404)
    {
        parent::__construct('HTTP ' . $status, $status);
    }
}
