<?php

if (!file_exists(__DIR__ . "/../clover.xml")) {
    exec('echo "COVERAGE=Failed" >> $GITHUB_ENV');
    return;
}
$xml = new SimpleXMLElement(__DIR__ . "/../clover.xml", 0, true);
$reportMetrics = $xml->xpath('project/metrics')[0] ?? null;
$metricsAttributes = $reportMetrics->attributes();
$elements = (int)($metricsAttributes->elements ?? 0);
$coveredElements = (int)($metricsAttributes->coveredelements ?? 0);
$elements = $elements === 0 ? 1 : $elements;
$coverage = round($coveredElements / $elements * 100);

exec('echo "COVERAGE=' . $coverage . '%" >> $GITHUB_ENV');