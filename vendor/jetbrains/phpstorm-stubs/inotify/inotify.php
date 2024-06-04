<?php

namespace DEPTRAC_INTERNAL;

// Start of inotify v.0.1.6
/**
 * (PHP &gt;= 5.2.0, PECL inotify &gt;= 0.1.2)<br/>
 * Add a watch to an initialized inotify instance
 *
 * @link https://php.net/manual/en/function.inotify-add-watch.php
 *
 * @param resource $inotify_instance <p>resource returned by {@link https://php.net/manual/en/function.inotify-init.php inotify_init()}</p>
 * @param string   $pathname         <p>File or directory to watch</p>
 * @param int      $mask             <p>Events to watch for. See {@link https://php.net/manual/en/inotify.constants.php Predefined Constants}.</p>
 *
 * @return int a unique (<i>inotify</i> instance-wide) watch descriptor.
 */
function inotify_add_watch($inotify_instance, $pathname, $mask)
{
}
/**
 * (PHP &gt;= 5.2.0, PECL inotify &gt;= 0.1.2)<br/>
 * Initialize an inotify instance for use with {@see inotify_add_watch}
 *
 * @link https://php.net/manual/en/function.inotify-init.php
 * @return resource|false a stream resource or <b>FALSE</b> on error.
 */
function inotify_init()
{
}
/**
 * (PHP &gt;= 5.2.0, PECL inotify &gt;= 0.1.2)<br/>
 * This function allows to know if {@see inotify_read} will block or not.
 * If a number upper than zero is returned, there are pending events
 * and {@see inotify_read} will not block.
 *
 * @link https://php.net/manual/en/function.inotify-queue-len.php
 *
 * @param resource $inotify_instance <p>resource returned by {@link https://php.net/manual/en/function.inotify-init.php inotify_init()}</p>
 *
 * @return int a number greater than zero if events are pending, otherwise zero.
 */
function inotify_queue_len($inotify_instance)
{
}
/**
 * (PHP &gt;= 5.2.0, PECL inotify &gt;= 0.1.2)<br/>
 * Read inotify events from an inotify instance.
 *
 * @link https://php.net/manual/en/function.inotify-read.php
 *
 * @param resource $inotify_instance <p>resource returned by {@link https://php.net/manual/en/function.inotify-init.php inotify_init()}</p>
 *
 * @return array|false an array of inotify events or <b>FALSE</b> if no events
 * were pending and <i>inotify_instance</i> is non-blocking. Each event
 * is an array with the following keys:
 *
 * <ul>
 *  <li><b>wd</b> is a watch descriptor returned by inotify_add_watch()</li>
 *  <li><b>mask</b> is a bit mask of events</li>
 *  <li><b>cookie</b> is a unique id to connect related events (e.g. IN_MOVE_FROM and IN_MOVE_TO)</li>
 *  <li><b>name</b> is the name of a file (e.g. if a file was modified in a watched directory)</li>
 * </ul>
 */
function inotify_read($inotify_instance)
{
}
/**
 * (PHP &gt;= 5.2.0, PECL inotify &gt;= 0.1.2)<br/>
 * Removes the watch <i>$watch_descriptor</i> from the inotify instance <i>$inotify_instance</i>.
 *
 * @link     https://secure.php.net/manual/en/function.inotify-rm-watch.php
 *
 * @param resource $inotify_instance <p>resource returned by {@link https://php.net/manual/en/function.inotify-init.php inotify_init()}</p>
 * @param int      $mask <p>watch to remove from the instance</p>
 *
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 */
function inotify_rm_watch($inotify_instance, $mask)
{
}
\define('IN_ACCESS', 1);
\define('IN_MODIFY', 2);
\define('IN_ATTRIB', 4);
\define('IN_CLOSE_WRITE', 8);
\define('IN_CLOSE_NOWRITE', 16);
\define('IN_OPEN', 32);
\define('IN_MOVED_FROM', 64);
\define('IN_MOVED_TO', 128);
\define('IN_CREATE', 256);
\define('IN_DELETE', 512);
\define('IN_DELETE_SELF', 1024);
\define('IN_MOVE_SELF', 2048);
\define('IN_UNMOUNT', 8192);
\define('IN_Q_OVERFLOW', 16384);
\define('IN_IGNORED', 32768);
\define('IN_CLOSE', 24);
\define('IN_MOVE', 192);
\define('IN_ALL_EVENTS', 4095);
\define('IN_ONLYDIR', 16777216);
\define('IN_DONT_FOLLOW', 33554432);
\define('IN_MASK_ADD', 536870912);
\define('IN_ISDIR', 1073741824);
\define('IN_ONESHOT', 2147483648);
// End of inotify v.0.1.6
