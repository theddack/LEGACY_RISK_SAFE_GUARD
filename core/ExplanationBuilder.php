<?php

class ExplanationBuilder
{
    /**
     * IR + RiskData 기반 한국어 설명 생성
     * 템플릿 기반 (LLM 사용 안 함)
     */
    public function build(array $ir, array $riskData): array
    {
        $m = $ir['metrics'];
        $reasons = [];

        if ($m['dependency']['inbound_count'] > 5) {
            $reasons[] = "다수의 파일에서 직접 참조되고 있습니다.";
        }

        if (count($m['db']['tables']) > 3) {
            $reasons[] = "여러 DB 테이블과 결합되어 있습니다.";
        }

        if ($m['complexity']['loc'] > 2000) {
            $reasons[] = "파일 크기가 커 복잡도가 높습니다.";
        }

        $summary = "위험 등급: {$riskData['risk_level']} / 점수: {$riskData['risk_score']}";

        return [
            "risk_reasons" => $reasons,
            "summary" => $summary
        ];
    }
}