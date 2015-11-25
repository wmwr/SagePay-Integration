<?php namespace Academe\SagePayMsg\Message;

/**
 * Shared message abstract.
 * Contains base methods that request messages will use.
 */

use Exception;
use UnexpectedValueException;

use DateTime;
use DateTimeZone;

abstract class AbstractRequest extends AbstractMessage
{
    protected $auth;
    protected $resource_path = [];

    /**
     * @var string Most messages are sent as POST requests, so this is the default
     */
    protected $method = 'POST';

    /**
     * Support substitution strings; any {fooBar} mapped to $this->getFooBar()
     *
     * @returns array The path of this resource, as an array of path segments
     */
    public function getResourcePath()
    {
        $path = $this->resource_path;

        // Look for segments that need a substitution.
        $subtitution_parameters = preg_grep('/^\{.*\}$/', $path);

        if ( ! empty($subtitution_parameters)) {
            foreach($subtitution_parameters as $key => $sub) {
                // The name of the getter method.
                $method_name = 'get' . ucfirst(substr($sub, 1, -1));

                // Replace the value from the getter method.
                $path[$key] = $this->$method_name();
            }
        }

        return $path;
    }

    /**
     * @returns string The full URL of this resource
     */
    public function getUrl()
    {
        return $this->auth->getUrl($this->getResourcePath());
    }

    /**
     * @returns string The HTTP method that the 
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * The HTTP Basic Auth header, as an array.
     * Use this if your transport tool does not do "Basic Auth" out of the box.
     */
    protected function getBasicAuthHeaders()
    {
        return [
            'Authorization' => 'Basic '
                . base64_encode(
                    $this->auth->getIntegrationKey()
                    . ':' . $this->auth->getIntegrationPassword()
                ),
        ];
    }

}
