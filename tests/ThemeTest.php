<?php

class ThemeTest extends \Orchestra\Testbench\TestCase {

    protected function getPackageProviders()
    {
        return array('Teepluss\Theme\ThemeServiceProvider');
    }

    protected function getPackageAliases()
    {
        return array(
            'Theme' => 'Teepluss\Theme\Facades\Theme'
        );
    }

    public function testSomethingIsTrue()
    {
        $this->assertTrue(true);
    }

    public function testSomethingIsFalse()
    {
        $this->assertTrue(false);
    }

}