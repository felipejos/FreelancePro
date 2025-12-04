<?php

namespace App\Services;

class DocumentParser
{
    public static function parse(string $path, string $originalName = ''): string
    {
        $ext = strtolower(pathinfo($originalName ?: $path, PATHINFO_EXTENSION));
        if ($ext === 'txt') {
            return self::parseTxt($path);
        }
        if ($ext === 'docx') {
            return self::parseDocx($path);
        }
        if ($ext === 'pdf') {
            return self::parsePdf($path);
        }
        if ($ext === 'doc') {
            return self::parseDoc($path);
        }
        return '';
    }

    protected static function parseTxt(string $path): string
    {
        $c = @file_get_contents($path);
        return is_string($c) ? trim($c) : '';
    }

    protected static function parseDocx(string $path): string
    {
        if (!class_exists('ZipArchive')) {
            return '';
        }
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        if ($xml === false) {
            return '';
        }
        $text = strip_tags($xml);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text ?? '');
    }

    protected static function parsePdf(string $path): string
    {
        $binary = self::which('pdftotext');
        if ($binary) {
            $cmd = escapeshellcmd($binary) . ' -enc UTF-8 -q ' . escapeshellarg($path) . ' -';
            $out = @shell_exec($cmd);
            if (is_string($out) && trim($out) !== '') {
                return trim($out);
            }
        }
        return '';
    }

    protected static function parseDoc(string $path): string
    {
        $binary = self::which('antiword');
        if ($binary) {
            $cmd = escapeshellcmd($binary) . ' -m UTF-8.txt ' . escapeshellarg($path);
            $out = @shell_exec($cmd);
            if (is_string($out) && trim($out) !== '') {
                return trim($out);
            }
        }
        return '';
    }

    protected static function which(string $bin): ?string
    {
        $paths = getenv('PATH') ?: '';
        $sep = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? ';' : ':';
        foreach (explode($sep, $paths) as $p) {
            $candidate = rtrim($p, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $bin;
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                foreach (['.exe', '.bat', '.cmd'] as $suf) {
                    if (is_file($candidate . $suf) && is_executable($candidate . $suf)) {
                        return $candidate . $suf;
                    }
                }
            } else {
                if (is_file($candidate) && is_executable($candidate)) {
                    return $candidate;
                }
            }
        }
        return null;
    }
}
