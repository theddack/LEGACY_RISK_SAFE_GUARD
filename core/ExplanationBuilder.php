<?php

class ExplanationBuilder
{
    /**
     * IR + RiskData 기반 한국어 설명 생성
     * 템플릿 기반 (LLM 사용 안 함)
     */
    public function build(array $ir, array $riskData)
    {
        $m = $ir['metrics'];
        $reasons = [];

        if ($m['dependency']['inbound_count'] > 5) {
            $reasons[] = "다수의 파일({$m['dependency']['inbound_count']}개)에서 직접 참조되고 있습니다.";
        }

        if ($m['dependency']['outbound_count'] > 5) {
            $reasons[] = "다수의 외부 파일({$m['dependency']['outbound_count']}개)을 include/require하고 있습니다.";
        }

        if ($m['db']['query_count'] > 5) {
            $reasons[] = "SQL 쿼리가 다수({$m['db']['query_count']}개) 포함되어 있어 DB 영향 범위가 넓습니다.";
        }

        if (count($m['db']['tables']) > 3) {
            $reasons[] = "여러 DB 테이블(" . count($m['db']['tables']) . "개)과 결합되어 있습니다.";
        }

        if ($m['complexity']['loc'] > 500) {
            $reasons[] = "파일 크기가 커({$m['complexity']['loc']}줄) 복잡도가 높습니다.";
        }

        if ($m['globals']['count'] > 3) {
            $reasons[] = "global 변수를 다수({$m['globals']['count']}개) 사용하고 있어 부작용 위험이 있습니다.";
        }

        $summary = "위험 등급: {$riskData['risk_level']} / 점수: {$riskData['risk_score']}";

        return [
            "risk_reasons" => $reasons,
            "summary" => $summary
        ];
    }
}