<?php

declare(strict_types = 1);

/**
 * Author   : AVONTURE Christophe.
 *
 * Date     : July 2019
 *
 * Description
 * Wrapper for pandoc
 * Convert a markdown content to multiple formats like DOCX, EPUB,
 * ODT, PDF, TXT, ...
 * See the list of supported formats on
 * https://pandoc.org/MANUAL.html#general-options
 *
 * Notes:
 *
 * - pandoc should first be installed globally
 * - List of supported types is defined in pandoc.json, add yours
 * (as soon as supported by pandoc)
 *
 * @see https://pandoc.org/installing.html
 */

namespace Avonture;

use Exceptions\PandocFileCreationError;
use Exceptions\PandocFileNotFound;
use Exceptions\PandocFileNotSpecified;
use Exceptions\PandocFileProtected;
use Exceptions\PandocFolderNotExists;
use Exceptions\PandocFolderNotWritable;
use Exceptions\PandocNoMarkdownContent;
use Exceptions\PandocNotInstalled;
use Exceptions\PandocOutputTypeNotSupported;
use Exceptions\PandocRunException;
use Exceptions\PandocSettingsNoPandocRootElement;
use Exceptions\PandocSettingsNotDefined;
use Exceptions\PandocSettingsNotFound;
use Exceptions\PandocTemplateNotFound;
use Exceptions\PandocUnsupportedType;
use Helpers\Sanitize;

class Pandoc
{
    const DS = DIRECTORY_SEPARATOR;

    const ERR_FILE_NOT_GENERATED = -1;
    const ERR_TYPE_NOT_SUPPORTED = -2;

    /**
     * Debug mode On/Off.
     *
     * @var bool
     */
    private $debug = false;

    /**
     * Check is pandoc is installed.
     *
     * @var bool
     */
    private $isInstalled = false;

    /**
     * Name of the file with the settings needed for this class.
     *
     * @var string
     */
    private $settingJsonFile = 'pandoc.json';

    /**
     * Markdown content to export to PDF / DOCX / ….
     *
     * @var string
     */
    private $markdown   = '';

    /**
     * Export type: PDF or DOCX or ….
     *
     * @var string
     */
    private $outputType = '';

    /**
     * Fullname of the exported file.
     *
     * @var string
     */
    private $outputFile = '';

    /**
     * Folder where to store files.
     *
     * @var string
     */
    private $outputFolder = '';

    /**
     * Filename of the source file in the output folder
     * Random filename.
     *
     * @var string
     */
    private $sourceFile = '';

    /**
     * Debug file name (empty when debug mode not enabled).
     *
     * @var string
     */
    private $debugFile = '';

    /**
     * List of supported export types.
     *
     * @var array
     */
    private $arrSupportedOutputType = [];

    /**
     * Settings; comes from the pandoc.json file.
     *
     * @var array
     */
    private $arrSettings = [];

    /**
     * Last error encountered. The text of the error can be retrieved
     * by using the getLastError() method.
     *
     * @var int
     */
    private $lastError = 0;

