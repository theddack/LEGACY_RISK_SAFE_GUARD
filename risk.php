<?php

// 단순 autoload (PSR-4 없이 최소 구조)
require_once __DIR__ . '/analyzers/php/PhpAnalyzer.php';
require_once __DIR__ . '/core/RiskCalculator.php';
require_once __DIR__ . '/core/ExplanationBuilder.php';
require_once __DIR__ . '/core/JsonFormatter.php';
require_once __DIR__ . '/core/ReportFormatter.php';

// -----------------------------
// CLI 옵션 파싱
// -----------------------------
$options = getopt("", ["lang:", "root:", "file:", "format:", "color"]);

if (!isset($options['lang'], $options['root'], $options['file'], $options['format'])) {
    exit(3); // 잘못된 옵션
}

$lang   = $options['lang'];
$root   = $options['root'];
$file   = $options['file'];
$format = $options['format'];

// -----------------------------
// Analyzer 선택 (현재는 PHP만)
// -----------------------------
switch ($lang) {
    case 'php':
        $analyzer = new PhpAnalyzer();
        break;
    default:
        exit(2); // 분석 실패 (미지원 언어)
}

// -----------------------------
// IR 생성 (Analyzer 역할)
// -----------------------------
try {
    $ir = $analyzer->analyze($root, $file);
} catch (Exception $e) {
    echo "분석 실패: " . $e->getMessage() . PHP_EOL;
    exit(2);
}

// -----------------------------
// Risk 계산
// -----------------------------
$calculator = new RiskCalculator();
$riskData = $calculator->calculate($ir);

// -----------------------------
// 설명 생성
// -----------------------------
$builder = new ExplanationBuilder();
$explanation = $builder->build($ir, $riskData);

// -----------------------------
// 출력
// -----------------------------
if ($format === 'json') {
    $formatter = new JsonFormatter();
    echo $formatter->format($ir, $riskData, $explanation);
} elseif ($format === 'report') {
    $formatter = new ReportFormatter($useColor);
    echo $formatter->format($ir, $riskData, $explanation);
} else {
    exit(3);
}

exit(0);