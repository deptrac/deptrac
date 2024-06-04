<?php

namespace DEPTRAC_INTERNAL;

\define('EXP_GLOB', 1);
\define('EXP_EXACT', 2);
\define('EXP_REGEXP', 3);
\define('EXP_EOF', -11);
\define('EXP_TIMEOUT', -2);
\define('EXP_FULLBUFFER', -5);
/**
 * Execute command via Bourne shell, and open the PTY stream to the process
 *
 * @param string $command Command to execute.
 * @return resource|false Returns an open PTY stream to the processes stdio, stdout, and stderr.
 *                        On failure this function returns FALSE.
 * @since PECL expect >= 0.1.0
 * @link https://www.php.net/manual/en/function.expect-popen.php
 */
function expect_popen(string $command)
{
    unset($command);
    return \false;
}
/**
 * Waits until the output from a process matches one of the patterns, a specified time period has passed,
 * or an EOF is seen.
 *
 * If match is provided, then it is filled with the result of search. The matched string can be found in match[0].
 * The match substrings (according to the parentheses) in the original pattern can be found in match[1], match[2],
 * and so on, up to match[9] (the limitation of libexpect).
 *
 * @param resource $expect An Expect stream, previously opened with expect_popen()
 * @param array $cases <p>An array of expect cases. Each expect case is an indexed array, as described in the following table:</p>
 * <p>
 * <tr valign="top">
 * <td>Index Key</td>
 * <td>Value Type</td>
 * <td>Description</td>
 * <td>Is Mandatory</td>
 * <td>Default Value</td>
 * </tr>
 * <tr valign="top">
 * <td>0</td>
 * <td>string</td>
 * <td>pattern, that will be matched against the output from the stream</td>
 * <td>Yes</td>
 * <td></td>
 * </tr>
 * <tr valign="top">
 * <td>1</td>
 * <td>mixed</td>
 * <td>value, that will be returned by this function, if the pattern matches</td>
 * <td>Yes</td>
 * <td></td>
 * </tr>
 * <tr valign="top">
 * <td>2</td>
 * <td>integer</td>
 * <td>pattern type, one of: <b>EXP_GLOB</b>, <b>EXP_EXACT</b> or <b>EXP_REGEXP</b></td>
 * <td>No</td>
 * <td><b>EXP_GLOB</b></td>
 * </tr>
 * </p>
 * @param array &$match
 *
 * @return int Returns value associated with the pattern that was matched.
 * 			   On failure this function returns: <b>EXP_EOF</b>, <b>EXP_TIMEOUT</b> or <b>EXP_FULLBUFFER</b>
 * @since PECL expect >= 0.1.0
 * @link https://www.php.net/manual/en/function.expect-expectl.php
 */
function expect_expectl($expect, array $cases, array &$match = []) : int
{
    unset($expect, $cases, $match);
    return 0;
}
