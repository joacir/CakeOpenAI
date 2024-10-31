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
            /**
             * PUT CREDENTIALS HERE
             */
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

    public function testCreateThread(): void
    {
        $messages = [
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Hello, what is AI?',
                    'file_ids' => [],
                ],
            ],
        ];

        $this->OpenAI->openAI = $this->getMockBuilder('\Orhanerday\OpenAi\OpenAi')
            ->disableOriginalConstructor()
            ->getMock();
        $this->OpenAI->openAI
            ->expects($this->once())
            ->method('createThread')
            ->willReturn(json_encode([
                'id' => 'thread_Vnh93IHmBaJ4TLNCIjT1hleh',
                'object' => 'thread',
                'created_at' => 1729195060,
                'metadata' => [],
            ]));

        $responses = $this->OpenAI->createThread($messages);

        $this->assertEquals('thread_Vnh93IHmBaJ4TLNCIjT1hleh', $responses['id']);
    }

    public function testCreateThreadMessage(): void
    {
        $threadId = 'thread_Vnh93IHmBaJ4TLNCIjT1hleh';
        $message = [
            'role' => 'user',
            'content' => 'How does AI work? Explain it in simple terms.',
        ];

        $this->OpenAI->openAI = $this->getMockBuilder('\Orhanerday\OpenAi\OpenAi')
            ->disableOriginalConstructor()
            ->getMock();
        $this->OpenAI->openAI
            ->expects($this->once())
            ->method('createThreadMessage')
            ->willReturn(json_encode([
                'id' => 'msg_5PFLCttYzxUUwdC6LHhQkl9L',
                'object' => 'thread.message',
                'created_at' => 1729195577,
                'assistant_id' => null,
                'thread_id' => 'thread_Vnh93IHmBaJ4TLNCIjT1hleh',
                'run_id' => null,
                'role' => 'user',
                'content' => [
                  [
                    'type' => 'text',
                    'text' => [
                      'value' => 'How does AI work? Explain it in simple terms.',
                      'annotations' => []
                    ]
                  ]
                ],
                'file_ids' => [],
                'metadata' => [],
            ]));

        $responses = $this->OpenAI->createThreadMessage($threadId, $message);

        $this->assertEquals('msg_5PFLCttYzxUUwdC6LHhQkl9L', $responses['id']);
    }

    public function testCreateThreadAndRun(): void
    {
        $data = [
            'assistant_id' => 'asst_NKNVahV0IzggZWSSKEr3XFhh',
            'thread' => [
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => '1 Software supimpa por R$ 20.000,00 para o José da Esquina',
                    ],
                ],
            ],
        ];

        $this->OpenAI->openAI = $this->getMockBuilder('\Orhanerday\OpenAi\OpenAi')
            ->disableOriginalConstructor()
            ->getMock();
        $this->OpenAI->openAI
            ->expects($this->once())
            ->method('createThreadAndRun')
            ->willReturn(json_encode([
                'id' => 'run_JMvrnuDvdYiUdB4mfKXN0Nsd',
                'object' => 'thread.run',
                'created_at' => 1729198026,
                'assistant_id' => 'asst_NKNVahV0IzggZWSSKEr3XFhh',
                'thread_id' => 'thread_YFODhuL0VzQRohgxQrILotss',
                'status' => 'queued',
                'started_at' => null,
                'expires_at' => 1729198626,
                'cancelled_at' => null,
                'failed_at' => null,
                'completed_at' => null,
                'required_action' => null,
                'last_error' => null,
                'model' => 'gpt-4o-mini',
                'instructions' => 'Agir como se fosse um assistente gerador de orçamento comercial.',
                'tools' => [],
                'tool_resources' => [],
                'metadata' => [],
                'temperature' => 1,
                'top_p' => 1,
                'max_completion_tokens' => null,
                'max_prompt_tokens' => null,
                'truncation_strategy' => [
                    'type' => 'auto',
                    'last_messages' => null,
                ],
                'incomplete_details' => null,
                'usage' => null,
                'response_format' => 'auto',
                'tool_choice' => 'auto',
                'parallel_tool_calls' => true,
            ]));
        $this->OpenAI->openAI
            ->expects($this->once())
            ->method('retrieveRun')
            ->willReturn(json_encode([
                'id' => 'run_JMvrnuDvdYiUdB4mfKXN0Nsd',
                'object' => 'thread.run',
                'created_at' => 1729198026,
                'assistant_id' => 'asst_NKNVahV0IzggZWSSKEr3XFhh',
                'thread_id' => 'thread_YFODhuL0VzQRohgxQrILotss',
                'status' => 'completed',
                'started_at' => null,
                'expires_at' => 1729198626,
                'cancelled_at' => null,
                'failed_at' => null,
                'completed_at' => 1729513021,
                'required_action' => null,
                'last_error' => null,
                'model' => 'gpt-4o-mini',
                'instructions' => 'Agir como se fosse um assistente gerador de orçamento comercial.',
                'tools' => [],
                'tool_resources' => [],
                'metadata' => [],
                'temperature' => 1,
                'top_p' => 1,
                'max_completion_tokens' => null,
                'max_prompt_tokens' => null,
                'truncation_strategy' => [
                    'type' => 'auto',
                    'last_messages' => null,
                ],
                'incomplete_details' => null,
                'usage' => null,
                'response_format' => 'auto',
                'tool_choice' => 'auto',
                'parallel_tool_calls' => true,
            ]));
        $this->OpenAI->openAI
            ->expects($this->once())
            ->method('listThreadMessages')
            ->willReturn(json_encode([
                'object' => 'list',
                'data' => [
                    [
                        'id' => 'msg_2fM7yiAGTU6R6bWtr87nriZy',
                        'object' => 'thread.message',
                        'created_at' => 1729513020,
                        'assistant_id' => 'asst_NKNVahV0IzggZWSSKEr3XFhh',
                        'thread_id' => 'thread_okFEvZppFKNQlnAdxj1sALyd',
                        'run_id' => 'run_C8MMMxkRGoOf63DN6RHTdm4j',
                        'role' => 'assistant',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => [
                                    'value' => '{"cliente":{"nome": "José da Esquina"},"orcamento_items":[{"produto":{"descricao":"Software supimpa"},"quantidade":1,"preco_unitario": 20000,"valor_total":20000}]}',
                                    'annotations' => [],
                                ],
                            ],
                        ],
                        'attachments' => [],
                        'metadata' => []
                    ],
                    [
                        'id' => 'msg_Xjr4yxAl2R0LgLMsxEbB3n2d',
                        'object' => 'thread.message',
                        'created_at' => 1729513019,
                        'assistant_id' => null,
                        'thread_id' => 'thread_okFEvZppFKNQlnAdxj1sALyd',
                        'run_id' => null,
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => [
                                    'value' => '1 Software supimpa por R$ 20.000,00 para o José da Esquina',
                                    'annotations' => [],
                                ],
                            ],
                        ],
                        'attachments' => [],
                        'metadata' => []
                    ]
                ],
                'first_id' => 'msg_2fM7yiAGTU6R6bWtr87nriZy',
                'last_id' => 'msg_Xjr4yxAl2R0LgLMsxEbB3n2d',
                'has_more' => false
            ]));

        $responses = $this->OpenAI->createThreadAndRun($data);

        $expected = '{"cliente":{"nome": "José da Esquina"},"orcamento_items":[{"produto":{"descricao":"Software supimpa"},"quantidade":1,"preco_unitario": 20000,"valor_total":20000}]}';
        $this->assertEquals($expected, $responses['data'][0]['content'][0]['text']['value']);
    }

}
