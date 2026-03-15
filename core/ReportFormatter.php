<?php

class ReportFormatter
{
    private $useColor;

    public function __construct($useColor = false)
    {
        $this->useColor = $useColor;
    }

    public function format(array $ir, array $riskData, array $explanation)
    {
        $m = $ir['metrics'];

        $riskLevel = $this->colorRiskLevel($riskData['risk_level']);

        $output = "";
        $output .= "========================================\n";
        $output .= "Legacy Risk Safe Guard Report\n";
        $output .= "========================================\n\n";

        $output .= "파일:\n  {$ir['target']['path']}\n\n";
        $output .= "언어:\n  {$ir['target']['language']}\n\n";

        $output .= "위험 등급:\n";
        $output .= "  {$riskLevel} ({$riskData['risk_score']})\n\n";

        $output .= "----------------------------------------\n";
        $output .= "[복잡도]\n\n";
        $output .= "LOC: {$m['complexity']['loc']}\n";
        $output .= "Outbound(include): {$m['dependency']['outbound_count']}\n";
        $output .= "Inbound(참조파일): {$m['dependency']['inbound_count']}\n";
        $output .= "Global 변수: {$m['globals']['count']}\n\n";

        $output .= "----------------------------------------\n";
        $output .= "[DB 영향]\n\n";

        if (!empty($m['db']['query_type_counts'])) {
            $q = $m['db']['query_type_counts'];
            $output .= "쿼리 타입 요약:\n";
            $output .= "  - READ(SELECT): " . (isset($q['read']) ? $q['read'] : 0) . "\n";
            $output .= "  - WRITE(INSERT/UPDATE/DELETE): " . (isset($q['write']) ? $q['write'] : 0) . "\n";
            $output .= "  - SELECT: " . (isset($q['SELECT']) ? $q['SELECT'] : 0) . "\n";
            $output .= "  - INSERT: " . (isset($q['INSERT']) ? $q['INSERT'] : 0) . "\n";
            $output .= "  - UPDATE: " . (isset($q['UPDATE']) ? $q['UPDATE'] : 0) . "\n";
            $output .= "  - DELETE: " . (isset($q['DELETE']) ? $q['DELETE'] : 0) . "\n\n";
        }

        if (!empty($m['db']['tables'])) {
            $output .= "사용 테이블:\n";
            foreach ($m['db']['tables'] as $table) {
                $output .= "  - {$table}\n";
            }
            $output .= "\n";
        }

        if (!empty($m['db']['query_locations'])) {
            $output .= "쿼리 위치(대상 파일):\n";
            foreach ($m['db']['query_locations'] as $loc) {
                $line = isset($loc['line']) ? $loc['line'] : '?';
                $type = isset($loc['type']) ? $loc['type'] : 'UNKNOWN';
                $table = isset($loc['table']) && $loc['table'] !== null ? $loc['table'] : '-';
                $snippet = isset($loc['snippet']) ? $loc['snippet'] : '';
                $output .= "  - L{$line} [{$type}] (table: {$table}) {$snippet}\n";
            }
            $output .= "\n";
        }

        if (!empty($m['db']['table_query_map'])) {
            $output .= "테이블별 쿼리 맵:\n";
            foreach ($m['db']['table_query_map'] as $table => $entries) {
                $output .= "  - {$table}:\n";
                foreach ($entries as $entry) {
                    $line = isset($entry['line']) ? $entry['line'] : '?';
                    $type = isset($entry['type']) ? $entry['type'] : 'UNKNOWN';
                    $output .= "      * L{$line} [{$type}]\n";
                }
            }
            $output .= "\n";
        }

        if (!empty($m['db']['related_files'])) {
            $output .= "연관 파일(자동 추론):\n";
            foreach ($m['db']['related_files'] as $file) {
                $output .= "  - {$file}\n";
            }
            $output .= "\n";
        }

        if (!empty($m['db']['same_table_users'])) {
            $output .= "동일 테이블 사용 파일:\n\n";

            foreach ($m['db']['same_table_users'] as $table => $files) {
                if (empty($files)) continue;

                $output .= "{$table}:\n";
                foreach ($files as $file) {
                    $output .= "  - {$file}\n";
                }
                $output .= "\n";
            }
        }

        if (!empty($m['dependency']['inbound_paths'])) {
            $output .= "----------------------------------------\n";
            $output .= "[직접 참조 파일 목록]\n\n";

            foreach ($m['dependency']['inbound_paths'] as $index => $file) {
                $num = $index + 1;
                $output .= "  {$num}. {$file}\n";
            }

            $output .= "\n";
        }

        if (!empty($explanation['risk_reasons'])) {
            $output .= "----------------------------------------\n";
            $output .= "[위험 사유]\n\n";

            foreach ($explanation['risk_reasons'] as $reason) {
                $output .= "  - {$reason}\n";
            }

            $output .= "\n";
        }

        $output .= "----------------------------------------\n";
        $output .= "[요약]\n\n";
        $output .= "{$explanation['summary']}\n\n";

        $output .= "========================================\n";

        return $output;
    }

    /**
     * 위험 등급 색상 처리
     */
    private function colorRiskLevel($level)
    {
        if (!$this->useColor) {
            return $level;
        }

        switch ($level) {
            case 'LOW':
                return "\033[32m{$level}\033[0m"; // 초록
            case 'MEDIUM':
                return "\033[33m{$level}\033[0m"; // 노랑
            case 'HIGH':
                return "\033[31m{$level}\033[0m"; // 빨강
            case 'CRITICAL':
                return "\033[1;31m{$level}\033[0m"; // 굵은 빨강
            default:
                return $level;
        }
    }
}
