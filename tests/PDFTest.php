<?php

declare(strict_types = 1);

use Avonture\Pandoc;
use PHPUnit\Framework\TestCase;

final class PDFTest extends TestCase
{
    /**
     * @var Avonture\Pandoc
     */
    private $pandoc = null;

    /**
     * Convert MD to PDF.
     *
     * @return void
     */
    public function testCanConvertToPdf(): void
    {
        $this->pandoc->readSettingsFromJson(
            '{
                "pandoc": {
                    "supported_types": [
                        "pdf"
                    ],
                    "export": {
                        "pdf": {
                            "content": {
                                "encoding": "binary",
                                "type": "application/pdf"
                            }
                        }
                    }
                }
            }'
        );

        $md = file_get_contents(__DIR__ . '/demo/sample.md');

        $this->pandoc->setMarkdown($md);
        $this->pandoc->setOutputType('pdf');

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
