<?php

// 단순 autoload (PSR-4 없이 최소 구조)
require_once __DIR__ . '/analyzers/php/PhpAnalyzer.php';
require_once __DIR__ . '/core/RiskCalculator.php';
require_once __DIR__ . '/core/ExplanationBuilder.php';
require_once __DIR__ . '/core/JsonFormatter.php';
require_once __DIR__ . '/core/ReportFormatter.php';

function printUsage()
{
    echo "Usage:\n";
    echo "  php risk.php --lang=php --root=<project_root> --file=<target_file> --format=<json|report> [--color]\n\n";
    echo "Options:\n";
    echo "  --lang    Analyzer language (currently only: php)\n";
    echo "  --root    Project root path\n";
    echo "  --file    Target file path to analyze\n";
    echo "  --format  Output format: json or report\n";
    echo "  --color   Enable ANSI color output in report format\n";
    echo "  --help    Show this help message\n";
}

// -----------------------------
// CLI 옵션 파싱
// -----------------------------
$options = getopt('', ['lang:', 'root:', 'file:', 'format:', 'color', 'help']);

if (isset($options['help'])) {
    printUsage();
    exit(0);
}

if (!isset($options['lang'], $options['root'], $options['file'], $options['format'])) {
    printUsage();
    exit(3); // 잘못된 옵션
}

$lang     = $options['lang'];
$root     = $options['root'];
$file     = $options['file'];
$format   = $options['format'];
$useColor = isset($options['color']);

if (!in_array($format, ['json', 'report'], true)) {
    echo "지원하지 않는 format 입니다: {$format}\n\n";
    printUsage();
    exit(3);
}

// -----------------------------
// Analyzer 선택 (현재는 PHP만)
// -----------------------------
switch ($lang) {
    case 'php':
        $analyzer = new PhpAnalyzer();
        break;
    default:
        echo "지원하지 않는 언어입니다: {$lang}\n\n";
        printUsage();
        exit(2); // 분석 실패 (미지원 언어)
}

// -----------------------------
// IR 생성 (Analyzer 역할)
// -----------------------------
try {
    $ir = $analyzer->analyze($root, $file);
} catch (Exception $e) {
    echo '분석 실패: ' . $e->getMessage() . PHP_EOL;
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
} else {
    $formatter = new ReportFormatter($useColor);
    echo $formatter->format($ir, $riskData, $explanation);
}

exit(0);
