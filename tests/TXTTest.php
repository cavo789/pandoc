<?php

declare(strict_types = 1);

use Avonture\Pandoc;
use PHPUnit\Framework\TestCase;

final class TXTTest extends TestCase
{
    /**
     * @var Avonture\Pandoc
     */
    private $pandoc = null;

    /**
     * Convert MD to TXT.
     *
     * @return void
     */
    public function testCanConvertToTxt(): void
    {
        $this->pandoc->readSettingsFromJson(
            '{
                "pandoc": {
                    "supported_types": [
                        "txt"
                    ],
                    "export": {
                        "txt": {
                            "content": {
                                "encoding": "ascii",
                                "type": "text/plain"
                            }
                        }
                    }
                }
            }'
        );

        $md = file_get_contents(__DIR__ . '/demo/sample.md');

        $this->pandoc->setMarkdown($md);
        $this->pandoc->setOutputType('txt');

        // Should be successfull
        $this->assertTrue($this->pandoc->doIt());

        // Get the exported filename
        $this->assertNotEmpty($this->pandoc->getOutputFile());

        // No error should be returned
        $this->assertEmpty($this->pandoc->getLastError());
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
