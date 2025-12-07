<?php


namespace App\Logging;


use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;

class CustomizeLineFormatter extends LineFormatter
{
    /**
     * @param string $format                     The format of the message
     * @param string $dateFormat                 The format of the timestamp: one supported by DateTime::format
     * @param bool   $allowInlineLineBreaks      Whether to allow inline line breaks in log entries
     * @param bool   $ignoreEmptyContextAndExtra
     */
    public function __construct($format = null, $dateFormat = null,
                                $allowInlineLineBreaks = false,
                                $ignoreEmptyContextAndExtra = false)
    {
        parent::__construct($format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);
    }

    /**
     * {@inheritdoc}
     */
    public function format(array|LogRecord $record): string
    {
        $vars = parent::format($record);
        $output = array_merge([
            'timestamp' => $vars['datetime'],
            'message' => $vars['message']
        ], $vars['context']);
        return json_encode($output) . "\n";
    }

}
