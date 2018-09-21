<?php

/*
 * The MIT License
 *
 * Copyright 2018 Daniel Popiniuc
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace danielgp\io_operations;

trait InputOutputTiming
{

    use InputOutputMemory;

    public $intTimeCounter = 0;

    /**
     * Converts a
     *
     * @param string $inStrictIso8601DtTm
     * @return string
     */
    public function convertDateTimeToUtcTimeZone($inStrictIso8601DtTm)
    {
        $tmpDateTimeIn = $this->convertTimeFromFormatSafely($inStrictIso8601DtTm);
        $tmpDateTimeIn->setTimezone(new \DateTimeZone('UTC'));
        return $tmpDateTimeIn->format('Y-m-d H:i:s');
    }

    public function convertTimeFromFormatSafely($inStrictIso8601DtTm)
    {
        $tmpDateTimeIn = \DateTime::createFromFormat(\DATE_ATOM, $inStrictIso8601DtTm);
        if ($tmpDateTimeIn === false) {
            throw new \RuntimeException(''
                . sprintf('Unable to create DateTime object from %s string!', $inStrictIso8601DtTm));
        }
        return $tmpDateTimeIn;
    }

    public function convertTimeToMilliseconds($time)
    {
        $dateTime = new \DateTime($time);
        $seconds  = array_sum([
            (((int) $dateTime->format('H')) * 3600),
            (((int) $dateTime->format('i')) * 60),
            ((int) $dateTime->format('s')),
        ]);
        return floatval($seconds . '.' . $dateTime->format('u'));
    }

    public function getTimestampArray($crtTime)
    {
        return [
            'float'  => $this->getTimestampFloat($crtTime),
            'string' => $this->getTimestampString($crtTime)
        ];
    }

    public function getTimestampFloat($crtTime)
    {
        return ($crtTime['sec'] + $crtTime['usec'] / pow(10, 6));
    }

    private function getTimestampString($dateTime)
    {
        return '<span style="color:black!important;font-weight:bold;">['
            . $dateTime->format('Y-m-d H:i:s.u') . ']</span>';
    }

    public function getTimestampedInfo()
    {
        $dateTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $sMessage = $this->getTimestampString($dateTime) . '~' . $this->getMemoryUsageString();
        $this->intTimeCounter++;
        if (PHP_SAPI === 'cli') {
            return strip_tags($sMessage);
        }
        return $sMessage;
    }
}
