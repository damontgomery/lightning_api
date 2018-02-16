<?php

namespace Drupal\Tests\api_test\Functional;

use Drupal\user\Entity\User;
use Drupal\consumers\Entity\Consumer;

/**
 * Tests the ability to Create, Read, and Update config and config entities via
 * the API.
 *
 * @group lightning
 * @group lightning_api
 * @group headless
 * @group api_test
 */
class EntityCrudTest extends ApiTestBase {

  /**
   * OAuth token for the admin client.
   *
   * @var string
   */
  private $token;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create an admin user that has permission to do everything for testing.
    $edit = [
      'name' => 'api-admin-user',
      'mail' => 'api-admin-user@example.com',
      'pass' => 'admin',
    ];

    $account = User::create($edit);
    $account->addRole('administrator');
    $account->activate();
    $account->save();
    $api_admin_user_id = $account->id();

    // Create an associated OAuth client to use for testing.
    $data = [
      'uuid' => 'api_test-admin-oauth2-client',
      'label' => 'API Test Admin Client',
      'secret' => 'oursecret',
      'confidential' => 1,
      'user_id' => $api_admin_user_id,
      'roles' => 'administrator',
    ];

    $client = Consumer::create($data);
    $client->save();

    // Retrieve and store a token to use in the requests.
    $admin_client_options = [
      'form_params' => [
        'grant_type' => 'password',
        'client_id' => 'api_test-admin-oauth2-client',
        'client_secret' => 'oursecret',
        'username' => 'api-admin-user',
        'password' => 'admin',
      ],
    ];
    $this->token = $this->getToken($admin_client_options);
  }

  /**
   * Tests create, read, and update of content and config entities via the
   * API.
   */
  public function testEntities() {
    $name = 'I\'m a vocab';
    $data = [
      'data' => [
        'type' => 'taxonomy_vocabulary--taxonomy_vocabulary',
        'id' => 'taxonomy_test_vocabulary',
        'attributes' => [
          'uuid' => 'taxonomy_test_vocabulary',
          'name' => $name,
          'vid' => 'im_a_vocab',
          'status' => TRUE,
        ]
      ]
    ];

    // Create a taxonomy vocabulary (config entity).
    $this->request('/jsonapi/taxonomy_vocabulary/taxonomy_vocabulary', 'post', $this->token, $data);

    // Read the newly created vocabulary.
    $response = $this->request('/jsonapi/taxonomy_vocabulary/taxonomy_vocabulary/taxonomy_test_vocabulary', 'get', $this->token);
    $body = $this->decodeResponse($response);
    $this->assertEquals($name, $body['data']['attributes']['name']);

    $new_name = 'Still a vocab, just different title';
    $data = [
      'data' => [
        'type' => 'taxonomy_vocabulary--taxonomy_vocabulary',
        'id' => 'taxonomy_test_vocabulary',
        'attributes' => [
          'name' => $new_name,
        ]
      ]
    ];

    // Update the vocabulary.
    $this->request('/jsonapi/taxonomy_vocabulary/taxonomy_vocabulary/taxonomy_test_vocabulary', 'patch', $this->token, $data);

    // Read the updated vocabulary.
    $response = $this->request('/jsonapi/taxonomy_vocabulary/taxonomy_vocabulary/taxonomy_test_vocabulary', 'get', $this->token);
    $body = $this->decodeResponse($response);
    $this->assertEquals($new_name, $body['data']['attributes']['name']);

    // Assert that the newly created vocabulary's endpoint is reachable.
    // @todo figure out why we need to rebuild caches for it to be available.
    drupal_flush_all_caches();
    $response = $this->request('/jsonapi/taxonomy_term/im_a_vocab');
    $this->assertEquals(200, $response->getStatusCode());

    $name = 'zebra';
    $data = [
      'data' => [
        'type' => 'taxonomy_term--im_a_vocab',
        'id' => 'zebra_taxonomy_term',
        'attributes' => [
          'name' => $name,
          'uuid' => 'zebra_taxonomy_term',
        ],
        'relationships' => [
          'vid' => [
            'data' => [
              'type' => 'taxonomy_vocabulary--taxonomy_vocabulary',
              'id' => 'taxonomy_test_vocabulary',
            ]
          ]
        ]
      ]
    ];

    // Create a taxonomy term (content entity).
    $this->request('/jsonapi/taxonomy_term/im_a_vocab', 'post', $this->token, $data);

    // Read the taxonomy term.
    $response = $this->request('/jsonapi/taxonomy_term/im_a_vocab/zebra_taxonomy_term', 'get', $this->token);
    $body = $this->decodeResponse($response);
    $this->assertEquals($name, $body['data']['attributes']['name']);

    $new_name = 'squid';
    $data = [
      'data' => [
        'type' => 'taxonomy_term--im_a_vocab',
        'id' => 'zebra_taxonomy_term',
        'attributes' => [
          'name' => $new_name,
        ]
      ]
    ];

    // Update the taxonomy term.
    $this->request('/jsonapi/taxonomy_term/im_a_vocab/zebra_taxonomy_term', 'patch', $this->token, $data);

    // Read the updated taxonomy term.
    $response = $this->request('/jsonapi/taxonomy_term/im_a_vocab/zebra_taxonomy_term', 'get', $this->token);
    $body = $this->decodeResponse($response);
    $this->assertSame($new_name, $body['data']['attributes']['name']);
  }

}