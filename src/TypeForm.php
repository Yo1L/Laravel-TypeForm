<?php

namespace Yo1L\LaravelTypeForm;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Log;

class TypeForm
{
    protected $api = null;

    public function __construct()
    {
        $this->api = new Client([
            'base_uri' => config('typeform.base_uri'),
            'headers' => $this->getHeaders(),
        ]);
    }

    /**
     * Headers necessary to instantiate a connection
     *
     * @return array
     */
    protected function getHeaders()
    {
        $headers = [
            'accept' => 'application/json',
            'Authorization' => 'Bearer '.config('typeform.token'),
        ];

        foreach (config('typeform.headers') as $key => $value) {
            if ($value) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    /**
     * fetch wrapper
     *
     * @param string $uri
     * @param array $params
     * @param string $method
     * @return array
     * @throws \Exception
     */
    public function call(string $uri, array $params, $method = 'GET')
    {
        $key_params = $method == 'GET' ? 'query' : 'json';
        $response = $this->api->request($method, $uri, [
            $key_params => $params,
            'debug'     => config('typeform.debug'),
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode >= 300) {
            throw new \Exception("Failed to retrieve froms, code:" . $statusCode);
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * fetch forms list
     *
     * @param User $user
     * @throws \Exception
     * @return array
     * @throws \Exception
     */
    public function getForms(array $params = [])
    {
        return $this->call('forms', $params);
    }

    /**
     * fetch a form with its question
     *
     * @param string $formId
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getForm($formId, array $params = [])
    {
        return $this->call('forms/' . $formId, $params);
    }

    /**
     * fetch responses
     *
     * @param string $formId
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getResponses($formId, array $params = [])
    {
        return $this->call('forms/' . $formId . '/responses', $params);
    }

    /**
     * compute a generator for paginited results
     *
     * @param array $params
     * @return generator
     */
    // protected function generator(array $params, \Closure $closure)
    // {
    //     $pages = 1;
    //     for ($page=1; $page <= $pages; $page++) {
    //         $params['page'] = $page;
    //         $response = $closure($params);
    //         $pages = $response['page_count'];

    //         yield $response;
    //     }
    // }

    /**
     * generator: fetch all forms by chunk
     *
     * @param array $params
     * @return generator
     * @throws \Exception
     */
    public function getFormsByChunk(array $params = [])
    {
        $pages = 1;
        for ($page=1; $page <= $pages; $page++) {
            $params['page'] = $page;
            $response = $this->getForms($params);
            $pages = $response['page_count'];

            yield $response;#
        }
    }

    /**
     * generator: fetch all forms by chunk
     * https://developer.typeform.com/responses/walkthroughs/#use-query-parameters-to-retrieve-specific-data
     *
     * @param array $params
     * @return generator
     * @throws \Exception
     */
    public function getResponsesByChunk(string $formId, array $params = [])
    {
        $total_items = 1;
        while ($total_items>0) {
            $result = $this->getResponses($formId, $params);
                
            $token = $this->getLastResponseToken($result);
            if ($token) {
                $params['before'] = $token;
            } else {
                unset($params['before']);
            }
            $total_items = $result['total_items'];

            yield $result;
        }
    }

    /**
     * get last token of a response call to use it with 'before' params
     * https://developer.typeform.com/responses/walkthroughs/#use-query-parameters-to-retrieve-specific-data
     *
     * @param array $result
     * @return string
     */
    protected function getLastResponseToken($result)
    {
        if (!isset($result['items'])) {
            return null;
        }
        $last = count($result['items']) - 1;
        if ($last<=0) {
            return null;
        }
        return $result['items'][$last]['token'] ?? null;
    }

    /**
     * get the local url for webhook called by typeform server
     *
     * @return string
     */
    protected function getWebhookUrl()
    {
        $base = config('typeform.webhook.base_uri') ?? config('app.url');
        return $base . '/' . config('typeform.webhook.uri');
    }

        /**
     * get the secret for webhook
     *
     * @return string
     */
    protected function getWebhookSecret()
    {
        return config('typeform.webhook.secret');
    }

    /**
     * get the tag for webhook
     *
     * @return string
     */
    protected function getWebhookTag()
    {
        return config('typeform.webhook.tag') ?? config('app.name');
    }

    /**
     * create / update a webhook
     *
     * @param string $formId
     * @return void
     * @throws \Exception
     */
    public function registerWebhook(string $formId)
    {
        $params = [
            'enabled'    => true,
            'url'        => $this->getWebhookUrl(),
            'secret'     => $this->getWebhookSecret(),
            'verify_ssl' => config('typeform.webhook.verify_ssl'),
        ];

        return $this->call('forms/' . $formId . '/webhooks/' . $this->getWebhookTag(), $params, 'put');
    }

    /**
     * create / update a webhook
     *
     * @param string $formId
     * @return void
     * @throws \Exception
     */
    public function deleteWebhook(string $formId)
    {
        $this->call('forms/' . $formId . '/webhooks/' . $this->getWebhookTag(), [], 'DELETE');
    }

    /**
     * validate a signature
     * https://developer.typeform.com/webhooks/secure-your-webhooks/
     * @param string $signature
     * @param string $body
     * @return boolean
     */
    public function validatePayload(Request $request)
    {
        $secret = $this->getWebhookSecret();
        if (!$secret) {
            // no secret => no need to validate
            return;
        }

        $signature = $request->header('Typeform-Signature');
        Log::debug('signature: '.$signature);
        abort_if(!$signature, 400, "Signature not in header");

        $body = $request->getContent();
        abort_if(!$body, 400, "No body contents");

        $sha256 = hash_hmac('sha256', $body, $secret, true);
        $actual = 'sha256=' . base64_encode($sha256);
        Log::debug('actual: '.$actual);
        abort_unless(hash_equals($signature, $actual), 401, "Invalid signature");
    }
}
