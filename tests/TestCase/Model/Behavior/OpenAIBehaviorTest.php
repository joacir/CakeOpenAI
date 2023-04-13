<?php
declare(strict_types=1);

namespace CakeOpenAI\Test\TestCase\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;
use CakeOpenAI\Model\Behavior\OpenAIBehavior;

/**
 * CakeOpenAI\Model\Behavior\OpenAIBehavior Test Case
 */
class OpenAIBehaviorTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \CakeOpenAI\Model\Behavior\OpenAIBehavior
     */
    protected $OpenAI;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Configure::write('OpenAI', [
            'apiKey' => '1f8-089vsvadasdvasdvsa',
            'organizationID' => 'org-v56a4s6v4a6sd5v46a5sdv65',
        ]);
        $table = new Table();
        $this->OpenAI = new OpenAIBehavior($table);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->OpenAI);

        parent::tearDown();
    }

    public function testInstance(): void
    {
        $this->assertInstanceOf('CakeOpenAI\Model\Behavior\OpenAIBehavior', $this->OpenAI);
    }

    public function testSettersAndGetters(): void
    {
        $config = [
            'model' => 'gpt-4-turbo',
            'temperature' => 2.0,
            'max_tokens' => 1000,
            'frequency_penalty' => 1,
            'presence_penalty' => 3,
        ];
        $this->OpenAI->setChatConfig($config);
        $this->assertEquals($config, $this->OpenAI->getChatConfig());

        $config = [
            'n' => 2,
            'size' => '512x512',
            'response_format' => 'b64_json',
        ];
        $this->OpenAI->setImageConfig($config);
        $this->assertEquals($config, $this->OpenAI->getImageConfig());
    }

    public function testChat(): void
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a helpful assistant.',
            ],
            [
                'role' => 'user',
                'content' => 'Who won the world series in 2020?',
            ],
            [
                'role' => 'assistant',
                'content' => 'The Los Angeles Dodgers won the World Series in 2020.',
            ],
            [
                'role' => 'user',
                'content' => 'Where was it played?',
            ],
        ];

        $this->OpenAI->openAI = $this->getMockBuilder('\Orhanerday\OpenAi\OpenAi')
            ->disableOriginalConstructor()
            ->getMock();
        $this->OpenAI->openAI
            ->expects($this->once())
            ->method('chat')
            ->willReturn(json_encode([
                'id' => 'chatcmpl-74UwritbPzMa6dQqpmDIqX3lnMPqU',
                'object' => 'chat.completion',
                'created' => 1681306633,
                'model' => 'gpt-3.5-turbo-0301',
                'usage' => [
                    'prompt_tokens' => 57,
                    'completion_tokens' => 17,
                    'total_tokens' => 74,
                ],
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'The 2020 World Series was played at Globe Life Field in Arlington, Texas.',
                        ],
                        'finish_reason' => 'stop',
                        'index' => 0,
                    ],
                ],
            ]));

        $responses = $this->OpenAI->chat($messages);

        $this->assertEquals(
            'The 2020 World Series was played at Globe Life Field in Arlington, Texas.',
            $responses['choices'][0]['message']['content']
        );
    }

    public function testImage(): void
    {
        $prompt = 'Darth Vader riding on a bike.';

        $this->OpenAI->openAI = $this->getMockBuilder('\Orhanerday\OpenAi\OpenAi')
            ->disableOriginalConstructor()
            ->getMock();
        $this->OpenAI->openAI
            ->expects($this->once())
            ->method('image')
            ->willReturn(json_encode([
                'created' => 1681309882,
                'data' => [
                    ['url' => 'https://oaidalleapiprodscus.blob'],
                ],
            ]));

        $responses = $this->OpenAI->image($prompt);

        $this->assertNotEmpty($responses['data'][0]['url']);
    }
}
