<?php

namespace OpenCATS\Tests\Behat\BrowserKit;

use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;

class StreamHttpBrowser extends AbstractBrowser
{
    public function __construct(array $serverParameters = array())
    {
        parent::__construct($serverParameters);
    }

    protected function doRequest($request)
    {
        if (!$request instanceof Request) {
            throw new \InvalidArgumentException('Unsupported request object.');
        }

        $headers = $this->buildHeaders($request);
        $content = $this->buildContent($request);

        $options = array(
            'http' => array(
                'method' => $request->getMethod(),
                'header' => implode("\r\n", $headers),
                'content' => $content,
                'ignore_errors' => true,
                'follow_location' => 0,
                'max_redirects' => 0,
            ),
        );

        $responseContent = @file_get_contents($request->getUri(), false, stream_context_create($options));
        if ($responseContent === false) {
            $responseContent = '';
        }

        $responseHeaders = isset($http_response_header) && is_array($http_response_header)
            ? $http_response_header
            : array();

        return new Response(
            $responseContent,
            $this->extractStatusCode($responseHeaders),
            $this->extractHeaders($responseHeaders)
        );
    }

    private function buildContent(Request $request)
    {
        if (null !== $request->getContent()) {
            return $request->getContent();
        }

        $method = strtoupper($request->getMethod());
        if ($method === 'GET' || $method === 'HEAD') {
            return '';
        }

        $parameters = $request->getParameters();

        return empty($parameters) ? '' : http_build_query($parameters, '', '&');
    }

    private function buildHeaders(Request $request)
    {
        $headers = array();
        $server = $request->getServer();

        foreach ($server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $name = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[] = $name . ': ' . $value;
                continue;
            }

            if ($key === 'CONTENT_TYPE') {
                $headers[] = 'content-type: ' . $value;
                continue;
            }

            if ($key === 'CONTENT_LENGTH') {
                $headers[] = 'content-length: ' . $value;
            }
        }

        $cookies = $request->getCookies();
        if (!empty($cookies)) {
            $pairs = array();
            foreach ($cookies as $name => $value) {
                $pairs[] = $name . '=' . $value;
            }
            $headers[] = 'cookie: ' . implode('; ', $pairs);
        }

        if (!$this->hasHeader($headers, 'content-type') && $this->hasBody($request)) {
            $headers[] = 'content-type: application/x-www-form-urlencoded';
        }

        return $headers;
    }

    private function hasBody(Request $request)
    {
        if (null !== $request->getContent()) {
            return $request->getContent() !== '';
        }

        $method = strtoupper($request->getMethod());
        if ($method === 'GET' || $method === 'HEAD') {
            return false;
        }

        return !empty($request->getParameters());
    }

    private function hasHeader(array $headers, $needle)
    {
        foreach ($headers as $header) {
            if (strpos(strtolower($header), strtolower($needle) . ':') === 0) {
                return true;
            }
        }

        return false;
    }

    private function extractStatusCode(array $responseHeaders)
    {
        if (!isset($responseHeaders[0])) {
            return 500;
        }

        if (preg_match('/^HTTP\/\d(?:\.\d)?\s+(\d+)/', $responseHeaders[0], $matches)) {
            return (int) $matches[1];
        }

        return 500;
    }

    private function extractHeaders(array $responseHeaders)
    {
        $headers = array();

        foreach ($responseHeaders as $line) {
            if (strpos($line, ':') === false) {
                continue;
            }

            list($name, $value) = explode(':', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (array_key_exists($name, $headers)) {
                if (!is_array($headers[$name])) {
                    $headers[$name] = array($headers[$name]);
                }
                $headers[$name][] = $value;
                continue;
            }

            $headers[$name] = $value;
        }

        return $headers;
    }
}