    /**
     * Pandoc command line used.
     *
     * @var string
     */
    private $commandLine = '';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->lastError = 0;
        $this->debugFile = '';
    }

    /**
     * Remove temporary file.
     */
    public function __destruct()
    {
        if (\file_exists($this->sourceFile)) {
            unlink($this->sourceFile);
        }
    }

    /**
     * Return the version of pandoc.
     *
     * @return string
     */
    public function getVersion(): string
    {
        // Make sure pandoc is installed and in the PATH
        $this->isInstalled();

        exec('pandoc --version', $output);

        return trim(str_replace('pandoc', '', $output[0]));
    }

    /**
     * Define the file with the settings.
     *
     * @param string $filename
     *
     * @throws PandocSettingsNotFound
     *
     * @return void
     */
    public function setSettingsFileName(string $filename)
    {
        $filename = trim($filename);

        if (!file_exists($filename)) {
            throw new PandocSettingsNotFound($filename);
        }

        $this->settingJsonFile = trim($filename);
    }

    /**
     * Return the name of the file with the settings.
     *
     * @return string
     */
    public function getSettingsFileName(): string
    {
        return $this->settingJsonFile;
    }

    /**
     * Define the debug mode.
     *
     * @param bool $onOff
     *
     * @return void
     */
    public function setDebugMode(bool $onOff)
    {
        $this->debug = $onOff;
    }

    /**
     * Return the state of the debug flag.
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * Set the markdown content.
     *
     * @param string $content
     *
     * @throws PandocNoMarkdownContent
     *
     * @return void
     *
     * @phan-suppress PhanUnreferencedPublicMethod
     */
    public function setMarkdown(string $content)
    {
        $content = trim($content);

        if ('' === $content) {
            throw new PandocNoMarkdownContent();
        }

        $this->markdown = $content;
    }

    /**
     * Set the export type (pdf, docx, …).
     *
     * @param string $type
     *
     * @throws PandocOutputTypeNotSupported
     *
     * @return void
     *
     * @phan-suppress PhanUnreferencedPublicMethod
     */
    public function setOutputType(string $type)
    {
        if (!in_array($type, $this->arrSupportedOutputType)) {
            $this->lastError = self::ERR_TYPE_NOT_SUPPORTED;

            throw new PandocOutputTypeNotSupported('The output type ' .
                $type . ' isn\'t supported');
        }

        $this->outputType = $type;
    }

    /**
     * Folder where to store files.
     *
     * @param string $folder If empty, use the OS temporary folder
     *
     * @throws PandocFolderNotExists
     *
     * @return void
     *
     * @phan-suppress PhanUnreferencedPublicMethod
     */
    public function setOutputFolder(string $folder)
    {
        if ('' == trim($folder)) {
            $folder = sys_get_temp_dir();
        }

        $folder = str_replace('/', self::DS, $folder);

        $this->outputFolder = rtrim($folder, self::DS) . self::DS;

        if (!is_dir($this->outputFolder)) {
            throw new PandocFolderNotExists('The output folder ' .
                $this->outputFolder . ' doesn\'t exists');
        }

        if (!is_writable($this->outputFolder)) {
            throw new PandocFolderNotWritable($this->outputFolder);
        }
    }

    /**
     * Retrieve the folder where files have been stored.
     *
     *
     * @return string
     *
     * @phan-suppress PhanUnreferencedPublicMethod
     */
    public function getOutputFolder(): string
    {
        return $this->outputFolder;
    }

    /**
     * Read the settings from a .json file.
     *
     * @param string $filename Name of the settings file.
     *                         Optional if the class has been created with the
     *                         filename (in the constructor)
     *
     * @throws PandocSettingsNotFound
     *
     * @return void
     */
    public function readSettings(string $filename = ''): void
    {
        if ('' == $filename) {
            $filename = $this->settingJsonFile;

            if (!file_exists($filename)) {
                throw new PandocSettingsNotFound($filename);
            }
        }

        try {
            $json = file_get_contents($filename);
        } catch (\Exception $e) {
            // Invalid JSON string
            die($e->getMessage());
        }

        $this->readSettingsFromJson($json);
    }

    /**
     * Read the settings from a json inline string.
     *
     * @param string $json The settings in a JSON format
     *
     * @throws PandocSettingsNotDefined, PandocSettingsNoPandocRootElement
     *
     * @return void
     */
    public function readSettingsFromJson(string $json = ''): void
    {
        if ('' === trim($json)) {
            throw new PandocSettingsNotDefined();
        }

        $this->arrSettings = json_decode($json, true);

        if ([] == $this->arrSettings) {
            // The conversion to an associative array has failed
            throw new PandocSettingsNotDefined();
        }

        // Settings should be placed into a "pandoc" element
        if (!isset($this->arrSettings['pandoc'])) {
            throw new PandocSettingsNoPandocRootElement();
        }

        $this->arrSettings = $this->arrSettings['pandoc'];

        // Set the debug mode, based on the debug node
        $debug = $this->arrSettings['debug'] ?? false;
        $this->setDebugMode(boolval($debug));

        // Retrieve the folder where to store files
        // default is the OK temporary folder
        $folder = $this->arrSettings['output']['folder'] ?? '';
        $this->setOutputFolder($folder);

        // Get the list of supported types (docx, odt, pdf, …)
        $this->arrSupportedOutputType = $this->arrSettings['supported_types'] ?? [];
    }

    /**
     * Return the created filename, only the basename.
     * To get the full path, also use getOutputFolder() for
     * retrieving the folder where the file has been stored.
     *
     * @return string
     */
    public function getOutputFile(): string
    {
        return str_replace($this->outputFolder, '', $this->outputFile);
    }

    /**
     * Return the last encountered error text.
     *
     * @return string
     */
    public function getLastError(): string
    {
        $msg = '';

        switch ($this->lastError) {
            case self::ERR_FILE_NOT_GENERATED:
                $msg = 'An error has occurred during the creation ' .
                    'of ' . $this->outputFile;

                break;
            case self::ERR_TYPE_NOT_SUPPORTED:
                $msg = 'Export type ' . $this->outputType . ' ' .
                    'not supported';

                break;
            default:
                break;
        }

        return $msg;
    }

    /**
     * Check if Pandoc is installed.
     *
     * @throws PandocNotInstalled
     *
     * @return bool
     */
    public function isInstalled(): bool
    {
        exec('pandoc --version', $output, $returnVar);
        if (0 === $returnVar) {
            // $output[0] is the first row of the output
            // We can retrieve the pandoc version like f.i.
            // "pandoc 1.19.2.1"
            $this->isInstalled = true;

            return true;
        } else {
            throw new PandocNotInstalled();
        }
    }

    /**
     * Run the conversion.
     *
     * @throws PandocFileProtected,
     *
     * @return bool
     */
    public function doIt(): bool
    {
        // Make sure pandoc executable is installed and in the PATH
        $this->isInstalled();

        // Generate a temporary filename, use that file for our
        // source file
        $this->sourceFile = tempnam($this->outputFolder, 'md2' . $this->outputType);
        unlink($this->sourceFile);

        // Put the markdown content into a temporary file
        $this->makeMDFilestring();

        $this->outputFile = $this->outputFolder . 'export.' . $this->outputType;

        if (is_file($this->outputFile)) {
            try {
                unlink($this->outputFile);
            } catch (\Exception $e) {
                throw new PandocFileProtected($this->outputFile);
            }
        }

        $output    = '';
        $return    = 0;

        exec(escapeshellcmd($this->makeCommandLine()), $output, $return);

        if (0 === $return) {
            try {
                unlink($this->sourceFile);
            } catch (\Exception $e) {
                throw new PandocFileProtected($this->sourceFile);
            }

            if (!is_file($this->outputFile)) {
                $this->lastError = self::ERR_FILE_NOT_GENERATED;
            }

            return is_file($this->outputFile);
        } else {
            $msg = sprintf('Pandoc could not convert successfully, ' .
                'error code: %d. Tried to run the following ' .
                'command: %s', $return, $this->makeCommandLine());

            throw new PandocRunException($msg);
        }
    }

    /**
     * Get the generated file and download it.
     *
     * @param string $filename
     *
     * @throws PandocFileNotSpecified
     *
     * @return void
     */
    public function download(string $filename): void
    {
        $filename = Sanitize::sanitizeFileName(trim($filename));

        if ('' == $filename) {
            throw new PandocFileNotSpecified();
        }

        if (!is_file($this->outputFolder . $filename)) {
            throw new PandocFileNotFound($filename);
        }

        // Make the filename absolute
        $filename = $this->outputFolder . ltrim($filename, self::DS);

        $this->outputType = pathinfo($filename)['extension'];

        $contentType = $this->getContentType()['type'];

        header('Content-Type: ' . $contentType);
        header('Content-Transfer-Encoding: ' . $this->getContentType()['encoding']);

        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize(utf8_decode($filename)));
        header('Accept-Ranges: bytes');
        header('Pragma: no-cache');
        header('Expires: 0');

        ob_end_flush();

        @readfile(utf8_decode($filename));
    }

    /**
     * Return the used command line.
     *
     * @return string
     */
    public function getCommandLine(): string
    {
        return $this->commandLine;
    }

    /**
     * Create the text file with the markdown content.
     *
     * @throws PandocFileCreationError
     *
     * @return bool
     */
    private function makeMDFilestring(): bool
    {
        //if ('txt' !== $this->outputType) {
        \file_put_contents($this->sourceFile, $this->markdown);
        /*} else {
            $id = \fopen($this->sourceFile, 'wb');
            \fwrite($id, utf8_encode($this->markdown));
            \fclose($id);
        }*/

        if (!is_file($this->sourceFile)) {
            throw new PandocFileCreationError($this->sourceFile);
        }

        return true;
    }

    /**
     * Return the content-type header to use for the selected
     * output type.
     *
     * Can't be empty… The pandoc.json should define how the
     * file should be processed by the browser.
     *
     * If empty, consider the output type not supported
     *
     * @throws PandocUnsupportedType
     *
     * @return array
     */
    private function getContentType(): array
    {
        $type     = '';
        $encoding = '';

        if (isset($this->arrSettings['export'][$this->outputType])) {
            $output  = $this->arrSettings['export'][$this->outputType];
            if (isset($output['content'])) {
                $type     = $output['content']['type'] ?? '';
                $encoding = $output['content']['encoding'] ?? 'binary';
            }
        }

        if ('' == $type) {
            throw new PandocUnsupportedType($this->outputType);
        }

        return ['type' => $type, 'encoding' => $encoding];
    }

    /**
     * Retrieve from the settings the template to use.
     * For instance, when converting to docx, a template can be
     * defined in pandoc.json.
     *
     * ```json
     * {
     *      "export": {
     *          "docx": {
     *              "template": "template/tmpl.docx",
     *          },
     *      }
     * }
     * ```
     *
     * @throws PandocTemplateNotFound
     *
     * @return string
     */
    private function getTemplate(): string
    {
        $tmpl = '';

        if (isset($this->arrSettings['export'][$this->outputType])) {
            $output = $this->arrSettings['export'][$this->outputType];
            if (isset($output['template'])) {
                $tmp = trim($output['template']);
                if ('' !== $tmp) {
                    if (!file_exists($tmp)) {
                        // Make sure the file exists
                        throw new PandocTemplateNotFound($tmp);
                    }

                    // if yes, use the pandoc command line argument
                    // and indicate the template to use
                    $tmpl = ' --reference-doc "' . $tmp . '"';
                }
            }
        }

        return $tmpl;
    }

    /**
     * Return the command line argument to use for adding a
     * table of content in the generated document.
     *
     * @return string
     */
    private function getTableOfContents(): string
    {
        $toc = '';

        if (isset($this->arrSettings['export'][$this->outputType])) {
            $output = $this->arrSettings['export'][$this->outputType];
            if (isset($output['table-of-contents'])) {
                $bOnOff = boolval($output['table-of-contents']);
                if ($bOnOff) {
                    $toc = ' --table-of-contents';
                }
            }
        }

        return $toc;
    }

    /**
     * Generate the command line to use for pandoc.
     *
     * @return string
     */
    private function makeCommandLine(): string
    {
        $template = $this->getTemplate();
        $toc      = $this->getTableOfContents();

        // Define the debug log filename
        if ($this->debug) {
            $this->debugFile =  ' > "' . $this->outputFolder . 'debug.log" 2>&1';
        }

        $this->commandLine =
            // Don't specify the ".exe" extension to make it works on Linux
            'pandoc ' .
            // "-s" to produce a standalone file
            '-s ' .
            // Source filename; format is markdown
            '-f markdown "' . $this->sourceFile . '" ' .
            // Output filename
            '-o "' . $this->outputFile . '"' .
            // Add command line arguments (template, table of contents, …)
            $template .
            $toc .
            // Redirect output to a debug file if debug mode is enabled
            $this->debugFile;

        return $this->commandLine;
    }
}
