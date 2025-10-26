<?php

namespace App\Utils;

use App\Utils\Logging\Logger;

class SmbHelper
{
    public static function listDirectory(string $fullPath, bool $recursive = false): array|false
    {
        Logger::debug("Liste Verzeichnis auf", [
            'path' => $fullPath,
            'recursive' => $recursive,
        ]);

        if (!@is_dir($fullPath)) {
            Logger::db('smb', 'error', "Verzeichnis nicht gefunden", [
                'path' => $fullPath,
            ]);
            return false;
        }

        $items = @scandir($fullPath);

        if ($items === false) {
            $error = error_get_last();
            Logger::db('smb', 'error', "Fehler beim Auflisten des Verzeichnisses", [
                'path' => $fullPath,
                'error' => $error['message'] ?? 'Unbekannt',
            ]);
            return false;
        }

        $result = [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $itemPath = $fullPath . DIRECTORY_SEPARATOR . $item;
            $isDir = is_dir($itemPath);

            $entry = [
                'name' => $item,
                'path' => $itemPath,
                'type' => $isDir ? 'directory' : 'file',
                'size' => $isDir ? null : @filesize($itemPath),
            ];

            $result[] = $entry;

            // Rekursiv
            if ($recursive && $isDir) {
                $subItems = self::listDirectory($itemPath, true);
                if ($subItems !== false) {
                    $result = array_merge($result, $subItems);
                }
            }
        }

        Logger::debug("Verzeichnis aufgelistet", [
            'path' => $fullPath,
            'count' => count($result),
        ]);

        return $result;
    }

    public static function delete(string $fullPath): bool
    {
        Logger::debug("Lösche Pfad", ['path' => $fullPath]);

        if (!@file_exists($fullPath)) {
            Logger::db('smb', 'error', "Pfad nicht gefunden", ['path' => $fullPath]);
            return false;
        }

        try {
            if (is_dir($fullPath)) {
                self::deleteDirectory($fullPath);
                Logger::debug("Verzeichnis gelöscht", ['path' => $fullPath]);
            } else {
                if (!@unlink($fullPath)) {
                    throw new \Exception("Datei konnte nicht gelöscht werden");
                }
                Logger::debug("Datei gelöscht", ['path' => $fullPath]);
            }

            return true;
        } catch (\Exception $e) {
            Logger::db('smb', 'error', "Fehler beim Löschen", [
                'path' => $fullPath,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    protected static function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            throw new \Exception("Kein Verzeichnis: {$dir}");
        }

        $items = @scandir($dir);
        if ($items === false) {
            throw new \Exception("Verzeichnis kann nicht gelesen werden: {$dir}");
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $path = $dir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                self::deleteDirectory($path);
            } else {
                if (!@unlink($path)) {
                    throw new \Exception("Datei kann nicht gelöscht werden: {$path}");
                }
            }
        }

        if (!@rmdir($dir)) {
            throw new \Exception("Verzeichnis kann nicht gelöscht werden: {$dir}");
        }
    }

    public static function exists(string $fullPath): bool
    {
        return @file_exists($fullPath);
    }

    public static function isDirectory(string $fullPath): bool
    {
        return @is_dir($fullPath);
    }

    public static function isFile(string $fullPath): bool
    {
        return @is_file($fullPath);
    }
}