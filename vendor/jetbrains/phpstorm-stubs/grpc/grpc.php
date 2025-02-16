<?php

/**
 * Helper autocomplete for php grpc extension
 * @author Dmitry Gavriloff <info@imega.ru>
 * @link https://github.com/iMega/grpc-phpdoc
 */
/**
 * Grpc
 * @see https://grpc.io
 * @see https://github.com/grpc/grpc/tree/master/src/php/ext/grpc
 */
namespace DEPTRAC_INTERNAL\Grpc;

\define('Grpc\\CALL_OK', 0);
\define('Grpc\\CALL_ERROR', 1);
\define('Grpc\\CALL_ERROR_NOT_ON_SERVER', 2);
\define('Grpc\\CALL_ERROR_NOT_ON_CLIENT', 3);
\define('Grpc\\CALL_ERROR_ALREADY_ACCEPTED', 4);
\define('Grpc\\CALL_ERROR_ALREADY_INVOKED', 5);
\define('Grpc\\CALL_ERROR_NOT_INVOKED', 6);
\define('Grpc\\CALL_ERROR_ALREADY_FINISHED', 7);
\define('Grpc\\CALL_ERROR_TOO_MANY_OPERATIONS', 8);
\define('Grpc\\CALL_ERROR_INVALID_FLAGS', 9);
\define('Grpc\\CALL_ERROR_INVALID_METADATA', 10);
\define('Grpc\\CALL_ERROR_INVALID_MESSAGE', 11);
\define('Grpc\\CALL_ERROR_NOT_SERVER_COMPLETION_QUEUE', 12);
\define('Grpc\\CALL_ERROR_BATCH_TOO_BIG', 13);
\define('Grpc\\CALL_ERROR_PAYLOAD_TYPE_MISMATCH', 14);
\define('Grpc\\WRITE_BUFFER_HINT', 1);
\define('Grpc\\WRITE_NO_COMPRESS', 2);
\define('Grpc\\STATUS_OK', 0);
\define('Grpc\\STATUS_CANCELLED', 1);
\define('Grpc\\STATUS_UNKNOWN', 2);
\define('Grpc\\STATUS_INVALID_ARGUMENT', 3);
\define('Grpc\\STATUS_DEADLINE_EXCEEDED', 4);
\define('Grpc\\STATUS_NOT_FOUND', 5);
\define('Grpc\\STATUS_ALREADY_EXISTS', 6);
\define('Grpc\\STATUS_PERMISSION_DENIED', 7);
\define('Grpc\\STATUS_UNAUTHENTICATED', 16);
\define('Grpc\\STATUS_RESOURCE_EXHAUSTED', 8);
\define('Grpc\\STATUS_FAILED_PRECONDITION', 9);
\define('Grpc\\STATUS_ABORTED', 10);
\define('Grpc\\STATUS_OUT_OF_RANGE', 11);
\define('Grpc\\STATUS_UNIMPLEMENTED', 12);
\define('Grpc\\STATUS_INTERNAL', 13);
\define('Grpc\\STATUS_UNAVAILABLE', 14);
\define('Grpc\\STATUS_DATA_LOSS', 15);
\define('Grpc\\OP_SEND_INITIAL_METADATA', 0);
\define('Grpc\\OP_SEND_MESSAGE', 1);
\define('Grpc\\OP_SEND_CLOSE_FROM_CLIENT', 2);
\define('Grpc\\OP_SEND_STATUS_FROM_SERVER', 3);
\define('Grpc\\OP_RECV_INITIAL_METADATA', 4);
\define('Grpc\\OP_RECV_MESSAGE', 5);
\define('Grpc\\OP_RECV_STATUS_ON_CLIENT', 6);
\define('Grpc\\OP_RECV_CLOSE_ON_SERVER', 7);
\define('Grpc\\CHANNEL_IDLE', 0);
\define('Grpc\\CHANNEL_CONNECTING', 1);
\define('Grpc\\CHANNEL_READY', 2);
\define('Grpc\\CHANNEL_TRANSIENT_FAILURE', 3);
\define('Grpc\\CHANNEL_SHUTDOWN', 4);
\define('Grpc\\CHANNEL_FATAL_FAILURE', 4);
/**
 * Class Server
 * @see https://github.com/grpc/grpc/tree/master/src/php/ext/grpc
 */
