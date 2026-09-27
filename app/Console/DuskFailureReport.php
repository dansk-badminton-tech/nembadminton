<?php

namespace App\Console;

use UnexpectedValueException;

class DuskFailureReport
{
    public static function failedTestFilter(string $xml): ?string
    {
        $previous = libxml_use_internal_errors(true);

        try {
            $report = simplexml_load_string($xml);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if ($report === false) {
            throw new UnexpectedValueException('The Dusk JUnit report is not valid XML.');
        }

        $cases = $report->xpath('//testcase');
        if (! isset($report->testsuite[0]) || count($cases) === 0 || count($cases) !== (int) $report->testsuite[0]['tests']) {
            throw new UnexpectedValueException('The Dusk JUnit report is incomplete.');
        }

        $failed = [];
        foreach ($report->xpath('//testcase[failure or error]') as $test) {
            $class = (string) $test['class'];
            $name = (string) $test['name'];

            if ($class === '' || $name === '') {
                throw new UnexpectedValueException('A failed test in the Dusk JUnit report has no class or name.');
            }

            $failed[] = preg_quote($class, '/').'::'.preg_quote($name, '/');
        }

        return $failed ? '/^(?:'.implode('|', array_unique($failed)).')(?: with data set.*)?$/' : null;
    }
}
