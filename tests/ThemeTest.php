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
        //Theme::uses('default');
    }

    // public function testSomethingElseIsTrue()
    // {
    //     $this->assertTrue(false);
    // }

}