<?php

class PhpAnalyzer
{
    public function analyze($rootPath, $targetFile)
    {
        if (!file_exists($targetFile)) {
            throw new Exception("Target file not found: " . $targetFile);
        }

        $absolutePath = realpath($targetFile);
        $absoluteRoot = realpath($rootPath);

        $loc = $this->calculateLoc($absolutePath);

        // 파일 전체 내용 읽기
        $content = file_get_contents($absolutePath);
        if ($content === false) {
            throw new Exception("Failed to read file: " . $absolutePath);
        }

        // 주석만 제거 (문자열은 유지 — SQL이 문자열 안에 있으므로)
        $cleanContent = $this->stripCommentsOnly($content);

        $outboundCount = $this->calculateOutboundCount($cleanContent);
        $globalCount   = $this->calculateGlobalCount($cleanContent);
        $queryCount    = $this->calculateQueryCount($cleanContent);
        $tables        = $this->extractTables($cleanContent);

        // 스트리밍 방식: 파일을 한 번 순회하면서 inbound + same_table_users 동시 탐지
        // 13,000+ 파일을 메모리에 캐시하지 않고 한 번 읽고 바로 분석
        $scanResult = $this->scanAndAnalyze($absoluteRoot, $absolutePath, $tables);

        $inboundPaths   = $scanResult['inbound_paths'];
        $inboundCount   = count($inboundPaths);
        $sameTableUsers = $scanResult['same_table_users'];

        // 출력용 상대경로 변환
        $relativePath = $this->toRelativePath($absolutePath, $absoluteRoot);
        $relativeInbound = array_map(
            function($p) use ($absoluteRoot) { return $this->toRelativePath($p, $absoluteRoot); },
            $inboundPaths
        );
        $relativeSameTable = [];
        foreach ($sameTableUsers as $table => $files) {
            $relativeSameTable[$table] = array_map(
                function($p) use ($absoluteRoot) { return $this->toRelativePath($p, $absoluteRoot); },
                $files
            );
        }

        return [
            "schema_version" => "1.0",
            "target" => [
                "path"     => $relativePath,
                "language" => "php"
            ],
            "metrics" => [
                "complexity" => [
                    "loc" => $loc
                ],
                "dependency" => [
                    "inbound_count"  => $inboundCount,
                    "outbound_count" => $outboundCount,
                    "inbound_paths"  => $relativeInbound
                ],
                "db" => [
                    "tables"           => $tables,
                    "query_count"      => $queryCount,
                    "same_table_users" => $relativeSameTable
                ],
                "globals" => [
                    "count" => $globalCount
                ]
            ]
        ];
    }

    /**
     * 절대경로를 rootPath 기준 상대경로로 변환
     */
    private function toRelativePath($absolutePath, $rootPath)
    {
        $root = rtrim($rootPath, '/') . '/';
        if (strpos($absolutePath, $root) === 0) {
            return substr($absolutePath, strlen($root));
        }
        return $absolutePath;
    }

    // -------------------------------------------------------------------------
    // 주석 제거 (SQL 분석 정확도 향상)
    // -------------------------------------------------------------------------

    /**
     * PHP 주석(한 줄 주석: //, # / 블록 주석: /* ... *&#47;)만 제거하고 문자열은 유지한다.
     * SQL은 문자열 안에 존재하므로 문자열을 유지해야 정상 감지된다.
     * 주석 제거로 주석 안의 SQL/include/global 오탐을 방지한다.
     */
    private function stripCommentsOnly($content)
    {
        $tokens = token_get_all($content);
        $result = '';

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $type = $token[0];
                $text = $token[1];

                if ($type === T_COMMENT || $type === T_DOC_COMMENT) {
                    $result .= str_repeat("\n", substr_count($text, "\n"));
                    continue;
                }

                $result .= $text;
            } else {
                $result .= $token;
            }
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // 기본 메트릭 계산
    // -------------------------------------------------------------------------

