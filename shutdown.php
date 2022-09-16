<?php
// iunclude this file at earliest in your code
// define your url constant elsewhere: SECRET_SLACK_WEBHOOK_URL

if ( !defined( 'SECRET_SLACK_WEBHOOK_URL' ) ) {
    return;
}

register_shutdown_function( 'wpyra_fatal_handler' );

function wpyra_fatal_handler() {
    $error_types = [
        E_ERROR,
        E_WARNING,
        // E_NOTICE,
        E_PARSE,
        E_CORE_ERROR,
        E_COMPILE_ERROR,
        E_RECOVERABLE_ERROR
    ];

    $error_names = [
        E_ERROR                 => 'E_ERROR',
        E_WARNING               => 'E_WARNING',
        E_NOTICE                => 'E_NOTICE',
        E_PARSE                 => 'E_PARSE',
        E_CORE_ERROR            => 'E_CORE_ERROR',
        E_COMPILE_ERROR         => 'E_COMPILE_ERROR',
        E_RECOVERABLE_ERROR     => 'E_RECOVERABLE_ERROR'
    ];

    $error = error_get_last();

    if ( $error != NULL && in_array( $error['type'], $error_types ) ) {
        $ch = curl_init( SECRET_SLACK_WEBHOOK_URL );
        $payload = json_encode( [
            'blocks' => [
                [
                    'type'  => 'section',
                    'text'      => [
                        'type'      => 'mrkdwn',
                        'text'      => '--------------------------------------------------------------' . PHP_EOL . 
                                        date( 'Y-m-d H:i:s' ) . PHP_EOL . 
                                        '*' . $error_names[$error['type']] . '*' . PHP_EOL . 
                                        '`' . $error['file'] . '`' . PHP_EOL . 
                                        'on line ' . '_' . $error['line'] . '_' . PHP_EOL . 
                                        $error['message'],
                    ],
                ],
            ],
        ] );

        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type:application/json' ) );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );

        $result = curl_exec( $ch );

        curl_close( $ch );
    }
}