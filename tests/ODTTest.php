<?php

declare(strict_types = 1);

use Avonture\Pandoc;
use PHPUnit\Framework\TestCase;

final class ODTTest extends TestCase
{
    /**
     * @var Avonture\Pandoc
     */
    private $pandoc = null;

    /**
     * Convert MD to ODT.
     *
     * @return void
     */
    public function testCanConvertToOdt(): void
    {
        $this->pandoc->readSettingsFromJson(
            '{
                "pandoc": {
                    "supported_types": [
                        "odt"
                    ],
                    "export": {
                        "odt": {
                            "content": {
                                "encoding": "binary",
                                "type": "application/vnd.oasis.opendocument.text"
                            }
                        }
                    }
                }
            }'
        );

        $md = file_get_contents(__DIR__ . '/demo/sample.md');

        $this->pandoc->setMarkdown($md);
        $this->pandoc->setOutputType('odt');

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
