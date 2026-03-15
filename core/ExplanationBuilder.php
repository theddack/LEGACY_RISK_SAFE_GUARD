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

        $q = isset($m['db']['query_type_counts']) ? $m['db']['query_type_counts'] : [];
        $readCount = isset($q['read']) ? $q['read'] : 0;
        $writeCount = isset($q['write']) ? $q['write'] : 0;

        if ($writeCount > 3) {
            $reasons[] = "쓰기 쿼리(INSERT/UPDATE/DELETE)가 다수({$writeCount}개) 포함되어 데이터 변경 영향이 큽니다.";
        } elseif ($m['db']['query_count'] > 5) {
            $reasons[] = "SQL 쿼리가 다수({$m['db']['query_count']}개) 포함되어 DB 영향 범위가 넓습니다.";
        }

        if ($readCount > 8) {
            $reasons[] = "조회 쿼리(SELECT)가 많아({$readCount}개) 조회 성능/락 영향 점검이 필요합니다.";
        }

        if (count($m['db']['tables']) > 3) {
            $reasons[] = "여러 DB 테이블(" . count($m['db']['tables']) . "개)과 결합되어 있습니다.";
        }

        if (!empty($m['db']['related_files']) && count($m['db']['related_files']) > 5) {
            $reasons[] = "연관 파일이 " . count($m['db']['related_files']) . "개로 추론되어 수정 파급 범위가 큽니다.";
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
