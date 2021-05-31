<?php

namespace Eypiay\Eypiay\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EypiayBaseController extends Controller
{
    const STATUS_TEXT = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        421 => 'Misdirected Request',                                         // RFC7540
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        451 => 'Unavailable For Legal Reasons',                               // RFC7725
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',                                     // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    ];

    protected $dbTable = ''; // database table
    protected $dbHidden = []; // hidden table columns for results
    protected $eypiay = []; // eypiay global configuration

    public function __construct(Request $request)
    {

        $this->eypiay['route'] = $request->path();

        $dbFile = base_path(config('eypiay.EYPIAY_PATH') . '/build/db.php');
        if (File::exists($dbFile)) {
            $dbConfig = include $dbFile;
            $this->dbTable = $dbConfig[$this->eypiay['route']]['table'] ?? null;
            $this->dbHidden = $dbConfig[$this->eypiay['route']]['hidden'] ?? [];
        }

        $this->code = 404;
        $this->success = false;
        $this->response = (object) array();
    }

    public function eypiayReturn()
    {
        if ($this->success) {
            $this->code = 200;
        }

        $response = $this->response;
        $response->code = $this->code;
        $response->success = filter_var($this->success, FILTER_VALIDATE_BOOLEAN);
        $response->status = self::STATUS_TEXT[$this->code] ?? null;

        if (!filter_var(config('eypiay.EYPIAY_SHOW_PARAMS'), FILTER_VALIDATE_BOOLEAN)) {
            unset($response->params);
        }

        return response()->json($response, $this->code);
    }
}
