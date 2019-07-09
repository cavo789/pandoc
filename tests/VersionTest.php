<?php

declare(strict_types = 1);

use Avonture\Pandoc;
use PHPUnit\Framework\TestCase;

final class VersionTest extends TestCase
{
    /**
     * @var Avonture\Pandoc
     */
    private $pandoc = null;

    /**
     * The version number of pandoc needs to be a valid version number.
     *
     * @return void
     */
    public function testVersionNumberIsAValidOne(): void
    {
        $version = $this->pandoc->getVersion();
        $this->assertTrue(version_compare($version, '0.0.1', '>='));
    }

    protected function setUp(): void
    {
        $this->pandoc = new Pandoc();
    }

    protected function tearDown(): void
    {
        unset($this->pandoc);
    }
}
