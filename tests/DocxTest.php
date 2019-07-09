<?php

declare(strict_types = 1);

use Avonture\Pandoc;
use PHPUnit\Framework\TestCase;

final class DocxTest extends TestCase
{
    /**
     * @var Avonture\Pandoc
     */
    private $pandoc = null;

    /**
     * Check that pandoc is well installed and accessible (i.e. in the
     * PATH).
     *
     * @return void
     */
    public function testIsInstalled(): void
    {
        $this->assertTrue($this->pandoc->isInstalled());
    }

    /**
     * Convert MD to DOCX.
     *
     * @return void
     */
    public function testCanConvertToWord(): void
    {
        $this->pandoc->readSettingsFromJson(
            '{
                "pandoc": {
                    "supported_types": [
                        "docx"
                    ],
                    "export": {
                        "docx": {
                            "content": {
                                "encoding": "binary",
                                "type": "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                            }
                        }
                    }
                }
            }'
        );

        $md = file_get_contents(__DIR__ . '/demo/sample.md');

        $this->pandoc->setMarkdown($md);
        $this->pandoc->setOutputType('docx');

        // Should be successfull
        $this->assertTrue($this->pandoc->doIt());

        // Get the exported filename
        $this->assertNotEmpty($this->pandoc->getOutputFile());

        // No error should be returned
        $this->assertEmpty($this->pandoc->getLastError());
    }

    /**
     * Convert MD to DOCX with a table-of-content.
     *
     * @return void
     */
    public function testCanConvertToWordWithTOC(): void
    {
        $this->pandoc->readSettingsFromJson(
            '{
                "pandoc": {
                    "supported_types": [
                        "docx"
                    ],
                    "export": {
                        "docx": {
                            "table-of-contents": 1,
                            "content": {
                                "encoding": "binary",
                                "type": "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                            }
                        }
                    }
                }
            }'
        );

        $md = file_get_contents(__DIR__ . '/demo/sample.md');

        $this->pandoc->setMarkdown($md);
        $this->pandoc->setOutputType('docx');

        // Should be successfull
        $this->assertTrue($this->pandoc->doIt());

        // Get the exported filename
        $this->assertNotEmpty($this->pandoc->getOutputFile());

        // No error should be returned
        $this->assertEmpty($this->pandoc->getLastError());

        // Make sure pandoc is called with the --table-of-contents
        // argument
        $cmd = $this->pandoc->getCommandLine();
        $this->assertStringContainsString('--table-of-contents', $cmd);
    }

    /**
     * Convert MD to DOCX without a table-of-content.
     *
     * @return void
     */
    public function testCanConvertToWordNoTOC(): void
    {
        $this->pandoc->readSettingsFromJson(
            '{
                "pandoc": {
                    "supported_types": [
                        "docx"
                    ],
                    "export": {
                        "docx": {
                            "table-of-contents": 0,
                            "content": {
                                "encoding": "binary",
                                "type": "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                            }
                        }
                    }
                }
            }'
        );

        $md = file_get_contents(__DIR__ . '/demo/sample.md');

        $this->pandoc->setMarkdown($md);
        $this->pandoc->setOutputType('docx');

        // Should be successfull
        $this->assertTrue($this->pandoc->doIt());

        // Get the exported filename
        $this->assertNotEmpty($this->pandoc->getOutputFile());

        // No error should be returned
        $this->assertEmpty($this->pandoc->getLastError());

        // Make sure pandoc is not called with the --table-of-contents
        // argument
        $cmd = $this->pandoc->getCommandLine();
        $this->assertStringNotContainsString('--table-of-contents', $cmd);
    }

    /**
     * Convert MD to DOCX with a template.
     *
     * @return void
     */
    public function testCanConvertToWordWithTemplate(): void
    {
        $this->pandoc->readSettingsFromJson(
            '{
                "pandoc": {
                    "supported_types": [
                        "docx"
                    ],
                    "export": {
                        "docx": {
                            "template": "tests/demo/template.docx",
                            "content": {
                                "encoding": "binary",
                                "type": "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                            }
                        }
                    }
                }
            }'
        );

        $md = file_get_contents(__DIR__ . '/demo/sample.md');

        $this->pandoc->setMarkdown($md);
        $this->pandoc->setOutputType('docx');

        // Should be successfull
        $this->assertTrue($this->pandoc->doIt());

        // Get the exported filename
        $this->assertNotEmpty($this->pandoc->getOutputFile());

        // No error should be returned
        $this->assertEmpty($this->pandoc->getLastError());

        // Make sure pandoc is called with the template to use
        $cmd = $this->pandoc->getCommandLine();
        $this->assertStringContainsString('--reference-doc "tests/demo/template.docx"', $cmd);
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
