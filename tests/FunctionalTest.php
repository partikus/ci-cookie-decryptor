<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class FunctionalTest extends WebTestCase
{
    /** @var \Symfony\Bundle\FrameworkBundle\Client */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * @test
     * @dataProvider useCases
     */
    public function it_should_decode_ci_cookie($expectedData, $config, $cookie)
    {
        $this->client->request(Request::METHOD_POST, '/decode', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'cookie' => $cookie,
            'config' => $config,
        ]));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(json_encode($expectedData), $this->client->getResponse()->getContent());
    }

    /**
     * @test
     * @dataProvider useCases
     */
    public function it_should_encode_ci_cookie($givenData, $config, $expected)
    {
        $this->client->request(Request::METHOD_POST, '/encode', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'data' => $givenData,
            'config' => $config,
        ]));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request(Request::METHOD_POST, '/decode', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'cookie' => $this->client->getResponse()->getContent(),
            'config' => $config,
        ]));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(json_encode($givenData), $this->client->getResponse()->getContent());
    }

    public function useCases()
    {
        return [
            'with encryption' => [
                'decrypted' => [
                    'session_id' => '675102f3f153887c23bd727a5261d553514da9ad',
                    'ip_address' => '10.0.0.1',
                    'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36',
                    'last_activity' => 1521925829,
                    'user_data' => '',
                    'u_id' => '1',
                    'u_role' => 'SA',
                    'u_email' => 'admin@example.com',
                    'u_name' => 'admin',
                    'u_language' => 'english',
                    'u_is_admin' => true,
                ],
                'config' => [
                    'sess_encrypt_cookie' => true,
                    'sess_cookie_name' => 'ci_session',
                    'encryption_key' => '4f9783710d943f71c4dad88356829c8b64ab5dbb'
                ],
                'encrypted' => '0gr4w1NdTRrOFnU7pPJs2zuhtpgfYgUmMrCdmXPZ1En+c35x9aBEqzMf/pAHAX6vWw1Eq31PhR0t4xAfKBOzmKk413z/DYDLnaHaWT5ahfynb53cQpL8z6uwqlODvEXyq3CF8SBkskPZ/gzhFP+7Ob+1opeLcVDHnHsBeOemdiGYGCnGWh6Zs7S63K2ZuwY0Qcmx9uI5Ea5rsmKwaFfsacLFD/cDYP5wQN7yy2kYqvni8n7bbkJjf5/4WPFC+KjbJHzGilnrlbwFQiyVdesGkdvHGZo2qscSDDZ7kyUuQKQt3DwH8H9W4u+R6fX5diLIVeuj25lMXjIfd3UJ4N+Uq9+Czafogz5NCNXJlSTyV5Eg/CQeIlWyT4Crta3aKQcv8o57vomJORU4ZWZDzl3cyv7Pia7ICY15SOWAtKzaHsAkgUcBhJremm/2qPF0St5OppBOtsxJRtINzF6OfCFuBH7lgF/GygK0YMPqMN9DfZFhiprBdi1iHZa4MuV6q3HCzN91zKtNIixxGudsyy+pPMtXKJjWeRxCjRcMopHCNXTAuogzbTho9jXm64UVgCKiz4JO8tSbVRfJ04LkhPs5XciNC4rQqXRP8V4bPZTn89ArpfoOBhXXYLlBLX3QLlZORqbvW6qxb/R+dRbiqXPSacP01INfUTehuxmgm6Z0Rsw='
            ],
            'without encryption' => [
                'decrypted' => [
                    'session_id' => '8cab75e68bdea2c04b9fb1c2312ed2ae697ff733',
                    'ip_address' => '10.0.0.1',
                    'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36',
                    'last_activity' => 1521928543,
                    'user_data' => '',
                    'u_id' => '1',
                    'u_role' => 'SA',
                    'u_email' => 'admin@example.com',
                    'u_name' => 'admin',
                    'u_language' => 'english',
                    'u_is_admin' => true,
                ],
                'config' => [
                    'sess_cookie_name' => 'ci_session',
                    'encryption_key' => '4f9783710d943f71c4dad88356829c8b64ab5dbb'
                ],
                'encrypted' => 'a:11:{s:10:"session_id";s:40:"8cab75e68bdea2c04b9fb1c2312ed2ae697ff733";s:10:"ip_address";s:8:"10.0.0.1";s:10:"user_agent";s:105:"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36";s:13:"last_activity";i:1521928543;s:9:"user_data";s:0:"";s:4:"u_id";s:1:"1";s:6:"u_role";s:2:"SA";s:7:"u_email";s:17:"admin@example.com";s:6:"u_name";s:5:"admin";s:10:"u_language";s:7:"english";s:10:"u_is_admin";b:1;}51f10e1d0a6f360b23eed55b1eb43fec'
            ]
        ];
    }
}