class Server
{
    /**
     * Constructs a new instance of the Server class
     *
     * @param array $args The arguments to pass to the server (optional)
     */
    public function __construct(array $args)
    {
    }
    /**
     * Request a call on a server. Creates a single GRPC_SERVER_RPC_NEW event.
     *
     * @param int $tag_new    The tag to associate with the new request
     * @param int $tag_cancel The tag to use if the call is cancelled
     */
    public function requestCall($tag_new, $tag_cancel)
    {
    }
    /**
     * Add a http2 over tcp listener.
     *
     * @param string $addr The address to add
     *
     * @return bool true on success, false on failure
     */
    public function addHttp2Port($addr)
    {
    }
    /**
     * Add a secure http2 over tcp listener.
     *
     * @param string             $addr      The address to add
     * @param ServerCredentials $creds_obj
     *
     * @return bool true on success, false on failure
     */
    public function addSecureHttp2Port($addr, $creds_obj)
    {
    }
    /**
     * Start a server - tells all listeners to start listening
     */
    public function start()
    {
    }
}
/**
 * Class ServerCredentials
 * @see https://github.com/grpc/grpc/tree/master/src/php/ext/grpc
 */
class ServerCredentials
{
    /**
     * Create SSL credentials.
     *
     * @param string $pem_root_certs  PEM encoding of the server root certificates
     * @param string $pem_private_key PEM encoding of the client's private key
     * @param string $pem_cert_chain  PEM encoding of the client's certificate chain
     *
     * @return object Credentials The new SSL credentials object
     * @throws \InvalidArgumentException
     */
    public static function createSsl($pem_root_certs, $pem_private_key, $pem_cert_chain)
    {
    }
}
/**
 * Class Channel
 * @see https://github.com/grpc/grpc/tree/master/src/php/ext/grpc
 */
class Channel
{
    /**
     * Construct an instance of the Channel class. If the $args array contains a
     * "credentials" key mapping to a ChannelCredentials object, a secure channel
     * will be created with those credentials.
     *
     * @param string $target The hostname to associate with this channel
     * @param array  $args   The arguments to pass to the Channel (optional)
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($target, $args = [])
    {
    }
    /**
     * Get the endpoint this call/stream is connected to
     *
     * @return string The URI of the endpoint
     */
    public function getTarget()
    {
    }
    /**
     * Get the connectivity state of the channel
     *
     * @param bool $try_to_connect try to connect on the channel
     *
     * @return int The grpc connectivity state
     * @throws \InvalidArgumentException
     */
    public function getConnectivityState($try_to_connect = \false)
    {
    }
    /**
     * Watch the connectivity state of the channel until it changed
     *
     * @param int     $last_state   The previous connectivity state of the channel
     * @param Timeval $deadline_obj The deadline this function should wait until
     *
     * @return bool If the connectivity state changes from last_state
     *              before deadline
     * @throws \InvalidArgumentException
     */
    public function watchConnectivityState($last_state, Timeval $deadline_obj)
    {
    }
    /**
     * Close the channel
     */
    public function close()
    {
    }
}
/**
 * Class ChannelCredentials
 * @see https://github.com/grpc/grpc/tree/master/src/php/ext/grpc
 */
class ChannelCredentials
{
    /**
     * Set default roots pem.
     *
     * @param string $pem_roots PEM encoding of the server root certificates
     *
     * @throws \InvalidArgumentException
     */
    public static function setDefaultRootsPem($pem_roots)
    {
    }
    /**
     * Create a default channel credentials object.
     *
     * @return ChannelCredentials The new default channel credentials object
     */
    public static function createDefault()
    {
    }
    /**
     * Create SSL credentials.
     *
     * @param string|null $pem_root_certs  PEM encoding of the server root certificates
     * @param string|null $pem_private_key PEM encoding of the client's private key
     * @param string|null $pem_cert_chain  PEM encoding of the client's certificate chain
     *
     * @return ChannelCredentials The new SSL credentials object
     * @throws \InvalidArgumentException
     */
    public static function createSsl(string $pem_root_certs = null, string $pem_private_key = null, string $pem_cert_chain = null)
    {
    }
    /**
     * Create composite credentials from two existing credentials.
     *
     * @param ChannelCredentials $cred1 The first credential
     * @param CallCredentials    $cred2 The second credential
     *
     * @return ChannelCredentials The new composite credentials object
     * @throws \InvalidArgumentException
     */
    public static function createComposite(ChannelCredentials $cred1, CallCredentials $cred2)
    {
    }
    /**
     * Create insecure channel credentials
     *
     * @return null
     */
    public static function createInsecure()
    {
    }
}
/**
 * Class Call
 * @see https://github.com/grpc/grpc/tree/master/src/php/ext/grpc
 */
