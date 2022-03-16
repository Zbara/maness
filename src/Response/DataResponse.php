<?php

namespace App\Response;

use App\Kernel;
use App\Service\GitRevision;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;

class DataResponse
{
    const STATUS_ERROR = 0;
    const STATUS_SUCCESS = 1;

    private GitRevision $git;
    private bool $debug;
    private string $environment;

    public function __construct($debug, $environment, GitRevision $gitRevision)
    {
        $this->debug = $debug;
        $this->git = $gitRevision;
        $this->environment = $environment;
    }

    #[ArrayShape(['response' => "array", 'status' => "", 'result' => "", 'system' => "array"])]
    public function success($status, $result): array
    {
        return [
            'response' => [
                'status' => $status,
                'result' => $result,
                'system' => $this->system()
            ]
        ];
    }

    #[ArrayShape(['response' => "array", 'status' => "", 'error' => "array", 'system' => "array"])]
    public function error($status, $messages): array
    {
        return [
            'response' => [
                'error' => [
                    'messages' => $messages,
                    'status' => $status
                ],
                'system' => $this->system()
            ]
        ];
    }

    #[ArrayShape([
        'environment' => "string",
        'memory' => "mixed",
        'gitDate' => "string",
        'gitRev' => "string",
        'gitMessages' => "string",
        'time' => "int",
        'debug' => "bool",
        'request' => "array",
        'response' => "array"
    ])]
    private function system(): array
    {
        return [
            'environment' => $this->environment,
            'gitDate' => $this->git->getDate(),
            'gitRev' => $this->git->getBuild(),
            'gitMessages' => $this->git->getCommand("log -1 --pretty=format:'%s'"),
            'time' => time(),
            'debug' => $this->debug,
            'memory' => sys_getloadavg()[0]
        ];
    }
}
