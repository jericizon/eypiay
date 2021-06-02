<?php

namespace Eypiay\Eypiay\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class EypiayBuild extends Command
{
    protected $eypiayPath = '';
    protected $buildPath = '';
    protected $tmpPath = '';

    const OPTIONS = ['get', 'post', 'put', 'patch', 'delete', 'options'];

    const DEFAULT_CONTROLLERS = [
        'get' => 'EypiayGetController::class',
        'post' => 'EypiayPostController::class',
        'put' => 'EypiayPutController::class',
        'patch' => 'EypiayPutController::class',
        'delete' => 'EypiayDeleteController::class',
        'options' => 'EypiayGetController::class',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eypiay:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build eypiay package.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->eypiayPath = base_path(config('eypiay.path'));
        $this->buildPath = "{$this->eypiayPath}/build";
        $this->tmpPath = "{$this->eypiayPath}/tmp";
    }

    public function handle()
    {        // check if directory exists
        if (!File::exists($this->eypiayPath)) {
            $this->error("Cannot find eypiay directory. run php artisan eypiay:install");
            return;
        }

        $files = File::allFiles("{$this->eypiayPath}/src");

        if (count($files) === 0) {
            $this->comment("There are no available src files.");
            return;
        }

        // clean tmp directory
        File::cleanDirectory($this->tmpPath);

        // init route header
        $this->_initBuildFiles();

        foreach ($files as $file) {
            try {
                // echo include $file['']
                // var_dump($file->pathName);                
                $this->_processFile(include $file->getPathname());
            } catch (\Exception $error) {
                $this->error($error->getMessage());
            }
        }

        $this->_postBuildFiles();

        $this->comment('Routes created!');
        $this->_moveFiles();
    }

    private function _initBuildFiles()
    {
        // routes.php
        $this->_appendRoute('<?php' . PHP_EOL);
        $this->_appendRoute('// Eypiay generated route file');
        $this->_appendRoute('use Illuminate\Support\Facades\Route;');
        $this->_appendRoute("use Eypiay\Eypiay\Controllers as EypiayControllers;" . PHP_EOL);

        // db.php
        $this->_appendDbConfig('<?php' . PHP_EOL);
        $this->_appendDbConfig('// Eypiay generated config file' . PHP_EOL);
        $this->_appendDbConfig('return [');

        // request.php
        $this->_appendRequestConfig('<?php' . PHP_EOL);
        $this->_appendRequestConfig('// Eypiay generated config file' . PHP_EOL);
        $this->_appendRequestConfig('return [');
    }

    private function _postBuildFiles()
    {
        $this->_appendRoute(PHP_EOL . PHP_EOL . '// Generated: ' . Carbon::now());

        $this->_appendDbConfig('];');
        $this->_appendDbConfig(PHP_EOL . PHP_EOL . '// Generated: ' . Carbon::now());

        $this->_appendRequestConfig('];');
        $this->_appendRequestConfig(PHP_EOL . PHP_EOL . '// Generated: ' . Carbon::now());
    }

    private function _appendRoute(string $content = '')
    {
        File::append("{$this->tmpPath}/routes.php", $content . PHP_EOL);
    }

    private function _appendDbConfig(string $content = '', $indent = 0)
    {
        $tabs = '';
        for ($i = 0; $i < $indent; $i++) {
            $tabs .= "\t";
        }

        File::append("{$this->tmpPath}/db.php", $tabs . $content . PHP_EOL);
    }

    private function _appendRequestConfig(string $content = '', $indent = 0)
    {
        $tabs = '';
        for ($i = 0; $i < $indent; $i++) {
            $tabs .= "\t";
        }

        File::append("{$this->tmpPath}/request.php", $tabs . $content . PHP_EOL);
    }

    private function _processFile(array $config)
    {
        if (!isset($config['url'])) {
            $this->error('Missing url parameter.');
            return;
        }

        $url = trim($config['url'], '/');

        if (!isset($config['methods'])) {
            // no available method
            $this->error("There are no available methods. [{$url}]");
            return;
        }

        $tableName = $config['database']['table'] ?? basename($url);

        $this->_appendRoute("// {$url}");
        $this->_appendDbConfig("'{$url}' => [", 1);
        $this->_appendRequestConfig("'{$url}' => [", 1);

        // db.php
        $this->_appendDbConfig("'table' => '{$tableName}',", 2);
        $hidden = json_encode($config['database']['hidden'] ?? []);
        $this->_appendDbConfig("'hidden' => {$hidden},", 2);
        $fillable = json_encode($config['database']['fillable'] ?? []);
        $this->_appendDbConfig("'fillable' => {$fillable},", 2);

        // request.php
        $validationsCollection = collect($config['request']['validations'] ?? [])->map(function ($item, $key) {
            return "'{$key}' => '{$item}'";
        })->toArray();

        $validations = json_encode(array_values(array_filter($validationsCollection)) ?? []);
        $validations = str_replace('"', '', $validations);
        $this->_appendRequestConfig("'validations' => {$validations},", 2);

        $castsCollection = collect($config['request']['casts'] ?? [])->map(function ($item, $key) {
            return "'{$key}' => '{$item}'";
        })->toArray();

        $casts = json_encode(array_values(array_filter($castsCollection)) ?? []);
        $casts = str_replace('"', '', $casts);
        $this->_appendRequestConfig("'casts' => {$casts},", 2);

        foreach ($config['methods'] as $methodKey => $methodValue) {

            $routeUrl = $url;

            if (is_numeric($methodKey)) {
                $method = strtolower($methodValue);

                $controller = "[EypiayControllers\\" . self::DEFAULT_CONTROLLERS[$method] . ", '{$method}']";
            } else {
                $method = strtolower($methodKey);
                $controller = "'{$methodValue}'";
            }

            if (!in_array($method, self::OPTIONS)) {
                $this->error("[X] Invalid method [{$method}] for {$url} route.");
                continue;
            }

            if (in_array($method, ['put', 'patch', 'delete'])) {
                $routeUrl .= '/{id}';
            }

            $this->comment("[✓] [{$method}]: " . config('app.url') . "/{$url}");
            $this->_appendRoute("Route::{$method}('/{$routeUrl}', {$controller});");
        }

        $this->_appendDbConfig("],", 1); // closing db.php
        $this->_appendRequestConfig("],", 1); // closing request.php

        $this->line('');
    }

    private function _moveFiles()
    {
        File::cleanDirectory($this->buildPath);
        File::copyDirectory($this->tmpPath, $this->buildPath);
        File::cleanDirectory($this->tmpPath);
    }
}
