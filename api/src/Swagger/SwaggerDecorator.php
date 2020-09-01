<?php
// api/src/Swagger/SwaggerDecorator.php

namespace App\Swagger;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * This class documents further endpoints that are not CRUD-related.
 */
final class SwaggerDecorator implements NormalizerInterface
{
    private $decorated;

    public function __construct(NormalizerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        /** @var \ArrayObject $docs */
        $docs = $this->decorated->normalize($object, $format, $context);
        $paths =& $docs['paths'];
        $paths['/api/users/{id}']['delete']['summary'] = "Deletes the user along with all of his or her games.";

        $paths['/login']['post']['summary'] = 'API endpoint to sign users in.';
        $paths['/login']['post']['requestBody'] = [
            'required' => true,
            'content' => [
                'application/json' => ['schema' => [
                    'type' => 'object',
                    'properties' => [
                        'email' => [
                            'type' => 'string',
                            'example' => 'admin@eos-uptrade.de',
                            'description' => 'The email address of the user to log in.'
                        ],
                        'password' => ['type' => 'string', 'example' => 'demo']
                    ],
                    'required' => ['email', 'password']]]
            ]
        ];


        $paths['/api/game/{id}/shoot']['post']['summary'] =
            'API endpoint for the player to shoot.
             This will also cause the computer player to shoot.
             The returned game will contain updated oceans.';
        $paths['/api/game/{id}/shoot']['post']['requestBody'] = [
            'required' => true,
            'content' => [
                'application/json' => ['schema' => [
                    'type' => 'object',
                    'properties' => [
                        'target' => [
                            'type' => 'string',
                            'example' => 'D4',
                            'description' => 'The coordinates of the target.'
                        ],
                    ],
                    'required' => ['target']]]
            ]
        ];
        $paths['/api/game/{id}/shoot']['post']['responses']['200'] = [
            'description' => 'Result of the shot.',
            'content' => [
                'application/json' => [
                    'schema' =>
                        [
                            'type' => 'object',
                            'properties' => [
                                'result' => [
                                    'type' => 'string',
                                    'enum' => ['Miss.', 'Hit. Carrier.', 'Hit. Battleship.', 'Hit. Cruiser.', 'Hit. Submarine.', 'Hit. Destroyer.']
                                ]
                            ]
                        ]
                ]]
        ];
        unset($paths['/api/game/{id}/shoot']['post']['responses']['201']);
        $paths['/login']['post']['responses'] = [
            '200' => ['description' => 'Success'],
            '403' => ['description' => 'Failure'],
        ];


        return $docs;
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }
}