    private function calculateLoc($filePath)
    {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return 0;
        }
        return count($lines);
    }

    /**
     * include / require / include_once / require_once 개수 계산
     * (주석 제거 후 content 사용 — 주석 안 include 오탐 방지)
     */
    private function calculateOutboundCount($content)
    {
        preg_match_all('/\b(include|require|include_once|require_once)\b/i', $content, $matches);
        return count($matches[0]);
    }

    /**
     * global 키워드 개수 계산
     */
    private function calculateGlobalCount($content)
    {
        preg_match_all('/\bglobal\b/i', $content, $matches);
        return count($matches[0]);
    }

    /**
     * SQL 쿼리 키워드 개수 계산 (주석 제거 후 content 사용)
     */
    private function calculateQueryCount($cleanContent)
    {
        preg_match_all('/\b(SELECT|INSERT|UPDATE|DELETE)\b/i', $cleanContent, $matches);
        return count($matches[0]);
    }

    /**
     * SQL 테이블 이름 추출 (주석 제거 후 content 사용)
     * JOIN 절, 백틱(`), DB.table 형식 대응
     *
     * DB.table 형식 (예: JUVIS2.j2t_staff) 에서는 DB명을 건너뛰고
     * 실제 테이블명(j2t_staff)만 추출한다.
     */
    private function extractTables($cleanContent)
    {
        $tables = [];

        // 테이블명 패턴: 선택적 DB접두어(db.)를 건너뛰고 실제 테이블명만 캡처
        // 예: JUVIS2.j2t_staff → j2t_staff, `orders` → orders
        $tablePattern = '`?(?:[a-zA-Z0-9_]+\.)?`?`?([a-zA-Z0-9_]+)`?';

        $patterns = [
            '/\bFROM\s+' . $tablePattern . '/i',
            '/\bJOIN\s+' . $tablePattern . '/i',
            '/\bINSERT\s+INTO\s+' . $tablePattern . '/i',
            '/\bUPDATE\s+' . $tablePattern . '/i',
            '/\bDELETE\s+FROM\s+' . $tablePattern . '/i',
        ];

        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $cleanContent, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $table) {
                    $tables[] = strtolower($table);
                }
            }
        }

        return array_values(array_unique($tables));
    }

    // -------------------------------------------------------------------------
    // 파일 탐색 및 캐싱
    // -------------------------------------------------------------------------

    /**
     * rootPath 아래 모든 PHP 파일 재귀 탐색
     * 불필요한 디렉토리(.git, vendor, node_modules 등) 제외
     */
    private function scanPhpFiles($rootPath)
    {
        $files = [];
        $excludeDirs = array('.git', 'vendor', 'node_modules', '.svn', '.claude');

        $directory = new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS);

        $filter = new RecursiveCallbackFilterIterator($directory, function($current, $key, $iterator) use ($excludeDirs) {
            if ($current->isDir()) {
                return !in_array($current->getFilename(), $excludeDirs);
            }
            return true;
        });

        $iterator = new RecursiveIteratorIterator($filter);

        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
                $files[] = $file->getRealPath();
            }
        }

        return $files;
    }

    // -------------------------------------------------------------------------
    // 스트리밍 분석 (inbound + same_table_users 동시 처리)
    // -------------------------------------------------------------------------

    /**
     * grep 기반 사전 필터로 후보 파일을 빠르게 추린 뒤,
     * PHP에서 정밀 분석하는 2단계 전략.
     *
     * 1단계: grep -rl 로 키워드가 포함된 파일만 빠르게 추출 (OS 레벨 최적화)
     * 2단계: 후보 파일만 PHP로 정밀 분석
     *
     * 13,000+ 파일을 전부 PHP로 읽던 방식 대비 대폭 성능 개선.
     *
     * @return array ['inbound_paths' => [...], 'same_table_users' => [...]]
     */
    private function scanAndAnalyze($rootPath, $targetFile, array $tables)
    {
        $inboundPaths = [];
        $sameTableUsers = [];
        foreach ($tables as $table) {
            $sameTableUsers[$table] = [];
        }

        $targetBasename = basename($targetFile);
        $targetRelative = basename(dirname($targetFile)) . '/' . $targetBasename;

        // --- 1단계: grep으로 후보 파일 추출 ---

        // inbound 후보: targetBasename을 포함하는 PHP 파일
        $inboundGrepPattern = preg_quote($targetBasename, '/');
        $inboundCandidates = $this->grepFiles($rootPath, $inboundGrepPattern);

        // same_table 후보: 테이블명 중 하나라도 포함하는 PHP 파일
        $tableCandidates = [];
        if (!empty($tables)) {
            $escapedTables = array_map(function($table) {
                return preg_quote($table, '/');
            }, $tables);
            $tableGrepPattern = implode('|', $escapedTables);
            $tableCandidates = $this->grepFiles($rootPath, $tableGrepPattern);
        }

        // --- 2단계: 후보 파일만 정밀 분석 ---

        // inbound 정밀 분석
        foreach ($inboundCandidates as $file) {
            if ($file === $targetFile) {
                continue;
            }

            $content = file_get_contents($file);
            if ($content === false) {
                continue;
            }

            preg_match_all('/\b(?:include|require|include_once|require_once)\b\s*[(\s]*[\'"]([^\'"]+)[\'"]/i', $content, $matches);

            if (empty($matches[1])) {
                continue;
            }

            foreach ($matches[1] as $includedPath) {
                if (basename($includedPath) !== $targetBasename) {
                    continue;
                }

                $normalizedIncluded = str_replace('\\', '/', $includedPath);
                $normalizedRelative = str_replace('\\', '/', $targetRelative);
                $normalizedBasename = str_replace('\\', '/', $targetBasename);

                if (
                    substr($normalizedIncluded, -strlen($normalizedRelative)) === $normalizedRelative
                    || substr($normalizedIncluded, -strlen($normalizedBasename)) === $normalizedBasename
                ) {
                    $inboundPaths[] = $file;
                    break;
                }
            }
        }

        // same_table_users 정밀 분석
        if (!empty($tables)) {
            $escaped = array_map(function($t) { return preg_quote($t, '/'); }, $tables);
            $tablePattern = '/\b(' . implode('|', $escaped) . ')\b/i';

            foreach ($tableCandidates as $file) {
                if ($file === $targetFile) {
                    continue;
                }

                $content = file_get_contents($file);
                if ($content === false) {
                    continue;
                }

                // 대상 파일 분석과 동일하게 주석을 제거해
                // 주석 내부 테이블명으로 인한 오탐을 줄인다.
                $cleanContent = $this->stripCommentsOnly($content);

                if (preg_match_all($tablePattern, $cleanContent, $tMatches)) {
                    $found = array_unique(array_map('strtolower', $tMatches[1]));
                    foreach ($found as $table) {
                        if (isset($sameTableUsers[$table])) {
                            $sameTableUsers[$table][] = $file;
                        }
                    }
                }
            }
        }

        return [
            'inbound_paths'    => array_values(array_unique($inboundPaths)),
            'same_table_users' => $sameTableUsers
        ];
    }

    /**
     * grep -rl 로 rootPath 아래 PHP 파일 중 pattern이 포함된 파일 목록 반환.
     * OS 레벨 최적화를 활용해 PHP file_get_contents보다 수십 배 빠름.
     */
    private function grepFiles($rootPath, $pattern)
    {
        if ($pattern === '') {
            return [];
        }

        $excludeDirs = ['.git', 'vendor', 'node_modules', '.svn', '.claude'];
        $excludeArgs = implode(' ', array_map(function($dir) {
            return '--exclude-dir=' . escapeshellarg($dir);
        }, $excludeDirs));

        $cmd = sprintf(
            'grep -E -rl --include="*.php" %s %s %s 2>/dev/null',
            escapeshellarg($pattern),
            $excludeArgs,
            escapeshellarg($rootPath)
        );

        $output = array();
        exec($cmd, $output);

        $result = [];
        foreach ($output as $line) {
            $line = trim($line);
            if ($line !== '' && file_exists($line)) {
                $real = realpath($line);
                if ($real !== false) {
                    $result[] = $real;
                }
            }
        }

        return array_values(array_unique($result));
    }
}
