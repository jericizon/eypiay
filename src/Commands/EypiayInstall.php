<?php

namespace Eypiay\Eypiay\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class EypiayInstall extends Command
{
    protected $eypiayPath = '';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eypiay:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install eypiay package.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->eypiayPath = base_path(config('eypiay.path'));
    }

    private function _initDirectory()
    {
        try {
            if (!File::exists($this->eypiayPath)) {
                File::makeDirectory($this->eypiayPath);
            }

            if (!File::exists("{$this->eypiayPath}/build")) {
                File::makeDirectory("{$this->eypiayPath}/build");
            }

            if (!File::exists("{$this->eypiayPath}/src")) {
                File::makeDirectory("{$this->eypiayPath}/src");
            }

            if (!File::exists("{$this->eypiayPath}/tmp")) {
                File::makeDirectory("{$this->eypiayPath}/tmp");
            }
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    public function handle()
    {
        $initStatus = $this->_initDirectory();
        if ($initStatus) {
            $this->error($initStatus);
            return;
        }
        // check if directory exists
        if (!File::exists($this->eypiayPath)) {
            $this->error("Cannot find eypiay directory: {$this->eypiayPath}");
            return;
        }
    }
}
