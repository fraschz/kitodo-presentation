<?php

/**
 * (c) Kitodo. Key to digital objects e.V. <contact@kitodo.org>
 *
 * This file is part of the Kitodo and TYPO3 projects.
 *
 * @license GNU General Public License version 3 or later.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Kitodo\Dlf\Eid;

use Kitodo\Dlf\Common\Helper;
use Kitodo\Dlf\Common\StdOutStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * eID image proxy for plugin 'Page View' of the 'dlf' extension
 *
 * Supported query parameters:
 * - `url` (mandatory): The URL to be proxied
 * - `uHash` (mandatory): HMAC of the URL
 *
 * @author Alexander Bigga <alexander.bigga@slub-dresden.de>
 * @package TYPO3
 * @subpackage dlf
 * @access public
 */
class PageViewProxy
{
    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var mixed
     */
    protected $extConf;

    public function __construct()
    {
        $this->requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        $this->extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('dlf');
    }

    /**
     * Return a response that is derived from $response and contains CORS
     * headers to be sent to the client.
     *
     * @return ResponseInterface $response
     * @return ServerRequestInterface $request The incoming request.
     * @return ResponseInterface
     */
    protected function withCorsResponseHeaders(
        ResponseInterface $response,
        ServerRequestInterface $request
    ): ResponseInterface {
        $origin = (string) ($request->getHeaderLine('Origin') ? : '*');

        return $response
            ->withHeader('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->withHeader('Access-Control-Allow-Origin', $origin)
            ->withHeader('Access-Control-Max-Age', '86400');
    }

    /**
     * Takes headers listed in $headerNames from $fromResponse, adds them to
     * $toResponse and returns the result.
     *
     * @param ResponseInterface $fromResponse
     * @param ResponseInterface $toResponse
     * @param array $headerNames
     * @return ResponseInterface
     */
    protected function copyHeaders(
        ResponseInterface $fromResponse,
        ResponseInterface $toResponse,
        array $headerNames
    ) {
        $result = $toResponse;

        foreach ($headerNames as $headerName) {
            $headerValues = $fromResponse->getHeader($headerName);
            // Don't include empty header field when not present
            if (!empty($headerValues)) {
                $result = $result->withAddedHeader($headerName, $headerValues);
            }
        }

        return $result;
    }

    /**
     * Handle an OPTIONS request.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    protected function handleOptions(ServerRequestInterface $request): ResponseInterface
    {
        // 204 No Content
        $response = GeneralUtility::makeInstance(Response::class)
            ->withStatus(204);
        return $this->withCorsResponseHeaders($response, $request);
    }

    /**
     * Handle a GET request.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    protected function handleGet(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        $url = (string) ($queryParams['url'] ?? '');
        if (!Helper::isValidHttpUrl($url)) {
            return new JsonResponse(['message' => 'Did not receive a valid URL.'], 400);
        }

        // get and verify the uHash
        $uHash = (string) ($queryParams['uHash'] ?? '');
        if (!hash_equals(GeneralUtility::hmac($url, 'PageViewProxy'), $uHash)) {
            return new JsonResponse(['message' => 'No valid uHash passed!'], 401);
        }

        try {
            $targetResponse = $this->requestFactory->request($url, 'GET', [
                'headers' => [
                    'User-Agent' => $this->extConf['useragent'] ?? 'Kitodo.Presentation Proxy',
                ],

                // For performance, don't download content up-front. Rather, we'll
                // download and upload simultaneously.
                // https://docs.guzzlephp.org/en/6.5/request-options.html#stream
                'stream' => true,

                // Don't throw exceptions when a non-success status code is
                // received. We handle these manually.
                'http_errors' => false,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Could not fetch resource of given URL.'], 500);
        }

        $body = new StdOutStream($targetResponse->getBody());

        $clientResponse = GeneralUtility::makeInstance(Response::class)
            ->withStatus($targetResponse->getStatusCode())
            ->withBody($body);

        $clientResponse = $this->copyHeaders($targetResponse, $clientResponse, [
            'Content-Length',
            'Content-Type',
            'Last-Modified',
        ]);

        return $this->withCorsResponseHeaders($clientResponse, $request);
    }

    /**
     * The main method of the eID script
     *
     * @access public
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function main(ServerRequestInterface $request)
    {
        switch ($request->getMethod()) {
            case 'OPTIONS':
                return $this->handleOptions($request);

            case 'GET':
                return $this->handleGet($request);

            default:
                // 405 Method Not Allowed
                return GeneralUtility::makeInstance(Response::class)
                    ->withStatus(405);
        }
    }
}
