<?php

class PhpAnalyzer
{
    public function analyze(string $rootPath, string $targetFile): array
    {
        if (!file_exists($targetFile)) {
            throw new Exception("Target file not found: " . $targetFile);
        }

        $absolutePath = realpath($targetFile);

        $loc = $this->calculateLoc($absolutePath);

        // 파일 전체 내용 읽기
        $content = file_get_contents($absolutePath);
        if ($content === false) {
            throw new Exception("Failed to read file: " . $absolutePath);
        }

        // outbound_count 계산 (include/require 개수)
        $outboundCount = $this->calculateOutboundCount($content);
        $globalCount = $this->calculateGlobalCount($content);
        $queryCount = $this->calculateQueryCount($content);
        $tables = $this->extractTables($content);
        
        $allFiles = $this->scanPhpFiles($rootPath);

        $inboundPaths = $this->calculateInbound($allFiles, $absolutePath);
        $inboundCount = count($inboundPaths);
        
        $sameTableUsers = $this->calculateSameTableUsers($allFiles, $tables, $absolutePath);
        
        return [
            "schema_version" => "1.0",
            "target" => [
                "path" => $absolutePath,
                "language" => "php"
            ],
            "metrics" => [
                "complexity" => [
                    "loc" => $loc
                ],
                "dependency" => [
                    "inbound_count" => $inboundCount,
                    "outbound_count" => $outboundCount,
                    "inbound_paths" => $inboundPaths
                ],
                "db" => [
                    "tables" => $tables,
                    "query_count" => $queryCount,
                    "same_table_users" => $sameTableUsers
                ],
                "globals" => [
                    "count" => $globalCount
                ]
            ]
        ];
    }

    private function calculateLoc(string $filePath): int
    {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return 0;
        }
        return count($lines);
    }

    /**
     * include / require / include_once / require_once 개수 계산
     */
    private function calculateOutboundCount(string $content): int
    {
        $pattern = '/\b(include|require|include_once|require_once)\b/i';

        preg_match_all($pattern, $content, $matches);

        return count($matches[0]);
    }

    /**
     * global 키워드 개수 계산
     */
    private function calculateGlobalCount(string $content): int
    {
        $pattern = '/\bglobal\b/i';

        preg_match_all($pattern, $content, $matches);

        return count($matches[0]);
    }

    /**
     * SQL 쿼리 키워드 개수 계산 (초안 버전)
     * SELECT, INSERT, UPDATE, DELETE 기준
     */
    private function calculateQueryCount(string $content): int
    {
        $pattern = '/\b(SELECT|INSERT|UPDATE|DELETE)\b/i';

        preg_match_all($pattern, $content, $matches);

        return count($matches[0]);
    }
    
    /**
     * SQL에서 테이블 이름 추출 (초안 버전)
     */
    private function extractTables(string $content): array
    {
        $tables = [];

        $patterns = [
            '/\bFROM\s+([a-zA-Z0-9_]+)/i',
            '/\bINSERT\s+INTO\s+([a-zA-Z0-9_]+)/i',
            '/\bUPDATE\s+([a-zA-Z0-9_]+)/i',
            '/\bDELETE\s+FROM\s+([a-zA-Z0-9_]+)/i',
        ];

        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $content, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $table) {
                    $tables[] = $table;
                }
            }
        }

        // 중복 제거
        return array_values(array_unique($tables));
    }

    /**
     * rootPath 아래 모든 PHP 파일 재귀 탐색
     */
    private function scanPhpFiles(string $rootPath): array
    {
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
                $files[] = $file->getRealPath();
            }
        }

        return $files;
    }

    /**
     * targetFile을 직접 참조하는 파일 목록 계산
     */
    private function calculateInbound(array $allFiles, string $targetFile): array
    {
        $inboundPaths = [];
        $targetName = basename($targetFile);

        foreach ($allFiles as $file) {

            // 자기 자신 제외
            if ($file === $targetFile) {
                continue;
            }

            $content = file_get_contents($file);
            if ($content === false) {
                continue;
            }

            // 단순 basename 포함 여부 체크 (초안)
            if (preg_match('/\b(include|require|include_once|require_once)\b.*' . preg_quote($targetName, '/') . '/i', $content)) {
                $inboundPaths[] = $file;
            }
        }

        return array_values(array_unique($inboundPaths));
    }

    
    /**
     * 동일 테이블을 사용하는 다른 파일 탐지 (초안)
     */
    private function calculateSameTableUsers(array $allFiles, array $tables, string $targetFile): array
    {
        $result = [];

        foreach ($tables as $table) {
            $result[$table] = [];

            foreach ($allFiles as $file) {

                // 자기 자신 제외
                if ($file === $targetFile) {
                    continue;
                }

                $content = file_get_contents($file);
                if ($content === false) {
                    continue;
                }

                // 테이블 이름 포함 여부 단순 체크
                if (preg_match('/\b' . preg_quote($table, '/') . '\b/i', $content)) {
                    $result[$table][] = $file;
                }
            }

            // 중복 제거
            $result[$table] = array_values(array_unique($result[$table]));
        }

        return $result;
    }
}