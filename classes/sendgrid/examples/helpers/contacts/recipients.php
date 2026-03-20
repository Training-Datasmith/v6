<?php

declare (strict_types=1);
// Next line will load dependencies to run this example
// Please refer to the README how to use in your project
require_once __DIR__ . '/../../../sendgrid-php.php';
// This will build an HTML form to be embedded in your page.
// This form allows users to subscribe using their name and email.
function build_recipient_form($url = 'http://www.example.com/recipientFormSubmit'): void
{
    $form = (string) new \Send_Grid\Contacts\Recipient_Form($url);
    echo $form . PHP_EOL;
}
// This will accept a form submission from the above form. Will create a new Recipient,
// adding them to "contactdb". Note, it does not add the recipient to any list.
function recipient_form_submit(): void
{
    $api_key = getenv('SENDGRID_API_KEY');
    $sg = new \Send_Grid($api_key);
    // These should be retrieved from $_POST
    $post_body = ['first-name' => 'Test', 'last-name' => 'Tester', 'email' => 'test@test.com'];
    $first_name = $post_body['first-name'];
    $last_name = $post_body['last-name'];
    $email = $post_body['email'];
    $recipient = new \Send_Grid\Contacts\Recipient($first_name, $last_name, $email);
    // $request_body = json_encode(array($recipient));
    $request_body = json_decode('[
        {
            "email": "' . $recipient->get_email() . '",
            "first_name": "' . $recipient->get_first_name() . '",
            "last_name": "' . $recipient->get_last_name() . '"
        }
    ]');
    try {
        $response = $sg->client->contactdb()->recipients()->post($request_body);
        print $response->status_code() . "\n";
        print_r($response->headers());
        print $response->body() . "\n";
    } catch (Exception $e) {
        echo 'Caught exception: ', $e->get_message(), "\n";
    }
}
build_recipient_form();
// This will build and output an HTML form
recipient_form_submit();
// This will simulate a form submission and will output the response.