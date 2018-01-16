<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactClientBundle\Model;

use stdClass;
use Mautic\PluginBundle\Exception\ApiErrorException;
use MauticPlugin\MauticContactClientBundle\Model\ApiPayloadRequest as ApiRequest;
use MauticPlugin\MauticContactClientBundle\Model\ApiPayloadResponse as ApiResponse;
use MauticPlugin\MauticContactClientBundle\Services\Transport;

/**
 * Class ApiPayloadOperation.
 */
class ApiPayloadOperation
{

    protected $operation;

    protected $name;

    protected $request;

    protected $responseExpected;

    protected $successDefinition;

    protected $responseActual;

    protected $test;

    protected $logs = [];

    protected $service;

    protected $updatePayload;

    protected $valid = false;

    protected $tokenHelper;

    public function __construct(
        $id,
        &$operation,
        Transport $service,
        $tokenHelper,
        $test = false,
        $updatePayload = true
    ) {
        $this->operation = &$operation;
        $this->service = $service;
        $this->name = $operation->name ?? $operation->id ?? 'Unknown';
        $this->request = $operation->request ?? [];
        $this->responseExpected = $operation->response ?? [];
        $this->successDefinition = $operation->response->success->definition ?? [];
        $this->tokenHelper = $tokenHelper;
        $this->test = $test;
        $this->updatePayload = $updatePayload;
    }

    /**
     * Run this single API Operation.
     *
     * @return $this|bool
     */
    public function run()
    {
        if (empty($this->request)) {
            $this->logs[] = 'Skipping empty operation: '.$this->name;

            return true;
        }
        $uri = $this->request->url ?? null;
        if ($this->test && !empty($this->request->testUrl)) {
            $uri = $this->request->testUrl;
        }
        if (!$uri) {
            $this->logs[] = 'Operation skipped. No URL: '.$this->name;

            return true;
        }

        $this->logs[] = 'Running operation in '.($this->test ? 'TEST' : 'PROD').' mode: '.$this->name;

        // Send the API request.
        $apiRequest = new ApiRequest($uri, $this->request, $this->service, $this->tokenHelper, $this->test);
        $apiRequest->send();
        $this->logs['request'] = $apiRequest->getLogs();

        // Parse the API response.
        $apiResponse = new ApiResponse($this->responseExpected, $this->successDefinition, $this->service, $this->test);
        $this->responseActual = $apiResponse->parse()->getResponse();
        $this->logs['response'] = $apiResponse->getLogs();

        // Validate the API response with the given success definition.
        try {
            $this->valid = $apiResponse->validate();
        } catch (ApiErrorException $e) {
            $this->logs['response'][] = $e->getMessage();
            $this->valid = false;
        }

        // @todo Update our Contact with the relevant field mapping. (to be handled in ClientIntegration)
        // @todo - $this->getResponseUpdated();

        if ($this->updatePayload) {
            $this->updatePayloadResponse();
        }

        return $this;
    }

    /**
     * Automatically updates a response with filled in data from the parsed response object from the API.
     */
    public function updatePayloadResponse()
    {
        $result = $this->responseExpected;
        $updates = false;
        foreach (['headers', 'body'] as $type) {
            if (isset($this->responseActual[$type])) {
                $fieldKeys = [];
                foreach ($result->{$type} as $id => $value) {
                    if (!empty($value->key) && is_numeric($id)) {
                        $fieldKeys[$value->key] = intval($id);
                    }
                }
                foreach ($this->responseActual[$type] as $key => $value) {
                    // Check if this type of response was not expected (headers / body).
                    if (!isset($result->{$type})) {
                        $result->{$type} = [];
                    }
                    // Check if header/body field is unexpected.
                    $fieldId = $fieldKeys[$key] ?? -1;
                    if ($fieldId == -1) {
                        // This is a new field.
                        $newField = new stdClass();
                        $newField->key = $key;
                        $newField->example = $value;
                        $result->{$type}[] = $newField;
                        $this->logs[] = 'New '.$type.' field "'.$key.'" added with example: '.$value;
                        $updates = true;
                    } else {
                        if (!empty($value) && empty($result->{$type}[$fieldId]->example)) {
                            // This is an existing field, but requires an updated example.
                            $result->{$type}[$fieldId]->example = $value;
                            $updates = true;
                            $this->logs[] = 'Existing '.$type.' field "'.$key.'" now has an example: '.$value;
                        }
                    }
                }
            }
        }
        if ($updates) {
            $this->operation->response = $result;
        }
    }

    public function getValid()
    {
        return $this->valid;
    }

    public function getLogs()
    {
        return $this->logs;
    }

    public function getResponseActual()
    {
        return $this->responseActual;
    }

    /**
     * Get filled responses that have Contact destinations defined.
     * @return array
     */
    public function getResponseMap()
    {
        $mappedFields = [];
        foreach (['headers', 'body'] as $type) {
            if (isset($this->responseExpected->{$type}) && isset($this->responseActual[$type])) {
                foreach ($this->responseExpected->{$type} as $value) {
                    if (!empty($value->destination) && !empty($value->key) && !empty($this->responseActual[$type][$value->key])) {
                        // We have a non-empty value with a mapped destination.
                        $mappedFields[$value->destination] = $this->responseActual[$type][$value->key];
                    }
                }
            }
        }

        return $mappedFields;
    }

}