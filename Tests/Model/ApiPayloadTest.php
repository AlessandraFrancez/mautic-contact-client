<?php

namespace MauticPlugin\MauticContactClientBundle\Tests\Model;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mautic\CoreBundle\Test\MauticMysqlTestCase;
use MauticPlugin\MauticContactClientBundle\Entity\ContactClient;
use MauticPlugin\MauticContactClientBundle\Services\Transport;

class ApiPayloadTest extends MauticMysqlTestCase
{
    /** @test */
    public function it_tokenizes_a_post_url_in_test_mode()
    {
        $container = [];
        $history   = Middleware::history($container);
        $stack     = HandlerStack::create(new MockHandler([new Response(200, ['X-Foo' => 'Bar'])]));
        // Add the history middleware to the handler stack.
        $stack->push($history);
        $this->container->set('mautic.contactclient.service.transport', new Transport(new Client(), $stack));

        $apiPayload = $this->container->get('mautic.contactclient.model.apipayload');

        $client = new ContactClient();
        $client->setType('api');
        $payload = $this->getPayload();
        $client->setAPIPayload($payload);

        $apiPayload->setTest(true)
                   ->setContactClient($client);

        $apiPayload->run();

        foreach ($container as $item) {
            /** @var Request $req */
            $req = $item['request'];
            $q   = $req->getUri()->getQuery();
            $this->assertEquals('subid=&addr=1234%20Test%20St.&ad1=1234%20Test%20St.', $q);
        }
    }

    private function getPayload()
    {
        $dir     = __DIR__.'/../assets/model/api_payload.json';
        $payload = file_get_contents($dir);

        return $payload;
    }
}
