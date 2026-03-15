<?php

class RiskCalculator
{
    /**
     * IR을 받아 Risk 점수 계산
     * 가중치는 config에서 불러온다.
     */
    public function calculate(array $ir)
    {
        $weights = require __DIR__ . '/../config/risk_weights.php';

        $m = $ir['metrics'];
        $q = isset($m['db']['query_type_counts']) ? $m['db']['query_type_counts'] : [];

        $readCount = isset($q['read']) ? $q['read'] : 0;
        $writeCount = isset($q['write']) ? $q['write'] : 0;
        $legacyQueryCount = isset($m['db']['query_count']) ? $m['db']['query_count'] : 0;

        // query_type_counts 미지원 구버전 analyzer 대응
        $baseQueryScore = 0;
        if ($readCount === 0 && $writeCount === 0) {
            $baseQueryScore = $legacyQueryCount * $weights['query'];
        }

        $tableCount = isset($m['db']['tables']) ? count($m['db']['tables']) : 0;
        $relatedFileCount = isset($m['db']['related_files']) ? count($m['db']['related_files']) : 0;

        $score =
            ($m['dependency']['inbound_count'] * $weights['inbound']) +
            ($m['dependency']['outbound_count'] * $weights['outbound']) +
            $baseQueryScore +
            ($readCount * $weights['query_read']) +
            ($writeCount * $weights['query_write']) +
            ($m['complexity']['loc'] * $weights['loc']) +
            ($m['globals']['count'] * $weights['globals']) +
            ($tableCount * $weights['table_count']) +
            ($relatedFileCount * $weights['related_file']);

        return [
            "risk_score" => round($score, 2),
            "risk_level" => $this->level($score)
        ];
    }

    private function level($score)
    {
        if ($score <= 3) return "LOW";
        if ($score <= 6) return "MEDIUM";
        if ($score <= 10) return "HIGH";
        return "CRITICAL";
    }
}
