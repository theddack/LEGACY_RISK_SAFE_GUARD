<?php

class JsonFormatter
{
    public function format(array $ir, array $riskData, array $explanation): string
    {
        return json_encode([
            "ir" => $ir,
            "risk_score" => $riskData['risk_score'],
            "risk_level" => $riskData['risk_level'],
            "risk_reasons" => $explanation['risk_reasons'],
            "summary" => $explanation['summary']
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}