class Call
{
    /**
     * Constructs a new instance of the Call class.
     *
     * @param Channel $channel           The channel to associate the call with.
     *                                   Must not be closed.
     * @param string  $method            The method to call
     * @param Timeval $absolute_deadline The deadline for completing the call
     * @param null|string $host_override The host is set by user (optional)
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Channel $channel, $method, Timeval $absolute_deadline, $host_override = null)
    {
    }
    /**
     * Start a batch of RPC actions.
     *
     * @param array $batch Array of actions to take
     *
     * @return object Object with results of all actions
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function startBatch(array $batch)
    {
    }
    /**
     * Set the CallCredentials for this call.
     *
     * @param CallCredentials $creds_obj The CallCredentials object
     *
     * @return int The error code
     * @throws \InvalidArgumentException
     */
    public function setCredentials(CallCredentials $creds_obj)
    {
    }
    /**
     * Get the endpoint this call/stream is connected to
     *
     * @return string The URI of the endpoint
     */
    public function getPeer()
    {
    }
    /**
     * Cancel the call. This will cause the call to end with STATUS_CANCELLED if it
     * has not already ended with another status.
     */
    public function cancel()
    {
    }
}
/**
 * Class CallCredentials
 * @see https://github.com/grpc/grpc/tree/master/src/php/ext/grpc
 */
class CallCredentials
{
    /**
     * Create composite credentials from two existing credentials.
     *
     * @param CallCredentials $cred1 The first credential
     * @param CallCredentials $cred2 The second credential
     *
     * @return CallCredentials The new composite credentials object
     * @throws \InvalidArgumentException
     */
    public static function createComposite(CallCredentials $cred1, CallCredentials $cred2)
    {
    }
    /**
     * Create a call credentials object from the plugin API
     *
     * @param \Closure $callback The callback function
     *
     * @return CallCredentials The new call credentials object
     * @throws \InvalidArgumentException
     */
    public static function createFromPlugin(\Closure $callback)
    {
    }
}
/**
 * Class Timeval
 *
 * @see https://github.com/grpc/grpc/tree/master/src/php/ext/grpc
 */
class Timeval
{
    /**
     * Constructs a new instance of the Timeval class
     *
     * @param int $usec The number of microseconds in the interval
     */
    public function __construct($usec)
    {
    }
    /**
     * Adds another Timeval to this one and returns the sum. Calculations saturate
     * at infinities.
     *
     * @param Timeval $other The other Timeval object to add
     *
     * @return Timeval A new Timeval object containing the sum
     * @throws \InvalidArgumentException
     */
    public function add(Timeval $other)
    {
    }
    /**
     * Return negative, 0, or positive according to whether a < b, a == b, or a > b
     * respectively.
     *
     * @param Timeval $a The first time to compare
     * @param Timeval $b The second time to compare
     *
     * @return int
     * @throws \InvalidArgumentException
     */
    public static function compare(Timeval $a, Timeval $b)
    {
    }
    /**
     * Returns the infinite future time value as a timeval object
     *
     * @return Timeval Infinite future time value
     */
    public static function infFuture()
    {
    }
    /**
     * Returns the infinite past time value as a timeval object
     *
     * @return Timeval Infinite past time value
     */
    public static function infPast()
    {
    }
    /**
     * Returns the current time as a timeval object
     *
     * @return Timeval The current time
     */
    public static function now()
    {
    }
    /**
     * Checks whether the two times are within $threshold of each other
     *
     * @param Timeval $a         The first time to compare
     * @param Timeval $b         The second time to compare
     * @param Timeval $threshold The threshold to check against
     *
     * @return bool True if $a and $b are within $threshold, False otherwise
     * @throws \InvalidArgumentException
     */
    public static function similar(Timeval $a, Timeval $b, Timeval $threshold)
    {
    }
    /**
     * Sleep until this time, interpreted as an absolute timeout
     */
    public function sleepUntil()
    {
    }
    /**
     * Subtracts another Timeval from this one and returns the difference.
     * Calculations saturate at infinities.
     *
     * @param Timeval $other The other Timeval object to subtract
     *
     * @return Timeval A new Timeval object containing the sum
     * @throws \InvalidArgumentException
     */
    public function subtract(Timeval $other)
    {
    }
    /**
     * Returns the zero time interval as a timeval object
     *
     * @return Timeval Zero length time interval
     */
    public static function zero()
    {
    }
}
