<?php
namespace Yo1L\LaravelTypeForm\Test;

use Yo1L\LaravelTypeForm\TypeForm;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

class TypeFormTest extends TestCase
{
    protected $mockHandler;
    protected $typeForm;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockHandler = new MockHandler();
        $this->typeForm = new TypeForm($this->mockHandler);
    }

    protected function setResponse($status, $contents)
    {
        $this->mockHandler->append(new Response($status, ['Content-Type' => 'application/json'], $contents));
    }

    public function testGetForms()
    {
        $this->setResponse(200, TypeFormResponses::retrieveForms());

        $result = $this->typeForm->getForms();
        $this->assertTrue(isset($result['total_items']));
    }

    public function testPayloadValidation()
    {
        $this->createRequest(TypeFormResponses::webhookCall());
    }
}
