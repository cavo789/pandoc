<?php

declare(strict_types = 1);

use Avonture\Pandoc;
use PHPUnit\Framework\TestCase;

final class EPUBTest extends TestCase
{
    /**
     * @var Avonture\Pandoc
     */
    private $pandoc = null;

    /**
     * Convert MD to EPUB.
     *
     * @return void
     */
    public function testCanConvertToEpub(): void
    {
        $this->pandoc->readSettingsFromJson(
            '{
                "pandoc": {
                    "supported_types": [
                        "epub"
                    ],
                    "export": {
                        "epub": {
                            "content": {
                                "encoding": "binary",
                                "type": "application/epub"
                            }
                        }
                    }
                }
            }'
        );

        $md = file_get_contents(__DIR__ . '/demo/sample.md');

        $this->pandoc->setMarkdown($md);
        $this->pandoc->setOutputType('epub');

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
