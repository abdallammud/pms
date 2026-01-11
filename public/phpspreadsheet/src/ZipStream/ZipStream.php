<?php
/**
 * ZipStream Stub
 * Uses PHP's ZipArchive to satisfy PHPSpreadsheet's dependency on ZipStream
 */

namespace ZipStream;

class ZipStream
{
    private $zip;
    private $tempFile;
    private $outputStream;

    public function __construct(
        bool $enableZip64 = false,
        $outputStream = null,
        bool $sendHttpHeaders = false,
        bool $defaultEnableZeroHeader = false,
        $storage = null,
        $flush = null,
        $computeChecksum = null
    ) {
        $this->outputStream = $outputStream;
        $this->tempFile = tempnam(sys_get_temp_dir(), 'zip');
        $this->zip = new \ZipArchive();

        if ($this->zip->open($this->tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Cannot create temporary zip file");
        }
    }

    public function addFile(string $fileName, string $data, $options = null): void
    {
        $this->zip->addFromString($fileName, $data);
    }

    public function finish(): void
    {
        $this->zip->close();

        if ($this->outputStream) {
            // Copy temp file to output stream
            $handle = fopen($this->tempFile, 'rb');
            if ($handle) {
                stream_copy_to_stream($handle, $this->outputStream);
                fclose($handle);
            }
        } else {
            readfile($this->tempFile);
        }

        if (file_exists($this->tempFile)) {
            @unlink($this->tempFile);
        }
    }
}
