<?php

namespace Eypiay\Eypiay\Tests\Unit;

use Eypiay\Eypiay\Tests\TestCase;
use Illuminate\Support\Facades\File;

class InitialTest extends TestCase
{

    const EYPIAY_APP_PATH = 'app/Eypiay';
    const DUMMMY_FILES = './api-src';

    private function _copyDemoData()
    {
        File::cleanDirectory(base_path(config('eypiay.EYPIAY_PATH') . '/src'));
        File::cleanDirectory(base_path(config('eypiay.EYPIAY_PATH') . '/build'));
        File::copyDirectory(self::DUMMMY_FILES, base_path(config('eypiay.EYPIAY_PATH') . '/src'));
    }

    public function test_install_data()
    {
        \Artisan::call('eypiay:install');
        $this->_copyDemoData();
        $this->assertTrue(true);
    }

    public function test_build_files()
    {
        \Artisan::call('eypiay:build');
        // dd(\Artisan::output());
        $this->assertTrue(true);
    }
}
