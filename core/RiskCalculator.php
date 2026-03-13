<?php

class RiskCalculator
{
    /**
     * IR을 받아 Risk 점수 계산
     * 가중치는 config에서 불러온다.
     */
    public function calculate(array $ir): array
    {
        $weights = require __DIR__ . '/../config/risk_weights.php';

        $m = $ir['metrics'];

        $score =
            ($m['dependency']['inbound_count'] * $weights['inbound']) +
            ($m['dependency']['outbound_count'] * $weights['outbound']) +
            ($m['db']['query_count'] * $weights['query']) +
            ($m['complexity']['loc'] * $weights['loc']) +
            ($m['globals']['count'] * $weights['globals']);

        return [
            "risk_score" => round($score, 2),
            "risk_level" => $this->level($score)
        ];
    }

    private function level(float $score): string
    {
        if ($score <= 3) return "LOW";
        if ($score <= 6) return "MEDIUM";
        if ($score <= 10) return "HIGH";
        return "CRITICAL";
    }
}